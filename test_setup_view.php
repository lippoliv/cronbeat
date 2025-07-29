<?php

define('APP_DIR', __DIR__ . '/src');

require_once APP_DIR . '/views/base.view.php';
require_once APP_DIR . '/views/setup.view.php';

use Cronbeat\Views\SetupView;

try {
    $view = new SetupView();
    echo "SetupView class instantiated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}