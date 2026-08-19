<section class="page-hero container">
    <p class="eyebrow"><?= (int) ($code ?? 404) ?></p>
    <h1><?= e($title ?? 'Página não encontrada') ?></h1>
    <p class="lede" style="margin-inline:auto"><?= e($message ?? '') ?></p>
    <p><a class="btn btn-gold" href="<?= e(url('/')) ?>">Voltar ao refúgio</a></p>
</section>
