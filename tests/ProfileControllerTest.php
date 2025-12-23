<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\DashboardController;
use Cronbeat\Controllers\ProfileController;
use Cronbeat\RedirectException;
use PHPUnit\Framework\Assert;

class ProfileControllerTest extends DatabaseTestCase {
    private ?ProfileController $controller = null;
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

        $this->controller = new ProfileController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    private function getController(): ProfileController {
        if ($this->controller === null) {
            throw new \RuntimeException('Controller not initialized');
        }
        return $this->controller;
    }

    public function testDoRoutingRedirectsToLoginWhenUnauthenticated(): void {
        // Given
        $_SESSION = [];
        $controller = new ProfileController($this->getDatabase());

        // When
        try {
            $controller->doRouting();
            Assert::fail('Expected RedirectException was not thrown');
        } catch (RedirectException $e) {
            // Then
            $headers = $e->getHeaders();
            Assert::assertArrayHasKey('Location', $headers);
            Assert::assertSame('/login', $headers['Location']);
        }
    }

    public function testShowProfileDisplaysCurrentValues(): void {
        // Given
        $this->getDatabase()->updateUserProfile($this->userId, 'Jane Doe', 'jane@example.com');

        // When
        $output = $this->getController()->showProfile();

        // Then
        Assert::assertStringContainsString('Jane Doe', $output);
        Assert::assertStringContainsString('jane@example.com', $output);
        Assert::assertStringContainsString($this->username, $output);
    }

    public function testUpdateProfileWithInvalidEmailShowsError(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'John Doe';
        $_POST['email'] = 'invalid-email';

        // When
        $output = $this->getController()->updateProfile();

        // Then
        Assert::assertStringContainsString('Please provide a valid email address', $output);
    }

    public function testChangePasswordUpdatesPassword(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $newPasswordHash = hash('sha256', 'newpassword');
        $_POST['password_hash'] = $newPasswordHash;

        // When
        $output = $this->getController()->changePassword();

        // Then
        Assert::assertStringContainsString('Password updated successfully', $output);

        $validatedId = $this->getDatabase()->validateUser($this->username, $newPasswordHash);
        Assert::assertSame($this->userId, $validatedId);
    }

    public function testDashboardContainsProfileLink(): void {
        // Given
        $dashboard = new DashboardController($this->getDatabase());

        // When
        $output = $dashboard->showDashboard();

        // Then
        Assert::assertStringContainsString('/profile', $output);
    }
}
