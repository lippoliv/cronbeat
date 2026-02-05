<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * @noinspection PhpUnused
 */
class Migration0005 extends BaseMigration {

    public function getName(): string {
        return 'Add expected interval and grace period to monitors';
    }

    public function getVersion(): int {
        return 5;
    }

    protected function execute(\PDO $pdo): void {
        Logger::debug("Altering monitors table to add expected/grace minutes");

        $result1 = $pdo->exec(
            "ALTER TABLE monitors ADD COLUMN expected_interval_minutes INTEGER NULL"
        );
        if ($result1 === false) {
            $err = implode(", ", $pdo->errorInfo());
            // Do not ignore duplicate/exists errors – surface them to fail the migration
            throw new \Exception("Failed to add expected_interval_minutes column: " . $err);
        }

        $result2 = $pdo->exec(
            "ALTER TABLE monitors ADD COLUMN grace_period_minutes INTEGER NULL"
        );
        if ($result2 === false) {
            $err = implode(", ", $pdo->errorInfo());
            // Do not ignore duplicate/exists errors – surface them to fail the migration
            throw new \Exception("Failed to add grace_period_minutes column: " . $err);
        }
    }
}
