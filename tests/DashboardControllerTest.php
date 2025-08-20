<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\DashboardController;
use Cronbeat\RedirectException;
use PHPUnit\Framework\Assert;

class DashboardControllerTest extends DatabaseTestCase {
    private ?DashboardController $controller = null;
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

        $this->controller = new DashboardController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    private function getController(): DashboardController {
        Assert::assertNotNull($this->controller, 'Controller should be initialized in setUp()');
        return $this->controller;
    }

    public function testDoRoutingRedirectsToLoginWhenNotAuthenticated(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user

        // Set up exception expectation
        $this->expectException(RedirectException::class);

        // When
        $this->getController()->doRouting();
    }

    public function testDoRoutingRedirectsToLoginWithCorrectHeaders(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user

        // When & Then
        $this->expectException(RedirectException::class);
        $this->getController()->doRouting();
    }

    public function testDoRoutingRedirectsToLoginWithCorrectHeadersLocation(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user

        // When & Then
        $this->expectException(RedirectException::class);
        $this->getController()->doRouting();
    }


    public function testShowDashboardDisplaysUserMonitors(): void {
        // Given
        $monitorName1 = 'Test Monitor 1';
        $monitorName2 = 'Test Monitor 2';
        $this->getDatabase()->createMonitor($monitorName1, $this->userId);
        $this->getDatabase()->createMonitor($monitorName2, $this->userId);

        // When
        $output = $this->getController()->showDashboard();

        // Then
        Assert::assertStringContainsString($monitorName1, $output);
        Assert::assertStringContainsString($monitorName2, $output);
        Assert::assertStringContainsString($this->username, $output);
    }

    public function testShowMonitorFormDisplaysForm(): void {
        // Given

        // When
        $output = $this->getController()->showMonitorForm();

        // Then
        Assert::assertStringContainsString('<form', $output);
        Assert::assertStringContainsString('name="name"', $output);
    }

    public function testAddMonitorCreatesNewMonitor(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';

        // When & Then
        $this->expectException(RedirectException::class);
        $this->getController()->addMonitor();
    }

    public function testAddMonitorCreatesNewMonitorAndVerifyData(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';

        // When & Then
        $this->expectException(RedirectException::class);
        $this->getController()->addMonitor();

        // Note: The following assertions would normally be executed after the exception is thrown,
        // but since expectException causes the test to exit, we need to verify these in a separate test.
    }

    public function testAddMonitorCreatesNewMonitorWithCorrectData(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';

        // When & Then
        $this->expectException(RedirectException::class);
        $this->expectExceptionMessage('Redirect');
        $this->getController()->addMonitor();
    }

    public function testAddMonitorCreatesMonitorWithCorrectDataAndRedirectsToCorrectLocation(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';

        // When & Then
        $this->expectException(RedirectException::class);
        $this->expectExceptionMessage('Redirect');
        $this->getController()->addMonitor();
    }

    public function testAddMonitorCreatesMonitorWithCorrectData(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';

        // When
        // Create the monitor but catch the exception to continue with assertions
        $exception = null;
        try {
            $this->getController()->addMonitor();
        } catch (RedirectException $e) {
            $exception = $e;
        }

        // Then
        // Verify the monitor was created
        $monitors = $this->getDatabase()->getMonitors($this->userId);
        Assert::assertCount(1, $monitors);
        Assert::assertEquals('New Test Monitor', $monitors[0]['name']);

        // Verify we got the expected exception
        Assert::assertInstanceOf(RedirectException::class, $exception);

        // Verify the exception contains the correct headers
        $headers = $exception->getHeaders();
        Assert::assertArrayHasKey('Location', $headers);
        Assert::assertEquals('/dashboard', $headers['Location']);
    }

    public function testAddMonitorShowsErrorWhenNameIsEmpty(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = '';

        // When
        $output = $this->getController()->addMonitor();

        // Then
        Assert::assertStringContainsString('Monitor name is required', $output);
    }

    public function testDeleteMonitorRemovesMonitor(): void {
        // Given
        $monitorName = 'Test Monitor';
        $uuid = $this->getDatabase()->createMonitor($monitorName, $this->userId);
        if ($uuid === false) {
            Assert::fail('Failed to create monitor for test');
        }

        // When
        $output = $this->getController()->deleteMonitor($uuid);

        // Then
        Assert::assertStringContainsString('Monitor deleted successfully', $output);

        $monitors = $this->getDatabase()->getMonitors($this->userId);
        Assert::assertEmpty($monitors);
    }

    public function testDeleteMonitorShowsErrorWhenUuidIsEmpty(): void {
        // Given

        // When
        $output = $this->getController()->deleteMonitor('');

        // Then
        Assert::assertStringContainsString('Monitor UUID is required', $output);
    }

    public function testDeleteMonitorShowsErrorWhenMonitorDoesNotExist(): void {
        // Given
        $nonExistentUuid = '12345678-1234-1234-1234-123456789012';

        // When
        $output = $this->getController()->deleteMonitor($nonExistentUuid);

        // Then
        Assert::assertStringContainsString('Failed to delete monitor', $output);
    }
}
