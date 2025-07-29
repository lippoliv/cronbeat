<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\LoginView;

class LoginController extends BaseController {
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