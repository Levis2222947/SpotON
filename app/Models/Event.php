<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Event
{
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT     => 'Concept',
        self::STATUS_PUBLISHED => 'Gepubliceerd',
        self::STATUS_CANCELLED => 'Geannuleerd',
    ];

    /**
     * Basisquery: evenement + categorienaam + aantal verkochte (niet-geannuleerde) tickets.
     * "sold" wordt altijd live berekend, zodat geannuleerde tickets direct weer beschikbaar zijn.
     */
    private const SELECT = "
        SELECT e.*, c.name AS category_name,
               COALESCE((SELECT SUM(r.quantity) FROM reservations r
                         WHERE r.event_id = e.id AND r.status = 'confirmed'), 0) AS sold
        FROM events e
        JOIN categories c ON c.id = e.category_id";

    /** Gepubliceerde, toekomstige evenementen, optioneel gefilterd op datum en categorie. */
    public static function searchPublished(?string $date, ?int $categoryId): array
    {
        $sql = self::SELECT . ' WHERE e.status = :status AND e.starts_at > NOW()';
        $params = ['status' => self::STATUS_PUBLISHED];

        if ($date !== null) {
            $sql .= ' AND DATE(e.starts_at) = :date';
            $params['date'] = $date;
        }
        if ($categoryId !== null) {
            $sql .= ' AND e.category_id = :category';
            $params['category'] = $categoryId;
        }

        $stmt = Database::connection()->prepare($sql . ' ORDER BY e.starts_at');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findPublished(int $id): ?array
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE e.id = ? AND e.status <> ?');
        $stmt->execute([$id, self::STATUS_DRAFT]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE e.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::connection()->query(self::SELECT . ' ORDER BY e.starts_at DESC')->fetchAll();
    }

    /** Korte lijst (id + titel) voor filters in het medewerkersgedeelte. */
    public static function options(): array
    {
        return Database::connection()
            ->query('SELECT id, title, starts_at FROM events ORDER BY starts_at DESC')
            ->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO events (title, description, program, location, category_id, starts_at,
                                 capacity, sale_starts_at, sale_ends_at, status)
             VALUES (:title, :description, :program, :location, :category_id, :starts_at,
                     :capacity, :sale_starts_at, :sale_ends_at, :status)'
        );
        $stmt->execute($data);
        return (int) Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE events SET title = :title, description = :description, program = :program,
                    location = :location, category_id = :category_id, starts_at = :starts_at,
                    capacity = :capacity, sale_starts_at = :sale_starts_at,
                    sale_ends_at = :sale_ends_at, status = :status
             WHERE id = :id'
        );
        $stmt->execute($data + ['id' => $id]);
    }

    public static function hasReservations(int $id): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM reservations WHERE event_id = ? LIMIT 1');
        $stmt->execute([$id]);
        return (bool) $stmt->fetchColumn();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
    }

    public static function remaining(array $event): int
    {
        return max(0, (int) $event['capacity'] - (int) $event['sold']);
    }

    /** Programma staat als regels in de database; geeft een lijst van niet-lege regels. */
    public static function programLines(array $event): array
    {
        $lines = preg_split('/\R/', (string) ($event['program'] ?? '')) ?: [];
        return array_values(array_filter(array_map('trim', $lines), static fn ($l) => $l !== ''));
    }

    /**
     * Bepaalt of er gereserveerd kan worden en welke status (tekst + kleur) getoond wordt.
     *
     * @return array{open: bool, label: string, badge: string}
     */
    public static function saleState(array $event): array
    {
        $now = time();
        $remaining = self::remaining($event);

        return match (true) {
            $event['status'] === self::STATUS_CANCELLED   => ['open' => false, 'label' => 'Geannuleerd', 'badge' => 'danger'],
            $event['status'] === self::STATUS_DRAFT       => ['open' => false, 'label' => 'Concept', 'badge' => 'muted'],
            strtotime($event['starts_at']) <= $now        => ['open' => false, 'label' => 'Afgelopen', 'badge' => 'muted'],
            $remaining === 0                              => ['open' => false, 'label' => 'Uitverkocht', 'badge' => 'danger'],
            strtotime($event['sale_starts_at']) > $now    => ['open' => false, 'label' => 'Verkoop vanaf ' . date('d-m', strtotime($event['sale_starts_at'])), 'badge' => 'info'],
            strtotime($event['sale_ends_at']) < $now      => ['open' => false, 'label' => 'Verkoop gesloten', 'badge' => 'muted'],
            $remaining <= max(5, (int) ceil($event['capacity'] * 0.1)) => ['open' => true, 'label' => 'Bijna uitverkocht', 'badge' => 'warning'],
            default                                       => ['open' => true, 'label' => 'Tickets beschikbaar', 'badge' => 'success'],
        };
    }
}
