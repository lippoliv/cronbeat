<?php

namespace Cronbeat\CLI;

use Cronbeat\Database;
use Cronbeat\Logger;

class CLI {

    private Database $database;
    /** @var array<string> */
    private array $args;

    /** @param array<string> $args */
    public function __construct(Database $database, array $args) {
        $this->database = $database;
        $this->args = $args;
    }

    public function run(): void {
        $command = $this->args[0] ?? 'help';

        switch ($command) {
            case 'migrate':
                $this->migrateCommand();
                break;
            case 'help':
            default:
                $this->showHelp();
                break;
        }
    }

    private function showHelp(): void {
        echo "CronBeat CLI\n";
        echo "============\n\n";
        echo "Available commands:\n";
        echo "  migrate           Run database migrations\n";
        echo "  help              Show this help information\n\n";
        echo "Usage:\n";
        echo "  php cli.php migrate\n";
        echo "  php cli.php help\n\n";
    }

    private function migrateCommand(): void {
        echo "CronBeat Database Migration\n";
        echo "==========================\n\n";

        try {
            // Check if database exists, create it if it doesn't
            if (!$this->database->databaseExists()) {
                echo "Database does not exist. Creating it...\n";
                try {
                    $this->database->createDatabase();
                    echo "Database created successfully.\n\n";
                } catch (\Exception $e) {
                    echo "Error: Failed to create database: " . $e->getMessage() . "\n";
                    exit(1);
                }
            }

            $currentVersion = $this->database->getDatabaseVersion();
            $expectedVersion = DB_VERSION;

            echo "Current database version: {$currentVersion}\n";
            echo "Expected database version: {$expectedVersion}\n\n";

            if ($currentVersion >= $expectedVersion) {
                echo "Database is already up to date. No migration needed.\n";
                exit(0);
            }

            echo "Running migrations...\n\n";

            // Get all available migrations
            $allMigrations = $this->database->getAllMigrations();

            // Filter migrations that need to be run
            $pendingMigrations = array_filter(
                $allMigrations,
                function ($migration) use ($currentVersion, $expectedVersion) {
                    return $migration->getVersion() > $currentVersion && $migration->getVersion() <= $expectedVersion;
                }
            );

            if (count($pendingMigrations) === 0) {
                echo "No migrations were needed.\n";
                exit(0);
            }

            ksort($pendingMigrations);

            foreach ($pendingMigrations as $migration) {
                $version = $migration->getVersion();
                $name = $migration->getName();

                echo "Migrating to version {$version}: {$name}... ";

                try {
                    $this->database->runMigration($migration);
                    echo "Done.\n";
                } catch (\Exception $e) {
                    echo "Failed!\n";
                    echo "Error: {$e->getMessage()}\n";
                    exit(1);
                }
            }

            echo "\nSuccessfully migrated database.\n";
            echo "Final database version: {$expectedVersion}\n";
        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}\n";
            exit(1);
        }
    }
}
