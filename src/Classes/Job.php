<?php

namespace CronBeat\Classes;

/**
 * Represents a cron job being monitored by CronBeat
 */
class Job
{
    private string $name;
    private string $identifier;
    private int $expectedInterval;
    private ?int $lastCheckIn = null;
    private int $graceInterval;

    /**
     * @param string $name Human-readable name of the job
     * @param string $identifier Unique identifier for the job
     * @param int $expectedInterval Expected interval between check-ins in seconds
     * @param int $graceInterval Grace period in seconds before job is considered late
     */
    public function __construct(
        string $name,
        string $identifier,
        int $expectedInterval,
        int $graceInterval = 300
    ) {
        $this->name = $name;
        $this->identifier = $identifier;
        $this->expectedInterval = $expectedInterval;
        $this->graceInterval = $graceInterval;
    }

    /**
     * Record a check-in for this job
     */
    public function checkIn(): void
    {
        $this->lastCheckIn = time();
    }

    /**
     * Get the name of the job
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the unique identifier for the job
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Get the expected interval between check-ins in seconds
     */
    public function getExpectedInterval(): int
    {
        return $this->expectedInterval;
    }

    /**
     * Get the time of the last check-in (Unix timestamp)
     */
    public function getLastCheckIn(): ?int
    {
        return $this->lastCheckIn;
    }

    /**
     * Get the grace interval in seconds
     */
    public function getGraceInterval(): int
    {
        return $this->graceInterval;
    }

    /**
     * Check if the job is currently late
     */
    public function isLate(): bool
    {
        if ($this->lastCheckIn === null) {
            return false; // New job, not late yet
        }

        $currentTime = time();
        $expectedCheckInTime = $this->lastCheckIn + $this->expectedInterval;
        $lateAfterTime = $expectedCheckInTime + $this->graceInterval;

        return $currentTime > $lateAfterTime;
    }
}