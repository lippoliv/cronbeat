<?php

namespace Cronbeat\Views;

class MonitorFormView extends BaseView {
    private ?string $error = null;
    private ?string $success = null;

    public function __construct() {
        parent::__construct();
        $this->setTitle('Add Monitor - CronBeat');
        $this->setTemplate('monitor_form.html.php');
    }

    public function setError(?string $error): void {
        $this->error = $error;
    }

    public function setSuccess(?string $success): void {
        $this->success = $success;
    }

    protected function getTemplateVars(): array {
        return [
            'error' => $this->error,
            'success' => $this->success
        ];
    }
}