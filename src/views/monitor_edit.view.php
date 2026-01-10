<?php

namespace Cronbeat\Views;

class MonitorEditView extends BaseView {
    private string $monitorUuid = '';
    private string $name = '';
    private ?string $error = null;

    public function __construct() {
        $this->setTitle('Edit Monitor');
        $this->setContainerClass('view-container');
        $this->setShowHeader(true);
    }

    public function setMonitorUuid(string $uuid): self {
        $this->monitorUuid = $uuid;
        return $this;
    }

    public function setName(string $name): self {
        $this->name = $name;
        return $this;
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $monitorUuid = $this->monitorUuid;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $name = $this->name;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;

        ob_start();
        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/monitor_edit.html.php';
        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');
        return parent::render();
    }
}
