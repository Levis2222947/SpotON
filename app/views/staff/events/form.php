<?php
// Is het een nieuw evenement of pas ik een bestaand evenement aan?
$isNew = $event === null;

if ($isNew) {
    $action = url('staff/events/create');
} else {
    $action = url('staff/events/edit', ['id' => $event['id']]);
}
?>
<p><a class="btn btn--gray btn--small" href="<?= e(url('staff/events')) ?>">Terug naar evenementbeheer</a></p>

<section class="box box--medium">
    <h1><?= $isNew ? 'Nieuw evenement' : 'Evenement bewerken' ?></h1>

    <?php if ($errors !== []): ?>
        <div class="alert alert--error">
            Het evenement is niet opgeslagen. Controleer de <?= count($errors) ?> gemarkeerde <?= count($errors) === 1 ? 'veld' : 'velden' ?>.
        </div>
    <?php endif; ?>

    <?php if (!$isNew && (int) $event['sold'] > 0): ?>
        <div class="alert alert--info">
            Er zijn al <strong><?= (int) $event['sold'] ?></strong> tickets uitgegeven. De capaciteit kan niet lager worden dan dit aantal.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e($action) ?>" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="title">Naam evenement</label>
            <input type="text" id="title" name="title" value="<?= e($values['title'] ?? '') ?>" maxlength="150" required>
            <?= fieldError($errors, 'title') ?>
        </div>

        <div class="form-group">
            <label for="description">Beschrijving</label>
            <textarea id="description" name="description" required><?= e($values['description'] ?? '') ?></textarea>
            <?= fieldError($errors, 'description') ?>
        </div>

        <div class="form-group">
            <label for="program">Programma <span class="muted">(optioneel)</span></label>
            <textarea id="program" name="program" placeholder="19:30 Deuren open&#10;20:00 Voorprogramma"><?= e($values['program'] ?? '') ?></textarea>
            <span class="hint">Eén onderdeel per regel.</span>
            <?= fieldError($errors, 'program') ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="location">Locatie (zaal)</label>
                <input type="text" id="location" name="location" value="<?= e($values['location'] ?? '') ?>" maxlength="150" required>
                <?= fieldError($errors, 'location') ?>
            </div>
            <div class="form-group">
                <label for="category_id">Categorie</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Kies een categorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) ($values['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= fieldError($errors, 'category_id') ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="starts_at">Datum en aanvangstijd</label>
                <input type="datetime-local" id="starts_at" name="starts_at" value="<?= e($values['starts_at'] ?? '') ?>" required>
                <?= fieldError($errors, 'starts_at') ?>
            </div>
            <div class="form-group">
                <label for="capacity">Capaciteit (aantal plaatsen)</label>
                <input type="number" id="capacity" name="capacity" value="<?= e($values['capacity'] ?? '') ?>" min="1" max="100000" required>
                <?= fieldError($errors, 'capacity') ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sale_starts_at">Start verkoop</label>
                <input type="datetime-local" id="sale_starts_at" name="sale_starts_at" value="<?= e($values['sale_starts_at'] ?? '') ?>" required>
                <?= fieldError($errors, 'sale_starts_at') ?>
            </div>
            <div class="form-group">
                <label for="sale_ends_at">Einde verkoop</label>
                <input type="datetime-local" id="sale_ends_at" name="sale_ends_at" value="<?= e($values['sale_ends_at'] ?? '') ?>" required>
                <?= fieldError($errors, 'sale_ends_at') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <?php foreach (EVENT_STATUS_LABELS as $status => $label): ?>
                    <option value="<?= e($status) ?>" <?= ($values['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="hint">Alleen gepubliceerde evenementen zijn zichtbaar voor bezoekers.</span>
            <?= fieldError($errors, 'status') ?>
        </div>

        <div class="form-actions">
            <a class="btn btn--gray" href="<?= e(url('staff/events')) ?>">Annuleren</a>
            <button type="submit" class="btn btn--green">Evenement opslaan</button>
        </div>
    </form>
</section>
