<?php

namespace Cronbeat\Tests;


use PHPUnit\Framework\Assert;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Database;

class SetupControllerTest extends DatabaseTestCase {
    private ?SetupController $controller = null;

    protected function setUp(): void {
        parent::setUp();
        $this->controller = new SetupController($this->getDatabase());
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
