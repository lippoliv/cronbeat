<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\LoginView;

class LoginController extends BaseController {
    public function index() {
        $view = new LoginView();
        $this->render($view);
    }
    
    public function process() {
        $error = null;
        
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $error = 'Username and password are required';
            } else {
                // Show hello message instead of redirecting to dashboard
                $error = "hello $username";
            }
        }
        
        $view = new LoginView();
        $view->setError($error);
        $this->render($view);
    }
}