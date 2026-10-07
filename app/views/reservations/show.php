<?php
/** @var array $reservation @var array $tickets */
?>
<p><a class="btn btn--gray btn--small" href="<?= e(url('my-reservations')) ?>">Terug naar mijn reserveringen</a></p>

<div class="box">
    <h1><?= e($reservation['event_title']) ?></h1>
    <div class="details">
        <p><strong>Datum:</strong> <?= e(formatDate($reservation['starts_at'])) ?></p>
        <p><strong>Tijd:</strong> <?= e(formatTime($reservation['starts_at'])) ?></p>
        <p><strong>Locatie:</strong> <?= e($reservation['location']) ?></p>
        <p><strong>Status:</strong> <?= reservationBadge($reservation['status']) ?></p>
    </div>

    <h2 class="mt-1">Uw tickets</h2>
    <p>Laat uw ticketcode aan de deur zien. Elke code is maar één keer geldig.</p>

    <div class="ticket-list">
        <?php foreach ($tickets as $index => $ticket): ?>
            <div class="ticket ticket--<?= e($ticket['status']) ?>">
                <span class="muted">Ticket <?= $index + 1 ?> van <?= count($tickets) ?></span>
                <p class="ticket__code"><?= e($ticket['code']) ?></p>
                <?= ticketBadge($ticket['status']) ?>
                <?php if ($ticket['used_at']): ?>
                    <p class="muted">Gebruikt op <?= e(formatDateTime($ticket['used_at'])) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (canCancelReservation($reservation)): ?>
        <form class="mt-1" method="post" action="<?= e(url('reservation/cancel')) ?>"
              data-confirm="Weet u zeker dat u deze reservering wilt annuleren? Uw tickets worden ongeldig.">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
            <button type="submit" class="btn btn--red">Reservering annuleren</button>
        </form>
    <?php endif; ?>
</div>
