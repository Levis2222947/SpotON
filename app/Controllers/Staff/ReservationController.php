<?php

/*
 * Controller voor medewerkers: reserveringen bekijken, filteren en annuleren.
 */

function showStaffReservations(): void
{
    $status = inputText('status');
    if (!array_key_exists($status, RESERVATION_STATUS_LABELS)) {
        $status = ''; // onbekende status = niet filteren
    }
    $eventId = inputInt('event');

    $reservations = searchReservations($status, $eventId);

    view('staff/reservations/index', [
        'title'        => 'Reserveringen',
        'reservations' => $reservations,
        'events'       => getEventOptions(),
        'filters'      => ['status' => $status, 'event' => $eventId],
        'isFiltered'   => $status !== '' || $eventId !== null,
        'ticketTotal'  => array_sum(array_column($reservations, 'quantity')),
    ]);
}

function handleStaffCancelReservation(): void
{
    $error = cancelReservation(inputInt('id') ?? 0);

    if ($error === '') {
        setFlash('success', 'De reservering is geannuleerd. De plaatsen zijn weer vrij.');
    } else {
        setFlash('error', $error);
    }

    // Terug naar het overzicht, met dezelfde filters als daarnet
    redirect('staff/reservations', [
        'status' => inputText('filter_status'),
        'event'  => inputText('filter_event'),
    ]);
}
