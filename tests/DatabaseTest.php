<?php

namespace Cronbeat\Tests;

use Cronbeat\Database;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private $testDbPath;
    
    protected function setUp(): void
    {
        // Create a temporary test database path
        $this->testDbPath = sys_get_temp_dir() . '/cronbeat_test_' . uniqid() . '.sqlite';
    }
    
    protected function tearDown(): void
    {
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
    
    public function testDatabaseDoesNotExistInitially()
    {
        // Given a database path that doesn't exist
        $database = new Database($this->testDbPath);
        
        // When checking if the database exists
        $exists = $database->databaseExists();
        
        // Then it should return false
        $this->assertFalse($exists);
    }
    
    public function testDatabaseExistsAfterCreation()
    {
        // Given a database path and we create the file
        $database = new Database($this->testDbPath);
        touch($this->testDbPath);
        
        // When checking if the database exists
        $exists = $database->databaseExists();
        
        // Then it should return true
        $this->assertTrue($exists);
    }
    
    public function testCreateDatabaseReturnsTrue()
    {
        // Given a database path in a directory that doesn't exist
        $testDbDir = sys_get_temp_dir() . '/cronbeat_test_dir';
        $testDbPath = $testDbDir . '/test.sqlite';
        $database = new Database($testDbPath);
        
        // When creating the database
        $result = $database->createDatabase();
        
        // Then it should return true
        $this->assertTrue($result);
    }
    
    public function testCreateDatabaseCreatesFile()
    {
        // Given a database path in a directory that doesn't exist
        $testDbDir = sys_get_temp_dir() . '/cronbeat_test_dir';
        $testDbPath = $testDbDir . '/test.sqlite';
        $database = new Database($testDbPath);
        
        // When creating the database
        $database->createDatabase();
        
        // Then it should create the database file
        $this->assertTrue(file_exists($testDbPath));
    }
    
    public function testCreateDatabaseCreatesDirectory()
    {
        // Given a database path in a directory that doesn't exist
        $testDbDir = sys_get_temp_dir() . '/cronbeat_test_dir';
        $testDbPath = $testDbDir . '/test.sqlite';
        $database = new Database($testDbPath);
        
        // When creating the database
        $database->createDatabase();
        
        // Then it should create the directory
        $this->assertTrue(is_dir($testDbDir));
    }
    
    public function testCreateUserReturnsTrue()
    {
        // Given a new database
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        
        // When creating a user
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $result = $database->createUser($username, $passwordHash);
        
        // Then it should return true
        $this->assertTrue($result);
    }
    
    public function testCreatedUserExists()
    {
        // Given a new database
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        
        // When creating a user
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);
        
        // Then the user should exist
        $this->assertTrue($database->userExists($username));
    }
    
    public function testValidateUserWithCorrectCredentials()
    {
        // Given a database with a user
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);
        
        // When validating with correct credentials
        $validResult = $database->validateUser($username, $passwordHash);
        
        // Then it should return true
        $this->assertTrue($validResult);
    }
    
    public function testValidateUserWithIncorrectPassword()
    {
        // Given a database with a user
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);
        
        // When validating with incorrect password
        $invalidResult = $database->validateUser($username, 'wronghash');
        
        // Then it should return false
        $this->assertFalse($invalidResult);
    }
    
    public function testValidateUserWithNonExistentUser()
    {
        // Given a database with a user
        $database = new Database($this->testDbPath);
        $database->createDatabase();
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $database->createUser($username, $passwordHash);
        
        // When validating with non-existent user
        $nonExistentResult = $database->validateUser('nonexistent', $passwordHash);
        
        // Then it should return false
        $this->assertFalse($nonExistentResult);
    }
}