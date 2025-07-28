<?php

namespace Cronbeat\Controllers;

class BaseController {
    protected $database;
    
    public function __construct() {
        $this->database = new \Cronbeat\Database();
    }
    
    public function render($view) {
        echo $view->render();
    }
}