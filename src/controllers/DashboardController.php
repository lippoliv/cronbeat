<?php

namespace Cronbeat\Controllers;

use Cronbeat\Logger;
use Cronbeat\Views\DashboardView;
use Cronbeat\Views\MonitorFormView;

class DashboardController extends BaseController {
    public function doRouting(): string {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
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
        $username = $this->database->getUsername($userId);

        $view = new DashboardView();
        $view->setUsername($username !== false ? $username : 'Unknown');
        $view->setMonitors($monitors);

        return $view->render();
    }

    public function addMonitor(): string {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = trim($_POST['name']);

            if ($name === '') {
                // Show error on monitor form
                $view = new MonitorFormView();
                $view->setError('Monitor name is required');
                return $view->render();
            } else {
                $userId = $_SESSION['user_id'];
                $result = $this->database->createMonitor($name, $userId);

                if ($result !== false) {
                    // Redirect to dashboard on success
                    header('Location: /dashboard');
                    exit;
                } else {
                    // Show error on monitor form
                    $view = new MonitorFormView();
                    $view->setError('Failed to create monitor');
                    return $view->render();
                }
            }
        }

        // If not POST request, show the form
        $view = new MonitorFormView();
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
        $username = $this->database->getUsername($userId);

        $view = new DashboardView();
        $view->setUsername($username !== false ? $username : 'Unknown');
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
