<?php

namespace Cronbeat\Views;

class DashboardView extends BaseView {
    public function __construct() {
        $this->setTitle('CronBeat Dashboard');
    }

    public function render(): string {
        ob_start();
        
        include APP_DIR . '/src/views/dashboard.html.php';
        
        $this->setContent(ob_get_clean());
        
        return parent::render();
    }
}