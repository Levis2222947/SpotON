<?php

declare(strict_types=1);

use App\Core\Csrf;

/** Tekst veilig in HTML zetten (bescherming tegen XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Basispad van de app, bijv. "/SpotON/public" of "" op Plesk. */
function base_url(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return rtrim($dir, '/');
}

/** URL naar een route, bijv. url('event', ['id' => 3]). */
function url(string $route = 'home', array $params = []): string
{
    if ($route === 'home' && $params === []) {
        return base_url() . '/';
    }
    return base_url() . '/index.php?' . http_build_query(['r' => $route] + $params);
}

function redirect(string $route = 'home', array $params = []): never
{
    header('Location: ' . url($route, $params));
    exit;
}

/** Alleen interne paden toestaan (voorkomt open redirects naar andere sites). */
function redirect_to_path(string $path): never
{
    if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
        redirect();
    }
    header('Location: ' . $path);
    exit;
}

function asset(string $path): string
{
    return base_url() . '/assets/' . ltrim($path, '/');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(Csrf::token()) . '">';
}

/** Foutmelding onder een formulierveld. */
function field_error(array $errors, string $field): string
{
    if (!isset($errors[$field])) {
        return '';
    }
    return '<p class="field-error" id="' . e($field) . '-error">' . e($errors[$field]) . '</p>';
}

/** aria-attributen voor een veld met een fout, zodat schermlezers de fout voorlezen. */
function field_aria(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="' . e($field) . '-error"' : '';
}

const DUTCH_MONTHS = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli',
    'augustus', 'september', 'oktober', 'november', 'december'];
const DUTCH_DAYS = ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'];

/** "zaterdag 18 oktober 2026" */
function format_date_long(string $datetime): string
{
    $ts = strtotime($datetime);
    return DUTCH_DAYS[(int) date('w', $ts)] . ' ' . date('j', $ts) . ' '
        . DUTCH_MONTHS[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

/** "18-10-2026 20:00" */
function format_datetime(?string $datetime): string
{
    return $datetime ? date('d-m-Y H:i', strtotime($datetime)) : '–';
}

function format_time(string $datetime): string
{
    return date('H:i', strtotime($datetime));
}

/** Korte maandnaam voor de datumblokken op de affichekaarten, bijv. "OKT". */
function month_short(string $datetime): string
{
    return strtoupper(substr(DUTCH_MONTHS[(int) date('n', strtotime($datetime)) - 1], 0, 3));
}
