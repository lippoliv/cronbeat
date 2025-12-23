<?php

namespace Cronbeat\Controllers;

use Cronbeat\Logger;
use Cronbeat\RedirectException;
use Cronbeat\Views\ProfileView;

class ProfileController extends BaseController {
    public function doRouting(): string {
        if (!isset($_SESSION['user_id'])) {
            Logger::warning("Unauthorized access attempt to profile");
            throw new RedirectException(['Location' => '/login']);
        }

        $path = $this->parsePathWithoutController();
        $pathParts = explode('/', $path);
        $action = ($pathParts[0] !== '') ? $pathParts[0] : 'index';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $action === 'password' ? 'change-password' : 'update-profile';
        }

        switch ($action) {
            case 'index':
                return $this->showProfile();
            case 'update-profile':
                return $this->updateProfile();
            case 'change-password':
                return $this->changePassword();
            default:
                return $this->showProfile();
        }
    }

    public function showProfile(?string $error = null, ?string $success = null): string {
        $userId = (int)$_SESSION['user_id'];
        $profile = $this->database->getUserProfile($userId);

        $view = new ProfileView();
        if ($profile !== false) {
            $view->setUsername($profile->getUsername());
            $view->setName($profile->getName() ?? '');
            $view->setEmail($profile->getEmail() ?? '');
        }
        if ($error !== null) {
            $view->setError($error);
        }
        if ($success !== null) {
            $view->setSuccess($success);
        }
        return $view->render();
    }

    public function updateProfile(): string {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
            $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';

            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $error = 'Please provide a valid email address';
            } else {
                $userId = (int)$_SESSION['user_id'];
                $saved = $this->database->updateUserProfile($userId, $name !== '' ? $name : null, $email !== '' ? $email : null);
                if ($saved) {
                    $success = 'Profile updated successfully';
                } else {
                    $error = 'Failed to update profile';
                }
            }
        }

        return $this->showProfile($error, $success);
    }

    public function changePassword(): string {
        $error = null;
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $passwordHash = isset($_POST['password_hash']) ? (string)$_POST['password_hash'] : '';

            if ($passwordHash === '') {
                $error = 'Password is required';
            } else {
                $userId = (int)$_SESSION['user_id'];
                $saved = $this->database->updateUserPassword($userId, $passwordHash);
                if ($saved) {
                    $success = 'Password updated successfully';
                } else {
                    $error = 'Failed to update password';
                }
            }
        }

        return $this->showProfile($error, $success);
    }
}
