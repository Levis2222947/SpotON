<?php

/*
 * Controller: tickets reserveren en je eigen reserveringen beheren.
 * Alleen voor bezoekers (dat wordt gecontroleerd in public/index.php).
 */

/** Stap 1: het bevestigingsscherm met het aantal tickets. */
function showReserveForm(): void
{
    $event = findOpenEvent(inputInt('event') ?? 0);
    $maxQuantity = maxTicketsFor($event);

    // Aantal uit de URL, maar altijd tussen 1 en het maximum
    $quantity = min(inputInt('quantity') ?? 1, $maxQuantity);

    view('reservations/create', [
        'title'       => 'Tickets reserveren',
        'event'       => $event,
        'quantity'    => $quantity,
        'maxQuantity' => $maxQuantity,
        'errors'      => [],
    ]);
}

/** Stap 2: de bezoeker klikt op "Reservering bevestigen". */
function handleReserve(): void
{
    $event = findOpenEvent(inputInt('event') ?? 0);
    $maxQuantity = maxTicketsFor($event);
    $quantity = inputInt('quantity');
    $errors = [];

    if ($quantity === null || $quantity > $maxQuantity) {
        $errors['quantity'] = "Kies een aantal tussen 1 en {$maxQuantity}.";
    } else {
        $result = createReservation(currentUserId(), $event['id'], $quantity);

        if ($result['error'] === '') {
            setFlash('success', "Uw reservering is bevestigd! Hieronder staan uw {$quantity} ticket(s).");
            redirect('reservation', ['id' => $result['id']]);
        }

        $errors['quantity'] = $result['error'];
        $event = findVisibleEvent($event['id']); // opnieuw ophalen: misschien zijn er net plaatsen verkocht
    }

    view('reservations/create', [
        'title'       => 'Tickets reserveren',
        'event'       => $event,
        'quantity'    => $quantity ?? 1,
        'maxQuantity' => maxTicketsFor($event),
        'errors'      => $errors,
    ], 422);
}

function showMyReservations(): void
{
    view('reservations/index', [
        'title'        => 'Mijn reserveringen',
        'reservations' => getReservationsForUser(currentUserId()),
    ]);
}

/** Eén reservering met de ticketcodes. */
function showReservation(): void
{
    $reservation = findReservationForUser(inputInt('id') ?? 0, currentUserId());

    if ($reservation === null) {
        showError(404, 'Deze reservering bestaat niet of is niet van u.');
    }

    view('reservations/show', [
        'title'       => 'Reservering ' . $reservation['event_title'],
        'reservation' => $reservation,
        'tickets'     => getTicketsForReservation($reservation['id']),
    ]);
}

function handleCancelReservation(): void
{
    $error = cancelReservation(inputInt('id') ?? 0, currentUserId());

    if ($error === '') {
        setFlash('success', 'Uw reservering is geannuleerd. De plaatsen zijn weer vrij.');
    } else {
        setFlash('error', $error);
    }
    redirect('my-reservations');
}

/** Zoekt het evenement op en controleert of je er nog tickets voor kunt reserveren. */
function findOpenEvent(int $eventId): array
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

/** Je mag niet meer tickets kiezen dan er vrij zijn, en niet meer dan het maximum per reservering. */
function maxTicketsFor(array $event): int
{
    return min(remainingSeats($event), CONFIG['max_tickets_per_reservation']);
}
