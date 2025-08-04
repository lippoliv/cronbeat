<?php

namespace Cronbeat\Migrations;

use Cronbeat\BaseMigration;
use Cronbeat\Logger;

/**
 * Migration 0002: Add jobs table
 * Creates the jobs table for tracking cron jobs
 */
class Migration0002 extends BaseMigration {
    /**
     * Get the migration name
     * 
     * @return string The name of the migration
     */
    public function getName(): string {
        return 'Add jobs table';
    }
    
    /**
     * Get the migration version
     * 
     * @return int The version number of the migration
     */
    public function getVersion(): int {
        return 2;
    }
    
    /**
     * Execute the migration logic
     * 
     * @param \PDO $pdo The PDO database connection
     * @return void
     * @throws \Exception If the migration logic fails
     */
    protected function execute(\PDO $pdo): void {
        Logger::debug("Creating jobs table");
        
        // Create jobs table
        $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            identifier TEXT NOT NULL UNIQUE,
            expected_interval INTEGER NOT NULL,
            grace_period INTEGER NOT NULL DEFAULT 0,
            last_check_in TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        // Add indexes for performance
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_jobs_identifier ON jobs (identifier)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_jobs_last_check_in ON jobs (last_check_in)");
    }
    
    /**
     * Revert the migration logic
     * 
     * @param \PDO $pdo The PDO database connection
     * @return void
     * @throws \Exception If reverting the migration logic fails
     */
    protected function revert(\PDO $pdo): void {
        Logger::debug("Dropping jobs table");
        
        // Drop the jobs table
        $pdo->exec("DROP TABLE IF EXISTS jobs");
    }
}