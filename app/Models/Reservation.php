<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BusinessRuleException;
use App\Core\Database;
use Throwable;

final class Reservation
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_BADGES = [
        self::STATUS_CONFIRMED => ['label' => 'Bevestigd', 'variant' => 'success'],
        self::STATUS_CANCELLED => ['label' => 'Geannuleerd', 'variant' => 'danger'],
    ];

    private const SELECT = "
        SELECT r.*, e.title AS event_title, e.starts_at, e.location, e.status AS event_status,
               u.name AS user_name, u.email AS user_email,
               (SELECT COUNT(*) FROM tickets t WHERE t.reservation_id = r.id AND t.status = 'used') AS used_count
        FROM reservations r
        JOIN events e ON e.id = r.event_id
        JOIN users u ON u.id = r.user_id";

    /**
     * Legt een reservering met tickets vast. Nooit meer tickets dan de capaciteit:
     * de evenementrij wordt vergrendeld (SELECT ... FOR UPDATE), zodat twee bezoekers
     * die tegelijk reserveren elkaar niet kunnen "inhalen".
     *
     * @throws BusinessRuleException als reserveren niet (meer) mogelijk is
     */
    public static function create(int $userId, int $eventId, int $quantity): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ? FOR UPDATE');
            $stmt->execute([$eventId]);
            $event = $stmt->fetch();
            if (!$event || $event['status'] === Event::STATUS_DRAFT) {
                throw new BusinessRuleException('Dit evenement bestaat niet.');
            }

            $event['sold'] = self::soldTickets($eventId);
            $state = Event::saleState($event);
            if (!$state['open']) {
                throw new BusinessRuleException('Reserveren is niet mogelijk: ' . mb_strtolower($state['label']) . '.');
            }

            $remaining = Event::remaining($event);
            if ($quantity > $remaining) {
                throw new BusinessRuleException(
                    "Er zijn nog maar {$remaining} " . ($remaining === 1 ? 'plaats' : 'plaatsen')
                    . " beschikbaar. Kies een kleiner aantal tickets."
                );
            }

            $pdo->prepare('INSERT INTO reservations (user_id, event_id, quantity, status) VALUES (?, ?, ?, ?)')
                ->execute([$userId, $eventId, $quantity, self::STATUS_CONFIRMED]);
            $reservationId = (int) $pdo->lastInsertId();

            Ticket::createForReservation($reservationId, $quantity);

            $pdo->commit();
            return $reservationId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Annuleert een reservering; de plaatsen komen direct weer vrij omdat alleen
     * bevestigde reserveringen meetellen. Met $userId kan alleen de eigenaar annuleren.
     *
     * @throws BusinessRuleException
     */
    public static function cancel(int $reservationId, ?int $userId = null): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $sql = 'SELECT r.*, e.starts_at FROM reservations r JOIN events e ON e.id = r.event_id WHERE r.id = ?';
            $params = [$reservationId];
            if ($userId !== null) {
                $sql .= ' AND r.user_id = ?';
                $params[] = $userId;
            }
            $stmt = $pdo->prepare($sql . ' FOR UPDATE');
            $stmt->execute($params);
            $reservation = $stmt->fetch();

            if (!$reservation) {
                throw new BusinessRuleException('Deze reservering bestaat niet.');
            }
            if ($reservation['status'] === self::STATUS_CANCELLED) {
                throw new BusinessRuleException('Deze reservering is al geannuleerd.');
            }
            if (strtotime($reservation['starts_at']) <= time()) {
                throw new BusinessRuleException('Het evenement is al begonnen; annuleren is niet meer mogelijk.');
            }

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM tickets WHERE reservation_id = ? AND status = ?');
            $stmt->execute([$reservationId, Ticket::STATUS_USED]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw new BusinessRuleException('Er is al een ticket van deze reservering gebruikt bij de ingang; annuleren is niet mogelijk.');
            }

            $pdo->prepare('UPDATE reservations SET status = ?, cancelled_at = NOW() WHERE id = ?')
                ->execute([self::STATUS_CANCELLED, $reservationId]);
            $pdo->prepare('UPDATE tickets SET status = ? WHERE reservation_id = ? AND status = ?')
                ->execute([Ticket::STATUS_CANCELLED, $reservationId, Ticket::STATUS_VALID]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function soldTickets(int $eventId): int
    {
        $stmt = Database::connection()->prepare(
            'SELECT COALESCE(SUM(quantity), 0) FROM reservations WHERE event_id = ? AND status = ?'
        );
        $stmt->execute([$eventId, self::STATUS_CONFIRMED]);
        return (int) $stmt->fetchColumn();
    }

    public static function forUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE r.user_id = ? ORDER BY e.starts_at DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Alleen de eigen reservering: een andere ID in de URL geeft null (en dus 404). */
    public static function findForUser(int $reservationId, int $userId): ?array
    {
        $stmt = Database::connection()->prepare(self::SELECT . ' WHERE r.id = ? AND r.user_id = ?');
        $stmt->execute([$reservationId, $userId]);
        return $stmt->fetch() ?: null;
    }

    /** Voor medewerkers: filteren op status en/of evenement. */
    public static function search(?string $status, ?int $eventId): array
    {
        $sql = self::SELECT . ' WHERE 1 = 1';
        $params = [];
        if ($status !== null) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }
        if ($eventId !== null) {
            $sql .= ' AND r.event_id = ?';
            $params[] = $eventId;
        }
        $stmt = Database::connection()->prepare($sql . ' ORDER BY r.created_at DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Mag de bezoeker deze reservering nog zelf annuleren? (voor het tonen van de knop) */
    public static function isCancellable(array $reservation): bool
    {
        return $reservation['status'] === self::STATUS_CONFIRMED
            && (int) $reservation['used_count'] === 0
            && strtotime($reservation['starts_at']) > time();
    }
}
