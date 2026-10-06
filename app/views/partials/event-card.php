<?php

use App\Core\View;
use App\Models\Event;

/** @var array $event */
$state = Event::saleState($event);
?>
<a class="poster" href="<?= e(url('event', ['id' => $event['id']])) ?>">
    <?= View::partial('poster-art', ['event' => $event]) ?>
    <div class="poster__body">
        <p class="poster__meta">
            <span>📅 <?= e(format_date_long($event['starts_at'])) ?></span>
            <span>🕗 <?= e(format_time($event['starts_at'])) ?></span>
            <span>📍 <?= e($event['location']) ?></span>
        </p>
        <?= View::partial('capacity-bar', ['event' => $event]) ?>
        <div class="poster__footer">
            <?= View::partial('badge', ['label' => $state['label'], 'variant' => $state['badge']]) ?>
            <span class="poster__cta">Bekijk →</span>
        </div>
    </div>
</a>
