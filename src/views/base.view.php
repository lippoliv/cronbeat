<?php

namespace Cronbeat\Views;

use Cronbeat\AppHelper;

class BaseView {


    protected string $title   = 'CronBeat';

    protected string $content = '';

    protected string $containerClass = 'container';
    protected bool $showHeader = false;
    protected ?string $username = null;
    protected bool $isDashboard = false;


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


    public function setShowHeader(bool $show): self {
        $this->showHeader = $show;

        return $this;
    }


    public function setUsername(string $username): self {
        $this->username = $username;

        return $this;
    }


    public function setIsDashboard(bool $isDashboard): self {
        $this->isDashboard = $isDashboard;

        return $this;
    }


    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $appVersion = AppHelper::getAppVersion();
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $title   = $this->title;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $content = $this->content;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $containerClass = $this->containerClass;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $showHeader = $this->showHeader;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $username = $this->username;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $isDashboard = $this->isDashboard;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/base.html.php';

        $result = ob_get_clean();

        return $result !== false ? $result : '';
    }
}
