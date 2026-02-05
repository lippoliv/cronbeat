<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\DashboardController;
use PHPUnit\Framework\Assert;

class DashboardStatusTest extends DatabaseTestCase {
    private ?DashboardController $controller = null;
    private int $userId = 0;

    protected function setUp(): void {
        parent::setUp();
        $this->getDatabase()->createUser('u', 'p');
        $uid = $this->getDatabase()->validateUser('u', 'p');
        if ($uid === false) {
            throw new \RuntimeException('validate');
        }
        $this->userId = $uid;
        $_SESSION = [];
        $_SESSION['user_id'] = $this->userId;
        $this->controller = new DashboardController($this->getDatabase());
    }

    protected function tearDown(): void {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testDashboardTileWarningWhenPastExpectedButWithinGrace(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('m-warn', $this->userId, 60, 5); // expected 60m, grace 5m
        if ($uuid === false) {
            throw new \RuntimeException('createMonitor');
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $db->insertPingAt($uuid, $now->modify('-62 minutes'));

        // When
        $html = $this->controller->showDashboard();

        // Then
        Assert::assertStringContainsString('monitor-tile-warning', $html);
        Assert::assertStringNotContainsString('monitor-tile-alert', $html);
    }

    public function testDashboardTileAlertWhenPastExpectedPlusGrace(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('m-alert', $this->userId, 60, 5);
        if ($uuid === false) {
            throw new \RuntimeException('createMonitor');
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $db->insertPingAt($uuid, $now->modify('-70 minutes'));

        // When
        $html = $this->controller->showDashboard();

        // Then
        Assert::assertStringContainsString('monitor-tile-alert', $html);
    }

    public function testDashboardTileNoHighlightWhenWithinExpected(): void {
        // Given
        $db = $this->getDatabase();
        $uuid = $db->createMonitor('m-ok', $this->userId, 60, 5);
        if ($uuid === false) {
            throw new \RuntimeException('createMonitor');
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $db->insertPingAt($uuid, $now->modify('-30 minutes'));

        // When
        $html = $this->controller->showDashboard();

        // Then
        Assert::assertStringNotContainsString('monitor-tile-warning', $html);
        Assert::assertStringNotContainsString('monitor-tile-alert', $html);
    }
}
