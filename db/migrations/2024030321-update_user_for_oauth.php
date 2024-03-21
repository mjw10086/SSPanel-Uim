<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class () implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec("
            ALTER TABLE user
            ADD telegram_id varchar(255) NOT NULL DEFAULT '',
            ADD telegram_username varchar(255) NOT NULL DEFAULT '',
            ADD google_id varchar(255) NOT NULL DEFAULT '',
            ADD google_username varchar(255) NOT NULL DEFAULT '';
        ");

        return 2024030321;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
            ALTER TABLE user
            DROP COLUMN IF EXISTS telegram_id,
            DROP COLUMN IF EXISTS telegram_username,
            DROP COLUMN IF EXISTS google_id,
            DROP COLUMN IF EXISTS google_username;
        ');

        return 2024030317;
    }
};
