<?php

// Include the Logger class
require_once __DIR__ . '/src/classes/Logger.php';

use Cronbeat\Logger;

// Create a logger instance with DEBUG level
$logger = new Logger(Logger::DEBUG);
echo "Testing Logger class...\n";

// Test all log levels
$logger->debug("This is a debug message");
$logger->info("This is an info message");
$logger->warning("This is a warning message");
$logger->error("This is an error message");

// Test with context data
$logger->info("User login attempt", [
    'username' => 'admin',
    'success' => true,
    'timestamp' => time()
]);

// Test changing log level
echo "\nChanging log level to WARNING...\n";
$logger->setMinLevel(Logger::WARNING);

// These should not be output
$logger->debug("This debug message should not be displayed");
$logger->info("This info message should not be displayed");

// These should be output
$logger->warning("This warning message should be displayed");
$logger->error("This error message should be displayed");

echo "\nLogger test completed.\n";