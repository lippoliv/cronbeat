<?php

namespace Cronbeat\Views;

class SetupView extends BaseView {
    protected ?string $error = null;

    public function __construct() {
        $this->setTitle('CronBeat Setup');
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/setup.html.php';

        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');

        return parent::render();
    }
}
