<?php

namespace Cronbeat\Tests;

use Cronbeat\Logger;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Assert;

class LoggerTest extends TestCase {

    /** @var resource|null */
    private mixed $originalLogStream = null;
    /** @var resource|null */
    private mixed $tempStream = null;
    private ?string $tempFile = null;

    protected function setUp(): void {
        // Reset the logger to default state before each test
        Logger::setMinLevel(Logger::INFO);

        // Create a temporary file for capturing log output
        $this->tempFile = tempnam(sys_get_temp_dir(), 'logger_test_');
        $stream = fopen($this->tempFile, 'w+');
        if ($stream === false) {
            throw new \RuntimeException('Unable to open temporary file for testing');
        }
        $this->tempStream = $stream;

        // Store the original log stream
        $this->originalLogStream = Logger::getLogStream();

        // Set the temporary stream for logging
        Logger::setLogStream($this->tempStream);
    }

    protected function tearDown(): void {
        // Restore the original log stream
        if (is_resource($this->originalLogStream)) {
            Logger::setLogStream($this->originalLogStream);
        }

        // Close and remove the temporary file
        if (is_resource($this->tempStream)) {
            fclose($this->tempStream);
            $this->tempStream = null;
        }

        if (is_string($this->tempFile) && file_exists($this->tempFile)) {
            unlink($this->tempFile);
            $this->tempFile = null;
        }
    }

    private function getLogOutput(): string {
        if ($this->tempFile === null || !file_exists($this->tempFile)) {
            return '';
        }

        // Ensure all data is written to the file
        if ($this->tempStream !== null) {
            fflush($this->tempStream);
        }

        $content = file_get_contents($this->tempFile);
        return $content !== false ? $content : '';
    }

    public function testDebugMethodLogsMessageWithDebugLevel(): void {
        // Given
        Logger::setMinLevel(Logger::DEBUG);
        $message = "Test debug message";
        $context = ["key" => "value"];

        // When
        Logger::debug($message, $context);
        $output = $this->getLogOutput();

        // Then
        Assert::assertStringContainsString("[DEBUG] $message", $output);
        $contextJson = json_encode($context);
        Assert::assertStringContainsString($contextJson !== false ? $contextJson : '{}', $output);
    }

    public function testInfoMethodLogsMessageWithInfoLevel(): void {
        // Given
        $message = "Test info message";
        $context = ["key" => "value"];

        // When
        Logger::info($message, $context);
        $output = $this->getLogOutput();

        // Then
        Assert::assertStringContainsString("[INFO] $message", $output);
        $contextJson = json_encode($context);
        Assert::assertStringContainsString($contextJson !== false ? $contextJson : '{}', $output);
    }

    public function testWarningMethodLogsMessageWithWarningLevel(): void {
        // Given
        $message = "Test warning message";
        $context = ["key" => "value"];

        // When
        Logger::warning($message, $context);
        $output = $this->getLogOutput();

        // Then
        Assert::assertStringContainsString("[WARNING] $message", $output);
        $contextJson = json_encode($context);
        Assert::assertStringContainsString($contextJson !== false ? $contextJson : '{}', $output);
    }

    public function testErrorMethodLogsMessageWithErrorLevel(): void {
        // Given
        $message = "Test error message";
        $context = ["key" => "value"];

        // When
        Logger::error($message, $context);
        $output = $this->getLogOutput();

        // Then
        Assert::assertStringContainsString("[ERROR] $message", $output);
        $contextJson = json_encode($context);
        Assert::assertStringContainsString($contextJson !== false ? $contextJson : '{}', $output);
    }

    public function testLogLevelFilteringPreventsLowerLevelLogs(): void {
        // Given
        Logger::setMinLevel(Logger::WARNING);

        // When
        Logger::debug("Debug message");
        Logger::info("Info message");
        $output = $this->getLogOutput();

        // Then
        Assert::assertEmpty($output);
    }

    public function testLogLevelFilteringAllowsHigherLevelLogs(): void {
        // Given
        Logger::setMinLevel(Logger::WARNING);

        // When
        Logger::warning("Warning message");
        Logger::error("Error message");
        $output = $this->getLogOutput();

        // Then
        Assert::assertStringContainsString("[WARNING] Warning message", $output);
        Assert::assertStringContainsString("[ERROR] Error message", $output);
    }

    public function testSetMinLevelThrowsExceptionForInvalidLevel(): void {
        // Given
        $invalidLevel = "INVALID_LEVEL";

        // When & Then
        $this->expectException(\InvalidArgumentException::class);
        Logger::setMinLevel($invalidLevel);
    }

    public function testGetMinLevelReturnsCurrentLevel(): void {
        // Given
        Logger::setMinLevel(Logger::DEBUG);

        // When
        $level = Logger::getMinLevel();

        // Then
        Assert::assertEquals(Logger::DEBUG, $level);
    }

    public function testLogFormatsTimestampCorrectly(): void {
        // Given
        $message = "Test message";

        // When
        Logger::info($message);
        $output = $this->getLogOutput();

        // Then
        Assert::assertMatchesRegularExpression('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/', $output);
    }

    public function testLogWithEmptyContextDoesNotAddJsonObject(): void {
        // Given
        $message = "Test message with empty context";

        // When
        Logger::info($message);
        $output = $this->getLogOutput();

        // Then
        Assert::assertStringContainsString("[INFO] $message", $output);
        Assert::assertStringNotContainsString("{}", $output);
    }

    public function testEnvironmentVariableConfiguration(): void {
        // Given
        $originalEnv = getenv('LOG_LEVEL');
        putenv('LOG_LEVEL=DEBUG');

        // When
        $logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
        Logger::setMinLevel($logLevel);

        // Then
        Assert::assertEquals(Logger::DEBUG, Logger::getMinLevel());

        // Cleanup
        if ($originalEnv !== false) {
            putenv("LOG_LEVEL=$originalEnv");
        } else {
            putenv('LOG_LEVEL');
        }
        Logger::setMinLevel(Logger::INFO); // Reset to default
    }

    public function testEnvironmentVariableFallbackToInfo(): void {
        // Given
        $originalEnv = getenv('LOG_LEVEL');
        putenv('LOG_LEVEL'); // Unset the environment variable

        // When
        $logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
        Logger::setMinLevel($logLevel);

        // Then
        Assert::assertEquals(Logger::INFO, Logger::getMinLevel());

        // Cleanup
        if ($originalEnv !== false) {
            putenv("LOG_LEVEL=$originalEnv");
        }
    }
}
