<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * @noinspection PhpUnused
 */
class Migration0003 extends BaseMigration {

    public function getName(): string {
        return 'Add profile fields to users';
    }

    public function getVersion(): int {
        return 3;
    }

    protected function execute(\PDO $pdo): void {
        Logger::debug("Adding name and email columns to users table");

        // SQLite supports adding columns via ALTER TABLE ... ADD COLUMN
        $result1 = $pdo->exec("ALTER TABLE users ADD COLUMN name TEXT NULL");
        if ($result1 === false) {
            throw new \Exception("Failed to add name column to users table: " . implode(", ", $pdo->errorInfo()));
        }

        $result2 = $pdo->exec("ALTER TABLE users ADD COLUMN email TEXT NULL");
        if ($result2 === false) {
            throw new \Exception("Failed to add email column to users table: " . implode(", ", $pdo->errorInfo()));
        }
    }
}
