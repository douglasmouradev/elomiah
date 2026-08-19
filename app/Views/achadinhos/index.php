<?php
use App\Core\View;
View::partial('partials/flash');
$lojaGeo = achadinhos_url();
?>
<section class="achadinhos-hero">
    <a href="<?= e($lojaGeo) ?>" target="_blank" rel="noopener noreferrer">
        <img src="<?= e(asset('images/banner-achadinhos.webp')) ?>" alt="Achadinhos da Geo — abrir a vitrine">
    </a>
</section>
<section class="page-hero container">
    <p class="lede" style="margin-inline:auto">Menos propaganda. Mais produtos que a Geo realmente usa, aprova e indicaria para uma amiga.</p>
    <p style="margin-top:1.8rem">
        <a class="btn btn-gold" href="<?= e($lojaGeo) ?>" target="_blank" rel="noopener noreferrer">Ver os achadinhos da Geo</a>
    </p>
</section>
<section class="container">
    <?php if (!empty($produtos)): ?>
        <div class="product-grid">
            <?php foreach ($produtos as $p): ?>
                <?php View::partial('partials/product-card', ['p' => $p]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
