<?php

namespace Cronbeat\Tests;

require_once __DIR__ . '/../src/controllers/BaseController.php';
require_once __DIR__ . '/../src/views/base.view.php';
require_once __DIR__ . '/../src/views/setup.view.php';
require_once __DIR__ . '/../src/controllers/SetupController.php';

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Database;

class SetupControllerTest extends TestCase {
    private string $tempDbPath = '';
    private ?SetupController $controller = null;
    private ?Database $database = null;

    protected function setUp(): void {
        parent::setUp();
        $this->tempDbPath = sys_get_temp_dir() . '/test_cronbeat_' . uniqid() . '.sqlite';
        $this->database = new Database($this->tempDbPath);
        $this->controller = new SetupController($this->database);
    }

    private function cleanupTestDatabase(string $tempDbPath): void {
        if (file_exists($tempDbPath)) {
            unlink($tempDbPath);
        }
    }

    protected function tearDown(): void {
        $this->cleanupTestDatabase($this->tempDbPath);
        parent::tearDown();
    }

    public function testValidateSetupDataAcceptsValidInput(): void {
        // Given
        $username = 'admin';
        $passwordHash = hash('sha256', 'password123');

        // When
        assert($this->controller !== null);
        $result = $this->controller->validateSetupData($username, $passwordHash);

        // Then
        Assert::assertNull($result, "Valid input should return null");
    }

    public function testValidateSetupDataRejectsEmptyUsername(): void {
        // Given
        $username = '';
        $passwordHash = hash('sha256', 'password123');

        // When
        assert($this->controller !== null);
        $result = $this->controller->validateSetupData($username, $passwordHash);

        // Then
        Assert::assertEquals('Username and password are required', $result);
    }

    public function testValidateSetupDataRejectsTooShortUsername(): void {
        // Given
        $username = 'ab';
        $passwordHash = hash('sha256', 'password123');

        // When
        assert($this->controller !== null);
        $result = $this->controller->validateSetupData($username, $passwordHash);

        // Then
        Assert::assertEquals('Username must be at least 3 characters', $result);
    }

    public function testRunSetupCreatesUserSuccessfully(): void {
        // Given
        $username = 'admin';
        $passwordHash = hash('sha256', 'password123');

        // When
        assert($this->controller !== null);
        $result = $this->controller->runSetup($username, $passwordHash);

        // Then
        Assert::assertNull($result, "Setup should succeed with valid data");
        assert($this->database !== null);
        Assert::assertTrue($this->database->userExists($username), "User should be created in database");
    }

    public function testRunSetupHandlesDatabaseErrors(): void {
        // Given
        $invalidDbPath = '/invalid/path/that/does/not/exist/test.sqlite';
        $invalidDatabase = new Database($invalidDbPath);

        // When/Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create database directory: /invalid/path/that/does/not/exist');

        $invalidDatabase->createDatabase();
    }
}
