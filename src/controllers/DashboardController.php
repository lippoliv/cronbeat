<?php

namespace Cronbeat\Controllers;

use Cronbeat\Views\DashboardView;

class DashboardController extends BaseController {
    public function index() {
        // In a real application, we would check if the user is authenticated here
        $view = new DashboardView();
        $this->render($view);
    }
}