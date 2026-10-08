<h1 class="text-center">Mijn reserveringen</h1>

<?php if ($reservations === []): ?>
    <div class="empty-state">
        <strong>U heeft nog geen reserveringen</strong>
        Bekijk de evenementen en reserveer uw eerste tickets.
        <p><a class="btn btn--blue" href="<?= e(url()) ?>">Naar de evenementen</a></p>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>Event naam</th>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Locatie</th>
                <th>Tickets</th>
                <th>Status</th>
                <th class="actions">Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= e($reservation['event_title']) ?></td>
                    <td><?= e(formatDate($reservation['starts_at'])) ?></td>
                    <td><?= e(formatTime($reservation['starts_at'])) ?></td>
                    <td><?= e($reservation['location']) ?></td>
                    <td><?= (int) $reservation['quantity'] ?></td>
                    <td><?= reservationBadge($reservation['status']) ?></td>
                    <td class="actions">
                        <a class="btn btn--blue btn--small" href="<?= e(url('reservation', ['id' => $reservation['id']])) ?>">Details</a>
                        <?php if (canCancelReservation($reservation)): ?>
                            <form method="post" action="<?= e(url('reservation/cancel')) ?>"
                                  onsubmit="return confirm('Weet u zeker dat u deze reservering wilt annuleren? Uw tickets worden ongeldig.')">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
                                <button type="submit" class="btn btn--red btn--small">Annuleren</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
