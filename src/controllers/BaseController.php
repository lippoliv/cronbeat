<?php

namespace Cronbeat\Controllers;

use Cronbeat\Database;
use Cronbeat\UrlHelper;

class BaseController {
    protected Database $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    protected function parsePathWithoutController(): string {
        return UrlHelper::parsePathWithoutController();
    }
}
