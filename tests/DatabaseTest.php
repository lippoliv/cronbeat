<?php

namespace Cronbeat\Tests;

use Cronbeat\Database;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

class DatabaseTest extends TestCase {
    private string $testDbPath = '';

    protected function setUp(): void {
        // Create a temporary test database path
        $this->testDbPath = sys_get_temp_dir() . '/cronbeat_test_' . uniqid() . '.sqlite';
    }

    protected function tearDown(): void {
        // Clean up test database if it exists
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }

        // Clean up parent directory if it was created
        $testDbDir = dirname($this->testDbPath);
        if (is_dir($testDbDir) && basename($testDbDir) === 'cronbeat_test_dir') {
            rmdir($testDbDir);
        }
    }

    public function testDatabaseDoesNotExistInitially(): void {
        // Given
        $database = new Database($this->testDbPath);

        // When
        $exists = $database->databaseExists();

        // Then
        Assert::assertFalse($exists);
    }

    public function testDatabaseExistsAfterCreation(): void {
        // Given
        $database = new Database($this->testDbPath);
        touch($this->testDbPath);

        // When
        $exists = $database->databaseExists();

        // Then
        Assert::assertTrue($exists);
    }

    public function testCreateDatabase(): void {
        // Given
        $testDbDir = sys_get_temp_dir() . '/cronbeat_test_dir';
        $testDbPath = $testDbDir . '/test.sqlite';
        $database = new Database($testDbPath);

        // When
        $result = $database->createDatabase();

        // Then
        Assert::assertTrue($result);
        Assert::assertTrue(file_exists($testDbPath));
        Assert::assertTrue(is_dir($testDbDir));
    }

    public function testCreateUserReturnsTrue(): void {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();

        // When
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $result = $database->createUser($username, $passwordHash);

        // Then
        Assert::assertTrue($result);
    }

    public function testCreatedUserExists(): void {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $userExists = $database->userExists($username);

        // Then
        Assert::assertTrue($userExists);
    }

    public function testValidateUserWithCorrectCredentials(): void {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $validResult = $database->validateUser($username, $passwordHash);

        // Then
        Assert::assertTrue($validResult);
    }

    public function testValidateUserWithIncorrectPassword(): void {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $invalidResult = $database->validateUser($username, 'wronghash');

        // Then
        Assert::assertFalse($invalidResult);
    }

    public function testValidateUserWithNonExistentUser(): void {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $nonExistentResult = $database->validateUser('nonexistent', $passwordHash);

        // Then
        Assert::assertFalse($nonExistentResult);
    }
}
