<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\SetupView;

class SetupController extends BaseController {
    public function doRouting(): string {
        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = ($pathParts[0] !== '') ? $pathParts[0] : 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }

        switch ($action) {
            case 'index':
                return $this->showSetupForm();
            case 'process':
                return $this->processSetupForm();
            default:
                return $this->showSetupForm();
        }
    }

    public function processSetupForm(): string {
        if (isset($_POST['username']) && isset($_POST['password_hash'])) {
            $username = trim($_POST['username']);
            $passwordHash = $_POST['password_hash'];

            $error = $this->validateSetupData($username, $passwordHash);

            if ($error === null) {
                $error = $this->runSetup($username, $passwordHash);

                if ($error === null) {
                    header('Location: /login');
                    exit;
                }
            }

            return $this->showSetupForm($error);
        } else {
            return $this->showSetupForm();
        }
    }

    public function showSetupForm(?string $error = null): string {
        $view = new SetupView();
        $view->setError($error);
        return $view->render();
    }

    public function validateSetupData(string $username, string $passwordHash): ?string {
        if ($username === '' || $passwordHash === '') {
            return 'Username and password are required';
        } elseif (strlen($username) < 3) {
            return 'Username must be at least 3 characters';
        }

        return null;
    }

    public function runSetup(string $username, string $passwordHash): ?string {
        try {
            $this->database->createDatabase();
            
            \Cronbeat\Logger::info("Running migrations during setup");
            
            $migrations = $this->database->getAllMigrations();
            foreach ($migrations as $migration) {
                try {
                    $this->database->runMigration($migration);
                } catch (\Exception $e) {
                    $version = $migration->getVersion();
                    \Cronbeat\Logger::error("Migration failed during setup", ['version' => $version, 'error' => $e->getMessage()]);
                    return "Migration to version {$version} failed: " . $e->getMessage();
                }
            }
            
            $result = $this->database->createUser($username, $passwordHash);

            if (!$result) {
                return 'Failed to create user. Please check the logs for more information.';
            }

            return null;
        } catch (\Exception $e) {
            return 'Error creating user: ' . $e->getMessage();
        }
    }
}
