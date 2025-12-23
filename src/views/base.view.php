<?php

namespace Cronbeat\Views;

class BaseView {


    protected string $title   = 'CronBeat';

    protected string $content = '';

    protected string $containerClass = 'container';


    public function setTitle(string $title): self {
        $this->title = $title;

        return $this;
    }


    public function setContent(string $content): self {
        $this->content = $content;

        return $this;
    }


    public function setContainerClass(string $containerClass): self {
        $this->containerClass = $containerClass;

        return $this;
    }


    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $title   = $this->title;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $content = $this->content;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $containerClass = $this->containerClass;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/base.html.php';

        $result = ob_get_clean();

        return $result !== false ? $result : '';
    }
}
