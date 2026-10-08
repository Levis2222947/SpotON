<div class="event-card">
    <h3><?= e($event['title']) ?></h3>
    <p><strong>Datum:</strong> <?= e(formatDate($event['starts_at'])) ?></p>
    <p><strong>Tijd:</strong> <?= e(formatTime($event['starts_at'])) ?></p>
    <p><strong>Locatie:</strong> <?= e($event['location']) ?></p>
    <p><strong>Categorie:</strong> <?= e($event['category_name']) ?></p>
    <p><strong>Beschikbare plaatsen:</strong> <?= remainingSeats($event) ?></p>
    <a class="btn btn--blue btn--small" href="<?= e(url('event', ['id' => $event['id']])) ?>">Bekijk evenement</a>
</div>
