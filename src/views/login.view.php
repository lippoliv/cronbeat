<?php

namespace Cronbeat\Views;

require_once APP_DIR . '/views/base.view.php';

class LoginView extends BaseView {
    protected $error = null;

    public function __construct() {
        $this->setTitle('CronBeat Login');
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function render(): string {
        $error = $this->error;
        
        ob_start();
        
        include APP_DIR . '/views/login.html.php';
        
        $this->setContent(ob_get_clean());
        
        return parent::render();
    }
}