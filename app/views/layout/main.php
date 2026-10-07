<?php
$user = currentUser();
$flashMessages = getFlashMessages();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Evenementen') . ' – ' . CONFIG['app_name']) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="<?= isStaff() ? 'is-staff' : '' ?>">

<header class="site-header">
    <div class="container site-header__inner">
        <nav class="site-nav" aria-label="Hoofdmenu">
            <a class="logo" href="<?= e(url()) ?>">
                <?= e(CONFIG['venue']) ?><?= isStaff() ? ' - Evenementbeheer' : '' ?>
            </a>
            <?php if (isStaff()): ?>
                <a href="<?= e(url('staff/events')) ?>">Evenementen</a>
                <a href="<?= e(url('staff/reservations')) ?>">Reserveringen</a>
                <a href="<?= e(url('staff/scan')) ?>">Tickets scannen</a>
            <?php else: ?>
                <a href="<?= e(url()) ?>">Home</a>
                <a href="<?= e(url()) ?>">Evenementen</a>
                <a href="<?= e(url('my-reservations')) ?>">Mijn reserveringen</a>
            <?php endif; ?>
        </nav>

        <div class="site-account">
            <?php if ($user === null): ?>
                <a class="btn btn--blue btn--small" href="<?= e(url('login')) ?>">Login</a>
                <a class="btn btn--green btn--small" href="<?= e(url('register')) ?>">Registreren</a>
            <?php else: ?>
                <span class="user-name"><?= e($user['name']) ?></span>
                <form method="post" action="<?= e(url('logout')) ?>">
                    <?= csrfField() ?>
                    <button class="btn btn--blue btn--small" type="submit">Uitloggen</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="alert alert--<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="site-footer">
    &copy; <?= date('Y') ?> <?= e(CONFIG['venue']) ?>. Alle rechten voorbehouden.
</footer>
</body>
</html>
