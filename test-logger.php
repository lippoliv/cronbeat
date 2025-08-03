<?php

require_once __DIR__ . '/src/classes/Logger.php';

use Cronbeat\Logger;

// Set log level to DEBUG to see all logs
Logger::setMinLevel(Logger::DEBUG);

echo "Testing Logger functionality...\n";

// Test debug log
echo "Testing debug log...\n";
Logger::debug("This is a debug message", ["test" => "debug"]);

// Test info log
echo "Testing info log...\n";
Logger::info("This is an info message", ["test" => "info"]);

// Test warning log
echo "Testing warning log...\n";
Logger::warning("This is a warning message", ["test" => "warning"]);

// Test error log
echo "Testing error log...\n";
Logger::error("This is an error message", ["test" => "error"]);

echo "Logger test completed.\n";