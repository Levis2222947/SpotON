<?php

/**
 * Laat een pagina zien: eerst de view (bijv. "events/index"), daarna de layout eromheen
 * (header, menu en footer).
 */
function view(string $viewName, array $data = [], int $statusCode = 200): void
{
    http_response_code($statusCode);
    $data['content'] = renderFile($viewName, $data);
    echo renderFile('layout/main', $data);
}

/** Een klein stukje HTML dat vaker gebruikt wordt, zoals een evenementkaart. */
function partial(string $partialName, array $data = []): string
{
    return renderFile('partials/' . $partialName, $data);
}

/** Vult een bestand uit app/views/ met de gegevens en geeft de HTML terug. */
function renderFile(string $viewName, array $data): string
{
    extract($data, EXTR_SKIP); // ['title' => 'Home'] wordt de variabele $title
    ob_start();
    require APP_ROOT . '/app/views/' . $viewName . '.php';
    return (string) ob_get_clean();
}

/** Toont een foutpagina (bijv. 404 Niet gevonden) en stopt daarna. */
function showError(int $statusCode, string $message): never
{
    $titles = [403 => 'Geen toegang', 404 => 'Niet gevonden', 400 => 'Formulier verlopen'];

    view('errors/http', [
        'title'   => $titles[$statusCode] ?? 'Fout',
        'status'  => $statusCode,
        'message' => $message,
    ], $statusCode);
    exit;
}

/**
 * Een melding bewaren voor de volgende pagina, bijv. "Uw reservering is bevestigd!".
 * $type is 'success' (groen), 'error' (rood) of 'info' (blauw).
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Haalt de meldingen op en verwijdert ze, zodat je ze maar één keer ziet. */
function getFlashMessages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
