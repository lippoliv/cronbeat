<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * Migration 0001: Initial schema setup
 * Creates the users and migrations tables
 */
class Migration0001 extends BaseMigration {
    /**
     * Get the migration name
     * 
     * @return string The name of the migration
     */
    public function getName(): string {
        return 'Initial schema setup';
    }
    
    /**
     * Get the migration version
     * 
     * @return int The version number of the migration
     */
    public function getVersion(): int {
        return 1;
    }
    
    /**
     * Execute the migration logic
     * 
     * @param \PDO $pdo The PDO database connection
     * @return void
     * @throws \Exception If the migration logic fails
     */
    protected function execute(\PDO $pdo): void {
        Logger::debug("Creating users table");
        
        // Create users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        Logger::debug("Creating migrations table");
        
        // Create migrations table
        $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version INTEGER NOT NULL UNIQUE,
            name TEXT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Check if this is a fresh installation (no migrations record exists)
        $stmt = $pdo->query("SELECT COUNT(*) FROM migrations");
        $count = $stmt->fetchColumn();
        
        if ((int)$count === 0) {
            Logger::debug("Setting initial database version");
            
            // Insert the initial migration record
            $stmt = $pdo->prepare("INSERT INTO migrations (version, name) VALUES (?, ?)");
            $stmt->execute([0, 'Initial setup']);
        }
    }
    
}