<?php $codigo = (int) ($code ?? 404); ?>
<section class="page-hero container">
    <p class="eyebrow">Erro <?= $codigo ?></p>
    <h1><?= e($title ?? 'Página não encontrada') ?></h1>
    <p class="lede"><?= e($message ?: ($codigo === 404 ? 'O endereço pode ter mudado ou o produto saiu da loja.' : 'Algo falhou do nosso lado. Tente de novo em instantes.')) ?></p>
    <p>
        <a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver os aromas</a>
        <a class="btn btn-ghost" href="<?= e(url('/')) ?>">Página inicial</a>
    </p>
</section>
