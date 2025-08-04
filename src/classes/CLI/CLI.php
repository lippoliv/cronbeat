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
            
            // Get all available migrations
            $allMigrations = $this->database->getAllMigrations();
            
            // Filter migrations that need to be run
            $pendingMigrations = array_filter($allMigrations, function($migration) use ($currentVersion, $force, $expectedVersion) {
                if ($force) {
                    // If force is true, run all migrations up to expected version
                    return $migration->getVersion() <= $expectedVersion;
                } else {
                    // Otherwise, only run migrations that are newer than current version and up to expected version
                    return $migration->getVersion() > $currentVersion && $migration->getVersion() <= $expectedVersion;
                }
            });
            
            if (empty($pendingMigrations)) {
                echo "No migrations were needed.\n";
                exit(0);
            }
            
            // Sort migrations by version
            ksort($pendingMigrations);
            
            // Run migrations sequentially
            $migrationsRun = 0;
            
            foreach ($pendingMigrations as $migration) {
                $version = $migration->getVersion();
                $name = $migration->getName();
                
                echo "Migrating to version {$version}: {$name}... ";
                
                try {
                    $this->database->runMigration($migration);
                    echo "Done.\n";
                    $migrationsRun++;
                } catch (\Exception $e) {
                    echo "Failed!\n";
                    echo "Error: {$e->getMessage()}\n";
                    exit(1);
                }
            }
            
            if ($migrationsRun > 0) {
                echo "\nSuccessfully migrated database";
                if ($force) {
                    echo " (forced)";
                }
                echo ".\n";
                echo "Final database version: {$expectedVersion}\n";
            } else {
                echo "\nNo migrations were needed.\n";
            }
        } catch (\Exception $e) {
            echo "Error: {$e->getMessage()}\n";
            exit(1);
        }
    }
}