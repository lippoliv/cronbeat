<?php

namespace Cronbeat;

final class MonitorData {
    public function __construct(
        private string $uuid,
        private string $name,
        private ?string $lastPingAt,
        private ?int $lastDurationMs,
        private bool $pendingStart,
        private ?int $expectedIntervalMinutes = null,
        private ?int $gracePeriodMinutes = null,
    ) {
    }

    public function getUuid(): string {
        return $this->uuid;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getLastPingAt(): ?string {
        return $this->lastPingAt;
    }

    public function getLastDurationMs(): ?int {
        return $this->lastDurationMs;
    }

    public function hasPendingStart(): bool {
        return $this->pendingStart;
    }

    public function getExpectedIntervalMinutes(): ?int {
        return $this->expectedIntervalMinutes;
    }

    public function getGracePeriodMinutes(): ?int {
        return $this->gracePeriodMinutes;
    }
}
