<?php
/** @var ?array $event @var array $values @var array $errors @var array $categories */
$isNew = $event === null;
$action = $isNew ? url('staff/events/create') : url('staff/events/edit', ['id' => $event['id']]);
$val = fn (string $field): string => e($values[$field] ?? '');
?>
<p><a class="btn btn--gray btn--small" href="<?= e(url('staff/events')) ?>">Terug naar evenementbeheer</a></p>

<section class="box box--medium">
    <h1><?= $isNew ? 'Nieuw evenement' : 'Evenement bewerken' ?></h1>

    <?php if ($errors !== []): ?>
        <div class="alert alert--error" role="alert">
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
            <input type="text" id="title" name="title" value="<?= $val('title') ?>" maxlength="150" required<?= fieldAria($errors, 'title') ?>>
            <?= fieldError($errors, 'title') ?>
        </div>

        <div class="form-group">
            <label for="description">Beschrijving</label>
            <textarea id="description" name="description" required<?= fieldAria($errors, 'description') ?>><?= $val('description') ?></textarea>
            <?= fieldError($errors, 'description') ?>
        </div>

        <div class="form-group">
            <label for="program">Programma <span class="muted">(optioneel)</span></label>
            <textarea id="program" name="program" placeholder="19:30 Deuren open&#10;20:00 Voorprogramma"<?= fieldAria($errors, 'program') ?>><?= $val('program') ?></textarea>
            <span class="hint">Eén onderdeel per regel.</span>
            <?= fieldError($errors, 'program') ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="location">Locatie (zaal)</label>
                <input type="text" id="location" name="location" value="<?= $val('location') ?>" maxlength="150" required<?= fieldAria($errors, 'location') ?>>
                <?= fieldError($errors, 'location') ?>
            </div>
            <div class="form-group">
                <label for="category_id">Categorie</label>
                <select id="category_id" name="category_id" required<?= fieldAria($errors, 'category_id') ?>>
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
                <input type="datetime-local" id="starts_at" name="starts_at" value="<?= $val('starts_at') ?>" required<?= fieldAria($errors, 'starts_at') ?>>
                <?= fieldError($errors, 'starts_at') ?>
            </div>
            <div class="form-group">
                <label for="capacity">Capaciteit (aantal plaatsen)</label>
                <input type="number" id="capacity" name="capacity" value="<?= $val('capacity') ?>" min="1" max="100000" required<?= fieldAria($errors, 'capacity') ?>>
                <?= fieldError($errors, 'capacity') ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="sale_starts_at">Start verkoop</label>
                <input type="datetime-local" id="sale_starts_at" name="sale_starts_at" value="<?= $val('sale_starts_at') ?>" required<?= fieldAria($errors, 'sale_starts_at') ?>>
                <?= fieldError($errors, 'sale_starts_at') ?>
            </div>
            <div class="form-group">
                <label for="sale_ends_at">Einde verkoop</label>
                <input type="datetime-local" id="sale_ends_at" name="sale_ends_at" value="<?= $val('sale_ends_at') ?>" required<?= fieldAria($errors, 'sale_ends_at') ?>>
                <?= fieldError($errors, 'sale_ends_at') ?>
            </div>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required<?= fieldAria($errors, 'status') ?>>
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
