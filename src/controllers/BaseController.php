<?php

namespace Cronbeat\Controllers;

use Cronbeat\Database;

class BaseController {
    protected Database $database;

    public function __construct(Database $database) {
        $this->database = $database;
    }

    protected function parsePathWithoutController(): string {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);

        if (count($uri) > 1) {
            return implode('/', array_slice($uri, 1));
        }

        return '';
    }
}
