<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\DashboardView;

class DashboardController extends BaseController {
    private $routeMap = [
        'index' => 'showDashboard',
        'process' => 'processDashboard'
    ];
    
    public function doRouting() {
        $path = $this->parsePath();
        $action = !empty($path[0]) ? $path[0] : 'index';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        if (isset($this->routeMap[$action]) && method_exists($this, $this->routeMap[$action])) {
            $method = $this->routeMap[$action];
            $this->$method();
        } else {
            $this->showDashboard();
        }
    }
    
    public function showDashboard() {
        $view = new DashboardView();
        $this->render($view);
    }
    
    public function processDashboard() {
        $this->showDashboard();
    }
}