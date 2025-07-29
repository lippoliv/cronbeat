<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\DashboardView;

class DashboardController extends BaseController {
    public function doRouting() {
        $path = $this->parsePathWithoutController();
        $action = !empty($path[0]) ? $path[0] : 'index';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = 'process';
        }
        
        switch ($action) {
            case 'index':
                $this->showDashboard();
                break;
            case 'process':
                $this->processDashboard();
                break;
            default:
                $this->showDashboard();
                break;
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