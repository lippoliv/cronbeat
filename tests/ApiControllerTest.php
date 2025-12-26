<?php

namespace Cronbeat\Tests;

use Cronbeat\Controllers\ApiController;
use PHPUnit\Framework\Assert;

class ApiControllerTest extends DatabaseTestCase {
    private function call(ApiController $controller, string $uri): string {
        $_SERVER['REQUEST_URI'] = $uri;
        return $controller->doRouting();
    }

    public function testPingStartAndCompleteEndpoints(): void {
        // Given
        $db = $this->getDatabase();
        $db->createUser('u', 'p');
        $userId = $db->validateUser('u', 'p');
        if ($userId === false) { $this->fail('user validate failed'); }
        $uuid = $db->createMonitor('m1', $userId);
        if ($uuid === false) { $this->fail('monitor create failed'); }
        $controller = new ApiController($db);

        // When
        $start = $this->call($controller, "/api/ping/$uuid/start");
        $ping = $this->call($controller, "/api/ping/$uuid");

        // Then
        $startJson = json_decode($start, true);
        $pingJson = json_decode($ping, true);
        Assert::assertEquals('ok', $startJson['status']);
        Assert::assertEquals('ok', $pingJson['status']);
        Assert::assertArrayHasKey('duration_ms', $pingJson);
        Assert::assertTrue(is_null($pingJson['duration_ms']) || is_int($pingJson['duration_ms']));
    }

    public function testUnknownUuidReturns404(): void {
        // Given
        $controller = new ApiController($this->getDatabase());

        // When
        $result = $this->call($controller, "/api/ping/00000000-0000-0000-0000-000000000000");

        // Then
        $json = json_decode($result, true);
        Assert::assertEquals('error', $json['status']);
        Assert::assertArrayHasKey('message', $json);
    }
}
