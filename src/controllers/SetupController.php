<?php

namespace Cronbeat\Controllers;

require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/setup.view.php';

use Cronbeat\Views\SetupView;

class SetupController extends BaseController {
    public function doRouting() {
        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = !empty($pathParts[0]) ? $pathParts[0] : 'index';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        switch ($action) {
            case 'index':
                $this->showSetupForm();
                break;
            case 'process':
                $this->processSetupForm();
                break;
            default:
                $this->showSetupForm();
                break;
        }
    }
    
    public function processSetupForm() {
        error_log("Setup: Processing form submission");
        
        if (isset($_POST['username']) && isset($_POST['password_hash'])) {
            $username = trim($_POST['username']);
            $passwordHash = $_POST['password_hash'];
            
            error_log("Setup: Form data received - username: " . $username);
            
            $error = $this->validateSetupData($username, $passwordHash);
            
            if ($error !== null) {
                error_log("Setup: Validation error - " . $error);
            }
            
            if ($error === null) {
                error_log("Setup: Validation successful, running setup");
                $error = $this->runSetup($username, $passwordHash);
                
                if ($error === null) {
                    error_log("Setup: Setup successful, redirecting to login");
                    header('Location: /login');
                    exit;
                } else {
                    error_log("Setup: Setup failed - " . $error);
                }
            }
            
            error_log("Setup: Showing setup form with error: " . ($error ?? 'none'));
            $this->showSetupForm($error);
        } else {
            error_log("Setup: Form data missing, showing setup form");
            $this->showSetupForm();
        }
    }
    
    public function showSetupForm($error = null) {
        error_log("Setup: Showing setup form" . ($error ? " with error: " . $error : " without error"));
        $view = new SetupView();
        $view->setError($error);
        $this->render($view);
    }
    
    public function validateSetupData($username, $passwordHash) {
        if (empty($username) || empty($passwordHash)) {
            return 'Username and password are required';
        } elseif (strlen($username) < 3) {
            return 'Username must be at least 3 characters';
        }
        
        return null;
    }
    
    public function runSetup($username, $passwordHash) {
        try {
            $this->database->createDatabase();
            $result = $this->database->createUser($username, $passwordHash);
            
            // Log the result of the database operation
            error_log("Setup: Database creation result: " . ($result ? "success" : "failure"));
            
            if (!$result) {
                return 'Failed to create user. Please check the logs for more information.';
            }
            
            return null;
        } catch (\Exception $e) {
            $errorMessage = 'Error creating user: ' . $e->getMessage();
            error_log("Setup error: " . $errorMessage);
            return $errorMessage;
        }
    }
}