<?php

/*
 * Controller voor medewerkers: tickets controleren aan de deur.
 */

function showScanPage(): void
{
    $eventId = inputInt('event');
    $event = $eventId !== null ? findEvent($eventId) : null;

    // De uitkomst van de vorige scan staat in de sessie (zie handleScan)
    $scanResult = $_SESSION['scan_result'] ?? null;
    $errors = $_SESSION['scan_errors'] ?? [];
    unset($_SESSION['scan_result'], $_SESSION['scan_errors']);

    view('staff/scan', [
        'title'      => 'Tickets scannen',
        'events'     => getEventOptions(),
        'event'      => $event,
        'counts'     => $event !== null ? ticketCountsForEvent($event['id']) : null,
        'scanResult' => $scanResult,
        'errors'     => $errors,
    ]);
}

function handleScan(): void
{
    $eventId = inputInt('event');
    $code = inputText('code');

    if ($code === '') {
        $_SESSION['scan_errors'] = ['code' => 'Vul een ticketcode in.'];
    } else {
        $outcome = useTicket($code, currentUserId(), $eventId);
        $_SESSION['scan_result'] = [
            'result' => $outcome['result'],
            'code'   => cleanTicketCode($code),
            'ticket' => $outcome['ticket'],
        ];
    }

    // Terug naar de scanpagina. Als je dan op F5 drukt, wordt het ticket niet nog eens gescand.
    redirect('staff/scan', $eventId !== null ? ['event' => $eventId] : []);
}
