<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\EventController;
use App\Controllers\ReservationController;
use App\Controllers\Staff\EventController as StaffEventController;
use App\Controllers\Staff\ReservationController as StaffReservationController;
use App\Controllers\Staff\ScanController;
use App\Core\Router;

/*
 * Alle routes van SpotOn op één plek.
 * Toegangscontrole per rol gebeurt in de controllers (Auth::requireRole).
 */
return static function (Router $router): void {
    // Openbaar
    $router->get('home', [EventController::class, 'index']);
    $router->get('event', [EventController::class, 'show']);

    // Account
    $router->get('register', [AuthController::class, 'showRegister']);
    $router->post('register', [AuthController::class, 'register']);
    $router->get('login', [AuthController::class, 'showLogin']);
    $router->post('login', [AuthController::class, 'login']);
    $router->post('logout', [AuthController::class, 'logout']);

    // Bezoeker: reserveren en eigen reserveringen
    $router->get('reserve', [ReservationController::class, 'create']);
    $router->post('reserve', [ReservationController::class, 'store']);
    $router->get('my-reservations', [ReservationController::class, 'index']);
    $router->get('reservation', [ReservationController::class, 'show']);
    $router->post('reservation/cancel', [ReservationController::class, 'cancel']);

    // Medewerker: evenementen beheren
    $router->get('staff/events', [StaffEventController::class, 'index']);
    $router->get('staff/events/create', [StaffEventController::class, 'create']);
    $router->post('staff/events/store', [StaffEventController::class, 'store']);
    $router->get('staff/events/edit', [StaffEventController::class, 'edit']);
    $router->post('staff/events/update', [StaffEventController::class, 'update']);
    $router->post('staff/events/delete', [StaffEventController::class, 'delete']);

    // Medewerker: reserveringen
    $router->get('staff/reservations', [StaffReservationController::class, 'index']);
    $router->post('staff/reservations/cancel', [StaffReservationController::class, 'cancel']);

    // Medewerker: toegangscontrole
    $router->get('staff/scan', [ScanController::class, 'index']);
    $router->post('staff/scan', [ScanController::class, 'scan']);
};
