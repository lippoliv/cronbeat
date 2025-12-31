<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\MonitorController;
use Cronbeat\RedirectException;
use PHPUnit\Framework\Assert;

class MonitorEditControllerTest extends DatabaseTestCase {
    private ?MonitorController $controller = null;
    private int $userId = 0;
    private string $username = 'editor';
    private string $passwordHash = '';

    protected function setUp(): void {
        parent::setUp();

        $this->passwordHash = hash('sha256', 'pw');
        $db = $this->getDatabase();
        $db->createUser($this->username, $this->passwordHash);
        $validated = $db->validateUser($this->username, $this->passwordHash);
        if ($validated === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $this->userId = $validated;

        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['user_id'] = $this->userId;

        $this->controller = new MonitorController($db);
    }

    protected function tearDown(): void {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        parent::tearDown();
    }

    private function getController(): MonitorController {
        if ($this->controller === null) {
            throw new \RuntimeException('Controller not initialized');
        }
        return $this->controller;
    }

    public function testEditPageShowsForm(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('Original Name', $this->userId);
        if ($uuid === false) {
            throw new \RuntimeException('Failed to create monitor for test');
        }

        // When
        $_SERVER['REQUEST_URI'] = "/monitor/$uuid/edit";
        $html = $this->getController()->doRouting();

        // Then
        Assert::assertStringContainsString('<form', $html);
        Assert::assertStringContainsString('name="name"', $html);
        Assert::assertStringContainsString('Original Name', $html);
    }

    public function testEditPageCanDeleteMonitor(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('To Be Deleted', $this->userId);
        if ($uuid === false) {
            throw new \RuntimeException('Failed to create monitor for test');
        }

        // When: simulate following the delete link from the edit page
        $dashboard = new \Cronbeat\Controllers\DashboardController($db);
        $output = $dashboard->deleteMonitor($uuid);

        // Then
        Assert::assertStringContainsString('Monitor deleted successfully', $output);
        $monitors = $db->getMonitors($this->userId);
        Assert::assertCount(0, $monitors);
    }

    public function testPostEditUpdatesNameAndRedirectsToHistory(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('Before Edit', $this->userId);
        if ($uuid === false) {
            throw new \RuntimeException('Failed to create monitor for test');
        }

        // When
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['name'] = 'After Edit';
        $_SERVER['REQUEST_URI'] = "/monitor/$uuid/edit";

        $thrown = null;
        try {
            $this->getController()->doRouting();
        } catch (RedirectException $e) {
            $thrown = $e;
        }

        // Then
        Assert::assertInstanceOf(RedirectException::class, $thrown);
        $headers = $thrown->getHeaders();
        Assert::assertArrayHasKey('Location', $headers);
        Assert::assertSame('/monitor/' . $uuid, $headers['Location']);

        $monitors = $db->getMonitors($this->userId);
        Assert::assertCount(1, $monitors);
        Assert::assertSame('After Edit', $monitors[0]->getName());
    }

    public function testHistoryPageContainsEditLink(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('My Monitor', $this->userId);
        if ($uuid === false) {
            throw new \RuntimeException('Failed to create monitor for test');
        }

        // When
        $_SERVER['REQUEST_URI'] = "/monitor/$uuid";
        $html = $this->getController()->doRouting();

        // Then
        Assert::assertStringContainsString('/monitor/' . $uuid . '/edit', $html);
    }
}
