<?php

require_once __DIR__ . '/src/classes/Logger.php';

use Cronbeat\Logger;

echo "Testing Logger with environment variables...\n";

// Default behavior (should use INFO level)
echo "\n1. Default behavior (no environment variable):\n";
$logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
Logger::setMinLevel($logLevel);
Logger::debug("This debug message should NOT be displayed");
Logger::info("This info message should be displayed");

// Set environment variable to DEBUG
echo "\n2. With LOG_LEVEL=DEBUG environment variable:\n";
putenv("LOG_LEVEL=DEBUG");
$logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
Logger::setMinLevel($logLevel);
Logger::debug("This debug message should be displayed");
Logger::info("This info message should be displayed");

// Set environment variable to WARNING
echo "\n3. With LOG_LEVEL=WARNING environment variable:\n";
putenv("LOG_LEVEL=WARNING");
$logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
Logger::setMinLevel($logLevel);
Logger::debug("This debug message should NOT be displayed");
Logger::info("This info message should NOT be displayed");
Logger::warning("This warning message should be displayed");

// Reset environment variable
putenv("LOG_LEVEL");
echo "\n4. After resetting environment variable (should fall back to INFO):\n";
$logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
Logger::setMinLevel($logLevel);
Logger::debug("This debug message should NOT be displayed");
Logger::info("This info message should be displayed");

echo "\nEnvironment variable test completed.\n";