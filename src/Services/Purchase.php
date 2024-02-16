<?php

declare(strict_types=1);

namespace App\Services;

use DateTime;
use App\Utils\Tools;
use App\Models\Config;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\UserMoneyLog;
use App\Models\Invoice;
use RateLimit\Exception\LimitExceeded;
use RateLimit\Rate;
use RateLimit\RedisRateLimiter;
use RedisException;

final class Purchase
{
    public static function createOrder($product_id, $user)
    {
        $product = (new Product())->find($product_id);

        if ($product === null || $product->stock === 0) {
            return false;
        }

        $buy_price = $product->price;

        if ($user->is_shadow_banned) {
            return false;
        }

        $product_limit = json_decode($product->limit);

        if ($product_limit->class_required !== '' && $user->class < (int) $product_limit->class_required) {
            return false;
        }

        if (
            $product_limit->node_group_required !== ''
            && $user->node_group !== (int) $product_limit->node_group_required
        ) {
            return false;
        }

        if ($product_limit->new_user_required !== 0) {
            $order_count = (new Order())->where('user_id', $user->id)->count();
            if ($order_count > 0) {
                return false;
            }
        }

        $order = new Order();
        $order->user_id = $user->id;
        $order->product_id = $product->id;
        $order->product_type = $product->type;
        $order->product_name = $product->name;
        $order->product_content = $product->content;
        $order->coupon = '';
        $order->price = $buy_price;
        $order->status = 'pending_payment';
        $order->create_time = time();
        $order->update_time = time();
        $order->save();

        $invoice_content = [];

        $invoice_content[] = [
            'content_id' => 0,
            'name' => $product->name,
            'price' => $product->price,
        ];


        $invoice = new Invoice();
        $invoice->user_id = $user->id;
        $invoice->order_id = $order->id;
        $invoice->content = json_encode($invoice_content);
        $invoice->price = $buy_price;
        $invoice->status = 'unpaid';
        $invoice->create_time = time();
        $invoice->update_time = time();
        $invoice->pay_time = 0;
        $invoice->save();

        if ($product->stock > 0) {
            $product->stock -= 1;
        }
        $product->sale_count += 1;
        $product->save();

        return $invoice->id;
    }

    public static function purchaseWithBalance($invoice_id, $user)
    {
        $invoice = (new Invoice())->where('user_id', $user->id)->where('id', $invoice_id)->first();

        if ($invoice === null) {
            return false;
        }

        if ($user->is_shadow_banned) {
            return false;
        }

        if ($user->money < $invoice->price) {
            return false;
        }

        $money_before = $user->money;
        $user->money -= $invoice->price;
        $user->save();

        (new UserMoneyLog())->add(
            $user->id,
            $money_before,
            (float) $user->money,
            -$invoice->price,
            '支付账单 #' . $invoice->id,
            "order_payment"
        );

        $invoice->status = 'paid_balance';
        $invoice->update_time = time();
        $invoice->pay_time = time();
        $invoice->save();

        Purchase::processPendingOrder($user);
        Purchase::processTabpOrderActivation($user);

        return true;
    }

    public static function processPendingOrder($user): void
    {
        $pending_payment_orders = (new Order())->where('user_id', $user->id)->where('status', 'pending_payment')->get();

        foreach ($pending_payment_orders as $order) {
            // 检查账单支付状态
            $invoice = (new Invoice())->where('order_id', $order->id)->first();

            if ($invoice === null) {
                continue;
            }
            // 标记订单为等待激活
            if (in_array($invoice->status, ['paid_gateway', 'paid_balance', 'paid_admin'])) {
                $order->status = 'pending_activation';
                $order->update_time = time();
                $order->save();

                continue;
            }
            // 取消超时未支付的订单和关联账单
            if ($order->create_time + 86400 < time()) {
                $order->status = 'cancelled';
                $order->update_time = time();
                $order->save();

                $invoice->status = 'cancelled';
                $invoice->update_time = time();
                $invoice->save();

            }
        }
    }

    public static function processTabpOrderActivation($user): void
    {
        $user_id = $user->id;
        // 获取用户账户等待激活的TABP订单
        $pending_activation_orders = (new Order())->where('user_id', $user_id)
            ->where('status', 'pending_activation')
            ->where('product_type', 'tabp')
            ->orderBy('id')
            ->get();
        // 获取用户账户已激活的TABP订单，一个用户同时只能有一个已激活的TABP订单
        $activated_order = (new Order())->where('user_id', $user_id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->orderBy('id')
            ->first();
        // 如果用户账户中没有已激活的TABP订单，且有等待激活的TABP订单，则激活最早的等待激活TABP订单
        if ($activated_order === null && count($pending_activation_orders) > 0) {
            $order = $pending_activation_orders[0];
            // 获取TABP订单内容准备激活
            $content = json_decode($order->product_content);
            // 激活TABP
            $user->u = 0;
            $user->d = 0;
            $user->transfer_today = 0;
            $user->transfer_enable = Tools::toGB($content->bandwidth);
            $user->class = $content->class;
            $old_class_expire = new DateTime();
            $user->class_expire = $old_class_expire
                ->modify('+' . $content->class_time . ' days')->format('Y-m-d H:i:s');
            $user->node_group = $content->node_group;
            $user->node_speedlimit = $content->speed_limit;
            $user->node_iplimit = $content->ip_limit;
            if ($user->plan_start_date === null) {
                $user->plan_start_date = new DateTime();
            }
            $user->save();
            $order->status = 'activated';
            $order->update_time = time();
            $order->save();
        }
        // 如果用户账户中有已激活的TABP订单，则判断是否过期
        if ($activated_order !== null) {
            $content = json_decode($activated_order->product_content);

            if ($activated_order->update_time + $content->time * 86400 < time()) {
                $activated_order->status = 'expired';
                $activated_order->update_time = time();
                $activated_order->save();
            }
        }
    }
}
