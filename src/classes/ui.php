<?php

namespace Cronbeat;

use Cronbeat\Views\BaseView;
use Cronbeat\Views\SetupView;
use Cronbeat\Views\LoginView;
use Cronbeat\Views\DashboardView;

class UI {
    /**
     * Render the setup form
     *
     * @param string|null $error Error message to display
     * @return string The rendered HTML
     */
    public function renderSetupForm($error = null) {
        $view = new SetupView();
        $view->setError($error);
        return $view->render();
    }

    /**
     * Render the login form
     *
     * @param string|null $error Error message to display
     * @return string The rendered HTML
     */
    public function renderLoginForm($error = null) {
        $view = new LoginView();
        $view->setError($error);
        return $view->render();
    }

    /**
     * Render the dashboard
     *
     * @return string The rendered HTML
     */
    public function renderDashboard() {
        $view = new DashboardView();
        return $view->render();
    }
}