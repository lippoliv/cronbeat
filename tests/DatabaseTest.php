<?php

namespace Cronbeat\Tests;

use Cronbeat\Database;
use PHPUnit\Framework\Assert;

class DatabaseTest extends DatabaseTestCase {

    public function testDatabaseDoesNotExistInitially(): void {
        // Given
        $tempPath = sys_get_temp_dir() . '/nonexistent_' . uniqid() . '.sqlite';
        $database = new Database($tempPath);

        // When
        $exists = $database->databaseExists();

        // Then
        Assert::assertFalse($exists);
    }

    public function testDatabaseExistsAfterCreation(): void {
        // Given

        // When
        $exists = $this->getDatabase()->databaseExists();

        // Then
        Assert::assertTrue($exists);
    }

    public function testCreateDatabase(): void {
        // Given
        $testDbDir = sys_get_temp_dir() . '/cronbeat_test_dir';
        $testDbPath = $testDbDir . '/test.sqlite';
        $database = new Database($testDbPath);

        // When
        $result = $database->createDatabase();

        // Then
        Assert::assertTrue($result);
        Assert::assertTrue(file_exists($testDbPath));
        Assert::assertTrue(is_dir($testDbDir));

        unlink($testDbPath);
        if (is_dir($testDbDir)) {
            rmdir($testDbDir);
        }
    }

    public function testCreateUserReturnsTrue(): void {
        // Given

        // When
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $result = $this->getDatabase()->createUser($username, $passwordHash);

        // Then
        Assert::assertTrue($result);
    }

    public function testCreatedUserExists(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $userExists = $this->getDatabase()->userExists($username);

        // Then
        Assert::assertTrue($userExists);
    }

    public function testValidateUserWithCorrectCredentials(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $validResult = $this->getDatabase()->validateUser($username, $passwordHash);

        // Then
        Assert::assertIsInt($validResult);
        Assert::assertGreaterThan(0, $validResult);
    }

    public function testValidateUserWithIncorrectPassword(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $invalidResult = $this->getDatabase()->validateUser($username, 'wronghash');

        // Then
        Assert::assertFalse($invalidResult);
    }

    public function testValidateUserWithNonExistentUser(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);

        // When
        $nonExistentResult = $this->getDatabase()->validateUser('nonexistent', $passwordHash);

