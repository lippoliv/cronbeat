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
                return $this->showLoginForm();
            case 'process':
                return $this->processLogin();
            default:
                return $this->showLoginForm();
        }
    }

    public function showLoginForm() {
        $view = new LoginView();
        return $this->render($view);
    }

    public function processLogin() {
        $error = null;

        if (isset($_POST['username']) && isset($_POST['password_hash'])) {
            $username = trim($_POST['username']);
            $passwordHash = $_POST['password_hash'];

            if (empty($username) || empty($passwordHash)) {
                $error = 'Username and password are required';
            } else {
                $error = "hello $username";
            }
        } else {
            $error = 'Username and password are required';
        }

        $view = new LoginView();
        $view->setError($error);
        return $this->render($view);
    }
}
