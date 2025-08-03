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
            default:
                return $this->showLoginForm();
        }
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
                    $error = "Login successful for $username";
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
