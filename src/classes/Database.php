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
        Logger::debug("Initializing database with migrations");

        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            if ($this->pdo === null) {
                throw new \RuntimeException("Failed to connect to database");
            }

            // Run all available migrations
            $allMigrations = $this->getAllMigrations();
            
            if (!empty($allMigrations)) {
                foreach ($allMigrations as $migration) {
                    $this->runMigration($migration);
                }
                Logger::info("Database initialized successfully");
            } else {
                Logger::warning("No migrations found");
            }
        } catch (\Exception $e) {
            Logger::error("Failed to initialize database", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * Get migration instance for a specific version
     * 
     * @param int $version The migration version to get
     * @return \Cronbeat\Migration|null The migration instance or null if not found
     */
    public function getMigration(int $version): ?\Cronbeat\Migration {
        $migrationFile = APP_DIR . '/migrations/' . sprintf('%04d', $version) . '.php';
        
        if (!file_exists($migrationFile)) {
            Logger::error("Migration file not found", ['version' => $version, 'file' => $migrationFile]);
            return null;
        }
        
        // Include the migration file
        require_once $migrationFile;
        
        // Construct the class name
        $className = '\\Cronbeat\\Migrations\\Migration' . sprintf('%04d', $version);
        
        if (!class_exists($className)) {
            Logger::error("Migration class not found", ['version' => $version, 'class' => $className]);
            return null;
        }
        
        // Create an instance of the migration class
        try {
            $migration = new $className();
            
            if (!$migration instanceof \Cronbeat\Migration) {
                Logger::error("Invalid migration class", ['version' => $version, 'class' => $className]);
                return null;
            }
            
            return $migration;
        } catch (\Exception $e) {
            Logger::error("Error creating migration instance", [
                'version' => $version,
                'class' => $className,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get all available migrations
     * 
     * @return array<int, \Cronbeat\Migration> Array of migration instances indexed by version
     */
    public function getAllMigrations(): array {
        $migrations = [];
        $migrationDir = APP_DIR . '/migrations';
        
        if (!is_dir($migrationDir)) {
            Logger::warning("Migrations directory not found", ['dir' => $migrationDir]);
            return [];
        }
        
        // Scan the migrations directory for migration files
        $files = scandir($migrationDir);
        
        foreach ($files as $file) {
            // Skip non-PHP files and directories
            if (!preg_match('/^(\d{4})\.php$/', $file, $matches)) {
                continue;
            }
            
            $version = (int) $matches[1];
            $migration = $this->getMigration($version);
            
            if ($migration) {
                $migrations[$version] = $migration;
            }
        }
        
        // Sort migrations by version
        ksort($migrations);
        
        return $migrations;
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
                Logger::debug("Migrations table does not exist, running initial migration");
                
                // Run the initial migration (version 0)
                $migration = $this->getMigration(0);
                if ($migration) {
                    $this->pdo->exec($migration['sql']);
                    
                    // Insert the version 0 record directly
                    $stmt = $this->pdo->prepare("INSERT INTO migrations (version, name) VALUES (?, ?)");
                    $stmt->execute([0, $migration['name']]);
                    
                    Logger::info("Initial migration completed successfully");
                    return 0;
                } else {
                    Logger::error("Initial migration not found");
                    throw new \RuntimeException("Initial migration not found");
                }
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
    
    /**
     * Run a migration
     * 
     * @param \Cronbeat\Migration|int $migration The migration instance or version number
     * @return bool True if the migration was successful, false otherwise
     * @throws \Exception If the migration fails
     */
    public function runMigration($migration): bool {
        if (is_int($migration)) {
            $migrationVersion = $migration;
            $migration = $this->getMigration($migrationVersion);
            
            if (!$migration) {
                throw new \RuntimeException("Migration not found for version {$migrationVersion}");
            }
        }
        
        if (!$migration instanceof \Cronbeat\Migration) {
            throw new \InvalidArgumentException("Invalid migration object");
        }
        
        $version = $migration->getVersion();
        $name = $migration->getName();
        
        Logger::info("Running migration", ['version' => $version, 'name' => $name]);
        
        if ($this->pdo === null) {
            $this->connect();
        }
        
        if ($this->pdo === null) {
            throw new \RuntimeException("Failed to connect to database");
        }
        
        try {
            // Execute the migration
            $migration->up($this->pdo);
            
            // Update database version
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
}
