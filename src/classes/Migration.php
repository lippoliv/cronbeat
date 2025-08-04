<?php

namespace Cronbeat;

/**
 * Interface for database migrations
 */
interface Migration {
    /**
     * Get the migration name
     * 
     * @return string The name of the migration
     */
    public function getName(): string;
    
    /**
     * Get the migration version
     * 
     * @return int The version number of the migration
     */
    public function getVersion(): int;
    
    /**
     * Execute the migration
     * 
     * @param \PDO $pdo The PDO database connection
     * @return bool True if the migration was successful, false otherwise
     * @throws \Exception If the migration fails
     */
    public function up(\PDO $pdo): bool;
}

/**
 * Abstract base class for database migrations
 * Provides common functionality for all migrations
 */
abstract class BaseMigration implements Migration {
    /**
     * Execute the migration within a transaction
     * 
     * @param \PDO $pdo The PDO database connection
     * @return bool True if the migration was successful, false otherwise
     * @throws \Exception If the migration fails
     */
    public function up(\PDO $pdo): bool {
        Logger::info("Running migration", ['version' => $this->getVersion(), 'name' => $this->getName()]);
        
        try {
            // Start transaction
            $pdo->beginTransaction();
            
            // Execute migration logic
            $this->execute($pdo);
            
            // Commit transaction
            $pdo->commit();
            
            Logger::info("Migration completed successfully", ['version' => $this->getVersion()]);
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
            Logger::error("Error running migration", [
                'version' => $this->getVersion(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Execute the migration logic
     * This method should be implemented by each migration class
     * 
     * @param \PDO $pdo The PDO database connection
     * @return void
     * @throws \Exception If the migration logic fails
     */
    abstract protected function execute(\PDO $pdo): void;
}