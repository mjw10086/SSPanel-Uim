<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Query\Builder;

/**
 * @property int    $id                 提款ID
 * @property int    $user_id            提款用户
 * @property string $transfer_id        提款标识
 * @property string $type               提款路径(stripe, cryptomus)
 * @property double $amount             提款用户
 * @property string $status             提款状态(pending, rejected, success)
 * @property string $create_time        订阅创建时间
 * @property string $update_time        订阅更新时间
 * @property string $to_account         提款地址
 * @property string $addition_msg       提款的额外信息
 * @property string $note               备注
 *
 * @mixin Builder
 */
final class Withdraw extends Model
{
    protected $connection = 'default';
    protected $table = 'withdraw';
}
