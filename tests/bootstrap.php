<?php

// PHPUnit Bootstrap file
// This file handles autoloading and setup that would otherwise cause side effects in test files

const APP_DIR = __DIR__ . '/../src';
const DB_VERSION = 1;

require_once APP_DIR . '/classes/Database.php';
require_once APP_DIR . '/classes/Logger.php';
require_once APP_DIR . '/classes/Migration.php';
require_once APP_DIR . '/classes/MigrationHelper.php';
require_once APP_DIR . '/classes/UserProfileData.php';
require_once APP_DIR . '/controllers/BaseController.php';
require_once APP_DIR . '/views/base.view.php';
require_once APP_DIR . '/views/setup.view.php';
require_once APP_DIR . '/views/monitor_form.view.php';
require_once APP_DIR . '/views/dashboard.view.php';
require_once APP_DIR . '/controllers/SetupController.php';
require_once APP_DIR . '/controllers/DashboardController.php';
require_once __DIR__ . '/DatabaseTestCase.php';
