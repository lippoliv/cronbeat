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
}