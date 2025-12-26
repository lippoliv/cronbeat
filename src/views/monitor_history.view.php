<?php

namespace Cronbeat\Views;

class MonitorHistoryView extends BaseView {
    private string $monitorUuid = '';
    private string $monitorName = '';
    /** @var array<\Cronbeat\PingData> */
    private array $history = [];
    private int $page = 1;
    private int $pageSize = 50;
    private int $total = 0;
    private string $username = '';

    public function __construct() {
        $this->setTitle('Monitor History');
        $this->setContainerClass('view-container');
    }

    public function setMonitorUuid(string $uuid): self { $this->monitorUuid = $uuid; return $this; }
    public function setMonitorName(string $name): self { $this->monitorName = $name; return $this; }
    /**
     * @param array<\Cronbeat\PingData> $history
     */
    public function setHistory(array $history): self { $this->history = $history; return $this; }
    public function setPage(int $page): self { $this->page = $page; return $this; }
    public function setPageSize(int $pageSize): self { $this->pageSize = $pageSize; return $this; }
    public function setTotal(int $total): self { $this->total = $total; return $this; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $monitorUuid = $this->monitorUuid;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $monitorName = $this->monitorName;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $history = $this->history;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $page = $this->page;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $pageSize = $this->pageSize;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $total = $this->total;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $username = $this->username;

        ob_start();
        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/monitor_history.html.php';
        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');
        return parent::render();
    }
}
