<section class="message-page">
    <p class="message-page__code">500</p>
    <h1>Er ging iets mis</h1>
    <p>Er is een onverwachte fout opgetreden. Probeer het later opnieuw.</p>
    <?php if (!empty($details)): ?>
        <pre class="debug"><?= e($details) ?></pre>
    <?php endif; ?>
    <a class="btn btn--blue" href="<?= e(url()) ?>">Naar de evenementen</a>
</section>
