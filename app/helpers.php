<?php

// Kleine hulpfuncties die ik overal gebruik.

// Tekst veilig tonen. <script> wordt gewoon tekst en wordt niet uitgevoerd (tegen XSS).
function e($text)
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

// De map van de site, bijv. "/fotoshooty/SpotON/public".
function baseUrl()
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

// Link naar een pagina. url('event', ['id' => 3]) wordt index.php?page=event&id=3
function url($page = 'home', $params = [])
{
    if ($page === 'home' && $params === []) {
        return baseUrl() . '/';
    }

    $link = baseUrl() . '/index.php?page=' . urlencode($page);
    foreach ($params as $key => $value) {
        $link .= '&' . $key . '=' . urlencode($value);
    }
    return $link;
}

// Naar een andere pagina sturen en stoppen.
function redirect($page = 'home', $params = [])
{
    header('Location: ' . url($page, $params));
    exit;
}

// Link naar een CSS-bestand.
function asset($path)
{
    return baseUrl() . '/assets/' . $path;
}

// Getal uit de URL of het formulier. Geen getal (of kleiner dan 1)? Dan null.
function inputInt($key)
{
    $value = $_REQUEST[$key] ?? '';

    if (!is_numeric($value) || $value < 1) {
        return null;
    }
    return (int) $value;
}

// Tekst uit de URL of het formulier, zonder spaties aan het begin en eind.
function inputText($key)
{
    $value = $_REQUEST[$key] ?? '';

    if (!is_string($value)) {
        return '';
    }
    return trim($value);
}

// Bestaat deze datum echt? '2026-02-31' bestaat bijvoorbeeld niet.
function isValidDate($value, $format)
{
    $time = strtotime($value);
    return $time !== false && date($format, $time) === $value;
}

// Foutmelding onder een formulierveld.
function fieldError($errors, $field)
{
    if (!isset($errors[$field])) {
        return '';
    }
    return '<p class="field-error">' . e($errors[$field]) . '</p>';
}

// Gekleurd label met tekst. $color: success, danger, warning, info of muted.
function badge($label, $color)
{
    return '<span class="badge badge--' . $color . '">' . e($label) . '</span>';
}

function formatDate($datetime)
{
    return date('d-m-Y', strtotime($datetime));
}

function formatTime($datetime)
{
    return date('H:i', strtotime($datetime));
}

function formatDateTime($datetime)
{
    if (!$datetime) {
        return '-';
    }
    return date('d-m-Y H:i', strtotime($datetime));
}
