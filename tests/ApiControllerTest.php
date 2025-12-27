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
        if ($userId === false) {
            throw new \RuntimeException('user validate failed');
        }
        $uuid = $db->createMonitor('m1', $userId);
        if ($uuid === false) {
            throw new \RuntimeException('monitor create failed');
        }
        $controller = new ApiController($db);

        // When
        $start = $this->call($controller, "/api/ping/$uuid/start");
        $ping = $this->call($controller, "/api/ping/$uuid");

        // Then
        Assert::assertSame('', $start);
        Assert::assertSame(200, http_response_code());
        Assert::assertSame('', $ping);
        Assert::assertSame(200, http_response_code());
    }

    public function testUnknownUuidReturns404(): void {
        // Given
        $controller = new ApiController($this->getDatabase());

        // Reset status code
        http_response_code(200);

        // When
        $result = $this->call($controller, "/api/ping/00000000-0000-0000-0000-000000000000");

        // Then
        $json = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        Assert::assertIsArray($json);
        /** @var array{status:string, message:string} $json */
        Assert::assertEquals('error', $json['status']);
        Assert::assertArrayHasKey('message', $json);
    }

    public function testPingWithoutStartReturns200(): void {
        // Given
        $db = $this->getDatabase();
        $db->createUser('u2', 'p2');
        $userId = $db->validateUser('u2', 'p2');
        if ($userId === false) {
            throw new \RuntimeException('user validate failed');
        }
        $uuid = $db->createMonitor('m2', $userId);
        if ($uuid === false) {
            throw new \RuntimeException('monitor create failed');
        }
        $controller = new ApiController($db);

        // When
        http_response_code(200);
        $ping = $this->call($controller, "/api/ping/$uuid");

        // Then
        Assert::assertSame('', $ping);
        Assert::assertSame(200, http_response_code());
    }
}
