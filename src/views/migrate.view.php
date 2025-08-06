<?php

namespace Cronbeat\Views;

class MigrateView extends BaseView {

    protected ?string $error = null;
    protected ?string $success = null;
    protected int $currentVersion = 0;
    protected int $expectedVersion = 0;

    public function __construct() {
        $this->setTitle('CronBeat Database Migration');
    }

    public function setError(?string $error): self {
        $this->error = $error;
        return $this;
    }

    public function setSuccess(?string $success): self {
        $this->success = $success;
        return $this;
    }

    public function setCurrentVersion(int $version): self {
        $this->currentVersion = $version;
        return $this;
    }

    public function setExpectedVersion(int $version): self {
        $this->expectedVersion = $version;
        return $this;
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $error = $this->error;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $success = $this->success;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $currentVersion = $this->currentVersion;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $expectedVersion = $this->expectedVersion;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $needsMigration = $currentVersion < $expectedVersion;

        ob_start();

        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/migrate.html.php';

        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');

        return parent::render();
    }
}
