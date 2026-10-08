<?php

// Laat een pagina zien: header + de pagina zelf + footer.
// extract() maakt van ['title' => 'Home'] de variabele $title.
function view($viewName, $data = [], $statusCode = 200)
{
    http_response_code($statusCode);
    extract($data);

    require APP_ROOT . '/app/views/layout/header.php';
    require APP_ROOT . '/app/views/' . $viewName . '.php';
    require APP_ROOT . '/app/views/layout/footer.php';
}

// Foutpagina laten zien (bijv. 404) en stoppen.
function showError($statusCode, $message)
{
    $titles = [400 => 'Formulier verlopen', 403 => 'Geen toegang', 404 => 'Niet gevonden'];

    view('errors/http', [
        'title'   => $titles[$statusCode] ?? 'Fout',
        'status'  => $statusCode,
        'message' => $message,
    ], $statusCode);
    exit;
}

// Melding bewaren voor de volgende pagina. $type: success, error of info.
function setFlash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

// Meldingen ophalen en daarna weggooien, zodat je ze maar één keer ziet.
function getFlashMessages()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
