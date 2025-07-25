<?php

namespace CronBeat\Tests\Unit\Model;

use CronBeat\Model\JobStatus;
use PHPUnit\Framework\TestCase;

class JobStatusTest extends TestCase
{
    /**
     * Test that a job is marked as late when the expected interval has passed
     */
    public function testJobIsMarkedAsLateWhenExpectedIntervalHasPassed(): void
    {
        // Given
        $jobId = 'daily-backup';
        $expectedInterval = 1; // 1 second for quick testing
        $jobStatus = new JobStatus($jobId, $expectedInterval);
        $jobStatus->checkIn(); // Mark as active
        
        // When
        // Sleep for longer than the expected interval
        sleep(2);
        $status = $jobStatus->getStatus();
        
        // Then
        $this->assertEquals('late', $status, 'Job should be marked as late when the expected interval has passed');
    }
    
    /**
     * Test that a job remains active when within the expected interval
     */
    public function testJobRemainsActiveWhenWithinExpectedInterval(): void
    {
        // Given
        $jobId = 'hourly-report';
        $expectedInterval = 10; // 10 seconds
        $jobStatus = new JobStatus($jobId, $expectedInterval);
        $jobStatus->checkIn(); // Mark as active
        
        // When
        $status = $jobStatus->getStatus();
        
        // Then
        $this->assertEquals('active', $status, 'Job should remain active when within the expected interval');
    }
    
    /**
     * Test that a new job starts with pending status
     */
    public function testNewJobStartsWithPendingStatus(): void
    {
        // Given
        $jobId = 'weekly-cleanup';
        $expectedInterval = 604800; // 1 week in seconds
        
        // When
        $jobStatus = new JobStatus($jobId, $expectedInterval);
        $status = $jobStatus->getStatus();
        
        // Then
        $this->assertEquals('pending', $status, 'New job should start with pending status');
    }
}