<?php

declare(strict_types=1);

namespace App\Core;

/** Wie is er ingelogd en welke rol heeft hij/zij? */
final class Auth
{
    public const ROLE_VISITOR = 'visitor';
    public const ROLE_STAFF   = 'staff';

    /** @return array{id: int, name: string, email: string, role: string}|null */
    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function hasRole(string $role): bool
    {
        return (self::user()['role'] ?? null) === $role;
    }

    public static function isStaff(): bool
    {
        return self::hasRole(self::ROLE_STAFF);
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user', [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ]);
    }

    public static function logout(): void
    {
        Session::forget('user');
        Session::regenerate();
    }

    /** Stuurt niet-ingelogde gebruikers naar het inlogscherm. */
    public static function requireLogin(): void
    {
        if (self::check()) {
            return;
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            Session::set('intended_url', $_SERVER['REQUEST_URI'] ?? url());
        }
        Flash::info('Log eerst in om verder te gaan.');
        redirect('login');
    }

    /** Alleen gebruikers met deze rol mogen verder; anders 403. */
    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if (!self::hasRole($role)) {
            throw HttpException::forbidden();
        }
    }
}
