<?php

namespace Cronbeat;

final class MonitorData {
    public function __construct(
        private string $uuid,
        private string $name,
        private ?string $lastPingAt,
        private ?int $lastDurationMs,
        private bool $pendingStart,
    ) {}

    public function getUuid(): string { return $this->uuid; }
    public function getName(): string { return $this->name; }
    public function getLastPingAt(): ?string { return $this->lastPingAt; }
    public function getLastDurationMs(): ?int { return $this->lastDurationMs; }
    public function hasPendingStart(): bool { return $this->pendingStart; }
}
