<?php

const APP_DIR = __DIR__;
const DB_VERSION = 2; // Current expected database version

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

// Initialize database
$database = new Database(APP_DIR . '/db/db.sqlite');

// Initialize CLI and run
$cli = new CLI($database, array_slice($argv, 1));
$cli->run();