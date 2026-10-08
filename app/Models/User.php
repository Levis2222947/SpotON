<?php

// Model: gebruikers (tabel users)

function findUserByEmail($email)
{
    $query = db()->prepare('SELECT * FROM users WHERE email = ?');
    $query->execute([strtolower($email)]);
    $user = $query->fetch();

    if (!$user) {
        return null;
    }
    return $user;
}

// Nieuw account. Wie zich registreert is altijd een bezoeker.
function createVisitor($name, $email, $password)
{
    $hash = password_hash($password, PASSWORD_DEFAULT); // wachtwoord onleesbaar opslaan

    $query = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $query->execute([$name, strtolower($email), $hash, 'visitor']);

    return db()->lastInsertId();
}

// Kloppen e-mail en wachtwoord? Dan krijg je de gebruiker terug, anders null.
function checkLogin($email, $password)
{
    $user = findUserByEmail($email);

    if ($user === null || !password_verify($password, $user['password_hash'])) {
        return null;
    }
    return $user;
}
