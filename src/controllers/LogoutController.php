<?php

namespace Cronbeat\Controllers;

use Cronbeat\Logger;

class LogoutController extends BaseController {
    public function doRouting(): string {
        Logger::info("Processing logout request");
        return $this->logout();
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
}