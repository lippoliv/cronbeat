<?php

namespace Cronbeat\Controllers;

require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/login.view.php';

use Cronbeat\Views\LoginView;

class LoginController extends BaseController {
    public function doRouting() {
        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = !empty($pathParts[0]) ? $pathParts[0] : 'index';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        switch ($action) {
            case 'index':
                $this->showLoginForm();
                break;
            case 'process':
                $this->processLogin();
                break;
            default:
                $this->showLoginForm();
                break;
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