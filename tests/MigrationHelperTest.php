<?php

namespace Cronbeat\Tests;

use Cronbeat\MigrationHelper;
use Cronbeat\Migration;
use Cronbeat\Logger;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

class MigrationHelperTest extends TestCase {
    private string $tempMigrationsDir = '';
    private string $originalLogLevel = '';
    private string $testAppDir = '';

    private string $originalMigrationsDir = '';

    protected function setUp(): void {
        parent::setUp();

        // Store original migrations directory before changing it
        $this->originalMigrationsDir = MigrationHelper::$migrationsDir;

        // Create temporary app directory structure
        $this->testAppDir = sys_get_temp_dir() . '/cronbeat_test_app_' . uniqid();
        $this->tempMigrationsDir = $this->testAppDir . '/migrations';
        mkdir($this->tempMigrationsDir, 0777, true);

        // Set the static migrations directory for this test
        MigrationHelper::$migrationsDir = $this->tempMigrationsDir;

        // Store original log level and set to ERROR to reduce noise in tests
        $this->originalLogLevel = Logger::getMinLevel();
        Logger::setMinLevel(Logger::ERROR);
    }

    protected function tearDown(): void {
        // Restore
        MigrationHelper::$migrationsDir = $this->originalMigrationsDir;
        Logger::setMinLevel($this->originalLogLevel);

        // Clean up
        $this->cleanupTempDirectory($this->testAppDir);
        $this->cleanupTempDirectory($this->tempMigrationsDir);

        parent::tearDown();
    }

    private function cleanupTempDirectory(string $dir): void {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->cleanupTempDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }

