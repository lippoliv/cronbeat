<?php

namespace Cronbeat\Controllers;

use Cronbeat\Database;
use Cronbeat\Logger;
use Cronbeat\UrlHelper;

class BaseController {
    protected Database $database;
    protected ?Logger $logger = null;

    public function __construct(Database $database, ?Logger $logger = null) {
        $this->database = $database;
        $this->logger = $logger;
        
        if ($this->logger) {
            $this->logger->debug("Controller initialized", ['controller' => static::class]);
        }
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

    protected function parsePathWithoutController(): string {
        $path = UrlHelper::parsePathWithoutController();
        
        if ($this->logger) {
            $this->logger->debug("Parsed path without controller", ['path' => $path]);
        }
        
        return $path;
    }
    
    /**
     * Handle routing for the controller
     * 
     * @return string HTML output
     */
    public function doRouting(): string {
        if ($this->logger) {
            $this->logger->info("Processing route", [
                'controller' => static::class,
                'path' => $this->parsePathWithoutController()
            ]);
        }
        
        // This method should be overridden by child classes
        if ($this->logger) {
            $this->logger->warning("doRouting method not implemented", ['controller' => static::class]);
        }
        
        return '';
    }
}
