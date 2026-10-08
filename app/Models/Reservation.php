<?php

// Model: reserveringen (tabel reservations)
// Status: confirmed (bevestigd) of cancelled (geannuleerd)

const RESERVATION_STATUS_LABELS = [
    'confirmed' => 'Bevestigd',
    'cancelled' => 'Geannuleerd',
];

// Begin van mijn queries: reservering + evenement + bezoeker + aantal gescande tickets (used_count).
const RESERVATION_SELECT = "
    SELECT r.*, e.title AS event_title, e.starts_at, e.location,
           u.name AS user_name, u.email AS user_email,
           (SELECT COUNT(*) FROM tickets t WHERE t.reservation_id = r.id AND t.status = 'used') AS used_count
    FROM reservations r
    JOIN events e ON e.id = r.event_id
    JOIN users u ON u.id = r.user_id";

// Reservering maken. Er mogen nooit meer tickets verkocht worden dan er plaatsen zijn.
// Daarom zet ik het evenement op slot met een transactie + FOR UPDATE.
// Reserveren twee mensen tegelijk, dan moet de tweede wachten tot de eerste klaar is.
// Geeft terug: ['error' => '', 'id' => 12] of ['error' => 'foutmelding', 'id' => 0].
function createReservation($userId, $eventId, $quantity)
{
    $pdo = db();
    $pdo->beginTransaction();

    // Evenement op slot zetten
    $query = $pdo->prepare('SELECT * FROM events WHERE id = ? FOR UPDATE');
    $query->execute([$eventId]);
    $event = $query->fetch();

    if (!$event) {
        $pdo->rollBack();
        return ['error' => 'Dit evenement bestaat niet.', 'id' => 0];
    }

    // Opnieuw tellen hoeveel plaatsen er nog vrij zijn
    $event['sold'] = soldTickets($eventId);
    $status = saleStatus($event);

    if (!$status['open']) {
        $pdo->rollBack();
        return ['error' => 'Reserveren is niet mogelijk: ' . strtolower($status['label']) . '.', 'id' => 0];
    }
    if ($quantity > remainingSeats($event)) {
        $pdo->rollBack();
        return ['error' => 'Er zijn nog maar ' . remainingSeats($event) . ' plaatsen beschikbaar. Kies een kleiner aantal.', 'id' => 0];
    }

    // Reservering en tickets opslaan
    $query = $pdo->prepare("INSERT INTO reservations (user_id, event_id, quantity, status) VALUES (?, ?, ?, 'confirmed')");
    $query->execute([$userId, $eventId, $quantity]);
    $reservationId = $pdo->lastInsertId();

    createTickets($reservationId, $quantity);

    $pdo->commit(); // opslaan en slot eraf
    return ['error' => '', 'id' => $reservationId];
}

// Reservering annuleren. Met $userId kan alleen de eigenaar annuleren.
// Geeft een foutmelding terug, of '' als het gelukt is.
function cancelReservation($reservationId, $userId = null)
{
    if ($userId === null) {
        $reservation = findReservation($reservationId);
    } else {
        $reservation = findReservationForUser($reservationId, $userId);
    }

    if ($reservation === null) {
        return 'Deze reservering bestaat niet.';
    }
    if (!canCancelReservation($reservation)) {
        return 'Deze reservering kan niet meer geannuleerd worden.';
    }

    $query = db()->prepare("UPDATE reservations SET status = 'cancelled', cancelled_at = NOW() WHERE id = ?");
    $query->execute([$reservationId]);

    $query = db()->prepare("UPDATE tickets SET status = 'cancelled' WHERE reservation_id = ?");
    $query->execute([$reservationId]);

    return '';
}

// Aantal verkochte tickets (alleen bevestigde reserveringen).
function soldTickets($eventId)
{
    $query = db()->prepare("SELECT COALESCE(SUM(quantity), 0) FROM reservations WHERE event_id = ? AND status = 'confirmed'");
    $query->execute([$eventId]);
    return $query->fetchColumn();
}

function findReservation($reservationId)
{
    $query = db()->prepare(RESERVATION_SELECT . ' WHERE r.id = ?');
    $query->execute([$reservationId]);
    $reservation = $query->fetch();

    if (!$reservation) {
        return null;
    }
    return $reservation;
}

// Alleen je eigen reservering. Andermans nummer in de URL geeft null (en dus 404).
function findReservationForUser($reservationId, $userId)
{
    $query = db()->prepare(RESERVATION_SELECT . ' WHERE r.id = ? AND r.user_id = ?');
    $query->execute([$reservationId, $userId]);
    $reservation = $query->fetch();

    if (!$reservation) {
        return null;
    }
    return $reservation;
}

function getReservationsForUser($userId)
{
    $query = db()->prepare(RESERVATION_SELECT . ' WHERE r.user_id = ? ORDER BY e.starts_at DESC');
    $query->execute([$userId]);
    return $query->fetchAll();
}

// Voor medewerkers: filteren op status en/of evenement. Leeg = niet filteren.
function searchReservations($status, $eventId)
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

// Mag hij nog geannuleerd worden? Niet als hij al geannuleerd is, al gescand is of al begonnen is.
function canCancelReservation($reservation)
{
    return $reservation['status'] === 'confirmed'
        && $reservation['used_count'] == 0
        && strtotime($reservation['starts_at']) > time();
}

function reservationBadge($status)
{
    if ($status === 'confirmed') {
        return badge('Bevestigd', 'success');
    }
    return badge('Geannuleerd', 'danger');
}
