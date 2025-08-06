<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\LoginView;

class LoginController extends BaseController {
    public function doRouting(): string {
        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = ($pathParts[0] !== '') ? $pathParts[0] : 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }

        switch ($action) {
            case 'index':
                return $this->showLoginForm();
            case 'process':
                return $this->processLogin();
            case 'logout':
                return $this->logout();
            default:
                return $this->showLoginForm();
        }
    }
    
    public function logout(): string {
        Logger::info("User logging out");
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session variables
        $_SESSION = [];
        
        // Destroy the session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // Redirect to login page
        header('Location: /login');
        exit;
    }

    public function showLoginForm(): string {
        $view = new LoginView();
        return $view->render();
    }

    public function processLogin(): string {
        $error = null;

        if (isset($_POST['username']) && isset($_POST['password_hash'])) {
            $username = trim($_POST['username']);
            $passwordHash = $_POST['password_hash'];

            if ($username === '' || $passwordHash === '') {
                $error = 'Username and password are required';
            } else {
                if ($this->database->validateUser($username, $passwordHash)) {
                    // Start session and store user information
                    session_start();
                    $_SESSION['user'] = $username;
                    $_SESSION['logged_in'] = true;
                    
                    // Redirect to dashboard
                    header('Location: /dashboard');
                    exit;
                } else {
                    $error = 'Invalid username or password';
                }
            }
        } else {
            $error = 'Username and password are required';
        }

        $view = new LoginView();
        $view->setError($error);
        return $view->render();
    }
}
