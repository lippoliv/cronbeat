<?php

namespace CronBeat\Tests;

use CronBeat\Classes\Job;
use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    /**
     * Test that a new job is not considered late
     */
    public function testNewJobIsNotLate(): void
    {
        // Given a new job with a 1-hour interval
        $job = new Job(
            'Daily Backup',
            'daily-backup',
            3600, // 1 hour in seconds
            300   // 5 minutes grace period
        );

        // Then the job should not be considered late
        $this->assertFalse($job->isLate());
    }

    /**
     * Test that a job becomes late after the expected interval plus grace period
     */
    public function testJobBecomesLateAfterExpectedIntervalPlusGracePeriod(): void
    {
        // Given a job with a 1-hour interval and 5-minute grace period
        $job = new Job(
            'Hourly Report',
            'hourly-report',
            3600, // 1 hour in seconds
            300   // 5 minutes grace period
        );
        
        // When the job checks in
        $job->checkIn();
        
        // And we mock time to be before the late threshold
        $currentTime = time() + 3600 + 299; // Just before becoming late
        
        // Then the job should not be considered late yet
        $this->assertFalse($this->isJobLateAt($job, $currentTime));
        
        // When time advances past the grace period
        $currentTime = time() + 3600 + 301; // Just after becoming late
        
        // Then the job should be considered late
        $this->assertTrue($this->isJobLateAt($job, $currentTime));
    }
    
    /**
     * Test that job properties are correctly stored and retrieved
     */
    public function testJobPropertiesAreCorrectlyStored(): void
    {
        // Given a job with specific properties
        $name = 'Weekly Cleanup';
        $identifier = 'weekly-cleanup';
        $expectedInterval = 604800; // 1 week in seconds
        $graceInterval = 7200; // 2 hours grace period
        
        $job = new Job($name, $identifier, $expectedInterval, $graceInterval);
        
        // Then the properties should be correctly stored and retrievable
        $this->assertEquals($name, $job->getName());
        $this->assertEquals($identifier, $job->getIdentifier());
        $this->assertEquals($expectedInterval, $job->getExpectedInterval());
        $this->assertEquals($graceInterval, $job->getGraceInterval());
        $this->assertNull($job->getLastCheckIn());
        
        // When the job checks in
        $before = time();
        $job->checkIn();
        $after = time();
        
        // Then the last check-in time should be set correctly
        $this->assertGreaterThanOrEqual($before, $job->getLastCheckIn());
        $this->assertLessThanOrEqual($after, $job->getLastCheckIn());
    }
    
    /**
     * Helper method to check if a job is late at a specific time
     */
    private function isJobLateAt(Job $job, int $currentTime): bool
    {
        $lastCheckIn = $job->getLastCheckIn();
        $expectedInterval = $job->getExpectedInterval();
        $graceInterval = $job->getGraceInterval();
        
        if ($lastCheckIn === null) {
            return false;
        }
        
        $expectedCheckInTime = $lastCheckIn + $expectedInterval;
        $lateAfterTime = $expectedCheckInTime + $graceInterval;
        
        return $currentTime > $lateAfterTime;
    }
}