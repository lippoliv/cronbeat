<?php

const APP_DIR = __DIR__;

require_once APP_DIR . '/classes/UrlHelper.php';
require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/Logger.php';
require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/base.view.php';
require_once APP_DIR . '/views/setup.view.php';
require_once APP_DIR . '/views/login.view.php';
require_once APP_DIR . '/controllers/SetupController.php';
require_once APP_DIR . '/controllers/LoginController.php';

use Cronbeat\Controllers\LoginController;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Database;
use Cronbeat\Logger;
use Cronbeat\UrlHelper;

// Initialize logger with INFO level
Logger::setMinLevel(Logger::INFO);
Logger::info("Application starting", ['controller' => UrlHelper::parseControllerFromUrl()]);

$controllerName = UrlHelper::parseControllerFromUrl();

$database = new Database(__DIR__ . '/db/db.sqlite');
Logger::debug("Database initialized");

if (!$database->databaseExists() && $controllerName !== 'setup') {
    header('Location: /setup');
    exit;
}

Logger::info("Routing to controller", ['controller' => $controllerName]);

switch ($controllerName) {
    case 'setup':
        $controller = new SetupController($database);
        Logger::info("Setup controller initialized");
        break;
    case 'login':
    default:
        $controller = new LoginController($database);
        Logger::info("Login controller initialized");
        break;
}

try {
    $output = $controller->doRouting();
    Logger::info("Controller routing completed successfully");
    echo $output;
} catch (\Exception $e) {
    Logger::error("Error during controller routing", [
        'error' => $e->getMessage(),
        'controller' => $controllerName
    ]);
    // Re-throw the exception to maintain original error handling
    throw $e;
}
