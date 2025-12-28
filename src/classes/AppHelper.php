<?php

namespace Cronbeat;

class AppHelper {
    private static ?string $appVersion = null;

    public static function getAppVersion(): ?string {
        if (self::$appVersion === null) {
            $versionFile = (defined('APP_DIR') ? APP_DIR : __DIR__ . '/..') . '/version.txt';
            if (file_exists($versionFile)) {
                self::$appVersion = trim((string) file_get_contents($versionFile));
            } else {
                // Also check root if APP_DIR is defined but file not found there
                $rootVersionFile = (defined('APP_DIR') ? APP_DIR . '/..' : __DIR__ . '/../..') . '/version.txt';
                if (file_exists($rootVersionFile)) {
                    self::$appVersion = trim((string) file_get_contents($rootVersionFile));
                } else {
                    self::$appVersion = '';
                }
            }
        }

        return self::$appVersion !== '' ? self::$appVersion : null;
    }

    /**
     * Resets the cached app version. Mainly used for testing.
     */
    public static function resetAppVersion(): void {
        self::$appVersion = null;
    }
}
