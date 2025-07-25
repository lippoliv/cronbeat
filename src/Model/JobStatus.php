<?php

namespace CronBeat\Model;

/**
 * JobStatus represents the current status of a monitored cron job
 */
class JobStatus
{
    private string $jobId;
    private string $status;
    private \DateTimeImmutable $lastCheckIn;
    private int $expectedIntervalSeconds;
    
    /**
     * Create a new JobStatus
     *
     * @param string $jobId Unique identifier for the job
     * @param int $expectedIntervalSeconds Expected interval between check-ins in seconds
     */
    public function __construct(string $jobId, int $expectedIntervalSeconds)
    {
        $this->jobId = $jobId;
        $this->status = 'pending'; // Initial status is pending
        $this->lastCheckIn = new \DateTimeImmutable(); // Current time as initial check-in
        $this->expectedIntervalSeconds = $expectedIntervalSeconds;
    }
    
    /**
     * Record a new check-in for this job
     *
     * @return void
     */
    public function checkIn(): void
    {
        $this->lastCheckIn = new \DateTimeImmutable();
        $this->status = 'active';
    }
    
    /**
     * Get the current status of the job
     *
     * @return string 'pending', 'active', or 'late'
     */
    public function getStatus(): string
    {
        // If the job is already marked as active, check if it's now late
        if ($this->status === 'active') {
            $now = new \DateTimeImmutable();
            $interval = $now->getTimestamp() - $this->lastCheckIn->getTimestamp();
            
            if ($interval > $this->expectedIntervalSeconds) {
                return 'late';
            }
        }
        
        return $this->status;
    }
    
    /**
     * Get the job ID
     *
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }
    
    /**
     * Get the timestamp of the last check-in
     *
     * @return \DateTimeImmutable
     */
    public function getLastCheckIn(): \DateTimeImmutable
    {
        return $this->lastCheckIn;
    }
    
    /**
     * Get the expected interval between check-ins in seconds
     *
     * @return int
     */
    public function getExpectedIntervalSeconds(): int
    {
        return $this->expectedIntervalSeconds;
    }
}