<?php

// PHPUnit Bootstrap file
// This file handles autoloading and setup that would otherwise cause side effects in test files

// Define constants needed for the application
const APP_DIR = __DIR__ . '/../src';
const DB_VERSION = 1; // Current expected database version

// Include required files
require_once APP_DIR . '/classes/Logger.php';
require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/Migration.php';
require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/base.view.php';
require_once APP_DIR . '/views/setup.view.php';
require_once APP_DIR . '/controllers/SetupController.php';

// Set up logging
use Cronbeat\Logger;
$logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
Logger::setMinLevel($logLevel);

// Initialize database for tests
class TestDatabaseInitializer {
    public static function initializeDatabase(string $dbPath): void {
        // Create a new database instance
        $database = new Cronbeat\Database($dbPath);
        
        // Create the database if it doesn't exist
        if (!$database->databaseExists()) {
            $database->createDatabase();
        }
        
        // Run the first migration to create the necessary tables
        $migration = $database->getMigration(1);
        if ($migration !== null) {
            try {
                $database->runMigration($migration);
            } catch (\Exception $e) {
                echo "Error initializing test database: " . $e->getMessage() . "\n";
            }
        }
    }
}
