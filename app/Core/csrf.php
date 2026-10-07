<?php

/*
 * Bescherming tegen CSRF (Cross-Site Request Forgery).
 * Elk formulier krijgt een geheime code mee. Een andere website kent die code niet,
 * dus die kan geen formulier namens jou versturen.
 */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Verborgen veld dat in elk formulier met method="post" staat. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** Klopt de code niet? Dan stoppen we. */
function checkCsrf(): void
{
    $sentToken = $_POST['csrf_token'] ?? '';

    if (!is_string($sentToken) || !hash_equals(csrfToken(), $sentToken)) {
        showError(400, 'Het formulier is verlopen. Ga terug, vernieuw de pagina en probeer het opnieuw.');
    }
}
