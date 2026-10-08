<?php

// Controller: tickets reserveren en eigen reserveringen (alleen bezoekers, zie index.php)

// Scherm om het aantal tickets te bevestigen.
function showReserveForm()
{
    $event = findOpenEvent(inputInt('event'));
    $maxQuantity = maxTicketsFor($event);

    // Aantal uit de URL, maar niet meer dan het maximum
    $quantity = inputInt('quantity');
    if ($quantity === null) {
        $quantity = 1;
    }
    if ($quantity > $maxQuantity) {
        $quantity = $maxQuantity;
    }

    view('reservations/create', [
        'title'       => 'Tickets reserveren',
        'event'       => $event,
        'quantity'    => $quantity,
        'maxQuantity' => $maxQuantity,
        'errors'      => [],
    ]);
}

// Bezoeker klikt op "Reservering bevestigen".
function handleReserve()
{
    $event = findOpenEvent(inputInt('event'));
    $maxQuantity = maxTicketsFor($event);
    $quantity = inputInt('quantity');
    $errors = [];

    if ($quantity === null || $quantity > $maxQuantity) {
        $errors['quantity'] = 'Kies een aantal tussen 1 en ' . $maxQuantity . '.';
    } else {
        $result = createReservation(currentUserId(), $event['id'], $quantity);

        // Gelukt? Naar de pagina met je tickets.
        if ($result['error'] === '') {
            setFlash('success', 'Uw reservering is bevestigd! Hieronder staan uw ' . $quantity . ' ticket(s).');
            redirect('reservation', ['id' => $result['id']]);
        }

        $errors['quantity'] = $result['error'];

        // Opnieuw ophalen, misschien zijn er net plaatsen verkocht
        $event = findVisibleEvent($event['id']);
    }

    if ($quantity === null) {
        $quantity = 1;
    }

    view('reservations/create', [
        'title'       => 'Tickets reserveren',
        'event'       => $event,
        'quantity'    => $quantity,
        'maxQuantity' => maxTicketsFor($event),
        'errors'      => $errors,
    ], 422);
}

function showMyReservations()
{
    view('reservations/index', [
        'title'        => 'Mijn reserveringen',
        'reservations' => getReservationsForUser(currentUserId()),
    ]);
}

// Eén reservering met de ticketcodes.
function showReservation()
{
    $reservation = findReservationForUser(inputInt('id'), currentUserId());

    if ($reservation === null) {
        showError(404, 'Deze reservering bestaat niet of is niet van u.');
    }

    view('reservations/show', [
        'title'       => 'Reservering ' . $reservation['event_title'],
        'reservation' => $reservation,
        'tickets'     => getTicketsForReservation($reservation['id']),
    ]);
}

function handleCancelReservation()
{
    $error = cancelReservation(inputInt('id'), currentUserId());

    if ($error === '') {
        setFlash('success', 'Uw reservering is geannuleerd. De plaatsen zijn weer vrij.');
    } else {
        setFlash('error', $error);
    }

    redirect('my-reservations');
}

// Evenement ophalen en checken of je nog kunt reserveren.
function findOpenEvent($eventId)
{
    $event = findVisibleEvent($eventId);

    if ($event === null) {
        showError(404, 'Dit evenement bestaat niet.');
    }

    $status = saleStatus($event);
    if (!$status['open']) {
        setFlash('error', 'Reserveren is niet mogelijk: ' . strtolower($status['label']) . '.');
        redirect('event', ['id' => $eventId]);
    }

    return $event;
}

// Maximaal aantal tickets: niet meer dan er vrij zijn en niet meer dan 10.
function maxTicketsFor($event)
{
    $remaining = remainingSeats($event);
    $max = CONFIG['max_tickets_per_reservation'];

    if ($remaining < $max) {
        return $remaining;
    }
    return $max;
}
