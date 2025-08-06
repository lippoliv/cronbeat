<?php

namespace Cronbeat;

interface Migration {

    public function getName(): string;

    public function getVersion(): int;

    public function up(\PDO $pdo): bool;
}

/**
 * Abstract base class for database migrations
 * Provides common functionality for all migrations
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
abstract class BaseMigration implements Migration {

    public function up(\PDO $pdo): bool {
        Logger::info("Running migration", ['version' => $this->getVersion(), 'name' => $this->getName()]);

        try {
            $pdo->beginTransaction();
            $this->execute($pdo);
            $pdo->commit();

            Logger::info("Migration completed successfully", ['version' => $this->getVersion()]);
            return true;
        } catch (\Exception $e) {
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

    abstract protected function execute(\PDO $pdo): void;
}
