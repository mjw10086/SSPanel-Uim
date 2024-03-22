<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class () implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec("
            ALTER TABLE user
            ADD create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
        ");

        return 2024032100;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
            ALTER TABLE user
            DROP COLUMN IF EXISTS create_time;
        ');

        return 2024030322;
    }
};
