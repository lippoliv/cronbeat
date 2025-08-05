<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * Migration 0001: Initial schema setup
 * Creates the users and migrations tables
 * @noinspection PhpUnused
 */
class Migration0001 extends BaseMigration {

    public function getName(): string {
        return 'Initial schema setup';
    }

    public function getVersion(): int {
        return 1;
    }

    protected function execute(\PDO $pdo): void {
        Logger::debug("Creating users table");

        $result = $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        if ($result === false) {
            throw new \Exception("Failed to create users table: " . implode(", ", $pdo->errorInfo()));
        }

        Logger::debug("Creating migrations table");

        $result = $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version INTEGER NOT NULL UNIQUE,
            name TEXT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        if ($result === false) {
            throw new \Exception("Failed to create migrations table: " . implode(", ", $pdo->errorInfo()));
        }
        
        $result = $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_migrations_version ON migrations (version)");
        
        if ($result === false) {
            throw new \Exception("Failed to create index on migrations table: " . implode(", ", $pdo->errorInfo()));
        }
    }
}
