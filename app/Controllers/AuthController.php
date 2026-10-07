<?php

/*
 * Controller: registreren, inloggen en uitloggen.
 */

function showRegisterForm(): void
{
    redirectIfLoggedIn();
    view('auth/register', ['title' => 'Account aanmaken', 'errors' => [], 'old' => []]);
}

function handleRegister(): void
{
    redirectIfLoggedIn();

    $name = inputText('name');
    $email = strtolower(inputText('email'));
    $password = inputText('password');
    $passwordConfirm = inputText('password_confirm');

    // Alles controleren. Elke fout komt onder het juiste veld te staan.
    $errors = [];

    if ($name === '') {
        $errors['name'] = 'Vul uw naam in.';
    } elseif (strlen($name) > 100) {
        $errors['name'] = 'Uw naam mag maximaal 100 tekens lang zijn.';
    }

    if ($email === '') {
        $errors['email'] = 'Vul uw e-mailadres in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Dit is geen geldig e-mailadres.';
    } elseif (findUserByEmail($email) !== null) {
        $errors['email'] = 'Er bestaat al een account met dit e-mailadres.';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Het wachtwoord moet minimaal 8 tekens lang zijn.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Het wachtwoord moet minimaal één cijfer bevatten.';
    }

    if ($passwordConfirm !== $password) {
        $errors['password_confirm'] = 'De wachtwoorden zijn niet hetzelfde.';
    }

    // Fouten gevonden? Formulier opnieuw tonen (naam en e-mail blijven ingevuld).
    if ($errors !== []) {
        view('auth/register', [
            'title'  => 'Account aanmaken',
            'errors' => $errors,
            'old'    => ['name' => $name, 'email' => $email],
        ], 422);
        return;
    }

    $userId = createVisitor($name, $email, $password);
    loginUser(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'visitor']);

    setFlash('success', "Welkom, {$name}! Uw account is aangemaakt.");
    redirect();
}

function showLoginForm(): void
{
    redirectIfLoggedIn();
    view('auth/login', ['title' => 'Inloggen', 'errors' => [], 'old' => []]);
}

function handleLogin(): void
{
    redirectIfLoggedIn();

    $email = inputText('email');
    $password = inputText('password');
    $errors = [];

    if ($email === '') {
        $errors['email'] = 'Vul uw e-mailadres in.';
    }
    if ($password === '') {
        $errors['password'] = 'Vul uw wachtwoord in.';
    }

    if ($errors === []) {
        $user = checkLogin($email, $password);

        if ($user !== null) {
            loginUser($user);
            setFlash('success', 'U bent ingelogd. Welkom terug, ' . $user['name'] . '!');
            redirect(isStaff() ? 'staff/events' : 'home');
        }

        // We zeggen bewust niet wát er fout is, zodat niemand kan uitzoeken welke e-mailadressen bestaan.
        $errors['email'] = 'Het e-mailadres of wachtwoord is onjuist.';
    }

    view('auth/login', ['title' => 'Inloggen', 'errors' => $errors, 'old' => ['email' => $email]], 422);
}

function handleLogout(): void
{
    logoutUser();
    setFlash('success', 'U bent uitgelogd.');
    redirect();
}

/** Al ingelogd? Dan heeft het geen zin om het inlog- of registratieformulier te zien. */
function redirectIfLoggedIn(): void
{
    if (isLoggedIn()) {
        redirect(isStaff() ? 'staff/events' : 'home');
    }
}
