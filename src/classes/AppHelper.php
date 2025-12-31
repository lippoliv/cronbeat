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
     *  - up to and including 90 s: "ss:ms" with zero-padded seconds (2) and milliseconds (3)
     *  - above 90 s: "mm:ss" with zero-padded minutes (2) and seconds (2)
     */
    public static function formatDuration(int $milliseconds): string {
        if ($milliseconds <= 2000) {
            return $milliseconds . ' ms';
        }

        $totalSeconds = intdiv($milliseconds, 1000);
        $remainingMs = $milliseconds % 1000;

        if ($milliseconds <= 90_000) {
            // ss:ms (pad seconds to 2 digits, ms to 3 digits)
            $sec = str_pad((string) $totalSeconds, 2, '0', STR_PAD_LEFT);
            $ms  = str_pad((string) $remainingMs, 3, '0', STR_PAD_LEFT);
            return $sec . ':' . $ms;
        }

        // mm:ss (pad minutes and seconds to 2 digits)
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;
        $mm = str_pad((string) $minutes, 2, '0', STR_PAD_LEFT);
        $ss = str_pad((string) $seconds, 2, '0', STR_PAD_LEFT);
        return $mm . ':' . $ss;
    }

    /**
     * Resets the cached app version. Mainly used for testing.
     */
    public static function resetAppVersion(): void {
        self::$appVersion = null;
    }
}
