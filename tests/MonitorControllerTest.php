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
            Assert::fail('Failed to validate test user');
        }
        $this->userId = $validated;

        $_SESSION = [];
        $_SESSION['user_id'] = $this->userId;

        $this->controller = new MonitorController($db);
    }

    protected function tearDown(): void {
        $_SESSION = [];
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
        try {
            $this->getController()->doRouting();
            Assert::fail('Expected RedirectException was not thrown');
        } catch (RedirectException $e) {
            // Then
            $headers = $e->getHeaders();
            Assert::assertArrayHasKey('Location', $headers);
            Assert::assertSame('/login', $headers['Location']);
        }
    }

    public function testDoRoutingServesHistoryFirstPageWithPagination(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('My Monitor', $this->userId);
        if ($uuid === false) { Assert::fail('monitor create failed'); }

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
        // First page has disabled prev button and enabled next button
        Assert::assertMatchesRegularExpression('/<button[^>]*disabled[^>]*>\s*&lt;\s*<\/button>/', $html);
        Assert::assertDoesNotMatchRegularExpression('/<button[^>]*disabled[^>]*>\s*&gt;\s*<\/button>/', $html);
        // Page size is 50 items on first page
        $liCount = substr_count($html, 'class="history-item"');
        Assert::assertSame(50, $liCount);
    }

    public function testDoRoutingServesHistorySecondPageViaQueryParam(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('Another Monitor', $this->userId);
        if ($uuid === false) { Assert::fail('monitor create failed'); }

        for ($i = 0; $i < 75; $i++) {
            $db->completePing($uuid);
        }

        // When
        $_GET['page'] = 2;
        $_SERVER['REQUEST_URI'] = "/monitor/$uuid?page=2";
        $html = $this->getController()->doRouting();

        // Then
        Assert::assertStringContainsString('Page 2 / 2', $html);
        // Next button disabled on last page, Prev enabled (not disabled)
        Assert::assertMatchesRegularExpression('/<button[^>]*disabled[^>]*>\s*&gt;\s*<\/button>/', $html);
        Assert::assertDoesNotMatchRegularExpression('/<button[^>]*disabled[^>]*>\s*&lt;\s*<\/button>/', $html);
        // Remaining 25 items on second page
        $liCount = substr_count($html, 'class="history-item"');
        Assert::assertSame(25, $liCount);
    }
}
