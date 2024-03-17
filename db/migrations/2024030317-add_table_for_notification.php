<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec("
            CREATE TABLE notification (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                content TEXT NOT NULL,
                create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                has_read BOOLEAN DEFAULT FALSE
            );
        ");

        return 2024030317;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
            DROP TABLE IF EXISTS notification;
        ');

        return 2024030300;
    }
};
