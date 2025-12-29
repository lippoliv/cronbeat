<?php

namespace Cronbeat\Views;

class DashboardView extends BaseView {
    protected ?string $error = null;
    protected ?string $success = null;
    /** @var array<\Cronbeat\MonitorData> */
    protected array $monitors = [];

    public function __construct() {
        $this->setTitle('Dashboard');
        $this->setContainerClass('view-container');
        $this->setShowHeader(true);
        $this->setIsDashboard(true);
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function setSuccess(?string $success): self {
        $this->success = $success;
        return $this;
    }

    /**
     * @param array<\Cronbeat\MonitorData> $monitors
     */
    public function setMonitors(array $monitors): self {
        $this->monitors = $monitors;
        return $this;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $success = $this->success;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $monitors = $this->monitors;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/dashboard.html.php';

        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');

        return parent::render();
    }
}
