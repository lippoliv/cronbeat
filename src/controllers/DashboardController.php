<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\DashboardView;

class DashboardController extends BaseController {
    public function doRouting() {
        $this->showDashboard();
    }
    
    public function showDashboard() {
        $view = new DashboardView();
        $this->render($view);
    }
    
}