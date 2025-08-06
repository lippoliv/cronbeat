<?php

namespace Cronbeat\Tests;

use Cronbeat\Database;
use Cronbeat\MigrationHelper;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase {
    protected string $tempDbPath = '';
    protected ?Database $database = null;

    protected function setUp(): void {
        parent::setUp();
        $this->tempDbPath = sys_get_temp_dir() . '/test_cronbeat_' . uniqid() . '.sqlite';
        $this->database = new Database($this->tempDbPath);
        $this->database->createDatabase();

        $this->runAllMigrations();
    }

    protected function tearDown(): void {
        $this->cleanupTestDatabase();
        parent::tearDown();
    }

    private function runAllMigrations(): void {
        $migrations = MigrationHelper::loadAllMigrations();
        foreach ($migrations as $migration) {
            $this->getDatabase()->runMigration($migration);
        }
    }

    private function cleanupTestDatabase(): void {
        if (file_exists($this->tempDbPath)) {
            unlink($this->tempDbPath);
        }

        $testDbDir = dirname($this->tempDbPath);
        if (is_dir($testDbDir) && basename($testDbDir) === 'cronbeat_test_dir') {
            rmdir($testDbDir);
        }
    }

    protected function getDatabase(): Database {
        if ($this->database === null) {
            throw new \RuntimeException('Database not initialized. Call setUp() first.');
        }
        return $this->database;
    }
}