        // Then
        Assert::assertFalse($nonExistentResult);
    }

    public function testCreateMonitorReturnsUuid(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $monitorName = 'Test Monitor';

        // When
        $uuid = $this->getDatabase()->createMonitor($monitorName, $userId);

        // Then
        Assert::assertIsString($uuid);
        Assert::assertNotEmpty($uuid);
    }

    public function testGetMonitorsReturnsEmptyArrayWhenNoMonitors(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }

        // When
        $monitors = $this->getDatabase()->getMonitors($userId);

        // Then
        Assert::assertIsArray($monitors);
        Assert::assertEmpty($monitors);
    }

    public function testGetMonitorsReturnsMonitorsForUser(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $monitorName = 'Test Monitor';
        $uuid = $this->getDatabase()->createMonitor($monitorName, $userId);

        // When
        $monitors = $this->getDatabase()->getMonitors($userId);

        // Then
        Assert::assertIsArray($monitors);
        Assert::assertCount(1, $monitors);
        Assert::assertInstanceOf(\Cronbeat\MonitorData::class, $monitors[0]);
        Assert::assertEquals($uuid, $monitors[0]->getUuid());
        Assert::assertEquals($monitorName, $monitors[0]->getName());
    }

    public function testGetMonitorsReturnsMultipleMonitorsOrderedByName(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }

        $monitorName2 = 'B Test Monitor';
        $monitorName1 = 'A Test Monitor';
        $monitorName3 = 'C Test Monitor';

        $this->getDatabase()->createMonitor($monitorName2, $userId);
        $this->getDatabase()->createMonitor($monitorName1, $userId);
        $this->getDatabase()->createMonitor($monitorName3, $userId);

        // When
        $monitors = $this->getDatabase()->getMonitors($userId);

        // Then
        Assert::assertIsArray($monitors);
        Assert::assertCount(3, $monitors);
        Assert::assertInstanceOf(\Cronbeat\MonitorData::class, $monitors[0]);
        Assert::assertEquals($monitorName1, $monitors[0]->getName());
        Assert::assertEquals($monitorName2, $monitors[1]->getName());
        Assert::assertEquals($monitorName3, $monitors[2]->getName());
    }

    public function testDeleteMonitorReturnsTrueWhenMonitorExists(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $monitorName = 'Test Monitor';
        $uuid = $this->getDatabase()->createMonitor($monitorName, $userId);
        if ($uuid === false) {
            throw new \RuntimeException('Failed to create monitor for test');
        }

        // When
        $result = $this->getDatabase()->deleteMonitor($uuid, $userId);

        // Then
        Assert::assertTrue($result);

        $monitors = $this->getDatabase()->getMonitors($userId);
        Assert::assertEmpty($monitors);
    }

    public function testDeleteMonitorReturnsFalseWhenMonitorDoesNotExist(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $nonExistentUuid = '12345678-1234-1234-1234-123456789012';

        // When
        $result = $this->getDatabase()->deleteMonitor($nonExistentUuid, $userId);

        // Then
        Assert::assertFalse($result);
    }

    public function testDeleteMonitorReturnsFalseWhenMonitorBelongsToAnotherUser(): void {
        // Given
        $username1 = 'testuser1';
        $passwordHash1 = hash('sha256', 'password1');
        $this->getDatabase()->createUser($username1, $passwordHash1);
        $userId1 = $this->getDatabase()->validateUser($username1, $passwordHash1);
        if ($userId1 === false) {
            throw new \RuntimeException('Failed to validate test user 1');
        }
        $monitorName = 'Test Monitor';
        $uuid = $this->getDatabase()->createMonitor($monitorName, $userId1);
        if ($uuid === false) {
            throw new \RuntimeException('Failed to create monitor for test');
        }

        $username2 = 'testuser2';
        $passwordHash2 = hash('sha256', 'password2');
        $this->getDatabase()->createUser($username2, $passwordHash2);
        $userId2 = $this->getDatabase()->validateUser($username2, $passwordHash2);
        if ($userId2 === false) {
            throw new \RuntimeException('Failed to validate test user 2');
        }

        // When
        $result = $this->getDatabase()->deleteMonitor($uuid, $userId2);

        // Then
        Assert::assertFalse($result);

        $monitors = $this->getDatabase()->getMonitors($userId1);
        Assert::assertCount(1, $monitors);
    }

    public function testGetUsernameReturnsUsernameWhenUserExists(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }

        // When
        $result = $this->getDatabase()->getUsername($userId);

        // Then
        Assert::assertEquals($username, $result);
    }

    public function testGetUsernameReturnsFalseWhenUserDoesNotExist(): void {
        // Given
        $nonExistentUserId = 9999;

        // When
        $result = $this->getDatabase()->getUsername($nonExistentUserId);

        // Then
        Assert::assertFalse($result);
    }

    public function testGetUserProfileReturnsModelForExistingUser(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $name = 'Jane Doe';
        $email = 'jane@example.com';
        $this->getDatabase()->updateUserProfile($userId, $name, $email);

        // When
        $profile = $this->getDatabase()->getUserProfile($userId);

        // Then
        Assert::assertInstanceOf(\Cronbeat\UserProfileData::class, $profile);
        Assert::assertSame($username, $profile->getUsername());
        Assert::assertSame($name, $profile->getName());
        Assert::assertSame($email, $profile->getEmail());
    }

    public function testGetUserProfileReturnsFalseForMissingUser(): void {
        // Given
        $nonExistentUserId = 123456;

        // When
        $profile = $this->getDatabase()->getUserProfile($nonExistentUserId);

        // Then
        Assert::assertFalse($profile);
    }

    public function testUpdateUserProfileUpdatesNameAndEmail(): void {
        // Given
        $username = 'testuser';
        $passwordHash = hash('sha256', 'password');
        $this->getDatabase()->createUser($username, $passwordHash);
        $userId = $this->getDatabase()->validateUser($username, $passwordHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }

        // When
        $result = $this->getDatabase()->updateUserProfile($userId, 'John Smith', 'john.smith@example.com');

        // Then
        Assert::assertTrue($result);
        $profile = $this->getDatabase()->getUserProfile($userId);
        Assert::assertInstanceOf(\Cronbeat\UserProfileData::class, $profile);
        Assert::assertSame('John Smith', $profile->getName());
        Assert::assertSame('john.smith@example.com', $profile->getEmail());
    }

    public function testUpdateUserPasswordPersistsNewHash(): void {
        // Given
        $username = 'testuser';
        $oldHash = hash('sha256', 'oldpassword');
        $this->getDatabase()->createUser($username, $oldHash);
        $userId = $this->getDatabase()->validateUser($username, $oldHash);
        if ($userId === false) {
            throw new \RuntimeException('Failed to validate test user');
        }
        $newHash = hash('sha256', 'newpassword');

        // When
        $result = $this->getDatabase()->updateUserPassword($userId, $newHash);

        // Then
        Assert::assertTrue($result);
        Assert::assertFalse($this->getDatabase()->validateUser($username, $oldHash));
        $validated = $this->getDatabase()->validateUser($username, $newHash);
        Assert::assertIsInt($validated);
        Assert::assertSame($userId, $validated);
    }

    public function testGetMonitorIdByUuid(): void {
        // Given
        $db = $this->getDatabase();
        $db->createUser('u', 'p');
        $userId = $db->validateUser('u', 'p');
        if ($userId === false) { throw new \RuntimeException('validate'); }
        $uuid = $db->createMonitor('m', $userId);
        if ($uuid === false) { throw new \RuntimeException('createMonitor'); }

        // When
        $id = $db->getMonitorIdByUuid($uuid);

        // Then
        Assert::assertIsInt($id);
        Assert::assertGreaterThan(0, $id);
        Assert::assertFalse($db->getMonitorIdByUuid('00000000-0000-0000-0000-000000000000'));
    }

    public function testStartPingTrackingAndHasPendingStart(): void {
        // Given
        $db = $this->getDatabase();
        $db->createUser('u2', 'p2');
        $userId = $db->validateUser('u2', 'p2');
        if ($userId === false) { throw new \RuntimeException('validate'); }
        $uuid = $db->createMonitor('m2', $userId);
        if ($uuid === false) { throw new \RuntimeException('createMonitor'); }
        $monitorId = $db->getMonitorIdByUuid($uuid);
        if ($monitorId === false) { throw new \RuntimeException('id'); }

        // When
        $ok = $db->startPingTracking($uuid);

        // Then
        Assert::assertTrue($ok);
        Assert::assertTrue($db->hasPendingStart($monitorId));
    }

    public function testCompletePingRecordsHistoryAndClearsPending(): void {
        // Given
        $db = $this->getDatabase();
        $db->createUser('u3', 'p3');
        $userId = $db->validateUser('u3', 'p3');
        if ($userId === false) { throw new \RuntimeException('validate'); }
        $uuid = $db->createMonitor('m3', $userId);
        if ($uuid === false) { throw new \RuntimeException('createMonitor'); }
        $monitorId = $db->getMonitorIdByUuid($uuid);
        if ($monitorId === false) { throw new \RuntimeException('id'); }
        $db->startPingTracking($uuid);

        // When
        $res = $db->completePing($uuid);

        // Then
        Assert::assertIsArray($res);
        /** @var array{history_id:int, duration_ms:int|null} $res */
        Assert::assertArrayHasKey('history_id', $res);
        Assert::assertGreaterThan(0, $res['history_id']);
        Assert::assertFalse($db->hasPendingStart($monitorId));
        $count = $db->countPingHistory($monitorId);
        Assert::assertSame(1, $count);
    }

    public function testGetPingHistoryAndCount(): void {
        // Given
        $db = $this->getDatabase();
        $db->createUser('u4', 'p4');
        $userId = $db->validateUser('u4', 'p4');
        if ($userId === false) { throw new \RuntimeException('validate'); }
        $uuid = $db->createMonitor('m4', $userId);
        if ($uuid === false) { throw new \RuntimeException('createMonitor'); }
        $monitorId = $db->getMonitorIdByUuid($uuid);
        if ($monitorId === false) { throw new \RuntimeException('id'); }

        $db->completePing($uuid);
        $db->completePing($uuid);
        $db->completePing($uuid);

        // When
        $total = $db->countPingHistory($monitorId);
        $items = $db->getPingHistory($monitorId, 2, 0);
        $items2 = $db->getPingHistory($monitorId, 2, 2);

        // Then
        Assert::assertSame(3, $total);
        Assert::assertCount(2, $items);
        Assert::assertCount(1, $items2);
        Assert::assertInstanceOf(\Cronbeat\PingData::class, $items[0]);
        Assert::assertTrue($items[0]->getDurationMs() === null || is_int($items[0]->getDurationMs()));
    }
}
