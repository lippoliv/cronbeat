<?php

define('APP_DIR', __DIR__);

require_once APP_DIR . '/classes/UrlHelper.php';
require_once APP_DIR . '/classes/database.php';
require_once APP_DIR . '/controllers/SetupController.php';
require_once APP_DIR . '/controllers/LoginController.php';

use Cronbeat\Database;
use Cronbeat\UrlHelper;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Controllers\LoginController;

$controllerName = UrlHelper::parseControllerFromUrl();

$database = new Database();

if (!$database->databaseExists() && $controllerName !== 'setup') {
    header('Location: /setup');
    exit;
}

switch ($controllerName) {
    case 'setup':
        $controller = new SetupController();
        break;
    case 'login':
    default:
        $controller = new LoginController();
        break;
}

$controller->doRouting();
?>