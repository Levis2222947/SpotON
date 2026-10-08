<?php

// Inloggen en rollen. Rollen: 'visitor' (bezoeker) en 'staff' (medewerker).
// De ingelogde gebruiker staat in $_SESSION['user'].

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function currentUserId()
{
    return $_SESSION['user']['id'] ?? 0;
}

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function isStaff()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'staff';
}

function isVisitor()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'visitor';
}

function loginUser($user)
{
    session_regenerate_id(true); // nieuw sessie-id, zodat een oud id niet misbruikt kan worden

    $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
}

function logoutUser()
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

// Niet ingelogd? Naar het inlogscherm.
function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash('info', 'Log eerst in om verder te gaan.');
        redirect('login');
    }
}

// Alleen voor medewerkers.
function requireStaff()
{
    requireLogin();
    if (!isStaff()) {
        showError(403, 'Deze pagina is alleen voor medewerkers.');
    }
}

// Alleen voor bezoekers.
function requireVisitor()
{
    requireLogin();
    if (!isVisitor()) {
        showError(403, 'Alleen bezoekers kunnen tickets reserveren en reserveringen bekijken.');
    }
}
