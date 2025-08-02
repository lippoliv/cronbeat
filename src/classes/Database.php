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
        return file_exists($this->dbPath);
    }

    public function createDatabase(): bool {
        if (!is_dir($this->dbDir)) {
            if (!@mkdir($this->dbDir, 0755, true)) {
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        $this->connect();
        $this->createTables();
        return true;
    }

    public function connect(): \PDO {
        if ($this->pdo) {
            return $this->pdo;
        }

        if (!$this->databaseExists() && !is_dir($this->dbDir)) {
            if (!mkdir($this->dbDir, 0755, true)) {
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        $this->pdo = new \PDO("sqlite:{$this->dbPath}");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $this->pdo;
    }

    private function createTables(): void {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if ($this->pdo === null) {
            $this->connect();
        }
        
        $this->pdo->exec($sql);
    }

    public function createUser(string $username, string $passwordHash): bool {
        if ($this->pdo === null) {
            $this->connect();
        }

        $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $passwordHash]);
    }

    public function userExists(string $username): bool {
        if ($this->pdo === null) {
            $this->connect();
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function validateUser(string $username, string $passwordHash): bool {
        if ($this->pdo === null) {
            $this->connect();
        }

        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $storedHash = $stmt->fetchColumn();

        return $storedHash !== false && $storedHash === $passwordHash;
    }

    public function getDbPath(): string {
        return $this->dbPath;
    }
}
