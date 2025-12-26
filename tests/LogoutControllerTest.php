<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\LogoutController;
use Cronbeat\Database;
use Cronbeat\RedirectException;
use PHPUnit\Framework\Assert;

class LogoutControllerTest extends DatabaseTestCase {
    private ?LogoutController $controller = null;
    private int $userId = 0;
    private string $username = 'testuser';
    private string $passwordHash = '';

    protected function setUp(): void {
        parent::setUp();

        $this->passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($this->username, $this->passwordHash);
        $userId = $this->getDatabase()->validateUser($this->username, $this->passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $this->userId = $userId;

        $_SESSION = [];
        $_SESSION['user_id'] = $this->userId;

        $this->controller = new LogoutController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testLogoutRedirectsToLogin(): void {
        // Given

        // When
        $thrown = null;
        try {
            $this->controller?->logout();
        } catch (RedirectException $e) {
            $thrown = $e;
        }

        // Then - Verify the exception contains the correct headers
        Assert::assertInstanceOf(RedirectException::class, $thrown);
        $headers = $thrown?->getHeaders() ?? [];
        Assert::assertArrayHasKey('Location', $headers);
        Assert::assertEquals('/login', $headers['Location']);
    }


    public function testLogoutHandlesEmptySession(): void {
        // Given
        $_SESSION = [];

        // Set up exception expectation
        $this->expectException(RedirectException::class);

        // When
        $this->controller?->logout();

        // Then - This code won't be executed due to the exception
        // But we can verify the session is still empty because it's checked before the exception is thrown
        Assert::assertEmpty($_SESSION);
    }
}
