<?php

namespace Cronbeat\Views;

class BaseView {
    protected $title = 'CronBeat';
    protected $content = '';

    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function setContent(string $content): self {
        $this->content = $content;
        return $this;
    }

    public function render(): string {
        $title = $this->title;
        $content = $this->content;
        
        ob_start();
        
        include APP_DIR . '/src/views/base.html.php';
        
        return ob_get_clean();
    }
}