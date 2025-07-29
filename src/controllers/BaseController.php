<?php

namespace Cronbeat\Controllers;

use Cronbeat\Database;

class BaseController {
    protected $database;
    
    public function __construct() {
        $this->database = new Database();
    }
    
    public function render($view) {
        echo $view->render();
    }
    
    protected function parsePath() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        
        if (count($uri) > 1) {
            return array_slice($uri, 1);
        }
        
        return [];
    }
}