<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\Assert;

class PingDatabaseTest extends DatabaseTestCase {

    public function testCompletePingWithoutStartCreatesHistoryNoDuration(): void {
        // Given
        $this->getDatabase()->createUser('u', 'p');
        $userId = $this->getDatabase()->validateUser('u', 'p');
        if ($userId === false) { throw new \RuntimeException('user validate failed'); }
        $uuid = $this->getDatabase()->createMonitor('m1', $userId);
        if ($uuid === false) { throw new \RuntimeException('monitor create failed'); }

        // When
        $result = $this->getDatabase()->completePing($uuid);

        // Then
        Assert::assertIsArray($result);
        /** @var array{history_id:int, duration_ms:int|null} $result */
        Assert::assertArrayHasKey('duration_ms', $result);
        Assert::assertNull($result['duration_ms']);

        $monitorId = $this->getDatabase()->getMonitorIdByUuid($uuid);
        if ($monitorId === false) { throw new \RuntimeException('monitor id not found'); }
        $history = $this->getDatabase()->getPingHistory($monitorId, 10, 0);
        Assert::assertCount(1, $history);
        Assert::assertNull($history[0]->getDurationMs());
    }

    public function testStartThenCompletePingRecordsDurationAndClearsPending(): void {
        // Given
        $this->getDatabase()->createUser('u', 'p');
        $userId = $this->getDatabase()->validateUser('u', 'p');
        if ($userId === false) { throw new \RuntimeException('user validate failed'); }
        $uuid = $this->getDatabase()->createMonitor('m1', $userId);
        if ($uuid === false) { throw new \RuntimeException('monitor create failed'); }

        // When
        $started = $this->getDatabase()->startPingTracking($uuid);
        $result = $this->getDatabase()->completePing($uuid);

        // Then
        Assert::assertTrue($started);
        Assert::assertIsArray($result);
        /** @var array{history_id:int, duration_ms:int|null} $result */
        Assert::assertArrayHasKey('duration_ms', $result);
        Assert::assertIsInt($result['duration_ms']);
        Assert::assertGreaterThanOrEqual(0, $result['duration_ms']);

        $monitorId = $this->getDatabase()->getMonitorIdByUuid($uuid);
        if ($monitorId === false) { throw new \RuntimeException('monitor id not found'); }
        $pending = $this->getDatabase()->hasPendingStart($monitorId);
        Assert::assertFalse($pending);
    }

    public function testHistoryPaginationReturns50ItemsPerPage(): void {
        // Given
        $this->getDatabase()->createUser('u', 'p');
        $userId = $this->getDatabase()->validateUser('u', 'p');
        if ($userId === false) { throw new \RuntimeException('user validate failed'); }
        $uuid = $this->getDatabase()->createMonitor('m1', $userId);
        if ($uuid === false) { throw new \RuntimeException('monitor create failed'); }
        // produce 120 pings
        for ($i = 0; $i < 120; $i++) {
            $this->getDatabase()->completePing($uuid);
        }
        $monitorId = $this->getDatabase()->getMonitorIdByUuid($uuid);
        if ($monitorId === false) { throw new \RuntimeException('monitor id not found'); }

        // When
        $page1 = $this->getDatabase()->getPingHistory($monitorId, 50, 0);
        $page3 = $this->getDatabase()->getPingHistory($monitorId, 50, 100);

        // Then
        Assert::assertCount(50, $page1);
        Assert::assertCount(20, $page3);
    }
}
