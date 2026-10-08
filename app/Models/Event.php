<?php

// Model: evenementen (tabel events)
// Status: draft (concept), published (gepubliceerd) of cancelled (geannuleerd)

const EVENT_STATUS_LABELS = [
    'draft'     => 'Concept',
    'published' => 'Gepubliceerd',
    'cancelled' => 'Geannuleerd',
];

// Begin van mijn queries. Ik haal ook de categorienaam op en tel de verkochte tickets (sold).
// Alleen 'confirmed' reserveringen tellen mee, dus na annuleren is de plek weer vrij.
const EVENT_SELECT = "
    SELECT e.*, c.name AS category_name,
           (SELECT COALESCE(SUM(r.quantity), 0) FROM reservations r
            WHERE r.event_id = e.id AND r.status = 'confirmed') AS sold
    FROM events e
    JOIN categories c ON c.id = e.category_id";

// Gepubliceerde evenementen die nog moeten komen, eventueel gefilterd op datum en categorie.
function searchEvents($date, $categoryId)
{
    $sql = EVENT_SELECT . " WHERE e.status = 'published' AND e.starts_at > NOW()";
    $params = [];

    if ($date !== '') {
        $sql .= ' AND DATE(e.starts_at) = ?';
        $params[] = $date;
    }
    if ($categoryId !== null) {
        $sql .= ' AND e.category_id = ?';
        $params[] = $categoryId;
    }

    $query = db()->prepare($sql . ' ORDER BY e.starts_at');
    $query->execute($params);
    return $query->fetchAll();
}

function findEvent($id)
{
    $query = db()->prepare(EVENT_SELECT . ' WHERE e.id = ?');
    $query->execute([$id]);
    $event = $query->fetch();

    if (!$event) {
        return null;
    }
    return $event;
}

// Evenement dat een bezoeker mag zien (geen concept).
function findVisibleEvent($id)
{
    $event = findEvent($id);

    if ($event === null || $event['status'] === 'draft') {
        return null;
    }
    return $event;
}

function getAllEvents()
{
    return db()->query(EVENT_SELECT . ' ORDER BY e.starts_at DESC')->fetchAll();
}

// Lijstje voor de keuzelijsten bij de medewerkers.
function getEventOptions()
{
    return db()->query('SELECT id, title, starts_at FROM events ORDER BY starts_at DESC')->fetchAll();
}

function createEvent($data)
{
    $query = db()->prepare(
        'INSERT INTO events (title, description, program, location, category_id, starts_at,
                             capacity, sale_starts_at, sale_ends_at, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $query->execute(array_values($data));

    return db()->lastInsertId();
}

function updateEvent($id, $data)
{
    $query = db()->prepare(
        'UPDATE events
         SET title = ?, description = ?, program = ?, location = ?, category_id = ?, starts_at = ?,
             capacity = ?, sale_starts_at = ?, sale_ends_at = ?, status = ?
         WHERE id = ?'
    );
    $values = array_values($data);
    $values[] = $id;
    $query->execute($values);
}

function eventHasReservations($id)
{
    $query = db()->prepare('SELECT id FROM reservations WHERE event_id = ?');
    $query->execute([$id]);
    return $query->fetch() !== false;
}

function deleteEvent($id)
{
    $query = db()->prepare('DELETE FROM events WHERE id = ?');
    $query->execute([$id]);
}

// Aantal vrije plaatsen.
function remainingSeats($event)
{
    $remaining = $event['capacity'] - $event['sold'];

    if ($remaining < 0) {
        return 0;
    }
    return $remaining;
}

// Programma staat per regel in de database. Ik maak er een lijstje van zonder lege regels.
function programLines($event)
{
    $lines = [];

    foreach (explode("\n", (string) $event['program']) as $line) {
        if (trim($line) !== '') {
            $lines[] = trim($line);
        }
    }
    return $lines;
}

function eventStatusLabel($status)
{
    return EVENT_STATUS_LABELS[$status];
}

// Kun je reserveren? Geeft terug: open (true/false), label (tekst) en color (kleur).
function saleStatus($event)
{
    $now = time();
    $remaining = remainingSeats($event);

    if ($event['status'] === 'cancelled') {
        return ['open' => false, 'label' => 'Geannuleerd', 'color' => 'danger'];
    }
    if ($event['status'] === 'draft') {
        return ['open' => false, 'label' => 'Concept', 'color' => 'muted'];
    }
    if (strtotime($event['starts_at']) <= $now) {
        return ['open' => false, 'label' => 'Afgelopen', 'color' => 'muted'];
    }
    if ($remaining == 0) {
        return ['open' => false, 'label' => 'Uitverkocht', 'color' => 'danger'];
    }
    if (strtotime($event['sale_starts_at']) > $now) {
        return ['open' => false, 'label' => 'Verkoop start op ' . formatDate($event['sale_starts_at']), 'color' => 'info'];
    }
    if (strtotime($event['sale_ends_at']) < $now) {
        return ['open' => false, 'label' => 'Verkoop gesloten', 'color' => 'muted'];
    }
    if ($remaining <= 10) {
        return ['open' => true, 'label' => 'Bijna uitverkocht', 'color' => 'warning'];
    }
    return ['open' => true, 'label' => 'Tickets beschikbaar', 'color' => 'success'];
}
