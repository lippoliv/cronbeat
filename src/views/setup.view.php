<?php

namespace Cronbeat\Views;

class SetupView extends BaseView {
    protected $error = null;

    public function __construct() {
        $this->setTitle('CronBeat Setup');
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function render(): string {
        $error = $this->error;
        
        ob_start();
        
        include APP_DIR . '/src/views/setup.html.php';
        
        $this->setContent(ob_get_clean());
        
        return parent::render();
    }
}