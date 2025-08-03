<?php

namespace Cronbeat;

class Database {
    private string $dbPath;
    private string $dbDir;
    private ?\PDO $pdo = null;
    private ?Logger $logger = null;

    public function __construct(string $dbPath, ?Logger $logger = null) {
        $this->dbPath = $dbPath;
        $this->dbDir = dirname($this->dbPath);
        $this->logger = $logger;
    }
    
    /**
     * Set the logger instance
     * 
     * @param Logger $logger Logger instance
     * @return void
     */
    public function setLogger(Logger $logger): void {
        $this->logger = $logger;
    }

    public function databaseExists(): bool {
        $exists = file_exists($this->dbPath);
        if ($this->logger) {
            $this->logger->debug("Checking if database exists at {$this->dbPath}", ['exists' => $exists]);
        }
        return $exists;
    }

    public function createDatabase(): bool {
        if ($this->logger) {
            $this->logger->info("Creating new database at {$this->dbPath}");
        }
        
        if (!is_dir($this->dbDir)) {
            if ($this->logger) {
                $this->logger->debug("Creating database directory: {$this->dbDir}");
            }
            if (!@mkdir($this->dbDir, 0755, true)) {
                if ($this->logger) {
                    $this->logger->error("Failed to create database directory: {$this->dbDir}");
                }
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        $this->connect();
        $this->createTables();
        
        if ($this->logger) {
            $this->logger->info("Database created successfully");
        }
        return true;
    }

    public function connect(): \PDO {
        if ($this->pdo) {
            if ($this->logger) {
                $this->logger->debug("Reusing existing database connection");
            }
            return $this->pdo;
        }

        if ($this->logger) {
            $this->logger->debug("Connecting to database at {$this->dbPath}");
        }

        if (!$this->databaseExists() && !is_dir($this->dbDir)) {
            if ($this->logger) {
                $this->logger->debug("Creating database directory: {$this->dbDir}");
            }
            if (!mkdir($this->dbDir, 0755, true)) {
                if ($this->logger) {
                    $this->logger->error("Failed to create database directory: {$this->dbDir}");
                }
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        try {
            $this->pdo = new \PDO("sqlite:{$this->dbPath}");
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            if ($this->logger) {
                $this->logger->info("Successfully connected to database");
            }
            
            return $this->pdo;
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Failed to connect to database", ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    private function createTables(): void {
        if ($this->logger) {
            $this->logger->debug("Creating database tables");
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if ($this->pdo === null) {
            $this->connect();
        }
        
        try {
            $this->pdo->exec($sql);
            if ($this->logger) {
                $this->logger->info("Database tables created successfully");
            }
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Failed to create database tables", ['error' => $e->getMessage()]);
            }
            throw $e;
        }
    }

    public function createUser(string $username, string $passwordHash): bool {
        if ($this->logger) {
            $this->logger->info("Creating new user", ['username' => $username]);
        }
        
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $result = $stmt->execute([$username, $passwordHash]);
            
            if ($result) {
                if ($this->logger) {
                    $this->logger->info("User created successfully", ['username' => $username]);
                }
            } else {
                if ($this->logger) {
                    $this->logger->warning("Failed to create user", ['username' => $username]);
                }
            }
            
            return $result;
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Error creating user", [
                    'username' => $username,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    public function userExists(string $username): bool {
        if ($this->logger) {
            $this->logger->debug("Checking if user exists", ['username' => $username]);
        }
        
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $exists = (int) $stmt->fetchColumn() > 0;
            
            if ($this->logger) {
                $this->logger->debug("User existence check result", [
                    'username' => $username,
                    'exists' => $exists
                ]);
            }
            
            return $exists;
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Error checking if user exists", [
                    'username' => $username,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    public function validateUser(string $username, string $passwordHash): bool {
        if ($this->logger) {
            $this->logger->info("Validating user credentials", ['username' => $username]);
        }
        
        if ($this->pdo === null) {
            $this->connect();
        }

        try {
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $storedHash = $stmt->fetchColumn();
            $isValid = $storedHash !== false && $storedHash === $passwordHash;
            
            if ($isValid) {
                if ($this->logger) {
                    $this->logger->info("User authentication successful", ['username' => $username]);
                }
            } else {
                if ($this->logger) {
                    $this->logger->warning("User authentication failed", ['username' => $username]);
                }
            }
            
            return $isValid;
        } catch (\PDOException $e) {
            if ($this->logger) {
                $this->logger->error("Error validating user", [
                    'username' => $username,
                    'error' => $e->getMessage()
                ]);
            }
            throw $e;
        }
    }

    public function getDbPath(): string {
        return $this->dbPath;
    }
}
