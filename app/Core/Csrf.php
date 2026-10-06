<?php

declare(strict_types=1);

namespace App\Core;

/** Bescherming tegen Cross-Site Request Forgery: elk POST-formulier bevat een geheim token. */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    public static function isValid(mixed $submitted): bool
    {
        $token = Session::get('_csrf');
        return is_string($token) && is_string($submitted) && hash_equals($token, $submitted);
    }
}
