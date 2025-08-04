<?php

namespace Cronbeat;

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
        $this->createTables();

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

    private function createTables(): void {
        Logger::debug("Creating database tables");

        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            version INTEGER NOT NULL UNIQUE,
            name TEXT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            if ($this->pdo === null) {
                throw new \RuntimeException("Failed to connect to database");
            }

            $this->pdo->exec($sql);
            
            // Set initial database version to 0
            $this->setDatabaseVersion(0);
            
            Logger::info("Database tables created successfully");
        } catch (\PDOException $e) {
            Logger::error("Failed to create database tables", ['error' => $e->getMessage()]);
            throw $e;
        }
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

    public function validateUser(string $username, string $passwordHash): bool {
        Logger::info("Validating user credentials", ['username' => $username]);

        if ($this->pdo === null) {
            $this->connect();
        }

        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }

        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $storedHash = $stmt->fetchColumn();
            $isValid = $storedHash !== false && $storedHash === $passwordHash;

            if ($isValid) {
                Logger::info("User authentication successful", ['username' => $username]);
            } else {
                Logger::warning("User authentication failed", ['username' => $username]);
            }

            return $isValid;
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
    
    public function getDatabaseVersion(): int {
        Logger::debug("Getting database version");
        
        if ($this->pdo === null) {
            $this->connect();
        }
        
        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }
        
        try {
            // Check if migrations table exists
            $tableExists = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
            if ($tableExists->fetch() === false) {
                Logger::debug("Migrations table does not exist, returning version 0");
                return 0;
            }
            
            $stmt = $this->pdo->query("SELECT MAX(version) FROM migrations");
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
            // Check if migrations table exists
            $tableExists = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'");
            if ($tableExists->fetch() === false) {
                Logger::debug("Creating migrations table");
                $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    version INTEGER NOT NULL UNIQUE,
                    name TEXT NOT NULL,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
            }
            
            // Check if this version already exists
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM migrations WHERE version = ?");
            $stmt->execute([$version]);
            $exists = (int) $stmt->fetchColumn() > 0;
            
            if ($exists) {
                Logger::debug("Version already exists, skipping", ['version' => $version]);
                return true;
            }
            
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
    
    public function runMigration(int $version, string $name, string $sql): bool {
        Logger::info("Running migration", ['version' => $version, 'name' => $name]);
        
        if ($this->pdo === null) {
            $this->connect();
        }
        
        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }
        
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Execute migration SQL
            $this->pdo->exec($sql);
            
            // Update database version
            $this->setDatabaseVersion($version, $name);
            
            // Commit transaction
            $this->pdo->commit();
            
            Logger::info("Migration completed successfully", ['version' => $version]);
            return true;
        } catch (\PDOException $e) {
            // Rollback transaction on error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            
            Logger::error("Error running migration", [
                'version' => $version,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
