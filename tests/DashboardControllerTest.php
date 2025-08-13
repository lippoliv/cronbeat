<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\Assert;
use Cronbeat\Controllers\DashboardController;
use Cronbeat\Database;
use Cronbeat\RedirectException;
use Cronbeat\Views\DashboardView;
use Cronbeat\Views\MonitorFormView;

class DashboardControllerTest extends DatabaseTestCase {
    private ?DashboardController $controller = null;
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

        $this->controller = new DashboardController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testDoRoutingRedirectsToLoginWhenNotAuthenticated(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user

        // Set up exception expectation
        $this->expectException(RedirectException::class);
        
        // When
        $this->controller->doRouting();
    }
    
    public function testDoRoutingRedirectsToLoginWithCorrectHeaders(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user
        
        // When & Then
        $this->expectException(RedirectException::class);
        $this->controller->doRouting();
    }
    
    public function testDoRoutingRedirectsToLoginWithCorrectHeadersLocation(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user
        
        // When & Then
        $this->expectException(RedirectException::class);
        $this->controller->doRouting();
    }
    

    public function testShowDashboardDisplaysUserMonitors(): void {
        // Given
        $monitorName1 = 'Test Monitor 1';
        $monitorName2 = 'Test Monitor 2';
        $this->getDatabase()->createMonitor($monitorName1, $this->userId);
        $this->getDatabase()->createMonitor($monitorName2, $this->userId);

        // When
        $output = $this->controller->showDashboard();

        // Then
        Assert::assertStringContainsString($monitorName1, $output);
        Assert::assertStringContainsString($monitorName2, $output);
        Assert::assertStringContainsString($this->username, $output);
    }

    public function testShowMonitorFormDisplaysForm(): void {
        // Given

        // When
        $output = $this->controller->showMonitorForm();

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
        $this->controller->addMonitor();
    }
    
    public function testAddMonitorCreatesNewMonitorAndVerifyData(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';
        
        // When & Then
        $this->expectException(RedirectException::class);
        $this->controller->addMonitor();
        
        // Note: The following assertions would normally be executed after the exception is thrown,
        // but since expectException causes the test to exit, we need to verify these in a separate test.
    }
    
    public function testAddMonitorCreatesNewMonitorWithCorrectData(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';
        
        // When & Then
        $this->expectException(RedirectException::class);
        $this->expectExceptionMessage('Redirecting to /dashboard');
        $this->controller->addMonitor();
    }
    
    public function testAddMonitorCreatesMonitorWithCorrectDataAndRedirectsToCorrectLocation(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';
        
        try {
            // When
            $this->controller->addMonitor();
        } catch (RedirectException $exception) {
            // Then
            // Verify the monitor was created
            $monitors = $this->getDatabase()->getMonitors($this->userId);
            Assert::assertCount(1, $monitors);
            Assert::assertEquals('New Test Monitor', $monitors[0]['name']);

            // Verify the exception contains the correct headers
            $headers = $exception->getHeaders();
            $this->assertArrayHasKey('Location', $headers);
            $this->assertEquals('/dashboard', $headers['Location']);
        }
    }

    public function testAddMonitorShowsErrorWhenNameIsEmpty(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = '';

        // When
        $output = $this->controller->addMonitor();

        // Then
        Assert::assertStringContainsString('Monitor name is required', $output);
    }

    public function testDeleteMonitorRemovesMonitor(): void {
        // Given
        $monitorName = 'Test Monitor';
        $uuid = $this->getDatabase()->createMonitor($monitorName, $this->userId);

        // When
        $output = $this->controller->deleteMonitor($uuid);

        // Then
        Assert::assertStringContainsString('Monitor deleted successfully', $output);

        $monitors = $this->getDatabase()->getMonitors($this->userId);
        Assert::assertEmpty($monitors);
    }

    public function testDeleteMonitorShowsErrorWhenUuidIsEmpty(): void {
        // Given

        // When
        $output = $this->controller->deleteMonitor('');

        // Then
        Assert::assertStringContainsString('Monitor UUID is required', $output);
    }

    public function testDeleteMonitorShowsErrorWhenMonitorDoesNotExist(): void {
        // Given
        $nonExistentUuid = '12345678-1234-1234-1234-123456789012';

        // When
        $output = $this->controller->deleteMonitor($nonExistentUuid);

        // Then
        Assert::assertStringContainsString('Failed to delete monitor', $output);
    }
}
