<?php

namespace Cronbeat;

final class PingData {
    public function __construct(
        private string $pingedAt,
        private ?int $durationMs,
    ) {}

    public function getPingedAt(): string {
        return $this->pingedAt;
    }

    public function getDurationMs(): ?int {
        return $this->durationMs;
    }
}
