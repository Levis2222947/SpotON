<?php

/*
 * Elke pagina van de website begint hier.
 * In de URL staat welke pagina je wilt, bijvoorbeeld index.php?page=login
 * Hieronder kijken we welke pagina het is en roepen we de juiste functie aan.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

$page = inputText('page');
if ($page === '') {
    $page = 'home';
}
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST'; // true als er een formulier is verstuurd

// Elk verstuurd formulier moet de geheime CSRF-code bevatten
if ($isPost) {
    checkCsrf();
}

// Wie mag welke pagina zien?
$visitorPages = ['reserve', 'my-reservations', 'reservation', 'reservation/cancel'];
if (in_array($page, $visitorPages)) {
    requireVisitor();
}
if (str_starts_with($page, 'staff/')) {
    requireStaff();
}

// Welke pagina? -> welke functie?
// Bij formulieren: $isPost ? (formulier verwerken) : (formulier laten zien)
switch ($page) {
    // Voor iedereen
    case 'home':                $isPost ? redirect() : showEventList(); break;
    case 'event':               showEventDetail(); break;
    case 'register':            $isPost ? handleRegister() : showRegisterForm(); break;
    case 'login':               $isPost ? handleLogin() : showLoginForm(); break;
    case 'logout':              $isPost ? handleLogout() : redirect(); break;

    // Bezoekers
    case 'reserve':             $isPost ? handleReserve() : showReserveForm(); break;
    case 'my-reservations':     showMyReservations(); break;
    case 'reservation':         showReservation(); break;
    case 'reservation/cancel':  $isPost ? handleCancelReservation() : redirect('my-reservations'); break;

    // Medewerkers
    case 'staff/events':        showStaffEvents(); break;
    case 'staff/events/create': $isPost ? handleCreateEvent() : showNewEventForm(); break;
    case 'staff/events/edit':   $isPost ? handleUpdateEvent() : showEditEventForm(); break;
    case 'staff/events/delete': $isPost ? handleDeleteEvent() : redirect('staff/events'); break;
    case 'staff/reservations':  showStaffReservations(); break;
    case 'staff/reservations/cancel': $isPost ? handleStaffCancelReservation() : redirect('staff/reservations'); break;
    case 'staff/scan':          $isPost ? handleScan() : showScanPage(); break;

    default:                    showError(404, 'Deze pagina bestaat niet.');
}
