<?php

require_once 'src/classes/Logger.php';

use Cronbeat\Logger;

// Test all log levels
Logger::setMinLevel(Logger::DEBUG);
Logger::debug("This is a debug message", ["test" => true]);
Logger::info("This is an info message", ["test" => true]);
Logger::warning("This is a warning message", ["test" => true]);
Logger::error("This is an error message", ["test" => true]);

echo "Logger test completed successfully\n";