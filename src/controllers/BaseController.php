<?php

namespace Cronbeat\Controllers;

use Cronbeat\Database;
use Cronbeat\Logger;
use Cronbeat\UrlHelper;

class BaseController {
    protected Database $database;

    public function __construct(Database $database) {
        $this->database = $database;
        Logger::debug("Controller initialized", ['controller' => static::class]);
    }

    protected function parsePathWithoutController(): string {
        $path = UrlHelper::parsePathWithoutController();
        Logger::debug("Parsed path without controller", ['path' => $path]);
        return $path;
    }

    public function doRouting(): string {
        Logger::info("Processing route", [
            'controller' => static::class,
            'path' => $this->parsePathWithoutController()
        ]);
        Logger::warning("doRouting method not implemented", ['controller' => static::class]);
        return '';
    }
}
