<?php

namespace Cronbeat;

class Logger {
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    
    private static string $minLevel = self::INFO;
    
    private const LEVEL_PRIORITIES = [
        self::DEBUG => 0,
        self::INFO => 1,
        self::WARNING => 2,
        self::ERROR => 3
    ];
    
    public static function debug(string $message, array $context = []): void {
        self::log(self::DEBUG, $message, $context);
    }
    
    public static function info(string $message, array $context = []): void {
        self::log(self::INFO, $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void {
        self::log(self::WARNING, $message, $context);
    }
    
    public static function error(string $message, array $context = []): void {
        self::log(self::ERROR, $message, $context);
    }
    
    private static function log(string $level, string $message, array $context = []): void {
        if (self::LEVEL_PRIORITIES[$level] < self::LEVEL_PRIORITIES[self::$minLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        
        $formattedMessage = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        fwrite(\STDOUT, $formattedMessage);
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