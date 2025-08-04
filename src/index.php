<?php

const APP_DIR = __DIR__;
const DB_VERSION = 2; // Current expected database version

require_once APP_DIR . '/classes/UrlHelper.php';
require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/Logger.php';
require_once APP_DIR . '/classes/Migration.php';
require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/base.view.php';
require_once APP_DIR . '/views/setup.view.php';
require_once APP_DIR . '/views/login.view.php';
require_once APP_DIR . '/views/migrate.view.php';
require_once APP_DIR . '/controllers/SetupController.php';
require_once APP_DIR . '/controllers/LoginController.php';
require_once APP_DIR . '/controllers/MigrateController.php';

use Cronbeat\Controllers\LoginController;
use Cronbeat\Controllers\MigrateController;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Database;
use Cronbeat\Logger;
use Cronbeat\UrlHelper;

$logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
Logger::setMinLevel($logLevel);

$controllerName = UrlHelper::parseControllerFromUrl();

$database = new Database(__DIR__ . '/db/db.sqlite');

// Check if database exists
if (!$database->databaseExists() && $controllerName !== 'setup') {
    header('Location: /setup');
    exit;
}

// Check if database needs migration
if ($database->databaseExists() && $controllerName !== 'migrate' && $controllerName !== 'setup') {
    if ($database->needsMigration(DB_VERSION)) {
        Logger::info("Database needs migration, redirecting to migrate page");
        header('Location: /migrate');
        exit;
    }
}

switch ($controllerName) {
    case 'setup':
        $controller = new SetupController($database);
        break;
    case 'migrate':
        $controller = new MigrateController($database);
        break;
    case 'login':
    default:
        $controller = new LoginController($database);
        break;
}

echo $controller->doRouting();
