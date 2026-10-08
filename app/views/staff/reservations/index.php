<h1 class="text-center">Reserveringen</h1>

<form class="filter-bar" method="get" action="<?= e(baseUrl() . '/index.php') ?>">
    <input type="hidden" name="page" value="staff/reservations">
    <div class="form-group">
        <label for="filter-event">Evenement</label>
        <select id="filter-event" name="event">
            <option value="">Alle evenementen</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= (int) $event['id'] ?>" <?= $filters['event'] === (int) $event['id'] ? 'selected' : '' ?>>
                    <?= e($event['title']) ?> (<?= e(date('d-m-Y', strtotime($event['starts_at']))) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="filter-status">Status</label>
        <select id="filter-status" name="status">
            <option value="">Alle statussen</option>
            <?php foreach (RESERVATION_STATUS_LABELS as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn--blue">Filteren</button>
    <?php if ($isFiltered): ?>
        <a class="btn btn--gray" href="<?= e(url('staff/reservations')) ?>">Wis filters</a>
    <?php endif; ?>
</form>

<?php if ($reservations === []): ?>
    <div class="empty-state">
        <strong>Geen reserveringen gevonden</strong>
        <?= $isFiltered ? 'Er zijn geen reserveringen die passen bij deze filters.' : 'Er zijn nog geen reserveringen gemaakt.' ?>
    </div>
<?php else: ?>
    <p><?= count($reservations) ?> reserveringen · <?= (int) $ticketTotal ?> tickets</p>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Bezoeker</th>
                <th>Evenement</th>
                <th>Tickets</th>
                <th>Gescand</th>
                <th>Gereserveerd op</th>
                <th>Status</th>
                <th class="actions">Acties</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= (int) $reservation['id'] ?></td>
                    <td>
                        <?= e($reservation['user_name']) ?><br>
                        <span class="muted"><?= e($reservation['user_email']) ?></span>
                    </td>
                    <td>
                        <?= e($reservation['event_title']) ?><br>
                        <span class="muted"><?= e(formatDateTime($reservation['starts_at'])) ?></span>
                    </td>
                    <td><?= (int) $reservation['quantity'] ?></td>
                    <td><?= (int) $reservation['used_count'] ?></td>
                    <td><?= e(formatDateTime($reservation['created_at'])) ?></td>
                    <td><?= reservationBadge($reservation['status']) ?></td>
                    <td class="actions">
                        <?php if (canCancelReservation($reservation)): ?>
                            <form method="post" action="<?= e(url('staff/reservations/cancel')) ?>"
                                  onsubmit="return confirm('Deze reservering annuleren? De tickets worden ongeldig.')">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $reservation['id'] ?>">
                                <input type="hidden" name="filter_status" value="<?= e($filters['status']) ?>">
                                <input type="hidden" name="filter_event" value="<?= e($filters['event']) ?>">
                                <button type="submit" class="btn btn--red btn--small">Annuleren</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
