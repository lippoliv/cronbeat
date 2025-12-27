<?php

namespace Cronbeat\Controllers;

use Cronbeat\RedirectException;

class MonitorController extends BaseController {
    public function doRouting(): string {
        if (!isset($_SESSION['user_id'])) {
            throw new RedirectException(['Location' => '/login']);
        }

        $path = $this->parsePathWithoutController();
        $parts = array_values(array_filter(explode('/', $path), fn($p) => $p !== ''));

        $uuid = $parts[0] ?? '';
        $qPos = strpos($uuid, '?');
        if ($qPos !== false) {
            $uuid = substr($uuid, 0, $qPos);
        }
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

        return $this->showMonitorHistory($uuid, $page);
    }

    public function showMonitorHistory(string $uuid, int $page = 1): string {
        $userId = $_SESSION['user_id'];
        $monitorId = $this->database->getMonitorIdByUuid($uuid);
        if ($monitorId === false) {
            $view = new \Cronbeat\Views\DashboardView();
            $view->setError('Monitor not found');
            $username = $this->database->getUsername($userId);
            $view->setUsername($username !== false ? $username : 'Unknown');
            $view->setMonitors($this->database->getMonitors($userId));
            return $view->render();
        }

        $pageSize = 50;
        $offset = ($page - 1) * $pageSize;

        $total = $this->database->countPingHistory($monitorId);
        $history = $this->database->getPingHistory($monitorId, $pageSize, $offset);

        $monitorName = '';
        foreach ($this->database->getMonitors($userId) as $m) {
            if ($m->getUuid() === $uuid) {
                $monitorName = $m->getName();
                break;
            }
        }

        $view = new \Cronbeat\Views\MonitorHistoryView();
        $username = $this->database->getUsername($userId);
        $view->setMonitorUuid($uuid)
            ->setMonitorName($monitorName)
            ->setHistory($history)
            ->setPage($page)
            ->setPageSize($pageSize)
            ->setTotal($total)
            ->setUsername($username !== false ? $username : 'Unknown');

        return $view->render();
    }
}
