<?php

namespace Cronbeat\Tests;

interface TestMigrationHelperInterface {
    public function loadMigration(int $version): ?\Cronbeat\Migration;

    /**
     * @return array<int, \Cronbeat\Migration>
     */
    public function loadAllMigrations(): array;
}