        rmdir($dir);
    }

    private function createTestMigrationFile(
        int $version,
        string $className,
        bool $validClass = true,
        bool $throwException = false
    ): string {
        $filename = sprintf('%04d', $version) . '.php';
        $filePath = $this->tempMigrationsDir . '/' . $filename;

        if ($validClass) {
            $constructorCode = $throwException ?
                "public function __construct() { throw new \\Exception('Test exception during instantiation'); }" :
                "";

            $content = "<?php
namespace Cronbeat\\Migrations;

use Cronbeat\\BaseMigration;

class {$className} extends BaseMigration {
    {$constructorCode}

    public function getName(): string {
        return 'Test Migration {$version}';
    }

    public function getVersion(): int {
        return {$version};
    }

    protected function execute(\\PDO \$pdo): void {
        // Test migration implementation
    }
}";
        } else {
            $content = "<?php
namespace Cronbeat\\Migrations;

class {$className} {
    // Invalid migration class - doesn't implement Migration interface
}";
        }

        file_put_contents($filePath, $content);
        return $filePath;
    }


    public function testLoadMigrationReturnsNullWhenFileNotFound(): void {
        // Given
        $nonExistentVersion = 9999;

        // When
        $result = MigrationHelper::loadMigration($nonExistentVersion);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationReturnsNullWhenClassNotFound(): void {
        // Given
        $version = 5001;
        $filePath = $this->tempMigrationsDir . '/5001.php';
        file_put_contents($filePath, "<?php\n// File exists but no class defined");

        // When
        $result = MigrationHelper::loadMigration($version);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationReturnsNullWhenClassDoesNotImplementMigrationInterface(): void {
        // Given
        $version = 1002;
        $className = 'Migration1002';
        $this->createTestMigrationFile($version, $className, false);

        // When
        $result = MigrationHelper::loadMigration($version);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationHandlesExceptionDuringInstantiation(): void {
        // Given
        $version = 1003;
        $className = 'Migration1003';
        $this->createTestMigrationFile($version, $className, true, true);

        // When
        $result = MigrationHelper::loadMigration($version);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationWithValidMigrationFile(): void {
        // Given
        $version = 1001;
        $className = 'Migration1001';
        $this->createTestMigrationFile($version, $className, true);

        // When
        $result = MigrationHelper::loadMigration($version);

        // Then
        Assert::assertInstanceOf(Migration::class, $result);
        Assert::assertEquals($version, $result->getVersion());
        Assert::assertEquals('Test Migration 1001', $result->getName());
    }

    public function testLoadAllMigrationsReturnsEmptyArrayWhenDirectoryNotFound(): void {
        // Given
        $nonExistentAppDir = sys_get_temp_dir() . '/nonexistent_' . uniqid();
        MigrationHelper::$migrationsDir = $nonExistentAppDir . '/migrations';

        // When
        $result = MigrationHelper::loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertEmpty($result);
    }

    public function testLoadAllMigrationsIgnoresNonMigrationFiles(): void {
        // Given
        // Create valid migration files (using different version numbers to avoid conflicts)
        $this->createTestMigrationFile(2001, 'Migration2001', true);
        $this->createTestMigrationFile(2002, 'Migration2002', true);

        // Create non-migration files that should be ignored
        file_put_contents($this->tempMigrationsDir . '/readme.txt', 'Not a migration');
        file_put_contents($this->tempMigrationsDir . '/invalid.php', '<?php // Invalid name');
        file_put_contents($this->tempMigrationsDir . '/001.php', '<?php // Wrong format');
        file_put_contents($this->tempMigrationsDir . '/12345.php', '<?php // Too many digits');

        // When
        $result = MigrationHelper::loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertCount(2, $result);
        Assert::assertArrayHasKey(2001, $result);
        Assert::assertArrayHasKey(2002, $result);
        Assert::assertInstanceOf(Migration::class, $result[2001]);
        Assert::assertInstanceOf(Migration::class, $result[2002]);
    }

    public function testLoadAllMigrationsSortsResultsByVersion(): void {
        // Given
        // Create migrations in reverse order to test sorting (using different version numbers to avoid conflicts)
        $this->createTestMigrationFile(3005, 'Migration3005', true);
        $this->createTestMigrationFile(3001, 'Migration3001', true);
        $this->createTestMigrationFile(3003, 'Migration3003', true);

        // When
        $result = MigrationHelper::loadAllMigrations();

        // Then
        Assert::assertIsArray($result);

        $versions = array_keys($result);
        Assert::assertEquals([3001, 3003, 3005], $versions);
    }

    public function testLoadAllMigrationsReturnsOnlyValidMigrations(): void {
        // Given
        // Create mix of valid and invalid migrations
        $this->createTestMigrationFile(4001, 'Migration4001', true);  // Valid
        $this->createTestMigrationFile(4002, 'Migration4002', false); // Invalid - doesn't implement interface
        $this->createTestMigrationFile(4003, 'Migration4003', true, true); // Invalid - throws exception
        $this->createTestMigrationFile(4004, 'Migration4004', true);  // Valid

        // When
        $result = MigrationHelper::loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertCount(2, $result); // Only valid migrations should be returned
        Assert::assertArrayHasKey(4001, $result);
        Assert::assertArrayHasKey(4004, $result);

        // Verify all returned items implement Migration interface
        foreach ($result as $migration) {
            Assert::assertInstanceOf(Migration::class, $migration);
        }
    }

    public function testLoadAllMigrationsWithEmptyDirectory(): void {
        // Given
        // Directory exists but is empty (no migration files created)

        // When
        $result = MigrationHelper::loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertEmpty($result);
    }

    public function testActualMigrationHelperLoadMigrationWithExistingMigration(): void {
        // Given
        MigrationHelper::$migrationsDir = APP_DIR . '/migrations';

        // When
        $result = MigrationHelper::loadMigration(1); // Assuming migration 0001 exists

        // Then
        Assert::assertInstanceOf(Migration::class, $result);
        Assert::assertEquals(1, $result->getVersion());
        Assert::assertIsString($result->getName());
    }

    public function testActualMigrationHelperLoadAllMigrationsWithExistingMigrations(): void {
        // Given
        // Use the real MigrationHelper with existing migrations (uses default migrations directory)

        // When
        $result = MigrationHelper::loadAllMigrations();

        // Then
        Assert::assertIsArray($result);

        // If migrations exist, verify they're properly loaded
        if (count($result) > 0) {
            foreach ($result as $version => $migration) {
                Assert::assertIsInt($version);
                Assert::assertInstanceOf(Migration::class, $migration);
                Assert::assertEquals($version, $migration->getVersion());
            }

            // Verify sorting
            $versions = array_keys($result);
            $sortedVersions = $versions;
            sort($sortedVersions);
            Assert::assertEquals($sortedVersions, $versions);
        }
    }
}
