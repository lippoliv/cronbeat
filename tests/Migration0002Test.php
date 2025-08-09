<?php

namespace Cronbeat\Tests;

use PHPUnit\Framework\Assert;
use Cronbeat\Migrations\Migration0002;

class Migration0002Test extends DatabaseTestCase {
    private ?Migration0002 $migration = null;

    protected function setUp(): void {
        parent::setUp();
        $this->migration = new Migration0002();
    }

    public function testGetName(): void {
        // Given
        // Migration is already set up

        // When
        $name = $this->migration->getName();

        // Then
        Assert::assertEquals('Create monitors table', $name);
    }

    public function testGetVersion(): void {
        // Given
        // Migration is already set up

        // When
        $version = $this->migration->getVersion();

        // Then
        Assert::assertEquals(2, $version);
    }

    public function testExecuteCreatesMonitorsTable(): void {
        // Given
        // Migration is already set up
        $pdo = $this->getDatabase()->connect();

        // When
        $this->migration->up($pdo);

        // Then
        // Check if the monitors table exists
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='monitors'");
        $result = $stmt->fetch();
        Assert::assertNotFalse($result);
        Assert::assertEquals('monitors', $result['name']);

        // Check if the table has the expected columns
        $stmt = $pdo->query("PRAGMA table_info(monitors)");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Extract column names
        $columnNames = array_column($columns, 'name');
        
        // Check that all expected columns exist
        Assert::assertContains('id', $columnNames);
        Assert::assertContains('uuid', $columnNames);
        Assert::assertContains('name', $columnNames);
        Assert::assertContains('user_id', $columnNames);
        Assert::assertContains('created_at', $columnNames);
        
        // Check for unique constraint on uuid
        $stmt = $pdo->query("PRAGMA index_list(monitors)");
        $indexes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $hasUniqueUuid = false;
        $hasUniqueNameUserId = false;
        
        foreach ($indexes as $index) {
            if ($index['unique'] == 1) {
                $indexName = $index['name'];
                $stmt = $pdo->query("PRAGMA index_info($indexName)");
                $indexColumns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                $indexColumnNames = array_column($indexColumns, 'name');
                
                if (count($indexColumnNames) == 1 && $indexColumnNames[0] == 'uuid') {
                    $hasUniqueUuid = true;
                }
                
                if (count($indexColumnNames) == 2 && 
                    in_array('name', $indexColumnNames) && 
                    in_array('user_id', $indexColumnNames)) {
                    $hasUniqueNameUserId = true;
                }
            }
        }
        
        Assert::assertTrue($hasUniqueUuid, 'UUID column should have a unique constraint');
        Assert::assertTrue($hasUniqueNameUserId, 'Combination of name and user_id should have a unique constraint');
    }

    public function testMonitorNamesMustBeUniquePerUser(): void {
        // Given
        // Migration is already set up
        $pdo = $this->getDatabase()->connect();
        $this->migration->up($pdo);
        
        // Create a test user
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute(['testuser', 'password']);
        $userId = $pdo->lastInsertId();
        
        // Create a monitor
        $stmt = $pdo->prepare("INSERT INTO monitors (uuid, name, user_id) VALUES (?, ?, ?)");
        $stmt->execute(['uuid1', 'Test Monitor', $userId]);
        
        // When/Then
        // Try to create another monitor with the same name for the same user
        // This should fail due to the unique constraint
        $stmt = $pdo->prepare("INSERT INTO monitors (uuid, name, user_id) VALUES (?, ?, ?)");
        
        try {
            $stmt->execute(['uuid2', 'Test Monitor', $userId]);
            Assert::fail('Should have thrown an exception due to unique constraint violation');
        } catch (\PDOException $e) {
            // Expected exception
            Assert::assertStringContainsString('UNIQUE constraint failed', $e->getMessage());
        }
        
        // Create another user
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute(['testuser2', 'password']);
        $userId2 = $pdo->lastInsertId();
        
        // Try to create a monitor with the same name for a different user
        // This should succeed
        $stmt = $pdo->prepare("INSERT INTO monitors (uuid, name, user_id) VALUES (?, ?, ?)");
        $result = $stmt->execute(['uuid3', 'Test Monitor', $userId2]);
        
        Assert::assertTrue($result, 'Should be able to create a monitor with the same name for a different user');
    }
}