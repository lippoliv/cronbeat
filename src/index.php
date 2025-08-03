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

$logLevel = getenv('LOG_LEVEL') ?: Logger::INFO;
Logger::setMinLevel($logLevel);

$controllerName = UrlHelper::parseControllerFromUrl();

$database = new Database(__DIR__ . '/db/db.sqlite');

if (!$database->databaseExists() && $controllerName !== 'setup') {
    header('Location: /setup');
    exit;
}

switch ($controllerName) {
    case 'setup':
        $controller = new SetupController($database);
        break;
    case 'login':
    default:
        $controller = new LoginController($database);
        break;
}

echo $controller->doRouting();
