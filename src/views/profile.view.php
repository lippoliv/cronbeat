<?php

namespace Cronbeat\Views;

class ProfileView extends BaseView {
    protected ?string $error = null;
    protected ?string $success = null;
    protected string $username = '';
    protected string $name = '';
    protected string $email = '';

    public function __construct() {
        $this->setTitle('Your Profile');
        // Reuse dashboard container styling for profile while keeping a specific hook
        $this->setContainerClass('dashboard-container profile-container');
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function setSuccess(?string $success): self {
        $this->success = $success;
        return $this;
    }

    public function setUsername(string $username): self {
        $this->username = $username;
        return $this;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setEmail(string $email): self {
        $this->email = $email;
        return $this;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $success = $this->success;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $username = $this->username;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $name = $this->name;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $email = $this->email;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/profile.html.php';

        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');

        return parent::render();
    }
}
