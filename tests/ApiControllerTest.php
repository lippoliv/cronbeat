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
        if ($userId === false) { Assert::fail('user validate failed'); }
        $uuid = $db->createMonitor('m1', $userId);
        if ($uuid === false) { Assert::fail('monitor create failed'); }
        $controller = new ApiController($db);

        // When
        $start = $this->call($controller, "/api/ping/$uuid/start");
        $ping = $this->call($controller, "/api/ping/$uuid");

        // Then
        $startJson = json_decode($start, true, 512, JSON_THROW_ON_ERROR);
        $pingJson = json_decode($ping, true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($startJson);
        Assert::assertIsArray($pingJson);
        /** @var array{status:string, action:string, uuid:string} $startJson */
        /** @var array{status:string, action:string, uuid:string, duration_ms:int|null} $pingJson */
        Assert::assertEquals('ok', $startJson['status']);
        Assert::assertEquals('ok', $pingJson['status']);
        Assert::assertArrayHasKey('duration_ms', $pingJson);
        if ($pingJson['duration_ms'] !== null) {
            Assert::assertGreaterThanOrEqual(0, $pingJson['duration_ms']);
        }
    }

    public function testUnknownUuidReturns404(): void {
        // Given
        $controller = new ApiController($this->getDatabase());

        // When
        $result = $this->call($controller, "/api/ping/00000000-0000-0000-0000-000000000000");

        // Then
        $json = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($json);
        /** @var array{status:string, message:string} $json */
        Assert::assertEquals('error', $json['status']);
        Assert::assertArrayHasKey('message', $json);
    }
}
