<?php

// Verbinding met de database.
// "static" onthoudt $pdo, zodat ik maar één keer verbinding maak.
function db()
{
    static $pdo = null;

    if ($pdo === null) {
        $db = CONFIG['db'];
        $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['name'] . ';charset=utf8mb4', $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);      // bij een fout stoppen
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // rijen als ['kolom' => waarde]
        $pdo->exec("SET time_zone = '" . date('P') . "'");                   // zelfde tijd als PHP
    }

    return $pdo;
}
