<?php

// Define application root directory
define('APP_DIR', dirname(__DIR__));

// Include class files
require_once __DIR__ . '/classes/database.php';
require_once __DIR__ . '/classes/ui.php';
require_once __DIR__ . '/views/base.view.php';
require_once __DIR__ . '/views/setup.view.php';
require_once __DIR__ . '/views/login.view.php';
require_once __DIR__ . '/views/dashboard.view.php';

use Cronbeat\Database;
use Cronbeat\UI;

// Initialize classes
$database = new Database();
$ui = new UI();

// Handle form submissions
$action = $_POST['action'] ?? null;
$error = null;

if ($action === 'setup' && isset($_POST['username']) && isset($_POST['password_hash'])) {
    $username = trim($_POST['username']);
    $passwordHash = $_POST['password_hash'];
    
    // Validate input
    if (empty($username) || empty($passwordHash)) {
        $error = 'Username and password are required';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters';
    } else {
        try {
            // Create database and user
            $database->createDatabase();
            $database->createUser($username, $passwordHash);
            
            // Redirect to login page
            header('Location: index.php');
            exit;
        } catch (\Exception $e) {
            $error = 'Error creating user: ' . $e->getMessage();
        }
    }
}

// Check if database exists
if (!$database->databaseExists()) {
    // Show setup form
    echo $ui->renderSetupForm($error);
} else {
    // Show login form (dummy for now)
    echo $ui->renderLoginForm();
}
?>