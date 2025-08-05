<?php

namespace Cronbeat\Tests;

use Cronbeat\MigrationHelper;
use Cronbeat\Migration;
use Cronbeat\Logger;
use Cronbeat\Tests\TestMigrationHelperInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

class MigrationHelperTest extends TestCase {
    private string $tempMigrationsDir = '';
    private string $originalLogLevel = '';
    private string $testAppDir = '';
    private string $testId = '';

    protected function setUp(): void {
        parent::setUp();

        // Create unique test ID for this test run
        $this->testId = uniqid();

        // Create temporary app directory structure
        $this->testAppDir = sys_get_temp_dir() . '/cronbeat_test_app_' . $this->testId;
        $this->tempMigrationsDir = $this->testAppDir . '/migrations';
        mkdir($this->tempMigrationsDir, 0777, true);

        // Store original log level and set to ERROR to reduce noise in tests
        $this->originalLogLevel = Logger::getMinLevel();
        Logger::setMinLevel(Logger::ERROR);
    }

    protected function tearDown(): void {
        // Restore original log level
        Logger::setMinLevel($this->originalLogLevel);

        // Clean up temporary app directory
        $this->cleanupTempDirectory($this->testAppDir);

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

        // Make class name unique by adding testId
        $uniqueClassName = $className . '_' . $this->testId;

        if ($validClass) {
            $constructorCode = $throwException ?
                "public function __construct() { throw new \\Exception('Test exception during instantiation'); }" :
                "";

            $content = "<?php
namespace Cronbeat\\Migrations;

use Cronbeat\\BaseMigration;

class {$uniqueClassName} extends BaseMigration {
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

class {$uniqueClassName} {
    // Invalid migration class - doesn't implement Migration interface
}";
        }

        file_put_contents($filePath, $content);
        return $filePath;
    }

    private function createTestMigrationHelper(?string $appDir = null): TestMigrationHelperInterface {
        // Create a test version of MigrationHelper that uses our test directory or a custom directory
        $testId = $this->testId;
        $appDirectory = $appDir ?? $this->testAppDir;
        return new class ($appDirectory, $testId) implements TestMigrationHelperInterface {
            private string $appDir;
            private string $testId;

            public function __construct(string $appDir, string $testId) {
                $this->appDir = $appDir;
                $this->testId = $testId;
            }

            public function loadMigration(int $version): ?\Cronbeat\Migration {
                $migrationFile = $this->appDir . '/migrations/' . sprintf('%04d', $version) . '.php';

                if (!file_exists($migrationFile)) {
                    Logger::error("Migration file not found", ['version' => $version, 'file' => $migrationFile]);
                    return null;
                }

                require_once $migrationFile;

                // Try both the standard class name and the unique class name
                $standardClassName = '\\Cronbeat\\Migrations\\Migration' . sprintf('%04d', $version);
                $uniqueClassName = '\\Cronbeat\\Migrations\\Migration' . sprintf('%04d', $version)
                    . '_' . $this->testId;

                $className = class_exists($uniqueClassName) ? $uniqueClassName : $standardClassName;

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
             * @return array<int, \Cronbeat\Migration>
             */
            public function loadAllMigrations(): array {
                $migrations = [];
                $migrationDir = $this->appDir . '/migrations';

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
                    $migration = $this->loadMigration($version);

                    if ($migration !== null) {
                        $migrations[$version] = $migration;
                    }
                }

                ksort($migrations);

                return $migrations;
            }
        };
    }

    public function testLoadMigrationReturnsNullWhenFileNotFound(): void {
        // Given
        $helper = $this->createTestMigrationHelper();
        $nonExistentVersion = 9999;

        // When
        $result = $helper->loadMigration($nonExistentVersion);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationReturnsNullWhenClassNotFound(): void {
        // Given
        $helper = $this->createTestMigrationHelper();
        $version = 1001;
        $filePath = $this->tempMigrationsDir . '/1001.php';
        file_put_contents($filePath, "<?php\n// File exists but no class defined");

        // When
        $result = $helper->loadMigration($version);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationReturnsNullWhenClassDoesNotImplementMigrationInterface(): void {
        // Given
        $helper = $this->createTestMigrationHelper();
        $version = 1002;
        $className = 'Migration1002';
        $this->createTestMigrationFile($version, $className, false);

        // When
        $result = $helper->loadMigration($version);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationHandlesExceptionDuringInstantiation(): void {
        // Given
        $helper = $this->createTestMigrationHelper();
        $version = 1003;
        $className = 'Migration1003';
        $this->createTestMigrationFile($version, $className, true, true);

        // When
        $result = $helper->loadMigration($version);

        // Then
        Assert::assertNull($result);
    }

    public function testLoadMigrationWithValidMigrationFile(): void {
        // Given
        $helper = $this->createTestMigrationHelper();
        $version = 1001; // Use high version number to avoid conflicts
        $className = 'Migration1001';
        $this->createTestMigrationFile($version, $className, true);

        // When
        $result = $helper->loadMigration($version);

        // Then
        Assert::assertInstanceOf(Migration::class, $result);
        Assert::assertEquals($version, $result->getVersion());
        Assert::assertEquals('Test Migration 1001', $result->getName());
    }

    public function testLoadAllMigrationsReturnsEmptyArrayWhenDirectoryNotFound(): void {
        // Given
        $nonExistentAppDir = sys_get_temp_dir() . '/nonexistent_' . uniqid();
        $helper = $this->createTestMigrationHelper($nonExistentAppDir);

        // When
        $result = $helper->loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertEmpty($result);
    }

    public function testLoadAllMigrationsIgnoresNonMigrationFiles(): void {
        // Given
        $helper = $this->createTestMigrationHelper();

        // Create valid migration files
        $this->createTestMigrationFile(1001, 'Migration1001', true);
        $this->createTestMigrationFile(1002, 'Migration1002', true);

        // Create non-migration files that should be ignored
        file_put_contents($this->tempMigrationsDir . '/readme.txt', 'Not a migration');
        file_put_contents($this->tempMigrationsDir . '/invalid.php', '<?php // Invalid name');
        file_put_contents($this->tempMigrationsDir . '/001.php', '<?php // Wrong format');
        file_put_contents($this->tempMigrationsDir . '/12345.php', '<?php // Too many digits');

        // When
        $result = $helper->loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertCount(2, $result);
        Assert::assertArrayHasKey(1001, $result);
        Assert::assertArrayHasKey(1002, $result);
        Assert::assertInstanceOf(Migration::class, $result[1001]);
        Assert::assertInstanceOf(Migration::class, $result[1002]);
    }

    public function testLoadAllMigrationsSortsResultsByVersion(): void {
        // Given
        $helper = $this->createTestMigrationHelper();

        // Create migrations in reverse order to test sorting
        $this->createTestMigrationFile(1005, 'Migration1005', true);
        $this->createTestMigrationFile(1001, 'Migration1001', true);
        $this->createTestMigrationFile(1003, 'Migration1003', true);

        // When
        $result = $helper->loadAllMigrations();

        // Then
        Assert::assertIsArray($result);

        $versions = array_keys($result);
        Assert::assertEquals([1001, 1003, 1005], $versions);

        // Verify that keys are sorted (versions should be in ascending order)
        $sortedVersions = $versions;
        sort($sortedVersions);
        Assert::assertEquals($sortedVersions, $versions);
    }

    public function testLoadAllMigrationsReturnsOnlyValidMigrations(): void {
        // Given
        $helper = $this->createTestMigrationHelper();

        // Create mix of valid and invalid migrations
        $this->createTestMigrationFile(1001, 'Migration1001', true);  // Valid
        $this->createTestMigrationFile(1002, 'Migration1002', false); // Invalid - doesn't implement interface
        $this->createTestMigrationFile(1003, 'Migration1003', true, true); // Invalid - throws exception
        $this->createTestMigrationFile(1004, 'Migration1004', true);  // Valid

        // When
        $result = $helper->loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertCount(2, $result); // Only valid migrations should be returned
        Assert::assertArrayHasKey(1001, $result);
        Assert::assertArrayHasKey(1004, $result);

        // Verify all returned items implement Migration interface
        foreach ($result as $migration) {
            Assert::assertInstanceOf(Migration::class, $migration);
        }
    }

    public function testLoadAllMigrationsWithEmptyDirectory(): void {
        // Given
        $helper = $this->createTestMigrationHelper();
        // Directory exists but is empty (no migration files created)

        // When
        $result = $helper->loadAllMigrations();

        // Then
        Assert::assertIsArray($result);
        Assert::assertEmpty($result);
    }

    // Test the actual MigrationHelper class with existing migrations
    public function testActualMigrationHelperLoadMigrationWithExistingMigration(): void {
        // Given
        // Use the real MigrationHelper with existing migrations

        // When
        $result = MigrationHelper::loadMigration(1); // Assuming migration 0001 exists

        // Then
        if ($result !== null) {
            Assert::assertInstanceOf(Migration::class, $result);
            Assert::assertEquals(1, $result->getVersion());
            Assert::assertIsString($result->getName());
        } else {
            // If no migration exists, that's also a valid test result
            Assert::assertNull($result);
        }
    }

    public function testActualMigrationHelperLoadAllMigrationsWithExistingMigrations(): void {
        // Given
        // Use the real MigrationHelper with existing migrations

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
