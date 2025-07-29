<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\SetupView;

class SetupController extends BaseController {
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