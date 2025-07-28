<?php

namespace Cronbeat\Views;

class BaseView {
    protected $title = 'CronBeat';
    protected $content = '';

    /**
     * Set the page title
     *
     * @param string $title The page title
     * @return $this
     */
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the page content
     *
     * @param string $content The page content
     * @return $this
     */
    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    /**
     * Render the view
     *
     * @return string The rendered HTML
     */
    public function render(): string {
        // Extract variables for use in the template
        $title = $this->title;
        $content = $this->content;
        
        // Start output buffering
        ob_start();
        
        // Include the template file
        include __DIR__ . '/base.html.php';
        
        // Return the buffered output
        return ob_get_clean();
    }
}