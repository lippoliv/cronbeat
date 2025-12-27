<?php

namespace Cronbeat\Controllers;

use Cronbeat\Logger;

class ApiController extends BaseController {
    public function doRouting(): string {
        $path = $this->parsePathWithoutController();
        $parts = array_values(array_filter(explode('/', $path), fn($p) => $p !== ''));

        // Expected: /api/ping/<UUID>[/start]
        if (count($parts) < 2 || $parts[0] !== 'ping') {
            http_response_code(404);
            return $this->json(['status' => 'error', 'message' => 'Not Found']);
        }

        $uuid = $parts[1];
        $action = $parts[2] ?? 'complete';

        if ($action === 'start') {
            return $this->handleStart($uuid);
        }

        return $this->handleComplete($uuid);
    }

    private function handleStart(string $uuid): string {
        Logger::info('API start called', ['uuid' => $uuid]);
        $ok = $this->database->startPingTracking($uuid);
        if (!$ok) {
            http_response_code(404);
            return $this->json(['status' => 'error', 'message' => 'Monitor not found', 'uuid' => $uuid]);
        }
        http_response_code(200);
        return '';
    }

    private function handleComplete(string $uuid): string {
        Logger::info('API ping called', ['uuid' => $uuid]);
        $result = $this->database->completePing($uuid);
        if ($result === false) {
            http_response_code(404);
            return $this->json(['status' => 'error', 'message' => 'Monitor not found', 'uuid' => $uuid]);
        }
        http_response_code(200);
        return '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data): string {
        header('Content-Type: application/json');
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
