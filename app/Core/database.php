<?php

/**
 * Geeft de verbinding met de database terug.
 * "static" zorgt ervoor dat we maar één keer verbinding maken, ook als db() vaak wordt aangeroepen.
 *
 * Alle query's in de models gebruiken prepared statements (met ? in de SQL).
 * Zo kan een bezoeker nooit eigen SQL-code meesturen (SQL-injectie).
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $db = CONFIG['db'];
        $pdo = new PDO(
            "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4",
            $db['user'],
            $db['pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // fout = melding, niet stil doorgaan
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rijen als ['kolom' => waarde]
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        // PHP en MySQL dezelfde tijdzone laten gebruiken.
        $pdo->exec("SET time_zone = '" . date('P') . "'");
    }

    return $pdo;
}
