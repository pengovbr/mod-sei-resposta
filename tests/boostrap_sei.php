<?php

require_once 'vendor/autoload.php';
require_once 'src/sei/web/SEI.php';

define("DIR_TEST", __DIR__);
define("DIR_PROJECT", __DIR__ . '/..');
define("DIR_SEI", __DIR__ . '/../sei');
define("DIR_SEI_WEB", DIR_SEI . '/web');
define("DIR_INFRA", __DIR__ . '/../sei/src/infra/infra_php');

error_reporting(E_ERROR);
restore_error_handler();
