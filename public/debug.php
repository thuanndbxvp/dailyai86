<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$_SERVER['REQUEST_URI'] = '/admin/dashboard';
$_SERVER['REQUEST_METHOD'] = 'GET';
require_once dirname(__DIR__) . '/bootstrap/app.php';
require_once APP_ROOT . '/app/Auth.php';
require_once APP_ROOT . '/app/Router.php';
require_once APP_ROOT . '/routes.php';
Router::dispatch();
