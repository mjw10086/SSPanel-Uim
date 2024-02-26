<?php

declare(strict_types=1);

use App\Interfaces\MigrationInterface;
use App\Services\DB;

return new class() implements MigrationInterface {
    public function up(): int
    {
        DB::getPdo()->exec("
            CREATE TABLE withdraw (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                uuid VARCHAR(36) NOT NULL,
                type VARCHAR(36) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                withdraw_message longtext DEFAULT '',
                status VARCHAR(50) NOT NULL,
                create_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                update_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                create_message longtext DEFAULT '',
                message longtext DEFAULT ''
            );
        ");

        return 2024021600;
    }

    public function down(): int
    {
        DB::getPdo()->exec('
            DROP TABLE IF EXISTS withdraw;
        ');

        return 2023120700;
    }
};
