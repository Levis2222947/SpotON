<?php

/*
 * Model: tickets (tabel tickets).
 * Elk ticket heeft een unieke code, bijv. SO-AB12-CD34.
 * Status: 'valid' (geldig), 'used' (gebruikt) of 'cancelled' (geannuleerd).
 */

const TICKET_STATUS_LABELS = [
    'valid'     => 'Geldig',
    'used'      => 'Gebruikt',
    'cancelled' => 'Geannuleerd',
];

/** Maakt een aantal tickets met elk een eigen, unieke code. */
function createTickets(int $reservationId, int $quantity): void
{
    $query = db()->prepare('INSERT INTO tickets (reservation_id, code) VALUES (?, ?)');

    for ($i = 0; $i < $quantity; $i++) {
        // Nieuwe code maken totdat we er een hebben die nog niet bestaat
        do {
            $code = generateTicketCode();
        } while (ticketCodeExists($code));

        $query->execute([$reservationId, $code]);
    }
}

/** Willekeurige code zoals SO-AB12-CD34. Zonder 0/O en 1/I, zodat je ze niet door elkaar haalt. */
function generateTicketCode(): string
{
    $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $letters[random_int(0, strlen($letters) - 1)];
    }
    return 'SO-' . substr($code, 0, 4) . '-' . substr($code, 4, 4);
}

function ticketCodeExists(string $code): bool
{
    $query = db()->prepare('SELECT id FROM tickets WHERE code = ?');
    $query->execute([$code]);
    return $query->fetch() !== false;
}

function getTicketsForReservation(int $reservationId): array
{
    $query = db()->prepare('SELECT * FROM tickets WHERE reservation_id = ? ORDER BY id');
    $query->execute([$reservationId]);
    return $query->fetchAll();
}

/**
 * Een ticket scannen aan de deur.
 *
 * Een ticket mag maar één keer gebruikt worden. Daarom zetten we het ticket op slot
 * (FOR UPDATE) terwijl we het controleren. Scannen twee medewerkers dezelfde code
 * tegelijk, dan krijgt alleen de eerste "geldig".
 *
 * Uitkomst ('result'):
 *   valid        = geldig, persoon mag naar binnen (ticket is nu gebruikt)
 *   already_used = al eerder gescand
 *   cancelled    = reservering is geannuleerd
 *   wrong_event  = ticket hoort bij een ander evenement
 *   not_found    = code bestaat niet
 */
function useTicket(string $code, int $staffId, ?int $eventId): array
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
    $ticket = $query->fetch() ?: null;

    if ($ticket === null) {
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

/** Hoeveel tickets van een evenement zijn al gescand en hoeveel worden nog verwacht? */
function ticketCountsForEvent(int $eventId): array
{
    $query = db()->prepare(
        'SELECT t.status, COUNT(*) AS total FROM tickets t
         JOIN reservations r ON r.id = t.reservation_id
         WHERE r.event_id = ? GROUP BY t.status'
    );
    $query->execute([$eventId]);

    $counts = ['valid' => 0, 'used' => 0, 'cancelled' => 0];
    foreach ($query->fetchAll() as $row) {
        $counts[$row['status']] = (int) $row['total'];
    }
    return $counts;
}

/**
 * Maakt een ingetypte code netjes: "so ab12 cd34" en "soab12cd34" worden allebei "SO-AB12-CD34".
 */
function cleanTicketCode(string $code): string
{
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));

    if (strlen($code) === 10 && str_starts_with($code, 'SO')) {
        return 'SO-' . substr($code, 2, 4) . '-' . substr($code, 6, 4);
    }
    return $code;
}

function ticketBadge(string $status): string
{
    $colors = ['valid' => 'success', 'used' => 'muted', 'cancelled' => 'danger'];
    return badge(TICKET_STATUS_LABELS[$status] ?? $status, $colors[$status] ?? 'muted');
}
