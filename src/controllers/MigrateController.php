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
            
            // Get all available migrations
            $allMigrations = $this->database->getAllMigrations();
            
            // Filter migrations that need to be run
            $pendingMigrations = array_filter($allMigrations, function($migration) use ($currentVersion) {
                return $migration->getVersion() > $currentVersion && $migration->getVersion() <= DB_VERSION;
            });
            
            if (empty($pendingMigrations)) {
                return $this->showMigratePage(null, "No migrations were needed.");
            }
            
            // Run migrations sequentially
            $migrationsRun = 0;
            
            foreach ($pendingMigrations as $migration) {
                $version = $migration->getVersion();
                $name = $migration->getName();
                
                Logger::info("Running migration", ['version' => $version, 'name' => $name]);
                
                try {
                    $this->database->runMigration($migration);
                    $migrationsRun++;
                } catch (\Exception $e) {
                    Logger::error("Migration failed", ['version' => $version, 'error' => $e->getMessage()]);
                    return $this->showMigratePage("Migration to version {$version} failed: " . $e->getMessage());
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
}