<?php

// Controller: reserveringen bekijken, filteren en annuleren (medewerkers)

function showStaffReservations()
{
    // Onbekende status? Dan niet filteren op status.
    $status = inputText('status');
    if (!isset(RESERVATION_STATUS_LABELS[$status])) {
        $status = '';
    }

    $eventId = inputInt('event');
    $reservations = searchReservations($status, $eventId);

    // Tickets optellen
    $ticketTotal = 0;
    foreach ($reservations as $reservation) {
        $ticketTotal += $reservation['quantity'];
    }

    view('staff/reservations/index', [
        'title'        => 'Reserveringen',
        'reservations' => $reservations,
        'events'       => getEventOptions(),
        'filters'      => ['status' => $status, 'event' => $eventId],
        'isFiltered'   => $status !== '' || $eventId !== null,
        'ticketTotal'  => $ticketTotal,
    ]);
}

function handleStaffCancelReservation()
{
    $error = cancelReservation(inputInt('id'));

    if ($error === '') {
        setFlash('success', 'De reservering is geannuleerd. De plaatsen zijn weer vrij.');
    } else {
        setFlash('error', $error);
    }

    // Terug met dezelfde filters
    redirect('staff/reservations', [
        'status' => inputText('filter_status'),
        'event'  => inputText('filter_event'),
    ]);
}
