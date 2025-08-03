<?php

namespace Cronbeat\Tests;

use Cronbeat\Logger;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    private $originalLogStream;
    private $tempStream;
    private $tempFile;
    
    protected function setUp(): void
    {
        // Reset the logger to default state before each test
        Logger::setMinLevel(Logger::INFO);
        
        // Create a temporary file for capturing log output
        $this->tempFile = tempnam(sys_get_temp_dir(), 'logger_test_');
        $this->tempStream = fopen($this->tempFile, 'w+');
        
        // Store the original log stream
        $this->originalLogStream = Logger::getLogStream();
        
        // Set the temporary stream for logging
        Logger::setLogStream($this->tempStream);
    }
    
    protected function tearDown(): void
    {
        // Restore the original log stream
        Logger::setLogStream($this->originalLogStream);
        
        // Close and remove the temporary file
        if ($this->tempStream) {
            fclose($this->tempStream);
            $this->tempStream = null;
        }
        
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
            $this->tempFile = null;
        }
    }
    
    private function getLogOutput()
    {
        if (!$this->tempFile || !file_exists($this->tempFile)) {
            return '';
        }
        
        // Ensure all data is written to the file
        fflush($this->tempStream);
        
        return file_get_contents($this->tempFile);
    }
    
    public function testDebugMethodLogsMessageWithDebugLevel()
    {
        // Given
        Logger::setMinLevel(Logger::DEBUG);
        $message = "Test debug message";
        $context = ["key" => "value"];
        
        // When
        Logger::debug($message, $context);
        $output = $this->getLogOutput();
        
        // Then
        $this->assertStringContainsString("[DEBUG] $message", $output);
        $this->assertStringContainsString(json_encode($context), $output);
    }
    
    public function testInfoMethodLogsMessageWithInfoLevel()
    {
        // Given
        $message = "Test info message";
        $context = ["key" => "value"];
        
        // When
        Logger::info($message, $context);
        $output = $this->getLogOutput();
        
        // Then
        $this->assertStringContainsString("[INFO] $message", $output);
        $this->assertStringContainsString(json_encode($context), $output);
    }
    
    public function testWarningMethodLogsMessageWithWarningLevel()
    {
        // Given
        $message = "Test warning message";
        $context = ["key" => "value"];
        
        // When
        Logger::warning($message, $context);
        $output = $this->getLogOutput();
        
        // Then
        $this->assertStringContainsString("[WARNING] $message", $output);
        $this->assertStringContainsString(json_encode($context), $output);
    }
    
    public function testErrorMethodLogsMessageWithErrorLevel()
    {
        // Given
        $message = "Test error message";
        $context = ["key" => "value"];
        
        // When
        Logger::error($message, $context);
        $output = $this->getLogOutput();
        
        // Then
        $this->assertStringContainsString("[ERROR] $message", $output);
        $this->assertStringContainsString(json_encode($context), $output);
    }
    
    public function testLogLevelFilteringPreventsLowerLevelLogs()
    {
        // Given
        Logger::setMinLevel(Logger::WARNING);
        
        // When
        Logger::debug("Debug message");
        Logger::info("Info message");
        $output = $this->getLogOutput();
        
        // Then
        $this->assertEmpty($output);
    }
    
    public function testLogLevelFilteringAllowsHigherLevelLogs()
    {
        // Given
        Logger::setMinLevel(Logger::WARNING);
        
        // When
        Logger::warning("Warning message");
        Logger::error("Error message");
        $output = $this->getLogOutput();
        
        // Then
        $this->assertStringContainsString("[WARNING] Warning message", $output);
        $this->assertStringContainsString("[ERROR] Error message", $output);
    }
    
    public function testSetMinLevelThrowsExceptionForInvalidLevel()
    {
        // Given
        $invalidLevel = "INVALID_LEVEL";
        
        // When & Then
        $this->expectException(\InvalidArgumentException::class);
        Logger::setMinLevel($invalidLevel);
    }
    
    public function testGetMinLevelReturnsCurrentLevel()
    {
        // Given
        Logger::setMinLevel(Logger::DEBUG);
        
        // When
        $level = Logger::getMinLevel();
        
        // Then
        $this->assertEquals(Logger::DEBUG, $level);
    }
    
    public function testLogFormatsTimestampCorrectly()
    {
        // Given
        $message = "Test message";
        
        // When
        Logger::info($message);
        $output = $this->getLogOutput();
        
        // Then
        $this->assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $output);
    }
    
    public function testLogWithEmptyContextDoesNotAddJsonObject()
    {
        // Given
        $message = "Test message with empty context";
        
        // When
        Logger::info($message);
        $output = $this->getLogOutput();
        
        // Then
        $this->assertStringContainsString("[INFO] $message", $output);
        $this->assertStringNotContainsString("{}", $output);
    }
    
    public function testEnvironmentVariableConfiguration()
    {
        // Given
        $originalEnv = getenv('LOG_LEVEL');
        putenv('LOG_LEVEL=DEBUG');
        
        // When
        $logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
        Logger::setMinLevel($logLevel);
        
        // Then
        $this->assertEquals(Logger::DEBUG, Logger::getMinLevel());
        
        // Cleanup
        if ($originalEnv !== false) {
            putenv("LOG_LEVEL=$originalEnv");
        } else {
            putenv('LOG_LEVEL');
        }
        Logger::setMinLevel(Logger::INFO); // Reset to default
    }
    
    public function testEnvironmentVariableFallbackToInfo()
    {
        // Given
        $originalEnv = getenv('LOG_LEVEL');
        putenv('LOG_LEVEL'); // Unset the environment variable
        
        // When
        $logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
        Logger::setMinLevel($logLevel);
        
        // Then
        $this->assertEquals(Logger::INFO, Logger::getMinLevel());
        
        // Cleanup
        if ($originalEnv !== false) {
            putenv("LOG_LEVEL=$originalEnv");
        }
    }
}