<section class="box--medium">
    <p><a class="btn btn--gray btn--small" href="<?= e(url('event', ['id' => $event['id']])) ?>">Terug</a></p>

    <div class="box">
        <h1>Tickets reserveren voor <?= e($event['title']) ?></h1>

        <div class="box box--gray details">
            <p><strong>Evenement:</strong> <?= e($event['title']) ?></p>
            <p><strong>Datum:</strong> <?= e(formatDate($event['starts_at'])) ?></p>
            <p><strong>Tijd:</strong> <?= e(formatTime($event['starts_at'])) ?></p>
            <p><strong>Locatie:</strong> <?= e($event['location']) ?></p>
            <p><strong>Nog beschikbaar:</strong> <?= remainingSeats($event) ?> plaatsen</p>
        </div>

        <?php if ($maxQuantity < 1): ?>
            <div class="alert alert--error">
                <?= e($errors['quantity'] ?? 'Er zijn geen plaatsen meer beschikbaar.') ?>
            </div>
            <a class="btn btn--gray" href="<?= e(url()) ?>">Bekijk andere evenementen</a>
        <?php else: ?>
            <form method="post" action="<?= e(url('reserve', ['event' => $event['id']])) ?>" novalidate>
                <?= csrfField() ?>

                <div class="form-group">
                    <label for="quantity">Aantal tickets:</label>
                    <input type="number" id="quantity" name="quantity" value="<?= (int) $quantity ?>" min="1"
                           max="<?= $maxQuantity ?>" required oninput="document.getElementById('total').textContent = this.value">
                    <span class="hint">Maximaal <?= $maxQuantity ?> tickets per reservering.</span>
                    <?= fieldError($errors, 'quantity') ?>
                </div>

                <p><strong>Totaal aantal tickets: <span id="total"><?= (int) $quantity ?></span></strong></p>

                <div class="form-actions">
                    <a class="btn btn--red" href="<?= e(url('event', ['id' => $event['id']])) ?>">Annuleren</a>
                    <button type="submit" class="btn btn--green">Reservering bevestigen</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
