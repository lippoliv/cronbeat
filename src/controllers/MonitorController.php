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
        $action = $parts[1] ?? '';

        if ($action === 'edit') {
            return $this->updateMonitor($uuid);
        }

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

    private function updateMonitor(string $uuid): string {
        $userId = $_SESSION['user_id'];

        $currentName = '';
        foreach ($this->database->getMonitors($userId) as $m) {
            if ($m->getUuid() === $uuid) {
                $currentName = $m->getName();
                break;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
            if ($name === '') {
                $view = new \Cronbeat\Views\MonitorEditView();
                $username = $this->database->getUsername($userId);
                $view->setMonitorUuid($uuid)
                    ->setName($currentName)
                    ->setError('Monitor name is required')
                    ->setUsername($username !== false ? $username : 'Unknown');
                return $view->render();
            }

            $ok = $this->database->updateMonitorName($uuid, $userId, $name);
            if ($ok) {
                throw new RedirectException(['Location' => '/monitor/' . $uuid]);
            }

            $view = new \Cronbeat\Views\MonitorEditView();
            $username = $this->database->getUsername($userId);
            $view->setMonitorUuid($uuid)
                ->setName($currentName)
                ->setError('Failed to update monitor')
                ->setUsername($username !== false ? $username : 'Unknown');
            return $view->render();
        }

        $view = new \Cronbeat\Views\MonitorEditView();
        $username = $this->database->getUsername($userId);
        $view->setMonitorUuid($uuid)
            ->setName($currentName)
            ->setUsername($username !== false ? $username : 'Unknown');
        return $view->render();
    }
}
