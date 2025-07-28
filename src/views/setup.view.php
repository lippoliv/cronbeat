<?php

namespace Cronbeat\Views;

class SetupView extends BaseView {
    protected $error = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->setTitle('CronBeat Setup');
    }

    /**
     * Set the error message
     *
     * @param string|null $error The error message
     * @return $this
     */
    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    /**
     * Render the view
     *
     * @return string The rendered HTML
     */
    public function render(): string {
        // Extract variables for use in the template
        $error = $this->error;
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        include __DIR__ . '/setup.html.php';
        
        // Set the content for the base view
        $this->setContent(ob_get_clean());
        
        // Render the base view
        return parent::render();
    }
}