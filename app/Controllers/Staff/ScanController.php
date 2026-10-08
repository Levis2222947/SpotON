<?php

// Controller: tickets scannen bij de deur (medewerkers)

function showScanPage()
{
    $eventId = inputInt('event');

    $event = null;
    $counts = null;
    if ($eventId !== null) {
        $event = findEvent($eventId);
    }
    if ($event !== null) {
        $counts = ticketCountsForEvent($event['id']);
    }

    // Uitkomst van de vorige scan uit de sessie halen (zie handleScan)
    $scanResult = $_SESSION['scan_result'] ?? null;
    $errors = $_SESSION['scan_errors'] ?? [];
    unset($_SESSION['scan_result'], $_SESSION['scan_errors']);

    view('staff/scan', [
        'title'      => 'Tickets scannen',
        'events'     => getEventOptions(),
        'event'      => $event,
        'counts'     => $counts,
        'scanResult' => $scanResult,
        'errors'     => $errors,
    ]);
}

function handleScan()
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

    // Terug naar de scanpagina. Zo wordt bij F5 niet nog een keer gescand.
    if ($eventId !== null) {
        redirect('staff/scan', ['event' => $eventId]);
    }
    redirect('staff/scan');
}
