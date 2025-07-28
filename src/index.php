<?php
// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load autoloader, but provide fallback if not available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Manual class loading if autoloader is not available
    require_once __DIR__ . '/Classes/Database.php';
    require_once __DIR__ . '/Classes/UI.php';
}

use Cronbeat\Database;
use Cronbeat\UI;

// Start session
session_start();

// Get the action from the request
$action = $_GET['action'] ?? '';

// Handle form submissions
if ($action === 'setup' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle setup form submission
    $username = $_POST['username'] ?? '';
    $hashedPassword = $_POST['hashedPassword'] ?? '';
    
    // Validate input
    if (empty($username) || empty($hashedPassword)) {
        echo UI::renderSetupForm('Username and password are required');
        exit;
    }
    
    // Create the database and user
    $db = new Database();
    if ($db->initialize() && $db->createUser($username, $hashedPassword)) {
        // Redirect to login page
        header('Location: index.php');
        exit;
    } else {
        echo UI::renderSetupForm('Failed to create user. Please try again.');
        exit;
    }
}

// Check if the database exists
if (!Database::exists()) {
    // Database doesn't exist, show setup form
    echo UI::renderSetupForm();
} else {
    // Database exists, show login form
    echo UI::renderLoginForm();
}
?>