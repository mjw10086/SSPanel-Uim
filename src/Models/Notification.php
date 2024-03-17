<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Query\Builder;

/**
 * @property int    $id             提醒ID
 * @property int    $user_id        提醒用户
 * @property string $content        提醒内容
 * @property int    $create_time    创建时间
 * @property bool    $has_read       是否被阅读
 *
 * @mixin Builder
 */
final class Notification extends Model
{
    protected $connection = 'default';
    protected $table = 'notification';
}
