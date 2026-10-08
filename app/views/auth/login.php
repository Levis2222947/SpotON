<section class="box box--narrow">
    <h1 class="text-center">Inloggen</h1>

    <form method="post" action="<?= e(url('login')) ?>" novalidate>
        <?= csrfField() ?>

        <div class="form-group">
            <label for="email">E-mailadres:</label>
            <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email" placeholder="Voer uw e-mailadres in" required>
            <?= fieldError($errors, 'email') ?>
        </div>

        <div class="form-group">
            <label for="password">Wachtwoord:</label>
            <input type="password" id="password" name="password" autocomplete="current-password"
                   placeholder="Voer uw wachtwoord in" required>
            <?= fieldError($errors, 'password') ?>
        </div>

        <button type="submit" class="btn btn--blue btn--block">Inloggen</button>
    </form>

    <p class="form-footer">Nog geen account? <a href="<?= e(url('register')) ?>">Account aanmaken</a></p>
</section>
