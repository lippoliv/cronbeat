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
        $currentExpected = null;
        $currentGrace = null;
        foreach ($this->database->getMonitors($userId) as $m) {
            if ($m->getUuid() === $uuid) {
                $currentName = $m->getName();
                $currentExpected = $m->getExpectedIntervalMinutes();
                $currentGrace = $m->getGracePeriodMinutes();
                break;
            }
        }

        $view = new \Cronbeat\Views\MonitorEditView();
        $username = $this->database->getUsername($userId);
        $view->setUsername($username !== false ? $username : 'Unknown');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
            if ($name === '') {
                $view->setMonitorUuid($uuid)
                    ->setName($currentName)
                    ->setExpectedIntervalMinutes($currentExpected)
                    ->setGracePeriodMinutes($currentGrace)
                    ->setError('Monitor name is required');
                return $view->render();
            }

            $expHours = isset($_POST['expected_interval_hours']) ? (int)$_POST['expected_interval_hours'] : 0;
            $expMinutes = isset($_POST['expected_interval_minutes']) ? (int)$_POST['expected_interval_minutes'] : 0;
            $graceHours = isset($_POST['grace_period_hours']) ? (int)$_POST['grace_period_hours'] : 0;
            $graceMinutes = isset($_POST['grace_period_minutes']) ? (int)$_POST['grace_period_minutes'] : 0;

            $expHours = max(0, $expHours);
            $expMinutes = max(0, min(59, $expMinutes));
            $graceHours = max(0, $graceHours);
            $graceMinutes = max(0, min(59, $graceMinutes));

            $expectedTotal = ($expHours * 60) + $expMinutes;
            $graceTotal = ($graceHours * 60) + $graceMinutes;

            $expectedValue = $expectedTotal > 0 ? $expectedTotal : null;
            $graceValue = $graceTotal > 0 ? $graceTotal : null;

            $ok = $this->database->updateMonitorSettings($uuid, $userId, $name, $expectedValue, $graceValue);
            if ($ok) {
                throw new RedirectException(['Location' => '/monitor/' . $uuid]);
            }

            $view->setMonitorUuid($uuid)
                ->setName($currentName)
                ->setExpectedIntervalMinutes($currentExpected)
                ->setGracePeriodMinutes($currentGrace)
                ->setError('Failed to update monitor');
            return $view->render();
        }

        $view->setMonitorUuid($uuid)
            ->setName($currentName)
            ->setExpectedIntervalMinutes($currentExpected)
            ->setGracePeriodMinutes($currentGrace);
        return $view->render();
    }
}
