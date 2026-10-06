<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([mb_strtolower($email)]);
        return $stmt->fetch() ?: null;
    }

    public static function emailExists(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }

    /** Nieuwe accounts zijn altijd bezoekers; medewerkers worden door de beheerder aangemaakt. */
    public static function createVisitor(string $name, string $email, string $password): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $name,
            mb_strtolower($email),
            password_hash($password, PASSWORD_DEFAULT),
            Auth::ROLE_VISITOR,
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    /** Geeft de gebruiker terug als e-mail en wachtwoord kloppen, anders null. */
    public static function verifyCredentials(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $stmt = Database::connection()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }
        return $user;
    }
}
