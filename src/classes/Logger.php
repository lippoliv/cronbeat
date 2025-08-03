<?php

namespace Cronbeat;

class Logger {
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    
    private static string $minLevel = self::INFO;
    /** @var resource|null */
    private static $logStream = null;
    
    private const LEVEL_PRIORITIES = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3
    ];
    
    /**
     * @param string $message The message to log
     * @param array<string, mixed> $context Additional context data
     */
    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * @param string $message The message to log
     * @param array<string, mixed> $context Additional context data
     */
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * @param string $message The message to log
     * @param array<string, mixed> $context Additional context data
     */
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }
    
    /**
     * @param string $message The message to log
     * @param array<string, mixed> $context Additional context data
     */
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * @param string $level The log level
     * @param string $message The message to log
     * @param array<string, mixed> $context Additional context data
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
    
    /**
     * @throws \RuntimeException If unable to open the log stream
     * @return resource The log stream resource
     */
    public static function getLogStream() {
        if (self::$logStream === null) {
            $stream = fopen('php://stdout', 'w');
            if ($stream === false) {
                throw new \RuntimeException('Unable to open log stream');
            }
            self::$logStream = $stream;
        }
        return self::$logStream;
    }
    
    /**
     * @param resource $stream The log stream resource
     */
    public static function setLogStream($stream): void {
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