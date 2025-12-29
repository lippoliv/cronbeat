<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\TestCase;
use Cronbeat\AppHelper;
use PHPUnit\Framework\Assert;

class AppHelperTest extends TestCase {
    private string $srcVersionFile = '';

    protected function setUp(): void {
        if (!defined('APP_DIR')) {
            define('APP_DIR', __DIR__ . '/../src');
        }
        $this->srcVersionFile = APP_DIR . '/version.txt';
        AppHelper::resetAppVersion();
    }

    protected function tearDown(): void {
        AppHelper::resetAppVersion();
    }

    public function testGetAppVersionReturnsNullIfFileDoesNotExist(): void {
        // Given
        $originalVersion = null;
        if (file_exists($this->srcVersionFile)) {
            $originalVersion = file_get_contents($this->srcVersionFile);
            rename($this->srcVersionFile, $this->srcVersionFile . '.bak');
        }

        // When
        $version = AppHelper::getAppVersion();

        // Then
        Assert::assertNull($version);

        // Cleanup
        if ($originalVersion !== null && file_exists($this->srcVersionFile . '.bak')) {
            rename($this->srcVersionFile . '.bak', $this->srcVersionFile);
        }
    }

    public function testGetAppVersionReturnsVersionFromFile(): void {
        // Given
        $originalVersion = null;
        if (file_exists($this->srcVersionFile)) {
            $originalVersion = file_get_contents($this->srcVersionFile);
        }
        file_put_contents($this->srcVersionFile, "1.2.3\n");

        // When
        $version = AppHelper::getAppVersion();

        // Then
        Assert::assertEquals('1.2.3', $version);

        // Cleanup
        if ($originalVersion !== null) {
            file_put_contents($this->srcVersionFile, $originalVersion);
        } else if (file_exists($this->srcVersionFile)) {
            unlink($this->srcVersionFile);
        }
    }

    public function testGetAppVersionWorksWhenAppDirIsDefined(): void {
        // Given
        if (!defined('APP_DIR')) {
            define('APP_DIR', __DIR__ . '/../src');
        }
        $srcVersionFile = APP_DIR . '/version.txt';
        file_put_contents($srcVersionFile, "2.0.0");

        // When
        $version = AppHelper::getAppVersion();

        // Then
        Assert::assertEquals('2.0.0', $version);

        // Cleanup
        if (file_exists($srcVersionFile)) {
            unlink($srcVersionFile);
        }
    }
}
