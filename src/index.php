<?php

const APP_DIR = __DIR__;
const DB_VERSION = 4; // Current expected database version

require_once APP_DIR . '/classes/UrlHelper.php';
require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/MonitorData.php';
require_once APP_DIR . '/classes/PingData.php';
require_once APP_DIR . '/classes/UserProfileData.php';
require_once APP_DIR . '/classes/Logger.php';
require_once APP_DIR . '/classes/Migration.php';
require_once APP_DIR . '/classes/MigrationHelper.php';
require_once APP_DIR . '/classes/RedirectException.php';
require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/base.view.php';
require_once APP_DIR . '/views/setup.view.php';
require_once APP_DIR . '/views/login.view.php';
require_once APP_DIR . '/views/migrate.view.php';
require_once APP_DIR . '/controllers/SetupController.php';
require_once APP_DIR . '/controllers/LoginController.php';
require_once APP_DIR . '/controllers/LogoutController.php';
require_once APP_DIR . '/controllers/MigrateController.php';
require_once APP_DIR . '/controllers/DashboardController.php';
require_once APP_DIR . '/controllers/MonitorController.php';
require_once APP_DIR . '/controllers/ApiController.php';
require_once APP_DIR . '/views/dashboard.view.php';
require_once APP_DIR . '/views/monitor_form.view.php';
require_once APP_DIR . '/views/monitor_history.view.php';
require_once APP_DIR . '/controllers/ProfileController.php';
require_once APP_DIR . '/views/profile.view.php';

use Cronbeat\Controllers\LoginController;
use Cronbeat\Controllers\LogoutController;
use Cronbeat\Controllers\MigrateController;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Controllers\DashboardController;
use Cronbeat\Controllers\MonitorController;
use Cronbeat\Controllers\ApiController;
use Cronbeat\Controllers\ProfileController;
use Cronbeat\Database;
use Cronbeat\Logger;
use Cronbeat\RedirectException;
use Cronbeat\UrlHelper;

$logLevel = getenv('LOG_LEVEL') !== false ? getenv('LOG_LEVEL') : Logger::INFO;
Logger::setMinLevel($logLevel);

session_start();

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
    case 'api':
        $controller = new ApiController($database);
        break;
    case 'dashboard':
        $controller = new DashboardController($database);
        break;
    case 'monitor':
        $controller = new MonitorController($database);
        break;
    case 'profile':
        $controller = new ProfileController($database);
        break;
    case 'logout':
        $controller = new LogoutController($database);
        break;
    case 'login':
    default:
        $controller = new LoginController($database);
        break;
}

try {
    echo $controller->doRouting();
} catch (RedirectException $e) {
    // Set headers from the exception
    foreach ($e->getHeaders() as $name => $value) {
        header("$name: $value");
    }
    exit;
}
