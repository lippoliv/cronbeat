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
        
        // Set a constant to override the DB_PATH in the Database class
        if (!defined('TEST_DB_PATH')) {
            define('TEST_DB_PATH', $this->testDbPath);
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up test database if it exists
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
        
        // Clean up parent directory if it was created
        $dbDir = dirname($this->testDbPath);
        if (is_dir($dbDir) && basename($dbDir) === 'db') {
            rmdir($dbDir);
        }
    }
    
    /**
     * Test that exists() returns false when database doesn't exist
     */
    public function testExistsReturnsFalseWhenDatabaseDoesNotExist(): void
    {
        // Given the database file doesn't exist
        $this->assertFileDoesNotExist($this->testDbPath);
        
        // When checking if the database exists
        $result = $this->callExistsWithTestPath();
        
        // Then it should return false
        $this->assertFalse($result);
    }
    
    /**
     * Test that exists() returns true when database exists
     */
    public function testExistsReturnsTrueWhenDatabaseExists(): void
    {
        // Given the database file exists
        $dbDir = dirname($this->testDbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        file_put_contents($this->testDbPath, ''); // Create empty file
        $this->assertFileExists($this->testDbPath);
        
        // When checking if the database exists
        $result = $this->callExistsWithTestPath();
        
        // Then it should return true
        $this->assertTrue($result);
    }
    
    /**
     * Test that getConnection() creates a PDO connection
     */
    public function testGetConnectionCreatesPdoConnection(): void
    {
        // Given a Database instance
        $database = $this->createDatabaseWithTestPath();
        
        // When getting the connection
        $connection = $database->getConnection();
        
        // Then it should return a PDO instance
        $this->assertInstanceOf(\PDO::class, $connection);
        
        // And the database file should exist
        $this->assertFileExists($this->testDbPath);
    }
    
    /**
     * Test that initialize() creates the required tables
     */
    public function testInitializeCreatesRequiredTables(): void
    {
        // Given a Database instance
        $database = $this->createDatabaseWithTestPath();
        
        // When initializing the database
        $result = $database->initialize();
        
        // Then it should return true
        $this->assertTrue($result);
        
        // And the users table should exist
        $connection = $database->getConnection();
        $stmt = $connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        $this->assertNotFalse($stmt);
        $table = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals('users', $table['name']);
    }
    
    /**
     * Test that createUser() adds a user to the database
     */
    public function testCreateUserAddsUserToDatabase(): void
    {
        // Given a Database instance with initialized tables
        $database = $this->createDatabaseWithTestPath();
        $database->initialize();
        
        // When creating a user
        $username = 'testuser';
        $password = 'password123';
        $result = $database->createUser($username, $password);
        
        // Then it should return true
        $this->assertTrue($result);
        
        // And the user should exist in the database
        $connection = $database->getConnection();
        $stmt = $connection->prepare('SELECT username FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals($username, $user['username']);
    }
    
    /**
     * Test that createUser() properly hashes the password
     */
    public function testCreateUserHashesPassword(): void
    {
        // Given a Database instance with initialized tables
        $database = $this->createDatabaseWithTestPath();
        $database->initialize();
        
        // When creating a user
        $username = 'testuser';
        $password = 'password123';
        $database->createUser($username, $password);
        
        // Then the password should be hashed in the database
        $connection = $database->getConnection();
        $stmt = $connection->prepare('SELECT password FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // The password should be hashed (not stored as plaintext)
        $this->assertNotEquals($password, $user['password']);
        
        // The hashed password should be verifiable
        $this->assertTrue(password_verify($password, $user['password']));
    }
    
    /**
     * Helper method to call exists() with test path
     */
    private function callExistsWithTestPath(): bool
    {
        // Use reflection to call exists() with our test path
        $reflectionClass = new \ReflectionClass(Database::class);
        $existsMethod = $reflectionClass->getMethod('exists');
        $existsMethod->setAccessible(true);
        
        // Override the DB_PATH constant
        $reflectionProperty = $reflectionClass->getProperty('DB_PATH');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, $this->testDbPath);
        
        return $existsMethod->invoke(null);
    }
    
    /**
     * Helper method to create a Database instance with test path
     */
    private function createDatabaseWithTestPath(): Database
    {
        // Create a Database instance
        $database = new Database();
        
        // Use reflection to set the DB_PATH
        $reflectionClass = new \ReflectionClass(Database::class);
        $reflectionProperty = $reflectionClass->getProperty('DB_PATH');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($database, $this->testDbPath);
        
        return $database;
    }
}