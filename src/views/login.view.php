<?php

namespace Cronbeat\Views;

class LoginView extends BaseView {
    protected ?string $error = null;

    public function __construct() {
        $this->setTitle('Login');
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $title = $this->title;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/login.html.php';

        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');

        return parent::render();
    }
}
