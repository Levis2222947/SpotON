<?php

// Controller: registreren, inloggen en uitloggen

function showRegisterForm()
{
    redirectIfLoggedIn();

    view('auth/register', ['title' => 'Account aanmaken', 'errors' => [], 'old' => []]);
}

function handleRegister()
{
    redirectIfLoggedIn();

    $name = inputText('name');
    $email = strtolower(inputText('email'));
    $password = inputText('password');
    $passwordConfirm = inputText('password_confirm');

    // Velden checken. Elke fout komt onder het goede veld.
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

    // Fouten? Formulier opnieuw tonen (naam en e-mail blijven ingevuld).
    if ($errors !== []) {
        view('auth/register', [
            'title'  => 'Account aanmaken',
            'errors' => $errors,
            'old'    => ['name' => $name, 'email' => $email],
        ], 422);
        return;
    }

    // Alles goed: account maken en inloggen
    $userId = createVisitor($name, $email, $password);
    loginUser(['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'visitor']);

    setFlash('success', 'Welkom, ' . $name . '! Uw account is aangemaakt.');
    redirect();
}

function showLoginForm()
{
    redirectIfLoggedIn();

    view('auth/login', ['title' => 'Inloggen', 'errors' => [], 'old' => []]);
}

function handleLogin()
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

            // Medewerker naar evenementbeheer, bezoeker naar home
            if (isStaff()) {
                redirect('staff/events');
            }
            redirect();
        }

        // Expres niet zeggen wát er fout is, zodat niemand kan uitzoeken welke e-mails bestaan.
        $errors['email'] = 'Het e-mailadres of wachtwoord is onjuist.';
    }

    view('auth/login', ['title' => 'Inloggen', 'errors' => $errors, 'old' => ['email' => $email]], 422);
}

function handleLogout()
{
    logoutUser();
    setFlash('success', 'U bent uitgelogd.');
    redirect();
}

// Al ingelogd? Dan heb je inloggen/registreren niet nodig.
function redirectIfLoggedIn()
{
    if (!isLoggedIn()) {
        return;
    }

    if (isStaff()) {
        redirect('staff/events');
    }
    redirect();
}
