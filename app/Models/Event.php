<?php

/*
 * Model: evenementen (tabel events).
 *
 * Een evenement heeft een status:
 *   draft     = concept (nog niet zichtbaar voor bezoekers)
 *   published = gepubliceerd
 *   cancelled = geannuleerd
 */

const EVENT_STATUS_LABELS = [
    'draft'     => 'Concept',
    'published' => 'Gepubliceerd',
    'cancelled' => 'Geannuleerd',
];

/*
 * Basisquery voor evenementen. Naast de gegevens van het evenement halen we ook op:
 * - category_name: de naam van de categorie
 * - sold: hoeveel tickets er verkocht zijn. Alleen 'confirmed' reserveringen tellen mee,
 *   dus geannuleerde tickets komen vanzelf weer vrij.
 */
const EVENT_SELECT = "
    SELECT e.*, c.name AS category_name,
           (SELECT COALESCE(SUM(r.quantity), 0) FROM reservations r
            WHERE r.event_id = e.id AND r.status = 'confirmed') AS sold
    FROM events e
    JOIN categories c ON c.id = e.category_id";

/** Gepubliceerde evenementen in de toekomst. Optioneel filteren op datum en/of categorie. */
function searchEvents(string $date, ?int $categoryId): array
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

/** Eén evenement, ook concepten (voor medewerkers). */
function findEvent(int $id): ?array
{
    $query = db()->prepare(EVENT_SELECT . ' WHERE e.id = ?');
    $query->execute([$id]);
    return $query->fetch() ?: null;
}

/** Eén evenement dat bezoekers mogen zien (dus geen concept). */
function findVisibleEvent(int $id): ?array
{
    $event = findEvent($id);
    if ($event === null || $event['status'] === 'draft') {
        return null;
    }
    return $event;
}

function getAllEvents(): array
{
    return db()->query(EVENT_SELECT . ' ORDER BY e.starts_at DESC')->fetchAll();
}

/** Korte lijst (id, titel, datum) voor de keuzelijsten bij de medewerkers. */
function getEventOptions(): array
{
    return db()->query('SELECT id, title, starts_at FROM events ORDER BY starts_at DESC')->fetchAll();
}

function createEvent(array $data): int
{
    $query = db()->prepare(
        'INSERT INTO events (title, description, program, location, category_id, starts_at,
                             capacity, sale_starts_at, sale_ends_at, status)
         VALUES (:title, :description, :program, :location, :category_id, :starts_at,
                 :capacity, :sale_starts_at, :sale_ends_at, :status)'
    );
    $query->execute($data);
    return (int) db()->lastInsertId();
}

function updateEvent(int $id, array $data): void
{
    $data['id'] = $id;
    $query = db()->prepare(
        'UPDATE events SET title = :title, description = :description, program = :program,
                location = :location, category_id = :category_id, starts_at = :starts_at,
                capacity = :capacity, sale_starts_at = :sale_starts_at,
                sale_ends_at = :sale_ends_at, status = :status
         WHERE id = :id'
    );
    $query->execute($data);
}

function eventHasReservations(int $id): bool
{
    $query = db()->prepare('SELECT id FROM reservations WHERE event_id = ? LIMIT 1');
    $query->execute([$id]);
    return $query->fetch() !== false;
}

function deleteEvent(int $id): void
{
    $query = db()->prepare('DELETE FROM events WHERE id = ?');
    $query->execute([$id]);
}

/** Aantal plaatsen dat nog vrij is. */
function remainingSeats(array $event): int
{
    return max(0, $event['capacity'] - $event['sold']);
}

/** Het programma staat als losse regels in de database. Dit geeft een lijst van de regels. */
function programLines(array $event): array
{
    $lines = explode("\n", (string) $event['program']);
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, fn ($line) => $line !== ''));
}

function eventStatusLabel(string $status): string
{
    return EVENT_STATUS_LABELS[$status] ?? $status;
}

/**
 * Kan er gereserveerd worden? Geeft terug:
 *   open  = true als reserveren mag
 *   label = tekst voor de bezoeker
 *   color = kleur van het label
 */
function saleStatus(array $event): array
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
    if ($remaining === 0) {
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
