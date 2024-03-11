<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Query\Builder;
use function time;

/**
 * @property int $id 记录ID
 * @property int $user_id 用户ID
 * @property float $before 用户变动前账户余额
 * @property float $after 用户变动后账户余额
 * @property float $amount 变动总额
 * @property string $remark 备注
 * @property int $create_time 创建时间
 * @property string $type 余额变动类型
 *
 * @mixin Builder
 */
final class UserMoneyLog extends Model
{
    protected $connection = 'default';
    protected $table = 'user_money_log';

    public function add(int $user_id, float $before, float $after, float $amount, string $remark, string $type): void
    {
        $this->user_id = $user_id;
        $this->before = $before;
        $this->after = $after;
        $this->amount = $amount;
        $this->remark = $remark;
        $this->type = $type;    // admin, card manual, card recurring, crypto manual, crypto recurring, withdraw, withdraw failed, pay with balance, plan cancel, gift card, referral bonuses
        $this->create_time = time();
        $this->save();
    }
}
