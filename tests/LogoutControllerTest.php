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
        try {
            $this->controller->doRouting();
            $this->fail('Expected RedirectException was not thrown');
        } catch (RedirectException $e) {
// Verify the exception contains the correct headers
            $headers = $e->getHeaders();
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('/login', $headers['Location']);
        }
    }

    public function testLogoutClearsSession(): void {
        // Given
        Assert::assertArrayHasKey('user_id', $_SESSION);

        // When/Then
        try {
            $this->controller->logout();
            $this->fail('Expected RedirectException was not thrown');
} catch (RedirectException $e) {
            // Verify the session is cleared before the exception is thrown
            $this->assertEmpty($_SESSION);

        // Verify the exception contains the correct headers
            $headers = $e->getHeaders();
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('/login', $headers['Location']);
        }
    }


    public function testLogoutHandlesEmptySession(): void {
        // Given
        $_SESSION = [];

        // When/Then
        try {
            $this->controller->logout();
            $this->fail('Expected RedirectException was not thrown');
        } catch (RedirectException $e) {
            // Verify the session is still empty
            $this->assertEmpty($_SESSION);

            // Verify the exception contains the correct headers
            $headers = $e->getHeaders();
            $this->assertArrayHasKey(
                'Location',
                $headers
            );
            $this->assertEquals(
                '/login',
                $headers['Location']
            );
        }
    }
}
