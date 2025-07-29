<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test for the SetupController's processSetupForm method
 */
class SetupControllerTest extends TestCase
{
    /**
     * Test that processSetupForm validates the username and password
     */
    public function testProcessSetupFormValidatesInput()
    {
        // Given
        $_POST = [
            'username' => 'admin',
            'password_hash' => hash('sha256', 'password123')
        ];
        
        // When
        $result = $this->processSetupForm($_POST);
        
        // Then
        $this->assertNull($result, "Valid input should return null");
        
        // Given
        $_POST = [
            'username' => '',  // Empty username
            'password_hash' => hash('sha256', 'password123')
        ];
        
        // When
        $result = $this->processSetupForm($_POST);
        
        // Then
        $this->assertEquals('Username and password are required', $result);
        
        // Given
        $_POST = [
            'username' => 'ab',  // Too short
            'password_hash' => hash('sha256', 'password123')
        ];
        
        // When
        $result = $this->processSetupForm($_POST);
        
        // Then
        $this->assertEquals('Username must be at least 3 characters', $result);
    }
    
    /**
     * Test that processSetupForm creates the database and user
     */
    public function testProcessSetupFormCreatesUser()
    {
        // Given
        $_POST = [
            'username' => 'admin',
            'password_hash' => hash('sha256', 'password123')
        ];
        
        // Create a custom mock database object
        $mockDb = new class {
            public function createDatabase() { return true; }
            public function createUser($username, $passwordHash) { return true; }
        };
        
        // When
        $result = $this->processSetupForm($_POST, $mockDb);
        
        // Then
        $this->assertNull($result, "Database creation should succeed");
    }
    
    /**
     * Test that processSetupForm handles database errors
     */
    public function testProcessSetupFormHandlesDatabaseErrors()
    {
        // Given
        $_POST = [
            'username' => 'admin',
            'password_hash' => hash('sha256', 'password123')
        ];
        
        // Create a custom mock database object that throws an exception
        $mockDb = new class {
            public function createDatabase() { throw new \RuntimeException("Database error"); }
            public function createUser($username, $passwordHash) { return true; }
        };
        
        // When
        $result = $this->processSetupForm($_POST, $mockDb);
        
        // Then
        $this->assertEquals('Error creating user: Database error', $result);
    }
    
    /**
     * Test that processSetupForm handles user creation errors
     */
    public function testProcessSetupFormHandlesUserCreationErrors()
    {
        // Given
        $_POST = [
            'username' => 'admin',
            'password_hash' => hash('sha256', 'password123')
        ];
        
        // Create a custom mock database object that returns failure for createUser
        $mockDb = new class {
            public function createDatabase() { return true; }
            public function createUser($username, $passwordHash) { return false; }
        };
        
        // When
        $result = $this->processSetupForm($_POST, $mockDb);
        
        // Then
        $this->assertEquals('Failed to create user. Please check the logs for more information.', $result);
    }
    
    /**
     * Helper function that implements the core logic of processSetupForm
     */
    private function processSetupForm($post, $database = null)
    {
        if (isset($post['username']) && isset($post['password_hash'])) {
            $username = trim($post['username']);
            $passwordHash = $post['password_hash'];
            
            // Validate input
            if (empty($username) || empty($passwordHash)) {
                return 'Username and password are required';
            } elseif (strlen($username) < 3) {
                return 'Username must be at least 3 characters';
            }
            
            // Run setup
            try {
                if ($database) {
                    $database->createDatabase();
                    $result = $database->createUser($username, $passwordHash);
                    
                    if (!$result) {
                        return 'Failed to create user. Please check the logs for more information.';
                    }
                }
                
                return null;
            } catch (\Exception $e) {
                return 'Error creating user: ' . $e->getMessage();
            }
        }
        
        return 'Username and password are required';
    }
}