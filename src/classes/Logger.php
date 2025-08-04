<?php

namespace Cronbeat;

class Logger {
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';

    private static string $minLevel = self::INFO;
    /** @var resource|null */
    private static mixed $logStream = null;

    private const LEVEL_PRIORITIES = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3
    ];

    /**
     * @param array<string, mixed> $context
     */
    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function log(string $level, string $message, array $context = []): void {
        if (self::LEVEL_PRIORITIES[$level] < self::LEVEL_PRIORITIES[self::$minLevel]) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');

        $contextStr = $context !== [] ? ' ' . json_encode($context) : '';

        $formattedMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;

        $stream = self::getLogStream();
        fwrite($stream, $formattedMessage);
    }

    /** @return resource */
    public static function getLogStream(): mixed {
        if (self::$logStream === null) {
            $stream = fopen('php://stdout', 'w');
            if ($stream === false) {
                throw new \RuntimeException('Unable to open log stream');
            }
            self::$logStream = $stream;
        }
        return self::$logStream;
    }

    /** @param resource $stream */
    public static function setLogStream(mixed $stream): void {
        self::$logStream = $stream;
    }

    public static function setMinLevel(string $level): void {
        if (!isset(self::LEVEL_PRIORITIES[$level])) {
            throw new \InvalidArgumentException("Invalid log level: $level");
        }

        self::$minLevel = $level;
    }

    public static function getMinLevel(): string {
        return self::$minLevel;
    }
}
