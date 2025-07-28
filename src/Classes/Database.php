<?php

namespace Cronbeat;

class Database
{
    private const DB_PATH = __DIR__ . '/../../db/db.sqlite';
    private \PDO $connection;
    
    /**
     * Check if the database exists
     *
     * @return bool True if the database exists, false otherwise
     */
    public static function exists(): bool
    {
        return file_exists(self::DB_PATH);
    }
    
    /**
     * Get the database connection
     *
     * @return \PDO The database connection
     */
    public function getConnection(): \PDO
    {
        if (!isset($this->connection)) {
            // Create the db directory if it doesn't exist
            $dbDir = dirname(self::DB_PATH);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            // Create the database connection
            $this->connection = new \PDO('sqlite:' . self::DB_PATH);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        
        return $this->connection;
    }
    
    /**
     * Initialize the database with the required tables
     *
     * @return bool True if the database was initialized successfully, false otherwise
     */
    public function initialize(): bool
    {
        try {
            $connection = $this->getConnection();
            
            // Create the users table
            $connection->exec('
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');
            
            return true;
        } catch (\PDOException $e) {
            // Log the error or handle it appropriately
            return false;
        }
    }
    
    /**
     * Create a new user
     *
     * @param string $username The username
     * @param string $password The password (already hashed with SHA-256)
     * @return bool True if the user was created successfully, false otherwise
     */
    public function createUser(string $username, string $password): bool
    {
        try {
            $connection = $this->getConnection();
            
            // Store the password with additional server-side hashing for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $connection->prepare('
                INSERT INTO users (username, password)
                VALUES (:username, :password)
            ');
            
            $stmt->execute([
                ':username' => $username,
                ':password' => $hashedPassword
            ]);
            
            return true;
        } catch (\PDOException $e) {
            // Log the error or handle it appropriately
            return false;
        }
    }
}