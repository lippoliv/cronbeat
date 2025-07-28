<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\SetupView;

class SetupController extends BaseController {
    public function index() {
        $view = new SetupView();
        $this->render($view);
    }
    
    public function process() {
        $error = null;
        
        if (isset($_POST['username']) && isset($_POST['password_hash'])) {
            $username = trim($_POST['username']);
            $passwordHash = $_POST['password_hash'];
            
            if (empty($username) || empty($passwordHash)) {
                $error = 'Username and password are required';
            } elseif (strlen($username) < 3) {
                $error = 'Username must be at least 3 characters';
            } else {
                try {
                    $this->database->createDatabase();
                    $this->database->createUser($username, $passwordHash);
                    
                    header('Location: /login');
                    exit;
                } catch (\Exception $e) {
                    $error = 'Error creating user: ' . $e->getMessage();
                }
            }
        }
        
        $view = new SetupView();
        $view->setError($error);
        $this->render($view);
    }
}