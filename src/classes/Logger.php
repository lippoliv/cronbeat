<?php

namespace Cronbeat;

/**
 * Logger class for CronBeat application
 * 
 * Provides static logging functionality with different log levels:
 * - debug: Detailed information for debugging purposes
 * - info: General information about application flow
 * - warning: Warning messages that don't affect application functionality
 * - error: Error messages that affect application functionality
 */
class Logger {
    // Log levels
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    
    // Current minimum log level (static)
    private static string $minLevel = self::INFO;
    
    // Log level priorities (higher number = higher priority)
    private const LEVEL_PRIORITIES = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3
    ];
    
    /**
     * Log a debug message
     * 
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log an info message
     * 
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * Log a warning message
     * 
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }
    
    /**
     * Log an error message
     * 
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Log a message with the specified level
     * 
     * @param string $level Log level
     * @param string $message Message to log
     * @param array $context Additional context data
     * @return void
     */
    private static function log(string $level, string $message, array $context = []): void {
        // Check if this log level should be output
        if (self::LEVEL_PRIORITIES[$level] < self::LEVEL_PRIORITIES[self::$minLevel]) {
            return;
        }
        
        // Format timestamp
        $timestamp = date('Y-m-d H:i:s');
        
        // Format context data as JSON if present
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        
        // Format log message
        $formattedMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        // Write to stdout
        fwrite(STDOUT, $formattedMessage);
    }
    
    /**
     * Set the minimum log level
     * 
     * @param string $level Minimum log level
     * @return void
     */
    public static function setMinLevel(string $level): void {
        if (!isset(self::LEVEL_PRIORITIES[$level])) {
            throw new \InvalidArgumentException("Invalid log level: $level");
        }
        
        self::$minLevel = $level;
    }
    
    /**
     * Get the current minimum log level
     * 
     * @return string Current minimum log level
     */
    public static function getMinLevel(): string {
        return self::$minLevel;
    }
}