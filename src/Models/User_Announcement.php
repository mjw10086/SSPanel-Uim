<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Query\Builder;

/**
 * @property int $id   
 * @property int $user_id 
 * @property int $annoucement_id 
 * @property int $create_time
 * @property bool $has_read
 *
 * @mixin Builder
 */
final class User_Announcement extends Model
{
    protected $connection = 'default';
    protected $table = 'user_annoucement';
}
