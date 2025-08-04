<?php

const APP_DIR = __DIR__;
const DB_VERSION = 1; // Current expected database version

require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/Logger.php';

use Cronbeat\Database;
use Cronbeat\Logger;

// Set up logging
$logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
Logger::setMinLevel($logLevel);

// Parse command line arguments
$command = $argv[1] ?? 'help';
$args = array_slice($argv, 2);

// Initialize database
$database = new Database(APP_DIR . '/db/db.sqlite');

// Process commands
switch ($command) {
    case 'migrate':
        migrateCommand($database, $args);
        break;
    case 'help':
    default:
        showHelp();
        break;
}

/**
 * Display help information
 */
function showHelp(): void {
    echo "CronBeat CLI\n";
    echo "============\n\n";
    echo "Available commands:\n";
    echo "  migrate           Run database migrations\n";
    echo "  help              Show this help information\n\n";
    echo "Usage:\n";
    echo "  php src/cli.php migrate [--force]\n";
    echo "  php src/cli.php help\n\n";
}

/**
 * Run database migrations
 */
function migrateCommand(Database $database, array $args): void {
    $force = in_array('--force', $args);
    
    echo "CronBeat Database Migration\n";
    echo "==========================\n\n";
    
    try {
        // Check if database exists
        if (!$database->databaseExists()) {
            echo "Error: Database does not exist. Please run the setup process first.\n";
            exit(1);
        }
        
        $currentVersion = $database->getDatabaseVersion();
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
            $migration = getMigration($version);
            
            if ($migration) {
                echo "Migrating to version {$version}: {$migration['name']}... ";
                
                try {
                    $database->runMigration($version, $migration['name'], $migration['sql']);
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
function getMigration(int $version): ?array {
    // Define migrations
    $migrations = [
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