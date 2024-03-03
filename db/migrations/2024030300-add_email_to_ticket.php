<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec("
            ALTER TABLE ticket
            ADD email varchar(255) NOT NULL DEFAULT '' COMMENT '用户邮箱',
            ADD name varchar(255) NOT NULL DEFAULT '' COMMENT '用户名';
        ");

        return 2024030300;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
            ALTER TABLE ticket
            DROP COLUMN email,
            DROP COLUMN name;
        ');

        return 2024021800;
    }
};
