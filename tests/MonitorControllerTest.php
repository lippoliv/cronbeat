<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\MonitorController;
use Cronbeat\RedirectException;
use PHPUnit\Framework\Assert;

class MonitorControllerTest extends DatabaseTestCase {
    private ?MonitorController $controller = null;
    private int $userId = 0;
    private string $username = 'user1';
    private string $passwordHash = '';

    protected function setUp(): void {
        parent::setUp();

        $this->passwordHash = hash('sha256', 'secret');
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

    public function testDoRoutingRedirectsToLoginWhenNotAuthenticated(): void {
        // Given
        $_SESSION = [];
        $_SERVER['REQUEST_URI'] = '/monitor/anything';

        // When
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
        Assert::assertSame('/login', $headers['Location']);
    }

    public function testDoRoutingServesHistoryFirstPageWithPagination(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('My Monitor', $this->userId);
        if ($uuid === false) {
            throw new \RuntimeException('monitor create failed');
        }

        // Create 75 pings so that there are 2 pages (50 + 25)
        for ($i = 0; $i < 75; $i++) {
            $db->completePing($uuid);
        }

        // When
        $_SERVER['REQUEST_URI'] = "/monitor/$uuid";
        $html = $this->getController()->doRouting();

        // Then
        Assert::assertStringContainsString('History for', $html);
        Assert::assertStringContainsString('Total pings: 75', $html);
        Assert::assertStringContainsString('Page 1 / 2', $html);
        // First page has disabled prev link and enabled next link (blue styling)
        Assert::assertMatchesRegularExpression(
            '/<a[^>]*class="[^"]*page-button[^"]*disabled[^"]*"[^>]*>\s*&lt;\s*<\/a>/',
            $html
        );
        Assert::assertDoesNotMatchRegularExpression(
            '/<a[^>]*class="[^"]*page-button[^"]*disabled[^"]*"[^>]*>\s*&gt;\s*<\/a>/',
            $html
        );
        // Page size is 50 items on first page
        $liCount = substr_count($html, 'class="history-item"');
        Assert::assertSame(50, $liCount);
        $gapCount = substr_count($html, 'class="history-gap"');
        Assert::assertSame(49, $gapCount);
        Assert::assertMatchesRegularExpression('/\+\s*\d+:\d{2}:\d{2}:\d{2}/', $html);
    }

    public function testDoRoutingServesHistorySecondPageViaQueryParam(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('Another Monitor', $this->userId);
        if ($uuid === false) {
            throw new \RuntimeException('monitor create failed');
        }

        for ($i = 0; $i < 75; $i++) {
            $db->completePing($uuid);
        }

        // When
        $_GET['page'] = 2;
        $_SERVER['REQUEST_URI'] = "/monitor/$uuid?page=2";
        $html = $this->getController()->doRouting();

        // Then
        Assert::assertStringContainsString('Page 2 / 2', $html);
        // Next link disabled on last page, Prev enabled (not disabled)
        Assert::assertMatchesRegularExpression(
            '/<a[^>]*class="[^"]*page-button[^"]*disabled[^"]*"[^>]*>\s*&gt;\s*<\/a>/',
            $html
        );
        Assert::assertDoesNotMatchRegularExpression(
            '/<a[^>]*class="[^"]*page-button[^"]*disabled[^"]*"[^>]*>\s*&lt;\s*<\/a>/',
            $html
        );
        // Remaining 25 items on second page
        $liCount = substr_count($html, 'class="history-item"');
        Assert::assertSame(25, $liCount);
        $gapCount = substr_count($html, 'class="history-gap"');
        Assert::assertSame(24, $gapCount);
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
