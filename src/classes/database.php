<?php

namespace Cronbeat;

class Database {
    private $dbPath;
    private $dbDir;
    private $pdo;

    public function __construct($dbPath = null) {
        if ($dbPath !== null) {
            $this->dbPath = $dbPath;
        } else if (defined('APP_DIR')) {
            $this->dbPath = APP_DIR . '/db/db.sqlite';
        }
        $this->dbDir = dirname($this->dbPath);
    }

    public function databaseExists() {
        return file_exists($this->dbPath);
    }

    public function createDatabase() {
        if (!is_dir($this->dbDir)) {
            if (!mkdir($this->dbDir, 0755, true)) {
                throw new \RuntimeException("Failed to create database directory: {$this->dbDir}");
            }
        }

        $this->connect();
        $this->createTables();
        return true;
    }

    public function connect() {
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

    private function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->pdo->exec($sql);
    }

    public function createUser($username, $passwordHash) {
        if (!$this->pdo) {
            $this->connect();
        }

        $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        return $stmt->execute([$username, $passwordHash]);
    }

    public function userExists($username) {
        if (!$this->pdo) {
            $this->connect();
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function validateUser($username, $passwordHash) {
        if (!$this->pdo) {
            $this->connect();
        }

        $stmt = $this->pdo->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $storedHash = $stmt->fetchColumn();
        
        return $storedHash && $storedHash === $passwordHash;
    }

    public function getDbPath() {
        return $this->dbPath;
    }
}