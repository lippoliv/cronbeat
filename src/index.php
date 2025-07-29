<?php

define('APP_DIR', dirname(__DIR__));

require_once APP_DIR . '/src/classes/database.php';
require_once APP_DIR . '/src/controllers/BaseController.php';
require_once APP_DIR . '/src/controllers/SetupController.php';
require_once APP_DIR . '/src/controllers/LoginController.php';
require_once APP_DIR . '/src/controllers/DashboardController.php';

use Cronbeat\Database;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Controllers\LoginController;
use Cronbeat\Controllers\DashboardController;

function parseControllerFromUrl() {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = trim($uri, '/');
    $uri = explode('/', $uri);
    return !empty($uri[0]) ? $uri[0] : 'login';
}

$controllerName = parseControllerFromUrl();

$database = new Database();

if (!$database->databaseExists() && $controllerName !== 'setup') {
    header('Location: /setup');
    exit;
}

switch ($controllerName) {
    case 'setup':
        $controller = new SetupController();
        break;
    case 'dashboard':
        $controller = new DashboardController();
        break;
    case 'login':
    default:
        $controller = new LoginController();
        break;
}

$controller->doRouting();
?>