<?php

namespace Cronbeat;

use Cronbeat\Views\BaseView;
use Cronbeat\Views\SetupView;
use Cronbeat\Views\LoginView;

class UI {
    public function renderSetupForm($error = null) {
        $view = new SetupView();
        $view->setError($error);
        return $view->render();
    }

    public function renderLoginForm($error = null) {
        $view = new LoginView();
        $view->setError($error);
        return $view->render();
    }

}