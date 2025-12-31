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
     * Formats a duration in milliseconds according to UI rules:
     *  - up to and including 2000 ms: "<ms> ms"
     *  - up to and including 90 s: "<s>s <ms>ms" (no zero-padding)
     *  - above 90 s: "<m>m <s>s" (no zero-padding)
     */
    public static function formatDuration(int $milliseconds): string {
        if ($milliseconds <= 2000) {
            return $milliseconds . ' ms';
        }

        $totalSeconds = intdiv($milliseconds, 1000);
        $remainingMs = $milliseconds % 1000;

        if ($milliseconds <= 90_000) {
            // Format as "Xs Yms" without zero-padding
            return $totalSeconds . 's ' . $remainingMs . 'ms';
        }

        // Format as "Xm Ys" without zero-padding
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;
        return $minutes . 'm ' . $seconds . 's';
    }

    /**
     * Resets the cached app version. Mainly used for testing.
     */
    public static function resetAppVersion(): void {
        self::$appVersion = null;
    }
}
