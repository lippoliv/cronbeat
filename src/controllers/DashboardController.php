<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\DashboardView;

class DashboardController extends BaseController {
    public function doRouting() {
        // Parse the URL
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        
        // Get the action
        $action = isset($uri[1]) ? $uri[1] : 'index';
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        // Call the appropriate method
        if (method_exists($this, $action)) {
            $this->$action();
        } else {
            $this->index();
        }
    }
    
    public function index() {
        // In a real application, we would check if the user is authenticated here
        $view = new DashboardView();
        $this->render($view);
    }
}