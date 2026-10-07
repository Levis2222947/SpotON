<section class="hero">
    <h1>Ontdek en reserveer tickets voor aankomende evenementen!</h1>
    <p>Mis geen enkel evenement in uw buurt.</p>
</section>

<form class="filter-bar" method="get" action="<?= e(url()) ?>" role="search">
    <div class="form-group">
        <label for="filter-date">Datum</label>
        <input type="date" id="filter-date" name="date" value="<?= e($filters['date']) ?>">
    </div>
    <div class="form-group">
        <label for="filter-category">Categorie</label>
        <select id="filter-category" name="category">
            <option value="">Alle categorieën</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $filters['category'] === (int) $category['id'] ? 'selected' : '' ?>>
                    <?= e($category['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn--blue">Zoeken</button>
    <?php if ($isFiltered): ?>
        <a class="btn btn--gray" href="<?= e(url()) ?>">Wis filters</a>
    <?php endif; ?>
</form>

<?php if ($filterError): ?>
    <div class="alert alert--warning" role="alert"><?= e($filterError) ?></div>
<?php endif; ?>

<h2 class="text-center">Aankomende Evenementen</h2>

<?php if ($events === []): ?>
    <div class="empty-state">
        <strong>Geen evenementen gevonden</strong>
        <?= $isFiltered
            ? 'Er zijn geen evenementen die passen bij uw zoekopdracht. Probeer een andere datum of categorie.'
            : 'Er staan op dit moment geen evenementen gepland.' ?>
    </div>
<?php else: ?>
    <div class="event-grid">
        <?php foreach ($events as $event): ?>
            <?= partial('event-card', ['event' => $event]) ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
