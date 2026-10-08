<?php
/**
 * @var array $events @var ?array $event @var ?array $counts @var ?array $scanResult @var array $errors
 */

// Uitkomst van de scan -> opvallende status met kleur én tekst
$statusViews = [
    'valid'        => ['class' => 'valid', 'title' => 'Geldig – toegang'],
    'already_used' => ['class' => 'used', 'title' => 'Al gebruikt'],
    'cancelled'    => ['class' => 'used', 'title' => 'Geannuleerd'],
    'wrong_event'  => ['class' => 'warning', 'title' => 'Ander evenement'],
    'not_found'    => ['class' => 'invalid', 'title' => 'Onbekende code'],
];
$selectedEventId = $event !== null ? (int) $event['id'] : null;
?>
<div class="scan">
    <h1>Tickets scannen</h1>

    <?php if ($scanResult !== null): ?>
        <?php $view = $statusViews[$scanResult['result']]; $ticket = $scanResult['ticket']; ?>
        <section class="scan-status scan-status--<?= $view['class'] ?>" role="alert" aria-live="assertive">
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
        <form method="post" action="<?= e(url('staff/scan')) ?>" novalidate>
            <?= csrfField() ?>

            <div class="form-group">
                <label for="event">Controle voor evenement</label>
                <select id="event" name="event" data-navigate="<?= e(url('staff/scan')) ?>">
                    <option value="">Alle evenementen (geen controle op evenement)</option>
                    <?php foreach ($events as $option): ?>
                        <option value="<?= (int) $option['id'] ?>" <?= $selectedEventId === (int) $option['id'] ? 'selected' : '' ?>>
                            <?= e($option['title']) ?> (<?= e(date('d-m-Y', strtotime($option['starts_at']))) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="hint">Kies het evenement van vandaag, dan worden tickets voor andere evenementen geweigerd.</span>
            </div>

            <label for="code">Ticketcode</label>
            <div class="scan-form">
                <input type="text" id="code" name="code" placeholder="SO-XXXX-XXXX" maxlength="20"
                       autocomplete="off" autocapitalize="characters" spellcheck="false" autofocus
                       required<?= fieldAria($errors, 'code') ?>>
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
