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
        
        // Create a test user
        $this->passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($this->username, $this->passwordHash);
        $this->userId = $this->getDatabase()->validateUser($this->username, $this->passwordHash);
        
        // Set up session
        $_SESSION = [];
        $_SESSION['user_id'] = $this->userId;
        
        // Create controller
        $this->controller = new DashboardController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testDoRoutingRedirectsToLoginWhenNotAuthenticated(): void {
        // Given
        $_SESSION = []; // Clear session to simulate unauthenticated user
        
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

    public function testShowDashboardDisplaysUserMonitors(): void {
        // Given
        // Create some test monitors
        $monitorName1 = 'Test Monitor 1';
        $monitorName2 = 'Test Monitor 2';
        $this->getDatabase()->createMonitor($monitorName1, $this->userId);
        $this->getDatabase()->createMonitor($monitorName2, $this->userId);
        
        // When
        $output = $this->controller->showDashboard();
        
        // Then
        // We can't test the exact HTML output, but we can check that it contains the monitor names
        Assert::assertStringContainsString($monitorName1, $output);
        Assert::assertStringContainsString($monitorName2, $output);
        Assert::assertStringContainsString($this->username, $output);
    }

    public function testShowMonitorFormDisplaysForm(): void {
        // Given
        // Controller is already set up
        
        // When
        $output = $this->controller->showMonitorForm();
        
        // Then
        // Check that the output contains form elements
        Assert::assertStringContainsString('<form', $output);
        Assert::assertStringContainsString('name="name"', $output);
    }

    public function testAddMonitorCreatesNewMonitor(): void {
        // Given
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'New Test Monitor';
        
        // When/Then
        try {
            $this->controller->addMonitor();
            $this->fail('Expected RedirectException was not thrown');
        } catch (RedirectException $e) {
            // Verify the monitor was created before the exception was thrown
            $monitors = $this->getDatabase()->getMonitors($this->userId);
            Assert::assertCount(1, $monitors);
            Assert::assertEquals('New Test Monitor', $monitors[0]['name']);
            
            // Verify the exception contains the correct headers
            $headers = $e->getHeaders();
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
        
        // Verify that the monitor was deleted
        $monitors = $this->getDatabase()->getMonitors($this->userId);
        Assert::assertEmpty($monitors);
    }

    public function testDeleteMonitorShowsErrorWhenUuidIsEmpty(): void {
        // Given
        // Controller is already set up
        
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