<?php

namespace Cronbeat\CLI;

use Cronbeat\Database;
use Cronbeat\Logger;

class CLI {
    private Database $database;
    private array $args;
    
    public function __construct(Database $database, array $args) {
        $this->database = $database;
        $this->args = $args;
    }
    
    public function run(): void {
        // Parse command line arguments
        $command = $this->args[0] ?? 'help';
        $commandArgs = array_slice($this->args, 1);
        
        // Process commands
        switch ($command) {
            case 'migrate':
                $this->migrateCommand($commandArgs);
                break;
            case 'help':
            default:
                $this->showHelp();
                break;
        }
    }
    
    /**
     * Display help information
     */
    private function showHelp(): void {
        echo "CronBeat CLI\n";
        echo "============\n\n";
        echo "Available commands:\n";
        echo "  migrate           Run database migrations\n";
        echo "  help              Show this help information\n\n";
        echo "Usage:\n";
        echo "  php cli.php migrate [--force]\n";
        echo "  php cli.php help\n\n";
    }
    
    /**
     * Run database migrations
     */
    private function migrateCommand(array $args): void {
        $force = in_array('--force', $args);
        
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
            
            if ($currentVersion >= $expectedVersion && !$force) {
                echo "Database is already up to date. No migration needed.\n";
                echo "Use --force to run migrations anyway.\n";
                exit(0);
            }
            
            echo "Running migrations...\n\n";
            
            // Run migrations sequentially
            $migrationsRun = 0;
            
            for ($version = $currentVersion + 1; $version <= $expectedVersion; $version++) {
                $migration = $this->getMigration($version);
                
                if ($migration) {
                    echo "Migrating to version {$version}: {$migration['name']}... ";
                    
                    try {
                        $this->database->runMigration($version, $migration['name'], $migration['sql']);
                        echo "Done.\n";
                        $migrationsRun++;
                    } catch (\Exception $e) {
                        echo "Failed!\n";
                        echo "Error: {$e->getMessage()}\n";
                        exit(1);
                    }
                } else {
                    echo "Error: Migration to version {$version} not found.\n";
                    exit(1);
                }
            }
            
            if ($migrationsRun > 0) {
                echo "\nSuccessfully migrated database from version {$currentVersion} to {$expectedVersion}.\n";
            } else {
                echo "\nNo migrations were needed.\n";
            }
        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}\n";
            exit(1);
        }
    }
    
    /**
     * Get migration details for a specific version
     */
    private function getMigration(int $version): ?array {
        // Define migrations
        $migrations = [
            0 => [
                'name' => 'Initial schema setup',
                'sql' => "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    version INTEGER NOT NULL UNIQUE,
                    name TEXT NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );"
            ],
            1 => [
                'name' => 'Add jobs table',
                'sql' => "CREATE TABLE IF NOT EXISTS jobs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    identifier TEXT NOT NULL UNIQUE,
                    expected_interval INTEGER NOT NULL,
                    grace_period INTEGER NOT NULL DEFAULT 0,
                    last_check_in TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            // Add more migrations as needed
        ];
        
        return $migrations[$version] ?? null;
    }
}