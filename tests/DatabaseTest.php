<?php

namespace Cronbeat\Tests;

use Cronbeat\Database;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase {
    private $testDbPath;

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

    public function testDatabaseDoesNotExistInitially() {
        // Given
        $database = new Database($this->testDbPath);

        // When
        $exists = $database->databaseExists();

        // Then
        $this->assertFalse($exists);
    }

    public function testDatabaseExistsAfterCreation() {
        // Given
        $database = new Database($this->testDbPath);
        touch($this->testDbPath);

        // When
        $exists = $database->databaseExists();

        // Then
        $this->assertTrue($exists);
    }

    public function testCreateDatabase() {
        // Given
        $testDbDir = sys_get_temp_dir() . '/cronbeat_test_dir';
        $testDbPath = $testDbDir . '/test.sqlite';
        $database = new Database($testDbPath);

        // When
        $result = $database->createDatabase();

        // Then
        $this->assertTrue($result);
        $this->assertTrue(file_exists($testDbPath));
        $this->assertTrue(is_dir($testDbDir));
    }

    public function testCreateUserReturnsTrue() {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();

        // When
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $result = $database->createUser($username, $passwordHash);

        // Then
        $this->assertTrue($result);
    }

    public function testCreatedUserExists() {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);
        
        // When
        $userExists = $database->userExists($username);
        
        // Then
        $this->assertTrue($userExists);
    }

    public function testValidateUserWithCorrectCredentials() {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $validResult = $database->validateUser($username, $passwordHash);

        // Then
        $this->assertTrue($validResult);
    }

    public function testValidateUserWithIncorrectPassword() {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $invalidResult = $database->validateUser($username, 'wronghash');

        // Then
        $this->assertFalse($invalidResult);
    }

    public function testValidateUserWithNonExistentUser() {
        // Given
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);

        // When
        $nonExistentResult = $database->validateUser('nonexistent', $passwordHash);

        // Then
        $this->assertFalse($nonExistentResult);
    }
}
