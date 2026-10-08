<?php

// CSRF-beveiliging: elk formulier krijgt een geheime code mee.
// Een andere website kent die code niet, dus kan geen formulier namens jou versturen.

function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // willekeurige code
    }
    return $_SESSION['csrf_token'];
}

// Verborgen veld met de code, voor in elk formulier.
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// Klopt de code niet? Dan stop ik.
function checkCsrf()
{
    $sentToken = $_POST['csrf_token'] ?? '';

    if (!is_string($sentToken) || !hash_equals(csrfToken(), $sentToken)) {
        showError(400, 'Het formulier is verlopen. Ga terug, vernieuw de pagina en probeer het opnieuw.');
    }
}
