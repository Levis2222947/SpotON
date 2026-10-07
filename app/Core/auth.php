<?php

/*
 * Inloggen en rollen.
 * Er zijn twee rollen: 'visitor' (bezoeker) en 'staff' (ticketmedewerker).
 * De ingelogde gebruiker staat in de sessie: $_SESSION['user'].
 */

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function currentUserId(): int
{
    return (int) ($_SESSION['user']['id'] ?? 0);
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isStaff(): bool
{
    return (currentUser()['role'] ?? '') === 'staff';
}

function isVisitor(): bool
{
    return (currentUser()['role'] ?? '') === 'visitor';
}

function loginUser(array $user): void
{
    // Nieuw sessie-ID na inloggen, zodat niemand een oud sessie-ID kan misbruiken.
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
}

function logoutUser(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

/** Niet ingelogd? Dan naar het inlogscherm. */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('info', 'Log eerst in om verder te gaan.');
        redirect('login');
    }
}

/** Alleen medewerkers mogen verder. */
function requireStaff(): void
{
    requireLogin();
    if (!isStaff()) {
        showError(403, 'Deze pagina is alleen voor medewerkers.');
    }
}

/** Alleen bezoekers mogen verder (medewerkers reserveren geen tickets). */
function requireVisitor(): void
{
    requireLogin();
    if (!isVisitor()) {
        showError(403, 'Alleen bezoekers kunnen tickets reserveren en reserveringen bekijken.');
    }
}
