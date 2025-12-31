<?php

namespace Cronbeat\Tests;

use Cronbeat\AppHelper;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class DurationFormatTest extends TestCase {
    protected function setUp(): void {
        if (!defined('APP_DIR')) {
            define('APP_DIR', __DIR__ . '/../src');
        }
    }

    public function testMsFormatUpTo2000Inclusive(): void {
        // Given
        $oneAndHalfSec = 1500;
        $twoSec = 2000;

        // When
        $f1 = AppHelper::formatDuration($oneAndHalfSec);
        $f2 = AppHelper::formatDuration($twoSec);

        // Then
        Assert::assertSame('1500 ms', $f1);
        Assert::assertSame('2000 ms', $f2);
    }

    public function testSsMsFormatUpTo90SecondsInclusive(): void {
        // Given
        $justOverTwoSec = 2001;      // 2s 1ms => "2s 1ms"
        $sixtyOneSec = 61000;        // 61s     => "61s 0ms"
        $ninetySec = 90000;          // 90s     => "90s 0ms"

        // When
        $f1 = AppHelper::formatDuration($justOverTwoSec);
        $f2 = AppHelper::formatDuration($sixtyOneSec);
        $f3 = AppHelper::formatDuration($ninetySec);

        // Then
        Assert::assertSame('2s 1ms', $f1);
        Assert::assertSame('61s 0ms', $f2);
        Assert::assertSame('90s 0ms', $f3);
    }

    public function testMmSsFormatAfter90Seconds(): void {
        // Given
        $justOverNinetySec = 90001;  // 1m 30s 1ms => "1m 30s"
        $twoMinutesFiveSec = 125000; // 2m 5s       => "2m 5s"

        // When
        $f1 = AppHelper::formatDuration($justOverNinetySec);
        $f2 = AppHelper::formatDuration($twoMinutesFiveSec);

        // Then
        Assert::assertSame('1m 30s', $f1);
        Assert::assertSame('2m 5s', $f2);
    }
}
