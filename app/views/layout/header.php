<?php
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - SpotOn</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body class="<?= isStaff() ? 'is-staff' : '' ?>">

<header class="site-header">
    <div class="container site-header__inner">
        <nav class="site-nav">
            <?php if (isStaff()): ?>
                <a class="logo" href="<?= url('staff/events') ?>">Harbor Stage - Evenementbeheer</a>
                <a href="<?= url('staff/events') ?>">Evenementen</a>
                <a href="<?= url('staff/reservations') ?>">Reserveringen</a>
                <a href="<?= url('staff/scan') ?>">Tickets scannen</a>
            <?php else: ?>
                <a class="logo" href="<?= url() ?>">Harbor Stage</a>
                <a href="<?= url() ?>">Home</a>
                <a href="<?= url() ?>">Evenementen</a>
                <a href="<?= url('my-reservations') ?>">Mijn reserveringen</a>
            <?php endif; ?>
        </nav>

        <div class="site-account">
            <?php if ($user === null): ?>
                <a class="btn btn--blue btn--small" href="<?= url('login') ?>">Login</a>
                <a class="btn btn--green btn--small" href="<?= url('register') ?>">Registreren</a>
            <?php else: ?>
                <span class="user-name"><?= e($user['name']) ?></span>
                <form method="post" action="<?= url('logout') ?>">
                    <?= csrfField() ?>
                    <button class="btn btn--blue btn--small" type="submit">Uitloggen</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <?php foreach (getFlashMessages() as $flash): ?>
        <div class="alert alert--<?= $flash['type'] ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
