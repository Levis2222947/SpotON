<?php
/** @var array $event  (met capacity en sold) */

use App\Models\Event;

$capacity = max(1, (int) $event['capacity']);
$sold = min((int) $event['sold'], $capacity);
$percentage = (int) round($sold / $capacity * 100);
$remaining = Event::remaining($event);
$modifier = $remaining === 0 ? 'capacity--full' : ($percentage >= 80 ? 'capacity--busy' : '');
?>
<div class="capacity <?= $modifier ?>">
    <div class="capacity__bar" role="progressbar" aria-valuemin="0" aria-valuemax="<?= $capacity ?>"
         aria-valuenow="<?= $sold ?>" aria-label="Bezetting">
        <div class="capacity__fill" style="width: <?= $percentage ?>%"></div>
    </div>
    <div class="capacity__label">
        <span><strong><?= $remaining ?></strong> van <?= (int) $event['capacity'] ?> plaatsen vrij</span>
        <span><?= $percentage ?>% bezet</span>
    </div>
</div>
