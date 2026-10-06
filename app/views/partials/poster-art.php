<?php
/**
 * Affiche-kop van een evenement: datumblok, categorie en titel.
 * @var array $event
 * @var string $headingTag  'h2' in het overzicht, 'h1' op de detailpagina
 * @var string $extraClass
 */
$headingTag ??= 'h2';
$extraClass ??= '';
?>
<div class="poster__art poster__art--<?= (int) $event['category_id'] ?> <?= e($extraClass) ?>">
    <span class="poster__date">
        <span class="poster__day"><?= date('j', strtotime($event['starts_at'])) ?></span>
        <span class="poster__month"><?= e(month_short($event['starts_at'])) ?></span>
    </span>
    <span class="poster__category"><?= e($event['category_name']) ?></span>
    <<?= $headingTag ?> class="poster__title"><?= e($event['title']) ?></<?= $headingTag ?>>
</div>
