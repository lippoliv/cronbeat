<?php

// Define application root directory
define('APP_DIR', dirname(__DIR__));

// Include class files
require_once APP_DIR . '/src/classes/database.php';
require_once APP_DIR . '/src/classes/ui.php';
require_once APP_DIR . '/src/views/base.view.php';
require_once APP_DIR . '/src/views/setup.view.php';
require_once APP_DIR . '/src/views/login.view.php';
require_once APP_DIR . '/src/views/dashboard.view.php';
require_once APP_DIR . '/src/controllers/BaseController.php';
require_once APP_DIR . '/src/controllers/SetupController.php';
require_once APP_DIR . '/src/controllers/LoginController.php';
require_once APP_DIR . '/src/controllers/DashboardController.php';

use Cronbeat\Database;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Controllers\LoginController;
use Cronbeat\Controllers\DashboardController;

// Parse the URL
$uri = $_SERVER['REQUEST_URI'];
$uri = trim($uri, '/');
$uri = explode('/', $uri);

// Get the controller and action
$controllerName = !empty($uri[0]) ? $uri[0] : 'login';
$action = isset($uri[1]) ? $uri[1] : 'index';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = 'process';
}

// Initialize database to check if it exists
$database = new Database();

// Route to the appropriate controller
if (!$database->databaseExists() && $controllerName !== 'setup') {
    // Redirect to setup if database doesn't exist
    header('Location: /setup');
    exit;
} else {
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
    
    // Call the appropriate method
    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
}
?>