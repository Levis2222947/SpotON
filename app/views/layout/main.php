<?php

use App\Core\Auth;
use App\Core\Config;
use App\Core\Flash;

$user = Auth::user();
$currentRoute = is_string($_GET['r'] ?? null) ? $_GET['r'] : 'home';
$flashMessages = Flash::pullAll();

/** Navigatielink met "actief"-markering. */
$navLink = static function (string $route, string $label) use ($currentRoute): string {
    $active = $currentRoute === $route || str_starts_with($currentRoute, $route . '/');
    return '<a href="' . e(url($route)) . '"' . ($active ? ' class="is-active" aria-current="page"' : '') . '>'
        . e($label) . '</a>';
};
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Evenementen') . ' – ' . Config::get('app_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="<?= Auth::isStaff() ? 'is-staff' : '' ?>">
<a class="skip-link" href="#main">Naar inhoud</a>

<header class="site-header">
    <div class="container site-header__inner">
        <a class="logo" href="<?= e(url()) ?>">
            Spot<span>On</span>
            <small><?= e(Config::get('venue')) ?></small>
        </a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
            <span class="sr-only">Menu</span>☰
        </button>

        <nav class="site-nav" id="site-nav" aria-label="Hoofdmenu">
            <?= $navLink('home', 'Evenementen') ?>
            <?php if ($user === null): ?>
                <?= $navLink('login', 'Inloggen') ?>
                <a class="btn btn--accent btn--small" href="<?= e(url('register')) ?>">Registreren</a>
            <?php elseif (Auth::isStaff()): ?>
                <?= $navLink('staff/events', 'Beheer') ?>
                <?= $navLink('staff/reservations', 'Reserveringen') ?>
                <?= $navLink('staff/scan', 'Scannen') ?>
            <?php else: ?>
                <?= $navLink('my-reservations', 'Mijn reserveringen') ?>
            <?php endif; ?>

            <?php if ($user !== null): ?>
                <form class="logout-form" method="post" action="<?= e(url('logout')) ?>">
                    <?= csrf_field() ?>
                    <span class="user-chip" title="<?= e($user['email']) ?>">
                        <?= e($user['name']) ?>
                        <em><?= Auth::isStaff() ? 'Medewerker' : 'Bezoeker' ?></em>
                    </span>
                    <button class="btn btn--ghost btn--small" type="submit">Uitloggen</button>
                </form>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="main" class="container">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert--<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">
        &copy; <?= date('Y') ?> <?= e(Config::get('venue')) ?> · <?= e(Config::get('app_name')) ?> ticketing
    </div>
</footer>
</body>
</html>
