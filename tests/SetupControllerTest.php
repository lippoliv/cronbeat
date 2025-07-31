<?php

namespace Cronbeat\Tests;

require_once __DIR__ . '/../src/controllers/BaseController.php';
require_once __DIR__ . '/../src/views/base.view.php';
require_once __DIR__ . '/../src/views/setup.view.php';
require_once __DIR__ . '/../src/controllers/SetupController.php';

use PHPUnit\Framework\TestCase;
use Cronbeat\Controllers\SetupController;
use Cronbeat\Database;

class SetupControllerTest extends TestCase {
    private $tempDbPath;
    private $controller;
    private $database;

    protected function setUp(): void {
        parent::setUp();
        $this->tempDbPath = sys_get_temp_dir() . '/test_cronbeat_' . uniqid() . '.sqlite';
        $this->database = new Database($this->tempDbPath);
        $this->controller = new SetupController($this->database);
    }

    private function cleanupTestDatabase($tempDbPath) {
        if (file_exists($tempDbPath)) {
            unlink($tempDbPath);
        }
    }

    protected function tearDown(): void {
        if ($this->tempDbPath) {
            $this->cleanupTestDatabase($this->tempDbPath);
            $this->tempDbPath = null;
        }
        parent::tearDown();
    }

    public function testValidateSetupDataAcceptsValidInput() {
        // Given
        $username = 'admin';
        $passwordHash = hash('sha256', 'password123');

        // When
        $result = $this->controller->validateSetupData($username, $passwordHash);

        // Then
        $this->assertNull($result, "Valid input should return null");
    }

    public function testValidateSetupDataRejectsEmptyUsername() {
        // Given
        $username = '';
        $passwordHash = hash('sha256', 'password123');

        // When
        $result = $this->controller->validateSetupData($username, $passwordHash);

        // Then
        $this->assertEquals('Username and password are required', $result);
    }

    public function testValidateSetupDataRejectsTooShortUsername() {
        // Given
        $username = 'ab';
        $passwordHash = hash('sha256', 'password123');

        // When
        $result = $this->controller->validateSetupData($username, $passwordHash);

        // Then
        $this->assertEquals('Username must be at least 3 characters', $result);
    }

    public function testRunSetupCreatesUserSuccessfully() {
        // Given
        $username = 'admin';
        $passwordHash = hash('sha256', 'password123');

        // When
        $result = $this->controller->runSetup($username, $passwordHash);

        // Then
        $this->assertNull($result, "Setup should succeed with valid data");

        // And the user should exist in the database
        $this->assertTrue($this->database->userExists($username), "User should be created in database");
    }

    public function testRunSetupHandlesDatabaseErrors() {
        // Given
        $invalidDbPath = '/invalid/path/that/does/not/exist/test.sqlite';
        $invalidDatabase = new Database($invalidDbPath);

        // When/Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to create database directory: /invalid/path/that/does/not/exist');

        // Suppress PHP warnings from mkdir() while still allowing the exception to be thrown
        $invalidDatabase->createDatabase();
    }
}
