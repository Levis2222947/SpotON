<?php

/*
 * Model: reserveringen (tabel reservations).
 * Eén reservering = één of meer tickets voor één evenement.
 * Status: 'confirmed' (bevestigd) of 'cancelled' (geannuleerd).
 */

const RESERVATION_STATUS_LABELS = [
    'confirmed' => 'Bevestigd',
    'cancelled' => 'Geannuleerd',
];

/* Basisquery: reservering + evenement + bezoeker + hoeveel tickets er al gescand zijn. */
const RESERVATION_SELECT = "
    SELECT r.*, e.title AS event_title, e.starts_at, e.location,
           u.name AS user_name, u.email AS user_email,
           (SELECT COUNT(*) FROM tickets t WHERE t.reservation_id = r.id AND t.status = 'used') AS used_count
    FROM reservations r
    JOIN events e ON e.id = r.event_id
    JOIN users u ON u.id = r.user_id";

/**
 * Maakt een reservering met tickets.
 *
 * Belangrijk: er mogen nooit meer tickets verkocht worden dan er plaatsen zijn.
 * Daarom gebruiken we een transactie en "FOR UPDATE": het evenement wordt even op slot gezet.
 * Reserveren twee mensen precies tegelijk, dan moet de tweede wachten tot de eerste klaar is.
 * Zo kunnen ze niet allebei de laatste plaats krijgen.
 *
 * Geeft terug: ['error' => '', 'id' => 12] als het gelukt is,
 *              ['error' => 'foutmelding', 'id' => 0] als het niet gelukt is.
 */
function createReservation(int $userId, int $eventId, int $quantity): array
{
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Evenement op slot zetten
    $query = $pdo->prepare('SELECT * FROM events WHERE id = ? FOR UPDATE');
    $query->execute([$eventId]);
    $event = $query->fetch();

    if (!$event) {
        $pdo->rollBack();
        return ['error' => 'Dit evenement bestaat niet.', 'id' => 0];
    }

    // 2. Opnieuw tellen hoeveel plaatsen er nog vrij zijn
    $event['sold'] = soldTickets($eventId);
    $status = saleStatus($event);

    if (!$status['open']) {
        $pdo->rollBack();
        return ['error' => 'Reserveren is niet mogelijk: ' . strtolower($status['label']) . '.', 'id' => 0];
    }

    $remaining = remainingSeats($event);
    if ($quantity > $remaining) {
        $pdo->rollBack();
        return ['error' => "Er zijn nog maar {$remaining} plaatsen beschikbaar. Kies een kleiner aantal.", 'id' => 0];
    }

    // 3. Reservering en tickets opslaan
    $query = $pdo->prepare("INSERT INTO reservations (user_id, event_id, quantity, status) VALUES (?, ?, ?, 'confirmed')");
    $query->execute([$userId, $eventId, $quantity]);
    $reservationId = (int) $pdo->lastInsertId();

    createTickets($reservationId, $quantity);

    // 4. Alles in één keer opslaan en het slot eraf halen
    $pdo->commit();
    return ['error' => '', 'id' => $reservationId];
}

/**
 * Annuleert een reservering. De plaatsen komen meteen weer vrij,
 * omdat alleen bevestigde reserveringen meetellen.
 * Met $userId mag alleen de eigenaar zijn eigen reservering annuleren.
 *
 * Geeft een foutmelding terug, of een lege tekst ('') als het gelukt is.
 */
function cancelReservation(int $reservationId, ?int $userId = null): string
{
    $reservation = $userId === null
        ? findReservation($reservationId)
        : findReservationForUser($reservationId, $userId);

    if ($reservation === null) {
        return 'Deze reservering bestaat niet.';
    }
    if ($reservation['status'] === 'cancelled') {
        return 'Deze reservering is al geannuleerd.';
    }
    if (strtotime($reservation['starts_at']) <= time()) {
        return 'Het evenement is al begonnen. Annuleren kan niet meer.';
    }
    if ($reservation['used_count'] > 0) {
        return 'Er is al een ticket van deze reservering gebruikt bij de ingang. Annuleren kan niet meer.';
    }

    $query = db()->prepare("UPDATE reservations SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
    $query->execute([$reservationId]);

    $query = db()->prepare("UPDATE tickets SET status = 'cancelled' WHERE reservation_id = ? AND status = 'valid'");
    $query->execute([$reservationId]);

    return '';
}

/** Aantal verkochte tickets van een evenement (alleen bevestigde reserveringen). */
function soldTickets(int $eventId): int
{
    $query = db()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM reservations WHERE event_id = ? AND status = 'confirmed'");
    $query->execute([$eventId]);
    return (int) $query->fetchColumn();
}

function findReservation(int $reservationId): ?array
{
    $query = db()->prepare(RESERVATION_SELECT . ' WHERE r.id = ?');
    $query->execute([$reservationId]);
    return $query->fetch() ?: null;
}

/** Alleen je eigen reservering. Een ander nummer in de URL geeft null (en dus 404). */
function findReservationForUser(int $reservationId, int $userId): ?array
{
    $query = db()->prepare(RESERVATION_SELECT . ' WHERE r.id = ? AND r.user_id = ?');
    $query->execute([$reservationId, $userId]);
    return $query->fetch() ?: null;
}

function getReservationsForUser(int $userId): array
{
    $query = db()->prepare(RESERVATION_SELECT . ' WHERE r.user_id = ? ORDER BY e.starts_at DESC');
    $query->execute([$userId]);
    return $query->fetchAll();
}

/** Voor medewerkers: filteren op status en/of evenement. Leeg = niet filteren. */
function searchReservations(string $status, ?int $eventId): array
{
    $sql = RESERVATION_SELECT . ' WHERE 1 = 1';
    $params = [];

    if ($status !== '') {
        $sql .= ' AND r.status = ?';
        $params[] = $status;
    }
    if ($eventId !== null) {
        $sql .= ' AND r.event_id = ?';
        $params[] = $eventId;
    }

    $query = db()->prepare($sql . ' ORDER BY r.created_at DESC');
    $query->execute($params);
    return $query->fetchAll();
}

/** Mag deze reservering nog geannuleerd worden? (Bepaalt of we de knop laten zien.) */
function canCancelReservation(array $reservation): bool
{
    return $reservation['status'] === 'confirmed'
        && $reservation['used_count'] == 0
        && strtotime($reservation['starts_at']) > time();
}

function reservationBadge(string $status): string
{
    $color = $status === 'confirmed' ? 'success' : 'danger';
    return badge(RESERVATION_STATUS_LABELS[$status] ?? $status, $color);
}
