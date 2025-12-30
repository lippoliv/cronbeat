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

    public function __construct() {
        $this->setTitle('Monitor History');
        $this->setContainerClass('view-container');
        $this->setShowHeader(true);
    }

    public function setMonitorUuid(string $uuid): self {
        $this->monitorUuid = $uuid;
        return $this;
    }
    public function setMonitorName(string $name): self {
        $this->monitorName = $name;
        return $this;
    }
    /**
     * @param array<\Cronbeat\PingData> $history
     */
    public function setHistory(array $history): self {
        $this->history = $history;
        return $this;
    }
    public function setPage(int $page): self {
        $this->page = $page;
        return $this;
    }
    public function setPageSize(int $pageSize): self {
        $this->pageSize = $pageSize;
        return $this;
    }
    public function setTotal(int $total): self {
        $this->total = $total;
        return $this;
    }

    /**
     * Build a list of history items with an optional interval string to the next (older) entry.
     * The last item on the current page has no interval.
     *
     * @return array<int, array{entry:\Cronbeat\PingData, interval: ?string}>
     */
    public function getHistoryWithIntervals(): array {
        $result = [];
        $count = count($this->history);
        for ($i = 0; $i < $count; $i++) {
            $entry = $this->history[$i];
            $next = $i + 1 < $count ? $this->history[$i + 1] : null;
            $interval = $next !== null ? $this->computeIntervalString($entry, $next) : null;
            $result[] = [
                'entry' => $entry,
                'interval' => $interval,
            ];
        }
        return $result;
    }

    private function computeIntervalString(\Cronbeat\PingData $current, \Cronbeat\PingData $next): ?string {
        try {
            $t1 = new \DateTimeImmutable($current->getPingedAt());
            $t2 = new \DateTimeImmutable($next->getPingedAt());
            $diffSec = max(0, $t1->getTimestamp() - $t2->getTimestamp());
            $h = intdiv($diffSec, 3600);
            $m = intdiv($diffSec % 3600, 60);
            $s = $diffSec % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function render(): string {
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $monitorUuid = $this->monitorUuid;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $monitorName = $this->monitorName;
        // Provide precomputed history items including per-page intervals
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $historyWithIntervals = $this->getHistoryWithIntervals();
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $page = $this->page;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $pageSize = $this->pageSize;
        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable
        $total = $this->total;

        ob_start();
        include(defined('APP_DIR') ? APP_DIR . '/views' : __DIR__) . '/monitor_history.html.php';
        $result = ob_get_clean();
        $this->setContent($result !== false ? $result : '');
        return parent::render();
    }
}
