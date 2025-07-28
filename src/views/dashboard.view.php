<?php

namespace Cronbeat\Views;

class DashboardView extends BaseView {
    /**
     * Constructor
     */
    public function __construct() {
        $this->setTitle('CronBeat Dashboard');
    }

    /**
     * Render the view
     *
     * @return string The rendered HTML
     */
    public function render(): string {
        // Start output buffering
        ob_start();
        
        // Include the template file
        include __DIR__ . '/dashboard.html.php';
        
        // Set the content for the base view
        $this->setContent(ob_get_clean());
        
        // Render the base view
        return parent::render();
    }
}