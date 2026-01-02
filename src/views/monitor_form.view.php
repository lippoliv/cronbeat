<?php

namespace Cronbeat\Views;

class MonitorFormView extends BaseView {
    private ?string $error = null;

    public function __construct() {
        $this->setTitle('Add Monitor');
        // Render as a full-screen view similar to edit/history pages
        $this->setContainerClass('view-container');
        $this->setShowHeader(true);
    }

    public function setError(?string $error): void {
        $this->error = $error;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/monitor_form.html.php';

        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');

        return parent::render();
    }
}
