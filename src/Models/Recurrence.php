<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Query\Builder;

/**
 * @property int    $id             订阅ID
 * @property int    $user_id        订阅用户
 * @property string $uuid           订阅标识
 * @property string $type           订阅类型(cryptomus, stripe)
 * @property double $amount         订阅用户
 * @property string $status         订阅状态(pending, fail, rejected, success)
 * @property string $period         订阅周期
 * @property string $create_time    订阅创建时间
 * @property string $update_time    订阅更新时间
 * @property string $create_message 订阅创建备注
 * @property string $message        备注
 *
 * @mixin Builder
 */
final class Recurrence extends Model
{
    protected $connection = 'default';
    protected $table = 'recurrence';
}
