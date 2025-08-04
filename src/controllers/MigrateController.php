<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\MigrateView;
use Cronbeat\Logger;

class MigrateController extends BaseController {
    public function doRouting(): string {
        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = ($pathParts[0] !== '') ? $pathParts[0] : 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }

        switch ($action) {
            case 'index':
                return $this->showMigratePage();
            case 'process':
                return $this->processMigration();
            default:
                return $this->showMigratePage();
        }
    }

    public function showMigratePage(?string $error = null, ?string $success = null): string {
        $view = new MigrateView();
        $view->setError($error);
        $view->setSuccess($success);
        $view->setCurrentVersion($this->database->getDatabaseVersion());
        $view->setExpectedVersion(DB_VERSION);
        return $view->render();
    }

    public function processMigration(): string {
        Logger::info("Processing migration request");
        
        try {
            $currentVersion = $this->database->getDatabaseVersion();
            $expectedVersion = DB_VERSION;
            
            if ($currentVersion >= $expectedVersion) {
                return $this->showMigratePage(null, "Database is already up to date (version {$currentVersion}).");
            }
            
            // Run migrations sequentially
            $success = true;
            $migrationsRun = 0;
            
            for ($version = $currentVersion + 1; $version <= $expectedVersion; $version++) {
                $migration = $this->getMigration($version);
                
                if ($migration) {
                    Logger::info("Running migration", ['version' => $version, 'name' => $migration['name']]);
                    
                    try {
                        $this->database->runMigration($version, $migration['name'], $migration['sql']);
                        $migrationsRun++;
                    } catch (\Exception $e) {
                        Logger::error("Migration failed", ['version' => $version, 'error' => $e->getMessage()]);
                        return $this->showMigratePage("Migration to version {$version} failed: " . $e->getMessage());
                    }
                } else {
                    Logger::error("Migration not found", ['version' => $version]);
                    return $this->showMigratePage("Migration to version {$version} not found.");
                }
            }
            
            if ($migrationsRun > 0) {
                return $this->showMigratePage(null, "Successfully migrated database from version {$currentVersion} to {$expectedVersion}.");
            } else {
                return $this->showMigratePage(null, "No migrations were needed.");
            }
        } catch (\Exception $e) {
            Logger::error("Error during migration process", ['error' => $e->getMessage()]);
            return $this->showMigratePage("Error during migration process: " . $e->getMessage());
        }
    }
    
    private function getMigration(int $version): ?array {
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
}