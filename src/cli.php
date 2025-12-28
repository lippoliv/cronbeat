<?php

const APP_DIR = __DIR__;
const DB_VERSION = 1; // Current expected database version

require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/Logger.php';
require_once APP_DIR . '/classes/Migration.php';
require_once APP_DIR . '/classes/CLI/CLI.php';

use Cronbeat\Database;
use Cronbeat\Logger;
use Cronbeat\CLI\CLI;

// Set up logging
$logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
Logger::setMinLevel($logLevel);

// Configure logger to write to cli.log file instead of STDOUT
$logFile = APP_DIR . '/cli.log';
$logStream = fopen($logFile, 'a');
if ($logStream === false) {
    throw new \RuntimeException("Unable to open log file: $logFile");
}
Logger::setLogStream($logStream);

// Initialize database with default path inside the application directory
$database = new Database(APP_DIR . '/db/db.sqlite');

// Initialize CLI and run
$cli = new CLI($database, array_slice($argv, 1));
$cli->run();
