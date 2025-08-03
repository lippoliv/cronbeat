<?php

// Include the Logger class
require_once __DIR__ . '/src/classes/Logger.php';

use Cronbeat\Logger;

// Set the minimum log level to DEBUG
Logger::setMinLevel(Logger::DEBUG);
echo "Testing Logger class...\n";

// Test all log levels
Logger::debug("This is a debug message");
Logger::info("This is an info message");
Logger::warning("This is a warning message");
Logger::error("This is an error message");

// Test with context data
Logger::info("User login attempt", [
    'username' => 'admin',
    'success' => true,
    'timestamp' => time()
]);

// Test changing log level
echo "\nChanging log level to WARNING...\n";
Logger::setMinLevel(Logger::WARNING);

// These should not be output
Logger::debug("This debug message should not be displayed");
Logger::info("This info message should not be displayed");

// These should be output
Logger::warning("This warning message should be displayed");
Logger::error("This error message should be displayed");

echo "\nLogger test completed.\n";