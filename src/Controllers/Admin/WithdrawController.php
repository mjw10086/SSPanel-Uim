<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Withdraw;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function in_array;
use function json_decode;
use function time;

final class WithdrawController extends BaseController
{
    private static array $details = [
        'field' => [
            'op' => '操作',
            'id' => '提款ID',
            'uuid' => '提款标识',
            'user_id' => '提款用户',
            'amount' => '金额',
            'status' => '提款状态',
            'create_time' => '创建时间',
            'update_time' => '更新时间',
            'message' => '备注',
        ],
    ];

    /**
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        return $response->write(
            $this->view()
                ->assign('details', self::$details)
                ->fetch('admin/withdraw/index.tpl')
        );
    }

    public function reject(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $order_id = $args['id'];
        (new Order())->find($order_id)->delete();
        (new Invoice())->where('order_id', $order_id)->first()->delete();

        return $response->withJson([
            'ret' => 1,
            'msg' => '删除成功',
        ]);
    }

    public function proceed(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $order_id = $args['id'];
        (new Order())->find($order_id)->delete();
        (new Invoice())->where('order_id', $order_id)->first()->delete();

        return $response->withJson([
            'ret' => 1,
            'msg' => '删除成功',
        ]);
    }

    public function ajax(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $withdraws = (new Withdraw())->orderBy('id', 'desc')->get();

        foreach ($withdraws as $withdraw) {
            // if ($withdraw->status === 'pending') {
            //     $withdraw->op .= '
            //     <button type="button" class="btn btn-red" id="cancel-order-' . $withdraw->id . '"
            //      onclick="cancelOrder(' . $withdraw->id . ')">拒绝</button>';
            //     $withdraw->op .= '
            //      <button type="button" class="btn btn-blue" id="cancel-order-' . $withdraw->id . '"
            //       onclick="cancelOrder(' . $withdraw->id . ')">继续</button>';
            // }

            // if ($withdraw->status === 'fail') {
            //     $withdraw->op .= '
            //      <button type="button" class="btn btn-orange" id="cancel-order-' . $withdraw->id . '"
            //       onclick="cancelOrder(' . $withdraw->id . ')">重试</button>';
            //     $withdraw->op .= '
            //       <button type="button" class="btn btn-dark" id="cancel-order-' . $withdraw->id . '"
            //        onclick="cancelOrder(' . $withdraw->id . ')">标记处理</button>';
            // }
            $withdraw->op .= '
            <button type="button" class="btn btn-dark" id="cancel-order-' . $withdraw->id . '"
             onclick="cancelOrder(' . $withdraw->id . ')">标记处理</button>';
        }

        return $response->withJson([
            'withdraws' => $withdraws,
        ]);
    }
}
