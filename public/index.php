<?php

declare(strict_types=1);

/*
 * Front controller: elk verzoek komt hier binnen.
 * De ?r= parameter bepaalt welke controller-actie wordt uitgevoerd (zie app/routes.php).
 */

use App\Core\HttpException;
use App\Core\Router;
use App\Core\View;

require dirname(__DIR__) . '/app/bootstrap.php';

$router = new Router();
(require APP_ROOT . '/app/routes.php')($router);

$route = is_string($_GET['r'] ?? null) ? $_GET['r'] : 'home';

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $route);
} catch (HttpException $e) {
    $titles = [403 => 'Geen toegang', 404 => 'Niet gevonden', 405 => 'Niet toegestaan', 419 => 'Formulier verlopen'];
    View::render('errors/http', [
        'title'   => $titles[$e->status] ?? 'Fout',
        'status'  => $e->status,
        'message' => $e->getMessage(),
    ], $e->status);
}
