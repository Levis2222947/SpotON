<?php
// Hoeveel plaatsen zijn er nog, en hoeveel tickets mag je maximaal kiezen?
$remaining = remainingSeats($event);
$maxQuantity = maxTicketsFor($event);
?>
<p><a class="btn btn--gray btn--small" href="<?= e(url()) ?>">Terug naar evenementen</a></p>

<div class="box">
    <h1><?= e($event['title']) ?></h1>

    <div class="details">
        <p><strong>Datum:</strong> <?= e(formatDate($event['starts_at'])) ?></p>
        <p><strong>Tijd:</strong> <?= e(formatTime($event['starts_at'])) ?></p>
        <p><strong>Locatie:</strong> <?= e($event['location']) ?>, Harbor Stage</p>
        <p><strong>Categorie:</strong> <?= e($event['category_name']) ?></p>
        <p><strong>Beschikbare plaatsen:</strong> <?= $remaining ?> van <?= (int) $event['capacity'] ?></p>
        <p><strong>Verkoop:</strong> <?= e(formatDateTime($event['sale_starts_at'])) ?> t/m <?= e(formatDateTime($event['sale_ends_at'])) ?></p>
        <p><strong>Status:</strong> <?= badge($saleStatus['label'], $saleStatus['color']) ?></p>
    </div>

    <h2 class="mt-1">Beschrijving</h2>
    <p><?= nl2br(e($event['description'])) ?></p>

    <h2>Programma</h2>
    <?php if ($program === []): ?>
        <p class="muted">Het programma wordt binnenkort bekendgemaakt.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($program as $line): ?>
                <li><?= e($line) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="box box--gray box--reserve">
        <h2>Tickets Reserveren</h2>

        <?php if (!$saleStatus['open']): ?>
            <div class="alert alert--info">Reserveren is niet mogelijk: <?= e(mb_strtolower($saleStatus['label'])) ?>.</div>
        <?php elseif (isStaff()): ?>
            <div class="alert alert--info">U bent ingelogd als medewerker. Alleen bezoekers kunnen tickets reserveren.</div>
        <?php elseif (!isLoggedIn()): ?>
            <p>Log in om tickets te reserveren.</p>
            <a class="btn btn--blue btn--block" href="<?= e(url('reserve', ['event' => $event['id']])) ?>">Inloggen en reserveren</a>
        <?php else: ?>
            <form method="get" action="<?= e(baseUrl() . '/index.php') ?>">
                <input type="hidden" name="page" value="reserve">
                <input type="hidden" name="event" value="<?= (int) $event['id'] ?>">
                <div class="form-group">
                    <label for="quantity">Aantal tickets</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $maxQuantity ?>" required>
                    <span class="hint">Maximaal <?= $maxQuantity ?> per reservering.</span>
                </div>
                <button type="submit" class="btn btn--blue btn--block">Tickets reserveren</button>
            </form>
        <?php endif; ?>
    </div>
</div>
