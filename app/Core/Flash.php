<?php

declare(strict_types=1);

namespace App\Core;

/** Meldingen die één keer getoond worden na een redirect (succes, fout, info). */
final class Flash
{
    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('error', $message);
    }

    public static function info(string $message): void
    {
        self::add('info', $message);
    }

    /** @return list<array{type: string, message: string}> */
    public static function pullAll(): array
    {
        return Session::pull('_flash', []);
    }

    private static function add(string $type, string $message): void
    {
        $messages = Session::get('_flash', []);
        $messages[] = ['type' => $type, 'message' => $message];
        Session::set('_flash', $messages);
    }
}
