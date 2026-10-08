<?php

// Elke pagina begint hier. In de URL staat welke pagina je wilt, bijv. index.php?page=login

require dirname(__DIR__) . '/app/bootstrap.php';

// Welke pagina? Niks ingevuld = home.
$page = inputText('page');
if ($page === '') {
    $page = 'home';
}

// Is er een formulier verstuurd?
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

// Bij elk formulier eerst de CSRF-code checken.
if ($isPost) {
    checkCsrf();
}

// Pagina's alleen voor bezoekers
$visitorPages = ['reserve', 'my-reservations', 'reservation', 'reservation/cancel'];
if (in_array($page, $visitorPages)) {
    requireVisitor();
}

// Pagina's die met "staff/" beginnen zijn alleen voor medewerkers
if (str_starts_with($page, 'staff/')) {
    requireStaff();
}

// Welke functie hoort bij de pagina? Bij formulieren: verstuurd = verwerken, anders formulier tonen.
switch ($page) {
    // Pagina's voor iedereen
    case 'home':
        showEventList();
        break;

    case 'event':
        showEventDetail();
        break;

    case 'register':
        if ($isPost) {
            handleRegister();
        } else {
            showRegisterForm();
        }
        break;

    case 'login':
        if ($isPost) {
            handleLogin();
        } else {
            showLoginForm();
        }
        break;

    // Uitloggen, annuleren en verwijderen alleen via een formulier (dus met CSRF-check)
    case 'logout':
        if ($isPost) {
            handleLogout();
        } else {
            redirect();
        }
        break;

    // Pagina's voor bezoekers
    case 'reserve':
        if ($isPost) {
            handleReserve();
        } else {
            showReserveForm();
        }
        break;

    case 'my-reservations':
        showMyReservations();
        break;

    case 'reservation':
        showReservation();
        break;

    case 'reservation/cancel':
        if ($isPost) {
            handleCancelReservation();
        } else {
            redirect('my-reservations');
        }
        break;

    // Pagina's voor medewerkers
    case 'staff/events':
        showStaffEvents();
        break;

    case 'staff/events/create':
        if ($isPost) {
            handleCreateEvent();
        } else {
            showNewEventForm();
        }
        break;

    case 'staff/events/edit':
        if ($isPost) {
            handleUpdateEvent();
        } else {
            showEditEventForm();
        }
        break;

    case 'staff/events/delete':
        if ($isPost) {
            handleDeleteEvent();
        } else {
            redirect('staff/events');
        }
        break;

    case 'staff/reservations':
        showStaffReservations();
        break;

    case 'staff/reservations/cancel':
        if ($isPost) {
            handleStaffCancelReservation();
        } else {
            redirect('staff/reservations');
        }
        break;

    case 'staff/scan':
        if ($isPost) {
            handleScan();
        } else {
            showScanPage();
        }
        break;

    // Onbekende pagina
    default:
        showError(404, 'Deze pagina bestaat niet.');
}
