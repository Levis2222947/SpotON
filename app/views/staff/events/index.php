<?php
/** @var array $events */
?>
<h1 class="text-center">Evenementbeheer</h1>

<div class="page-head page-head--right">
    <a class="btn btn--green" href="<?= e(url('staff/events/create')) ?>">+ Nieuw evenement</a>
</div>

<?php if ($events === []): ?>
    <div class="empty-state">
        <strong>Er zijn nog geen evenementen</strong>
        Maak een evenement aan met de knop hierboven.
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
                <th>Categorie</th>
                <th>Capaciteit</th>
                <th>Beschikbaar</th>
                <th>Verkoopperiode</th>
                <th>Status</th>
                <th class="actions">Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($events as $event): ?>
                <?php $saleStatus = saleStatus($event); ?>
                <tr>
                    <td><?= e($event['title']) ?></td>
                    <td><?= e(formatDate($event['starts_at'])) ?></td>
                    <td><?= e(formatTime($event['starts_at'])) ?></td>
                    <td><?= e($event['location']) ?></td>
                    <td><?= e($event['category_name']) ?></td>
                    <td><?= (int) $event['capacity'] ?></td>
                    <td><?= remainingSeats($event) ?></td>
                    <td><?= e(formatDateTime($event['sale_starts_at'])) ?><br>t/m <?= e(formatDateTime($event['sale_ends_at'])) ?></td>
                    <td>
                        <?= badge(eventStatusLabel($event['status']), $event['status'] === 'published' ? 'info' : 'muted') ?>
                        <?php if ($event['status'] === 'published'): ?>
                            <br><?= badge($saleStatus['label'], $saleStatus['color']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <a class="btn btn--blue btn--small" href="<?= e(url('staff/events/edit', ['id' => $event['id']])) ?>">Bewerken</a>
                        <a class="btn btn--gray btn--small" href="<?= e(url('staff/reservations', ['event' => $event['id']])) ?>">Reserveringen</a>
                        <?php if ((int) $event['sold'] === 0): ?>
                            <form method="post" action="<?= e(url('staff/events/delete')) ?>"
                                  data-confirm="Weet u zeker dat u &quot;<?= e($event['title']) ?>&quot; wilt verwijderen?">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $event['id'] ?>">
                                <button type="submit" class="btn btn--red btn--small">Verwijderen</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
