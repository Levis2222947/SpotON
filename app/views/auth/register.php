<section class="box box--narrow">
    <h1 class="text-center">Account aanmaken</h1>

    <form method="post" action="<?= e(url('register')) ?>" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name" value="<?= e($old['name'] ?? '') ?>"
                   autocomplete="name" maxlength="100" placeholder="Voer uw naam in" required>
            <?= fieldError($errors, 'name') ?>
        </div>

        <div class="form-group">
            <label for="email">E-mailadres:</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email" maxlength="190" placeholder="Voer uw e-mailadres in" required>
            <?= fieldError($errors, 'email') ?>
        </div>

        <div class="form-group">
            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" autocomplete="new-password"
                   minlength="8" placeholder="Voer uw wachtwoord in" required>
            <span class="hint">Minimaal 8 tekens, waarvan minimaal één cijfer.</span>
            <?= fieldError($errors, 'password') ?>
        </div>

        <div class="form-group">
            <label for="password_confirm">Wachtwoord bevestigen:</label>
            <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password"
                   placeholder="Bevestig uw wachtwoord" required>
            <?= fieldError($errors, 'password_confirm') ?>
        </div>

        <button type="submit" class="btn btn--green btn--block">Account aanmaken</button>
    </form>

    <p class="form-footer">Al een account? <a href="<?= e(url('login')) ?>">Inloggen</a></p>
</section>
