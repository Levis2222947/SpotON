<?php

// Model: tickets (tabel tickets)
// Elk ticket heeft een eigen code, zoals SO-AB12-CD34.
// Status: valid (geldig), used (gebruikt) of cancelled (geannuleerd)

// Tickets maken, elk met een eigen code.
function createTickets($reservationId, $quantity)
{
    $query = db()->prepare('INSERT INTO tickets (reservation_id, code) VALUES (?, ?)');

    for ($i = 0; $i < $quantity; $i++) {
        $code = generateTicketCode();
        while (ticketCodeExists($code)) { // bestaat de code al? Nieuwe maken
            $code = generateTicketCode();
        }
        $query->execute([$reservationId, $code]);
    }
}

// Willekeurige code. Zonder 0, O, 1 en I, die haal je makkelijk door elkaar.
function generateTicketCode()
{
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';

    for ($i = 0; $i < 8; $i++) {
        $code .= $letters[random_int(0, strlen($letters) - 1)];
    }
    return 'SO-' . substr($code, 0, 4) . '-' . substr($code, 4, 4);
}

function ticketCodeExists($code)
{
    $query = db()->prepare('SELECT id FROM tickets WHERE code = ?');
    $query->execute([$code]);
    return $query->fetch() !== false;
}

function getTicketsForReservation($reservationId)
{
    $query = db()->prepare('SELECT * FROM tickets WHERE reservation_id = ? ORDER BY id');
    $query->execute([$reservationId]);
    return $query->fetchAll();
}

// Ticket scannen bij de deur. Een ticket mag maar één keer gebruikt worden,
// daarom zet ik het op slot (FOR UPDATE) terwijl ik het check.
// Uitkomst: valid, already_used, cancelled, wrong_event of not_found.
function useTicket($code, $staffId, $eventId)
{
    $pdo = db();
    $pdo->beginTransaction();

    $query = $pdo->prepare(
        'SELECT t.*, r.event_id, e.title AS event_title, e.starts_at, u.name AS user_name
         FROM tickets t
         JOIN reservations r ON r.id = t.reservation_id
         JOIN events e ON e.id = r.event_id
         JOIN users u ON u.id = r.user_id
         WHERE t.code = ?
         FOR UPDATE'
    );
    $query->execute([cleanTicketCode($code)]);
    $ticket = $query->fetch();

    if (!$ticket) {
        $ticket = null;
        $result = 'not_found';
    } elseif ($eventId !== null && $ticket['event_id'] != $eventId) {
        $result = 'wrong_event';
    } elseif ($ticket['status'] === 'used') {
        $result = 'already_used';
    } elseif ($ticket['status'] === 'cancelled') {
        $result = 'cancelled';
    } else {
        $result = 'valid';
        $update = $pdo->prepare("UPDATE tickets SET status = 'used', used_at = NOW(), used_by = ? WHERE id = ?");
        $update->execute([$staffId, $ticket['id']]);
    }

    $pdo->commit();
    return ['result' => $result, 'ticket' => $ticket];
}

// Hoeveel tickets zijn gescand (used) en hoeveel nog geldig (valid)?
function ticketCountsForEvent($eventId)
{
    $query = db()->prepare(
        'SELECT t.status, COUNT(*) AS total
         FROM tickets t
         JOIN reservations r ON r.id = t.reservation_id
         WHERE r.event_id = ?
         GROUP BY t.status'
    );
    $query->execute([$eventId]);

    $counts = ['valid' => 0, 'used' => 0, 'cancelled' => 0];
    foreach ($query->fetchAll() as $row) {
        $counts[$row['status']] = $row['total'];
    }
    return $counts;
}

// Ingetypte code netjes maken: "so ab12 cd34" wordt "SO-AB12-CD34".
function cleanTicketCode($code)
{
    $code = strtoupper(str_replace([' ', '-'], '', $code));

    if (strlen($code) === 10 && substr($code, 0, 2) === 'SO') {
        return 'SO-' . substr($code, 2, 4) . '-' . substr($code, 6, 4);
    }
    return $code;
}

function ticketBadge($status)
{
    if ($status === 'valid') {
        return badge('Geldig', 'success');
    }
    if ($status === 'used') {
        return badge('Gebruikt', 'muted');
    }
    return badge('Geannuleerd', 'danger');
}
