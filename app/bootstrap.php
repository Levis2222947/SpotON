<?php

/*
 * Dit bestand wordt als eerste geladen (vanuit public/index.php).
 * Het zet alles klaar wat elke pagina nodig heeft.
 */

define('APP_ROOT', dirname(__DIR__));

// 1. Instellingen laden (database, naam van de zaal, ...)
define('CONFIG', require __DIR__ . '/config/config.php');
date_default_timezone_set(CONFIG['timezone']);

// 2. Fouten niet aan de bezoeker laten zien, maar opslaan in een logbestand
ini_set('display_errors', CONFIG['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/error.log');
error_reporting(E_ALL);

// 3. Alle functies laden
require __DIR__ . '/helpers.php';
require __DIR__ . '/Core/database.php';
require __DIR__ . '/Core/view.php';
require __DIR__ . '/Core/auth.php';
require __DIR__ . '/Core/csrf.php';

require __DIR__ . '/Models/User.php';
require __DIR__ . '/Models/Category.php';
require __DIR__ . '/Models/Event.php';
require __DIR__ . '/Models/Reservation.php';
require __DIR__ . '/Models/Ticket.php';

require __DIR__ . '/Controllers/AuthController.php';
require __DIR__ . '/Controllers/EventController.php';
require __DIR__ . '/Controllers/ReservationController.php';
require __DIR__ . '/Controllers/Staff/EventController.php';
require __DIR__ . '/Controllers/Staff/ReservationController.php';
require __DIR__ . '/Controllers/Staff/ScanController.php';

// 4. Gaat er onverwacht iets mis? Dan schrijven we de fout in het logboek
//    en ziet de bezoeker een nette foutpagina.
set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    view('errors/500', [
        'title'   => 'Er ging iets mis',
        'details' => CONFIG['debug'] ? $error->getMessage() : null,
    ], 500);
});

// 5. Extra beveiliging voor de browser
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

// 6. Sessie starten: zo onthoudt de website wie er ingelogd is.
//    httponly = JavaScript kan het sessiecookie niet lezen.
session_name('spoton_session');
session_set_cookie_params([
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
session_start();
