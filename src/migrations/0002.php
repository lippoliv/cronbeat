<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * @noinspection PhpUnused
 */
class Migration0002 extends BaseMigration {

    public function getName(): string {
        return 'Create monitors table';
    }

    public function getVersion(): int {
        return 2;
    }

    protected function execute(\PDO $pdo): void {
        Logger::debug("Creating monitors table");

        $result = $pdo->exec("CREATE TABLE IF NOT EXISTS monitors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            user_id INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        if ($result === false) {
            throw new \Exception("Failed to create monitors table: " . implode(", ", $pdo->errorInfo()));
        }

        // Create index on name for sorting
        $result = $pdo->exec("CREATE INDEX IF NOT EXISTS idx_monitors_name ON monitors (name)");

        if ($result === false) {
            throw new \Exception("Failed to create index on monitors table: " . implode(", ", $pdo->errorInfo()));
        }

        // Create index on user_id for filtering
        $result = $pdo->exec("CREATE INDEX IF NOT EXISTS idx_monitors_user_id ON monitors (user_id)");

        if ($result === false) {
            throw new \Exception("Failed to create index on monitors table: " . implode(", ", $pdo->errorInfo()));
        }
    }
}
