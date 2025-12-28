<?php

namespace Cronbeat\Views;

class BaseView {


    protected string $title   = 'CronBeat';

    protected string $content = '';

    protected string $containerClass = 'container';
    protected ?string $appVersion = null;
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


    public function getAppVersion(): ?string {
        if ($this->appVersion === null) {
            $versionFile = (defined('APP_DIR') ? APP_DIR : __DIR__ . '/..') . '/version.txt';
            if (file_exists($versionFile)) {
                $this->appVersion = trim((string) file_get_contents($versionFile));
            } else {
                // Also check root if APP_DIR is defined but file not found there
                $rootVersionFile = (defined('APP_DIR') ? APP_DIR . '/..' : __DIR__ . '/../..') . '/version.txt';
                if (file_exists($rootVersionFile)) {
                    $this->appVersion = trim((string) file_get_contents($rootVersionFile));
                } else {
                    $this->appVersion = '';
                }
            }
        }

        return $this->appVersion !== '' ? $this->appVersion : null;
    }


    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $appVersion = $this->getAppVersion();
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
