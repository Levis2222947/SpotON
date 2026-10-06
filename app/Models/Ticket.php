<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDOException;
use Throwable;

final class Ticket
{
    public const STATUS_VALID     = 'valid';
    public const STATUS_USED      = 'used';
    public const STATUS_CANCELLED = 'cancelled';

    /** Statuslabel met kleur, zodat status nooit alleen met kleur wordt aangegeven. */
    public const STATUS_BADGES = [
        self::STATUS_VALID     => ['label' => 'Geldig', 'variant' => 'success'],
        self::STATUS_USED      => ['label' => 'Gebruikt', 'variant' => 'muted'],
        self::STATUS_CANCELLED => ['label' => 'Geannuleerd', 'variant' => 'danger'],
    ];

    /** Zonder tekens die op elkaar lijken (0/O, 1/I), zodat codes makkelijk over te typen zijn. */
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /** Maakt $quantity tickets met een unieke code. Moet binnen een transactie aangeroepen worden. */
    public static function createForReservation(int $reservationId, int $quantity): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO tickets (reservation_id, code) VALUES (?, ?)');

        for ($i = 0; $i < $quantity; $i++) {
            for ($attempt = 1; ; $attempt++) {
                try {
                    $stmt->execute([$reservationId, self::generateCode()]);
                    break;
                } catch (PDOException $e) {
                    // 23000 = dubbele code (UNIQUE KEY). Heel zeldzaam: probeer een nieuwe code.
                    if ($e->getCode() !== '23000' || $attempt >= 5) {
                        throw $e;
                    }
                }
            }
        }
    }

    public static function forReservation(int $reservationId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM tickets WHERE reservation_id = ? ORDER BY id');
        $stmt->execute([$reservationId]);
        return $stmt->fetchAll();
    }

    // Mogelijke uitkomsten van het scannen van een ticketcode
    public const SCAN_VALID        = 'valid';
    public const SCAN_ALREADY_USED = 'already_used';
    public const SCAN_CANCELLED    = 'cancelled';
    public const SCAN_WRONG_EVENT  = 'wrong_event';
    public const SCAN_NOT_FOUND    = 'not_found';

    /**
     * Registreert een ticket als gebruikt. Het ticket wordt vergrendeld (FOR UPDATE) en
     * alleen bijgewerkt als het nog 'valid' is, dus twee scanners tegelijk kunnen
     * hetzelfde ticket nooit allebei goedkeuren.
     *
     * @return array{result: string, ticket: ?array}
     */
    public static function redeem(string $code, int $staffId, ?int $eventId): array
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'SELECT t.*, r.event_id, r.quantity, e.title AS event_title, e.starts_at, u.name AS user_name
                 FROM tickets t
                 JOIN reservations r ON r.id = t.reservation_id
                 JOIN events e ON e.id = r.event_id
                 JOIN users u ON u.id = r.user_id
                 WHERE t.code = ?
                 FOR UPDATE'
            );
            $stmt->execute([self::normalizeCode($code)]);
            $ticket = $stmt->fetch() ?: null;

            $result = match (true) {
                $ticket === null                                          => self::SCAN_NOT_FOUND,
                $eventId !== null && (int) $ticket['event_id'] !== $eventId => self::SCAN_WRONG_EVENT,
                $ticket['status'] === self::STATUS_USED                   => self::SCAN_ALREADY_USED,
                $ticket['status'] === self::STATUS_CANCELLED              => self::SCAN_CANCELLED,
                default                                                   => self::SCAN_VALID,
            };

            if ($result === self::SCAN_VALID) {
                $update = $pdo->prepare(
                    'UPDATE tickets SET status = ?, used_at = NOW(), used_by = ? WHERE id = ? AND status = ?'
                );
                $update->execute([self::STATUS_USED, $staffId, $ticket['id'], self::STATUS_VALID]);
                if ($update->rowCount() !== 1) {
                    $result = self::SCAN_ALREADY_USED;
                }
            }

            $pdo->commit();
            return ['result' => $result, 'ticket' => $ticket];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Aantal gescande en nog geldige tickets voor een evenement. */
    public static function countsForEvent(int $eventId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT t.status, COUNT(*) AS total FROM tickets t
             JOIN reservations r ON r.id = t.reservation_id
             WHERE r.event_id = ? GROUP BY t.status'
        );
        $stmt->execute([$eventId]);
        $counts = [self::STATUS_VALID => 0, self::STATUS_USED => 0, self::STATUS_CANCELLED => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    /**
     * Maakt ingetypte codes vergelijkbaar: "so ab12 cd34", "soab12cd34" en "SO-AB12-CD34"
     * worden allemaal "SO-AB12-CD34".
     */
    public static function normalizeCode(string $code): string
    {
        $chars = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code) ?? '');
        if (strlen($chars) === 10 && str_starts_with($chars, 'SO')) {
            return 'SO-' . substr($chars, 2, 4) . '-' . substr($chars, 6, 4);
        }
        return $chars;
    }

    private static function generateCode(): string
    {
        $chars = '';
        for ($i = 0; $i < 8; $i++) {
            $chars .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
        }
        return 'SO-' . substr($chars, 0, 4) . '-' . substr($chars, 4, 4);
    }
}
