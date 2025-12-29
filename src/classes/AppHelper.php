<?php

namespace Cronbeat;

class AppHelper {
    private static ?string $appVersion = null;

    public static function getAppVersion(): ?string {
        if (self::$appVersion === null) {
            $versionFile = APP_DIR . '/version.txt';
            self::$appVersion = file_exists($versionFile)
                ? trim((string) file_get_contents($versionFile))
                : '';
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
