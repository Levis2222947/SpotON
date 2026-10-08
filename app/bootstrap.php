<?php

// Wordt als eerste geladen (vanuit public/index.php). Zet alles klaar voor elke pagina.

define('APP_ROOT', dirname(__DIR__));

// Instellingen laden
define('CONFIG', require __DIR__ . '/config/config.php');
date_default_timezone_set(CONFIG['timezone']);

// Fouten niet aan de bezoeker laten zien, maar opslaan in storage/logs/error.log
ini_set('display_errors', CONFIG['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/error.log');

// Al mijn functies laden
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

// Onverwachte fout (bijv. database staat uit)? Dan opslaan in het log en een nette foutpagina tonen.
function handleUnexpectedError($error)
{
    error_log($error);
    view('errors/500', ['title' => 'Er ging iets mis', 'details' => CONFIG['debug'] ? $error->getMessage() : '']);
}
set_exception_handler('handleUnexpectedError');

// Sessie starten, zodat de site onthoudt wie er ingelogd is.
// httponly: JavaScript kan het cookie niet lezen.
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
