<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\TestCase;
use Cronbeat\Views\BaseView;
use PHPUnit\Framework\Assert;

class BaseViewTest extends TestCase {
    private string $tempVersionFile = '';

    protected function setUp(): void {
        $this->tempVersionFile = __DIR__ . '/../version.txt';
    }

    public function testGetAppVersionReturnsNullIfFileDoesNotExist(): void {
        // Given
        if (file_exists($this->tempVersionFile)) {
            rename($this->tempVersionFile, $this->tempVersionFile . '.bak');
        }
        $view = new BaseView();

        // When
        $version = $view->getAppVersion();

        // Then
        Assert::assertNull($version);

        // Cleanup
        if (file_exists($this->tempVersionFile . '.bak')) {
            rename($this->tempVersionFile . '.bak', $this->tempVersionFile);
        }
    }

    public function testGetAppVersionReturnsVersionFromFile(): void {
        // Given
        $originalVersion = null;
        if (file_exists($this->tempVersionFile)) {
            $originalVersion = file_get_contents($this->tempVersionFile);
        }
        file_put_contents($this->tempVersionFile, "1.2.3\n");
        $view = new BaseView();

        // When
        $version = $view->getAppVersion();

        // Then
        Assert::assertEquals('1.2.3', $version);

        // Cleanup
        if ($originalVersion !== null) {
            file_put_contents($this->tempVersionFile, $originalVersion);
        } else {
            unlink($this->tempVersionFile);
        }
    }

    public function testGetAppVersionWorksWhenAppDirIsDefined(): void {
        // Given
        if (!defined('APP_DIR')) {
            define('APP_DIR', __DIR__ . '/../src');
        }
        $srcVersionFile = APP_DIR . '/version.txt';
        file_put_contents($srcVersionFile, "2.0.0");
        $view = new BaseView();

        // When
        $version = $view->getAppVersion();

        // Then
        Assert::assertEquals('2.0.0', $version);

        // Cleanup
        unlink($srcVersionFile);
    }
}
