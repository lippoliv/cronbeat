<?php

namespace Cronbeat\Controllers;

use Cronbeat\Logger;

class LogoutController extends BaseController {
    public function doRouting(): string {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        Logger::info("Processing logout request", $userId ? ['user_id' => $userId] : []);
        return $this->logout();
    }

    public function logout(): string {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        Logger::info("User logging out", $userId ? ['user_id' => $userId] : []);

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
}
