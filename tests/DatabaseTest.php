<?php

namespace Cronbeat\Tests;

use Cronbeat\Database;
use PHPUnit\Framework\Assert;

class DatabaseTest extends DatabaseTestCase {

    public function testDatabaseDoesNotExistInitially(): void {
        // Given
        $tempPath = sys_get_temp_dir() . '/nonexistent_' . uniqid() . '.sqlite';
        $database = new Database($tempPath);

        // When
        $exists = $database->databaseExists();

        // Then
        Assert::assertFalse($exists);
    }

    public function testDatabaseExistsAfterCreation(): void {
        // Given
        // Database is already created in setUp()

        // When
        $exists = $this->getDatabase()->databaseExists();

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

        // Cleanup
        if (file_exists($testDbPath)) {
            unlink($testDbPath);
        }
        if (is_dir($testDbDir)) {
            rmdir($testDbDir);
        }
    }

    public function testCreateUserReturnsTrue(): void {
        // Given
        // Database and migrations are already set up in setUp()

        // When
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $result = $this->getDatabase()->createUser($username, $passwordHash);

        // Then
        Assert::assertTrue($result);
    }

    public function testCreatedUserExists(): void {
        // Given
        // Database and migrations are already set up in setUp()
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $userExists = $this->getDatabase()->userExists($username);

        // Then
        Assert::assertTrue($userExists);
    }

    public function testValidateUserWithCorrectCredentials(): void {
        // Given
        // Database and migrations are already set up in setUp()
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $validResult = $this->getDatabase()->validateUser($username, $passwordHash);

        // Then
        Assert::assertTrue($validResult);
    }

    public function testValidateUserWithIncorrectPassword(): void {
        // Given
        // Database and migrations are already set up in setUp()
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $invalidResult = $this->getDatabase()->validateUser($username, 'wronghash');

        // Then
        Assert::assertFalse($invalidResult);
    }

    public function testValidateUserWithNonExistentUser(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $nonExistentResult = $this->getDatabase()->validateUser('nonexistent', $passwordHash);

        // Then
        Assert::assertFalse($nonExistentResult);
    }
}
