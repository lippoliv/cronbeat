<?php

namespace Cronbeat;

abstract class MigrationHelper {
    public static string $migrationsDir = APP_DIR . '/migrations';

    public static function loadMigration(int $version): ?\Cronbeat\Migration {
        $migrationFile = self::$migrationsDir . '/' . sprintf('%04d', $version) . '.php';

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

    /** @return array<int, \Cronbeat\Migration> */
    public static function loadAllMigrations(): array {
        $migrations = [];

        if (!is_dir(self::$migrationsDir)) {
            Logger::warning("Migrations directory not found", ['dir' => self::$migrationsDir]);
            return [];
        }

        $files = scandir(self::$migrationsDir);
        if ($files === false) {
            Logger::error("Failed to scan migrations directory", ['dir' => self::$migrationsDir]);
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
