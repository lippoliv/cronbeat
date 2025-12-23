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

            Logger::info("Successfully connected to database");

            return $this->pdo;
        } catch (\PDOException $e) {
            Logger::error("Failed to connect to database", ['error' => $e->getMessage()]);
            throw $e;
        }
    }


    /**
     * Ensures a PDO connection is available and returns it.
     */
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
                SELECT uuid, name 
                FROM monitors
                WHERE user_id = ?
                ORDER BY name ASC
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
            $stmt = $this->pdo->prepare("DELETE FROM monitors WHERE uuid = ? AND user_id = ?");
            $result = $stmt->execute([$uuid, $userId]);

            if ($result && $stmt->rowCount() > 0) {
                Logger::info("Monitor deleted successfully", ['uuid' => $uuid]);
                return true;
            } else {
                Logger::warning("Failed to delete monitor", ['uuid' => $uuid]);
                return false;
            }
        } catch (\PDOException $e) {
            Logger::error("Error deleting monitor", [
                'uuid' => $uuid,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
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
