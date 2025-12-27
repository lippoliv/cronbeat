<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * @noinspection PhpUnused
 */
class Migration0004 extends BaseMigration {

    public function getName(): string {
        return 'Add ping history and tracking tables';
    }

    public function getVersion(): int {
        return 4;
    }

    protected function execute(\PDO $pdo): void {
        Logger::debug("Creating ping_history table");

        $result = $pdo->exec("CREATE TABLE IF NOT EXISTS ping_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            monitor_id INTEGER NOT NULL,
            pinged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            duration_ms INTEGER NULL,
            FOREIGN KEY (monitor_id) REFERENCES monitors(id)
        )");

        if ($result === false) {
            throw new \Exception("Failed to create ping_history table: " . implode(", ", $pdo->errorInfo()));
        }

        $result = $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ping_history_monitor_id ON ping_history (monitor_id)");
        if ($result === false) {
            throw new \Exception("Failed to create index on ping_history table: " . implode(", ", $pdo->errorInfo()));
        }

        $result = $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ping_history_pinged_at ON ping_history (pinged_at DESC)");
        if ($result === false) {
            throw new \Exception(
                "Failed to create index on ping_history table (pinged_at): "
                . implode(", ", $pdo->errorInfo())
            );
        }

        Logger::debug("Creating ping_tracking table");

        $result = $pdo->exec("CREATE TABLE IF NOT EXISTS ping_tracking (
            monitor_id INTEGER PRIMARY KEY,
            started_at TIMESTAMP NOT NULL,
            FOREIGN KEY (monitor_id) REFERENCES monitors(id)
        )");

        if ($result === false) {
            throw new \Exception("Failed to create ping_tracking table: " . implode(", ", $pdo->errorInfo()));
        }

        $result = $pdo->exec("CREATE INDEX IF NOT EXISTS idx_ping_tracking_started_at ON ping_tracking (started_at)");
        if ($result === false) {
            throw new \Exception("Failed to create index on ping_tracking table: " . implode(", ", $pdo->errorInfo()));
        }
    }
}
