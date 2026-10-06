<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Session;
use App\Core\View;

define('APP_ROOT', dirname(__DIR__));

// Autoloader: App\Core\Router  ->  app/Core/Router.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/helpers.php';

Config::load(require __DIR__ . '/config/config.php');
date_default_timezone_set(Config::get('timezone'));

// Fouten nooit aan de bezoeker tonen (behalve in debugmodus), wel loggen.
ini_set('display_errors', Config::get('debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/error.log');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    View::render('errors/500', [
        'title'   => 'Er ging iets mis',
        'details' => Config::get('debug') ? $e->getMessage() : null,
    ], 500);
});

// Beveiligingsheaders
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

Session::start();
