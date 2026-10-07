<?php

/*
 * Kleine hulpfuncties die overal gebruikt worden.
 */

/**
 * Tekst veilig in de HTML zetten. Typt iemand <script> als naam,
 * dan wordt dat gewoon als tekst getoond en niet uitgevoerd (bescherming tegen XSS).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Map waarin de website staat, bijv. "/fotoshooty/SpotON/public" op XAMPP of "" op Plesk. */
function baseUrl(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

/** Link naar een pagina, bijv. url('event', ['id' => 3]) wordt index.php?page=event&id=3 */
function url(string $page = 'home', array $params = []): string
{
    if ($page === 'home' && $params === []) {
        return baseUrl() . '/';
    }
    return baseUrl() . '/index.php?' . http_build_query(['page' => $page] + $params);
}

/** Stuurt de browser door naar een andere pagina en stopt daarna. */
function redirect(string $page = 'home', array $params = []): never
{
    header('Location: ' . url($page, $params));
    exit;
}

/** Link naar een CSS- of JavaScript-bestand. */
function asset(string $path): string
{
    return baseUrl() . '/assets/' . $path;
}

/** Haalt een heel getal (groter dan 0) uit de URL of het formulier. Geen geldig getal? Dan null. */
function inputInt(string $key): ?int
{
    $value = filter_var($_REQUEST[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value === false ? null : $value;
}

/** Haalt tekst uit de URL of het formulier, zonder spaties aan het begin en eind. */
function inputText(string $key): string
{
    $value = $_REQUEST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/** Controleert of een datum echt bestaat, bijv. isValidDate('2026-10-18', 'Y-m-d'). */
function isValidDate(string $value, string $format): bool
{
    $date = DateTime::createFromFormat($format, $value);
    return $date !== false && $date->format($format) === $value;
}

/** Foutmelding onder een formulierveld. */
function fieldError(array $errors, string $field): string
{
    if (!isset($errors[$field])) {
        return '';
    }
    return '<p class="field-error" id="' . e($field) . '-error">' . e($errors[$field]) . '</p>';
}

/** Markeert een veld met een fout, zodat ook een schermlezer de fout voorleest. */
function fieldAria(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="' . e($field) . '-error"' : '';
}

/** Statuslabel: altijd een kleur én een tekst. $color is success, danger, warning, info of muted. */
function badge(string $label, string $color): string
{
    return '<span class="badge badge--' . e($color) . '">' . e($label) . '</span>';
}

/** "18-10-2026" */
function formatDate(string $datetime): string
{
    return date('d-m-Y', strtotime($datetime));
}

/** "20:00" */
function formatTime(string $datetime): string
{
    return date('H:i', strtotime($datetime));
}

/** "18-10-2026 20:00" */
function formatDateTime(?string $datetime): string
{
    return $datetime ? date('d-m-Y H:i', strtotime($datetime)) : '–';
}
