<?php

/*
 * Model: gebruikers (tabel users).
 */

function findUserByEmail(string $email): ?array
{
    $query = db()->prepare('SELECT * FROM users WHERE email = ?');
    $query->execute([strtolower($email)]);
    return $query->fetch() ?: null;
}

/** Nieuwe accounts zijn altijd bezoekers. Medewerkers worden in de database aangemaakt. */
function createVisitor(string $name, string $email, string $password): int
{
    $query = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $query->execute([
        $name,
        strtolower($email),
        password_hash($password, PASSWORD_DEFAULT), // het wachtwoord wordt nooit leesbaar opgeslagen
        'visitor',
    ]);
    return (int) db()->lastInsertId();
}

/** Kloppen e-mail en wachtwoord? Dan krijg je de gebruiker terug, anders null. */
function checkLogin(string $email, string $password): ?array
{
    $user = findUserByEmail($email);

    if ($user === null || !password_verify($password, $user['password_hash'])) {
        return null;
    }
    return $user;
}
