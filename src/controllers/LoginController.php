<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\LoginView;

class LoginController extends BaseController {
    private $routeMap = [
        'index' => 'showLoginForm',
        'process' => 'processLogin'
    ];
    
    public function doRouting() {
        $path = $this->parsePath();
        $action = !empty($path[0]) ? $path[0] : 'index';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        if (isset($this->routeMap[$action]) && method_exists($this, $this->routeMap[$action])) {
            $method = $this->routeMap[$action];
            $this->$method();
        } else {
            $this->showLoginForm();
        }
    }
    
    public function showLoginForm() {
        $view = new LoginView();
        $this->render($view);
    }
    
    public function processLogin() {
        $error = null;
        
        if (isset($_POST['username']) && isset($_POST['password'])) {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            
            if (empty($username) || empty($password)) {
                $error = 'Username and password are required';
            } else {
                $error = "hello $username";
            }
        }
        
        $view = new LoginView();
        $view->setError($error);
        $this->render($view);
    }
}