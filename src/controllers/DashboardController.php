<?php

namespace Cronbeat\Controllers;

use Cronbeat\Logger;
use Cronbeat\Views\DashboardView;
use Cronbeat\Views\MonitorFormView;

class DashboardController extends BaseController {
    public function doRouting(): string {
        // Check if user is logged in
        session_start();
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
            Logger::warning("Unauthorized access attempt to dashboard");
            header('Location: /login');
            exit;
        }

        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = ($pathParts[0] !== '') ? $pathParts[0] : 'index';

        switch ($action) {
            case 'index':
                return $this->showDashboard();
            case 'new-monitor':
                return $this->showMonitorForm();
            case 'add':
                return $this->addMonitor();
            case 'delete':
                $uuid = $pathParts[1] ?? '';
                return $this->deleteMonitor($uuid);
            default:
                return $this->showDashboard();
        }
    }

    public function showDashboard(): string {
        $userId = $_SESSION['user_id'];
        $monitors = $this->database->getMonitors($userId);
        
        // Get username for display
        $username = $this->database->getUsernameById($userId);
        
        $view = new DashboardView();
        $view->setUsername($username);
        $view->setMonitors($monitors);
        
        return $view->render();
    }

    public function addMonitor(): string {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim($_POST['name']);
            
            if ($name === '') {
                $error = 'Monitor name is required';
            } else {
                $userId = $_SESSION['user_id'];
                $result = $this->database->createMonitor($name, $userId);
                
                if ($result !== false) {
                    $success = "Monitor '$name' created successfully";
                } else {
                    $error = "Failed to create monitor";
                }
            }
        }

        $userId = $_SESSION['user_id'];
        $monitors = $this->database->getMonitors($userId);
        
        // Get username for display
        $username = $this->database->getUsernameById($userId);
        
        $view = new DashboardView();
        $view->setUsername($username);
        $view->setMonitors($monitors);
        
        if ($error !== null) {
            $view->setError($error);
        }
        
        if ($success !== null) {
            $view->setSuccess($success);
        }
        
        return $view->render();
    }

    public function showMonitorForm(): string {
        $view = new MonitorFormView();
        return $view->render();
    }

    public function deleteMonitor(string $uuid): string {
        $error = null;
        $success = null;

        if ($uuid === '') {
            $error = 'Monitor UUID is required';
        } else {
            $userId = $_SESSION['user_id'];
            $result = $this->database->deleteMonitor($uuid, $userId);
            
            if ($result) {
                $success = "Monitor deleted successfully";
            } else {
                $error = "Failed to delete monitor";
            }
        }

        $userId = $_SESSION['user_id'];
        $monitors = $this->database->getMonitors($userId);
        
        // Get username for display
        $username = $this->database->getUsernameById($userId);
        
        $view = new DashboardView();
        $view->setUsername($username);
        $view->setMonitors($monitors);
        
        if ($error !== null) {
            $view->setError($error);
        }
        
        if ($success !== null) {
            $view->setSuccess($success);
        }
        
        return $view->render();
    }
}