<?php

// Define application root directory
define('APP_DIR', dirname(__DIR__));

// Include only the necessary files
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

// Initialize database to check if it exists
$database = new Database();

// Redirect to setup if database doesn't exist and not already on setup page
if (!$database->databaseExists() && $controllerName !== 'setup') {
    header('Location: /setup');
    exit;
}

// Create the appropriate controller and let it handle routing
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

// Let the controller handle its own routing
$controller->doRouting();
?>