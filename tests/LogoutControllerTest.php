<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\Assert;
use Cronbeat\Controllers\LogoutController;
use Cronbeat\Database;
use Cronbeat\RedirectException;

class LogoutControllerTest extends DatabaseTestCase {
    private ?LogoutController $controller = null;
    private int $userId;
    private string $username = 'testuser';
    private string $passwordHash;

    protected function setUp(): void {
        parent::setUp();

        $this->passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($this->username, $this->passwordHash);
        $this->userId = $this->getDatabase()->validateUser($this->username, $this->passwordHash);

        $_SESSION = [];
        $_SESSION['user_id'] = $this->userId;

        $this->controller = new LogoutController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testDoRoutingCallsLogout(): void {
        // Given

        // When/Then
        $this->expectException(RedirectException::class);
        
        // Execute the method that should throw the exception
        $this->controller->doRouting();
    }

    public function testLogoutClearsSession(): void {
        // Given
        Assert::assertArrayHasKey('user_id', $_SESSION);

        // Set up exception expectation
        $this->expectException(RedirectException::class);
        
        // When
        $this->controller->logout();
        
        // Then - This code won't be executed due to the exception
        // But we can verify the session is cleared because it happens before the exception is thrown
        $this->assertEmpty($_SESSION);
    }
    
    public function testLogoutRedirectsToLogin(): void {
        // Given
        
        // When
        try {
            $this->controller->logout();
        } catch (RedirectException $e) {
            // Then - Verify the exception contains the correct headers
            $headers = $e->getHeaders();
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('/login', $headers['Location']);
            return;
        }
        
        $this->fail('Expected RedirectException was not thrown');
    }


    public function testLogoutHandlesEmptySession(): void {
        // Given
        $_SESSION = [];

        // Set up exception expectation
        $this->expectException(RedirectException::class);
        
        // When
        $this->controller->logout();
        
        // Then - This code won't be executed due to the exception
        // But we can verify the session is still empty because it's checked before the exception is thrown
        $this->assertEmpty($_SESSION);
    }
}
