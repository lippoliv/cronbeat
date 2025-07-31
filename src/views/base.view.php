<?php

namespace Cronbeat\Views;

class BaseView {


    protected string $title   = 'CronBeat';

    protected string $content = '';


    public function setTitle(string $title): self {
        $this->title = $title;

        return $this;
    }


    public function setContent(string $content): self {
        $this->content = $content;

        return $this;
    }


    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $title   = $this->title;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $content = $this->content;

        ob_start();

        include APP_DIR . '/views/base.html.php';

        $result = ob_get_clean();

        return $result !== false ? $result : '';
    }
}
