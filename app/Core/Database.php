<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/** Eén gedeelde PDO-verbinding. Alle query's gebruiken prepared statements. */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                Config::get('db.host'),
                Config::get('db.port', 3306),
                Config::get('db.name'),
                Config::get('db.charset', 'utf8mb4')
            );
            self::$pdo = new PDO($dsn, Config::get('db.user'), Config::get('db.pass'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            // PHP en MySQL dezelfde tijdzone laten gebruiken.
            self::$pdo->exec("SET time_zone = '" . date('P') . "'");
        }
        return self::$pdo;
    }
}
