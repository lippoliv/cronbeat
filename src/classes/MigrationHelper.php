<?php

namespace Cronbeat;

class MigrationHelper {
    /**
     * Load a migration by version number
     * 
     * @param int $version The version number of the migration to load
     * @return \Cronbeat\Migration|null The migration instance or null if not found
     */
    public static function loadMigration(int $version): ?\Cronbeat\Migration {
        $migrationFile = APP_DIR . '/migrations/' . sprintf('%04d', $version) . '.php';

        if (!file_exists($migrationFile)) {
            Logger::error("Migration file not found", ['version' => $version, 'file' => $migrationFile]);
            return null;
        }

        require_once $migrationFile;

        $className = '\\Cronbeat\\Migrations\\Migration' . sprintf('%04d', $version);

        if (!class_exists($className)) {
            Logger::error("Migration class not found", ['version' => $version, 'class' => $className]);
            return null;
        }
        try {
            $migration = new $className();

            if (!$migration instanceof \Cronbeat\Migration) {
                Logger::error("Invalid migration class", ['version' => $version, 'class' => $className]);
                return null;
            }

            return $migration;
        } catch (\Exception $e) {
            Logger::error("Error creating migration instance", [
                'version' => $version,
                'class' => $className,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Load all available migrations
     * 
     * @return array<int, \Cronbeat\Migration> Array of migrations indexed by version number
     */
    public static function loadAllMigrations(): array {
        $migrations = [];
        $migrationDir = APP_DIR . '/migrations';

        if (!is_dir($migrationDir)) {
            Logger::warning("Migrations directory not found", ['dir' => $migrationDir]);
            return [];
        }

        $files = scandir($migrationDir);
        if ($files === false) {
            Logger::error("Failed to scan migrations directory", ['dir' => $migrationDir]);
            return [];
        }

        foreach ($files as $file) {
            if (preg_match('/^(\d{4})\.php$/', $file, $matches) !== 1) {
                continue;
            }

            $version = (int) $matches[1];
            $migration = self::loadMigration($version);

            if ($migration !== null) {
                $migrations[$version] = $migration;
            }
        }

        ksort($migrations);

        return $migrations;
    }
}