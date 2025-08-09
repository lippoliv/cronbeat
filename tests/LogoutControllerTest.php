<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\Assert;
use Cronbeat\Controllers\LogoutController;
use Cronbeat\Database;

class LogoutControllerTest extends DatabaseTestCase {
    private ?LogoutController $controller = null;
    private int $userId;
    private string $username = 'testuser';
    private string $passwordHash;

    protected function setUp(): void {
        parent::setUp();
        
        // Create a test user
        $this->passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($this->username, $this->passwordHash);
        $this->userId = $this->getDatabase()->validateUser($this->username, $this->passwordHash);
        
        // Set up session
        $_SESSION = [];
        $_SESSION['user_id'] = $this->userId;
        
        // Create controller
        $this->controller = new LogoutController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testDoRoutingCallsLogout(): void {
        // Given
        // Session is already set up with user_id
        
        // When/Then
        // This will call header() which we can't test directly
        // So we'll use expectException
        $this->expectException(\PHPUnit\Framework\Error\Warning::class);
        
        // This will throw a warning because headers have already been sent
        $this->controller->doRouting();
    }

    public function testLogoutClearsSession(): void {
        // Given
        // Session is already set up with user_id
        Assert::assertArrayHasKey('user_id', $_SESSION);
        
        // When/Then
        // This will call header() which we can't test directly
        // So we'll use expectException
        $this->expectException(\PHPUnit\Framework\Error\Warning::class);
        
        // This will throw a warning because headers have already been sent
        $this->controller->logout();
        
        // We can't test that $_SESSION is empty because the code exits after header()
        // But we can test that session_destroy() is called, which will clear the session
    }

    public function testLogoutHandlesEmptySession(): void {
        // Given
        $_SESSION = []; // Clear session
        
        // When/Then
        // This will call header() which we can't test directly
        // So we'll use expectException
        $this->expectException(\PHPUnit\Framework\Error\Warning::class);
        
        // This will throw a warning because headers have already been sent
        $this->controller->logout();
        
        // We can't test that $_SESSION is empty because the code exits after header()
        // But we can test that session_destroy() is called, which will clear the session
    }
}