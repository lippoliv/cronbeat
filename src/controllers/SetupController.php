<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\SetupView;

class SetupController extends BaseController {
    public function doRouting() {
        // Parse the URL
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        
        // Get the action
        $action = isset($uri[1]) ? $uri[1] : 'index';
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        // Call the appropriate method
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }
    
    public function index() {
        $this->showSetupForm();
    }
    
    public function process() {
        if (isset($_POST['username']) && isset($_POST['password_hash'])) {
            $username = trim($_POST['username']);
            $passwordHash = $_POST['password_hash'];
            
            $error = $this->validateSetupData($username, $passwordHash);
            
            if ($error === null) {
                $error = $this->runSetup($username, $passwordHash);
                
                if ($error === null) {
                    header('Location: /login');
                    exit;
                }
            }
            
            $this->showSetupForm($error);
        } else {
            $this->showSetupForm();
        }
    }
    
    public function showSetupForm($error = null) {
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
            $this->database->createUser($username, $passwordHash);
            return null;
        } catch (\Exception $e) {
            return 'Error creating user: ' . $e->getMessage();
        }
    }
}