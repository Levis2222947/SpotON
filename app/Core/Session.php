<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $isHttps = ($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off';

        session_name('spoton_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,   // niet leesbaar met JavaScript
            'samesite' => 'Lax',  // extra bescherming tegen CSRF
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();
    }

    /** Nieuw sessie-ID na in- of uitloggen (voorkomt session fixation). */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** Waarde ophalen en meteen verwijderen. */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
