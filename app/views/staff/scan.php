<?php
// Per uitkomst van de scan: welke kleur (class) en welke tekst ik laat zien
$statusViews = [
    'valid'        => ['class' => 'valid', 'title' => 'Geldig – toegang'],
    'already_used' => ['class' => 'used', 'title' => 'Al gebruikt'],
    'cancelled'    => ['class' => 'used', 'title' => 'Geannuleerd'],
    'wrong_event'  => ['class' => 'warning', 'title' => 'Ander evenement'],
    'not_found'    => ['class' => 'invalid', 'title' => 'Onbekende code'],
];

// Welk evenement is gekozen in de keuzelijst?
$selectedEventId = null;
if ($event !== null) {
    $selectedEventId = (int) $event['id'];
}
?>
<div class="scan">
    <h1>Tickets scannen</h1>

    <?php if ($scanResult !== null): ?>
        <?php $view = $statusViews[$scanResult['result']]; $ticket = $scanResult['ticket']; ?>
        <section class="scan-status scan-status--<?= $view['class'] ?>">
            <p class="scan-status__title"><?= e($view['title']) ?></p>
            <p><code><?= e($scanResult['code']) ?></code></p>

            <?php if ($scanResult['result'] === 'valid'): ?>
                <p><?= e($ticket['user_name']) ?> · <?= e($ticket['event_title']) ?></p>
            <?php elseif ($scanResult['result'] === 'already_used'): ?>
                <p>Dit ticket is al gescand op <?= e(formatDateTime($ticket['used_at'])) ?>. Geen toegang.</p>
            <?php elseif ($scanResult['result'] === 'cancelled'): ?>
                <p>De reservering van dit ticket is geannuleerd. Geen toegang.</p>
            <?php elseif ($scanResult['result'] === 'wrong_event'): ?>
                <p>Dit ticket hoort bij <strong><?= e($ticket['event_title']) ?></strong> (<?= e(formatDateTime($ticket['starts_at'])) ?>). Het ticket is níet als gebruikt geregistreerd.</p>
            <?php else: ?>
                <p>Deze code bestaat niet. Controleer of de code goed is overgetypt.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="box">
        <!-- Evenement kiezen: de pagina laadt opnieuw zodra je iets kiest -->
        <form method="get" action="<?= baseUrl() ?>/index.php">
            <input type="hidden" name="page" value="staff/scan">
            <div class="form-group">
                <label for="event">Controle voor evenement</label>
                <select id="event" name="event" onchange="this.form.submit()">
                    <option value="">Alle evenementen (geen controle op evenement)</option>
                    <?php foreach ($events as $option): ?>
                        <option value="<?= $option['id'] ?>" <?= $selectedEventId == $option['id'] ? 'selected' : '' ?>>
                            <?= e($option['title']) ?> (<?= formatDate($option['starts_at']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="hint">Kies het evenement van vandaag, dan worden tickets voor andere evenementen geweigerd.</span>
            </div>
        </form>

        <!-- Ticketcode invullen -->
        <form method="post" action="<?= url('staff/scan') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="event" value="<?= $selectedEventId ?>">

            <label for="code">Ticketcode</label>
            <div class="scan-form">
                <input type="text" id="code" name="code" placeholder="SO-XXXX-XXXX" maxlength="20" autocomplete="off" autofocus required>
                <button type="submit" class="btn btn--blue">Controleren</button>
            </div>
            <?= fieldError($errors, 'code') ?>
        </form>
    </section>

    <?php if ($counts !== null): ?>
        <h2><?= e($event['title']) ?></h2>
        <div class="stats">
            <div class="stat">
                <div class="stat__value"><?= $counts['used'] ?></div>
                <div class="stat__label">Binnen (gescand)</div>
            </div>
            <div class="stat">
                <div class="stat__value"><?= $counts['valid'] ?></div>
                <div class="stat__label">Nog verwacht</div>
            </div>
            <div class="stat">
                <div class="stat__value"><?= (int) $event['capacity'] ?></div>
                <div class="stat__label">Capaciteit</div>
            </div>
        </div>
    <?php endif; ?>
</div>
