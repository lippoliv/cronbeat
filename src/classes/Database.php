<?php

namespace Cronbeat;

use Cronbeat\MigrationHelper;

class Database {
    private string $dbPath;
    private string $dbDir;
    private ?\PDO $pdo = null;

    public function __construct(string $dbPath) {
        $this->dbPath = $dbPath;
        $this->dbDir = dirname($this->dbPath);
    }

    public function databaseExists(): bool {
        $exists = file_exists($this->dbPath);
        Logger::debug("Checking if database exists at {$this->dbPath}", ['exists' => $exists]);
        return $exists;
    }

    public function createDatabase(): bool {
        Logger::info("Creating new database at {$this->dbPath}");

        if (!is_dir($this->dbDir)) {
            Logger::debug("Creating database directory: {$this->dbDir}");
            if (!@mkdir($this->dbDir, 0755, true)) {
                Logger::error("Failed to create database directory: {$this->dbDir}");
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        $this->connect();

        Logger::info("Database created successfully");
        return true;
    }

    public function connect(): \PDO {
        if ($this->pdo !== null) {
            Logger::debug("Reusing existing database connection");
            return $this->pdo;
        }

        Logger::debug("Connecting to database at {$this->dbPath}");

        if (!$this->databaseExists() && !is_dir($this->dbDir)) {
            Logger::debug("Creating database directory: {$this->dbDir}");
            if (!mkdir($this->dbDir, 0755, true)) {
                Logger::error("Failed to create database directory: {$this->dbDir}");
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        try {
            $this->pdo = new \PDO("sqlite:{$this->dbPath}");
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // Ensure UTC timestamps for consistency
            $this->pdo->exec('PRAGMA foreign_keys = ON');

            Logger::info("Successfully connected to database");

            return $this->pdo;
        } catch (\PDOException $e) {
            Logger::error("Failed to connect to database", ['error' => $e->getMessage()]);
            throw $e;
        }
    }


    private function getPdo(): \PDO {
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        return $this->pdo;
    }



    public function createUser(string $username, string $passwordHash): bool {
        Logger::info("Creating new user", ['username' => $username]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $result = $stmt->execute([$username, $passwordHash]);

            if ($result) {
                Logger::info("User created successfully", ['username' => $username]);
            } else {
                Logger::warning("Failed to create user", ['username' => $username]);
            }

            return $result;
        } catch (\PDOException $e) {
            Logger::error("Error creating user", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function userExists(string $username): bool {
        Logger::debug("Checking if user exists", ['username' => $username]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $exists = (int) $stmt->fetchColumn() > 0;

            Logger::debug("User existence check result", [
                'username' => $username,
                'exists' => $exists
            ]);

            return $exists;
        } catch (\PDOException $e) {
            Logger::error("Error checking if user exists", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function validateUser(string $username, string $passwordHash): int|false {
        Logger::info("Validating user credentials", ['username' => $username]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $stmt = $this->pdo->prepare("SELECT id, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user === false || !is_array($user) || $user['password'] !== $passwordHash) {
                Logger::warning("User authentication failed", ['username' => $username]);
                return false;
            }

            Logger::info("User authentication successful", ['username' => $username, 'user_id' => $user['id']]);
            return (int)$user['id'];
        } catch (\PDOException $e) {
            Logger::error("Error validating user", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getDbPath(): string {
        return $this->dbPath;
    }

    private function migrationsTableExists(): bool {
        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        $tableExists = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
        return $tableExists !== false && $tableExists->fetch() !== false;
    }

    public function getDatabaseVersion(): int {
        Logger::debug("Getting database version");

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            if (!$this->migrationsTableExists()) {
                Logger::debug("Migrations table does not exist, returning version 0");
                return 0;
            }

            $stmt = $this->pdo->query("SELECT MAX(version) FROM migrations");
            if ($stmt === false) {
                Logger::error("Failed to query migrations table");
                return 0;
            }
            $version = $stmt->fetchColumn();

            if ($version === false) {
                Logger::debug("No migrations found, returning version 0");
                return 0;
            }

            Logger::debug("Current database version", ['version' => $version]);
            return (int) $version;
        } catch (\PDOException $e) {
            Logger::error("Error getting database version", ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function setDatabaseVersion(int $version, string $name = 'Initial setup'): bool {
        Logger::info("Setting database version", ['version' => $version, 'name' => $name]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO migrations (version, name) VALUES (?, ?)");
            $result = $stmt->execute([$version, $name]);

            if ($result) {
                Logger::info("Database version set successfully", ['version' => $version]);
            } else {
                Logger::warning("Failed to set database version", ['version' => $version]);
            }

            return $result;
        } catch (\PDOException $e) {
            Logger::error("Error setting database version", [
                'version' => $version,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function needsMigration(int $expectedVersion): bool {
        $currentVersion = $this->getDatabaseVersion();
        $needsMigration = $currentVersion < $expectedVersion;

        Logger::debug("Checking if database needs migration", [
            'current_version' => $currentVersion,
            'expected_version' => $expectedVersion,
            'needs_migration' => $needsMigration
        ]);

        return $needsMigration;
    }

    /** @param \Cronbeat\Migration|int $migration */
    public function runMigration($migration): bool {
        if (is_int($migration)) {
            $migrationVersion = $migration;
            $migration = MigrationHelper::loadMigration($migrationVersion);

            if ($migration === null) {
                throw new \RuntimeException("Migration not found for version {$migrationVersion}");
            }
        }

        $version = $migration->getVersion();
        $name = $migration->getName();

        if (!$this->needsMigration($version)) {
            Logger::info("Migration already run, skipping", ['version' => $version, 'name' => $name]);
            return true;
        }

        Logger::info("Running migration", ['version' => $version, 'name' => $name]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $migration->up($this->pdo);
            $this->setDatabaseVersion($version, $name);

            Logger::info("Migration completed successfully", ['version' => $version]);
            return true;
        } catch (\Exception $e) {
            Logger::error("Error running migration", [
                'version' => $version,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function createMonitor(string $name, int $userId): string|false {
        Logger::info("Creating new monitor", ['name' => $name, 'user_id' => $userId]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $uuid = $this->generateUUID();

            $stmt = $this->pdo->prepare("INSERT INTO monitors (uuid, name, user_id) VALUES (?, ?, ?)");
            $result = $stmt->execute([$uuid, $name, $userId]);

            if ($result) {
                Logger::info("Monitor created successfully", ['name' => $name, 'uuid' => $uuid]);
                return $uuid;
            } else {
                Logger::warning("Failed to create monitor", ['name' => $name]);
                return false;
            }
        } catch (\PDOException $e) {
            Logger::error("Error creating monitor", [
                'name' => $name,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /** @return array<array{uuid: string, name: string}> */
    public function getMonitors(int $userId): array {
        Logger::info("Getting monitors for user", ['user_id' => $userId]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    m.uuid,
                    m.name,
                    (
                        SELECT ph.pinged_at 
                        FROM ping_history ph 
                        WHERE ph.monitor_id = m.id 
                        ORDER BY ph.pinged_at DESC 
                        LIMIT 1
                    ) AS last_ping_at,
                    (
                        SELECT ph2.duration_ms 
                        FROM ping_history ph2 
                        WHERE ph2.monitor_id = m.id 
                        ORDER BY ph2.pinged_at DESC 
                        LIMIT 1
                    ) AS last_duration_ms,
                    EXISTS(SELECT 1 FROM ping_tracking pt WHERE pt.monitor_id = m.id) AS pending_start
                FROM monitors m
                WHERE m.user_id = ?
                ORDER BY m.name ASC
            ");
            $stmt->execute([$userId]);
            $monitors = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            Logger::info("Found monitors for user", [
                'user_id' => $userId,
                'count' => count($monitors)
            ]);

            return $monitors;
        } catch (\PDOException $e) {
            Logger::error("Error getting monitors", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function deleteMonitor(string $uuid, int $userId): bool {
        Logger::info("Deleting monitor", ['uuid' => $uuid, 'user_id' => $userId]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            // Clean up dependent records first to avoid orphaned rows in environments
            // where SQLite foreign_keys pragma might be disabled
            $this->pdo->beginTransaction();

            $stmtId = $this->pdo->prepare("SELECT id FROM monitors WHERE uuid = ? AND user_id = ?");
            $stmtId->execute([$uuid, $userId]);
            $monitorId = $stmtId->fetchColumn();

            if ($monitorId !== false && $monitorId !== null) {
                $mid = (int)$monitorId;
                $this->pdo->prepare("DELETE FROM ping_history WHERE monitor_id = ?")->execute([$mid]);
                $this->pdo->prepare("DELETE FROM ping_tracking WHERE monitor_id = ?")->execute([$mid]);
            }

            $stmt = $this->pdo->prepare("DELETE FROM monitors WHERE uuid = ? AND user_id = ?");
            $result = $stmt->execute([$uuid, $userId]);

            if ($result && $stmt->rowCount() > 0) {
                $this->pdo->commit();
                Logger::info("Monitor deleted successfully", ['uuid' => $uuid]);
                return true;
            } else {
                $this->pdo->rollBack();
                Logger::warning("Failed to delete monitor", ['uuid' => $uuid]);
                return false;
            }
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            Logger::error("Error deleting monitor", [
                'uuid' => $uuid,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getMonitorIdByUuid(string $uuid): int|false {
        $pdo = $this->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT id FROM monitors WHERE uuid = ?");
            $stmt->execute([$uuid]);
            $id = $stmt->fetchColumn();
            if ($id === false || $id === null) {
                return false;
            }
            return (int)$id;
        } catch (\PDOException $e) {
            Logger::error("Error getting monitor id by uuid", ['uuid' => $uuid, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function startPingTracking(string $uuid): bool {
        $pdo = $this->getPdo();
        $monitorId = $this->getMonitorIdByUuid($uuid);
        if ($monitorId === false) {
            return false;
        }
        try {
            // Upsert style: try delete then insert to ensure a single row
            $pdo->prepare("DELETE FROM ping_tracking WHERE monitor_id = ?")->execute([$monitorId]);
            $stmt = $pdo->prepare("INSERT INTO ping_tracking (monitor_id, started_at) VALUES (?, CURRENT_TIMESTAMP)");
            return $stmt->execute([$monitorId]);
        } catch (\PDOException $e) {
            Logger::error("Error starting ping tracking", ['uuid' => $uuid, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Completes a ping for given monitor uuid. If a tracking start exists, a duration is recorded.
     * Returns the inserted history id and duration in milliseconds (or null if not available)
     * @return array{history_id:int, duration_ms: int|null}|false
     */
    public function completePing(string $uuid): array|false {
        $pdo = $this->getPdo();
        $monitorId = $this->getMonitorIdByUuid($uuid);
        if ($monitorId === false) {
            return false;
        }

        try {
            $pdo->beginTransaction();

            $stmtStart = $pdo->prepare("SELECT started_at FROM ping_tracking WHERE monitor_id = ?");
            $stmtStart->execute([$monitorId]);
            $startedAt = $stmtStart->fetchColumn();

            $durationMs = null;
            if ($startedAt !== false && $startedAt !== null) {
                // Compute duration as difference between now and started_at in milliseconds
                $stmtNow = $pdo->query("SELECT strftime('%s','now') * 1000");
                if ($stmtNow === false) {
                    throw new \RuntimeException('Failed to query current timestamp');
                }
                $nowCol = $stmtNow->fetchColumn();
                if ($nowCol === false || $nowCol === null) {
                    throw new \RuntimeException('Failed to fetch current timestamp');
                }
                $nowMs = (int) $nowCol;

                $stmtStartMs = $pdo->prepare("SELECT strftime('%s', ?) * 1000");
                $stmtStartMs->execute([(string) $startedAt]);
                $startCol = $stmtStartMs->fetchColumn();
                if ($startCol === false || $startCol === null) {
                    // If for some reason conversion failed, treat as no duration
                    $durationMs = null;
                } else {
                    $startMs = (int) $startCol;
                    $durationMs = max(0, $nowMs - $startMs);
                }
            }

            $stmtInsert = $pdo->prepare("INSERT INTO ping_history (monitor_id, pinged_at, duration_ms) VALUES (?, CURRENT_TIMESTAMP, ?)");
            $stmtInsert->execute([$monitorId, $durationMs]);
            $historyId = (int)$pdo->lastInsertId();

            // Clear tracking row after recording
            $pdo->prepare("DELETE FROM ping_tracking WHERE monitor_id = ?")->execute([$monitorId]);

            $pdo->commit();
            return ['history_id' => $historyId, 'duration_ms' => $durationMs];
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Error completing ping", ['uuid' => $uuid, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @return array<array{pinged_at:string, duration_ms:int|null}>
     */
    public function getPingHistory(int $monitorId, int $limit, int $offset = 0): array {
        $pdo = $this->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT pinged_at, duration_ms FROM ping_history WHERE monitor_id = ? ORDER BY pinged_at DESC LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $monitorId, \PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            Logger::error("Error getting ping history", ['monitor_id' => $monitorId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countPingHistory(int $monitorId): int {
        $pdo = $this->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM ping_history WHERE monitor_id = ?");
            $stmt->execute([$monitorId]);
            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            Logger::error("Error counting ping history", ['monitor_id' => $monitorId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function hasPendingStart(int $monitorId): bool {
        $pdo = $this->getPdo();
        try {
            $stmt = $pdo->prepare("SELECT 1 FROM ping_tracking WHERE monitor_id = ? LIMIT 1");
            $stmt->execute([$monitorId]);
            $row = $stmt->fetchColumn();
            return $row !== false && $row !== null;
        } catch (\PDOException $e) {
            Logger::error("Error checking pending start", ['monitor_id' => $monitorId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function generateUUID(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function getUsername(int $userId): string|false {
        Logger::debug("Getting username for user ID", ['user_id' => $userId]);
        $pdo = $this->getPdo();

        try {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $username = $stmt->fetchColumn();

            if ($username === false || $username === null) {
                Logger::warning("User not found", ['user_id' => $userId]);
                return false;
            }

            $usernameStr = (string)$username;
            Logger::debug("Found username", ['user_id' => $userId, 'username' => $usernameStr]);
            return $usernameStr;
        } catch (\PDOException $e) {
            Logger::error("Error getting username", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @return UserProfileData|false
     */
    public function getUserProfile(int $userId): UserProfileData|false {
        Logger::debug("Getting user profile", ['user_id' => $userId]);
        $pdo = $this->getPdo();

        try {
            $stmt = $pdo->prepare("SELECT username, name, email FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($row === false || !is_array($row)) {
                Logger::warning("User not found when fetching profile", ['user_id' => $userId]);
                return false;
            }

            return new UserProfileData(
                (string)$row['username'],
                $row['name'] !== null ? (string)$row['name'] : null,
                $row['email'] !== null ? (string)$row['email'] : null,
            );
        } catch (\PDOException $e) {
            Logger::error("Error getting user profile", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateUserProfile(int $userId, ?string $name, ?string $email): bool {
        Logger::info("Updating user profile", ['user_id' => $userId]);
        $pdo = $this->getPdo();

        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $userId]);

            if ($result) {
                Logger::info("User profile updated", ['user_id' => $userId]);
                return true;
            }

            Logger::warning("Failed to update user profile", ['user_id' => $userId]);
            return false;
        } catch (\PDOException $e) {
            Logger::error("Error updating user profile", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function updateUserPassword(int $userId, string $passwordHash): bool {
        Logger::info("Updating user password", ['user_id' => $userId]);
        $pdo = $this->getPdo();

        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $result = $stmt->execute([$passwordHash, $userId]);

            if ($result) {
                Logger::info("User password updated", ['user_id' => $userId]);
                return true;
            }

            Logger::warning("Failed to update user password", ['user_id' => $userId]);
            return false;
        } catch (\PDOException $e) {
            Logger::error("Error updating user password", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
