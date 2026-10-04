<?php
/** @var array $p */
$p = $p ?? $produto ?? [];
$img = $p['imagem'] ?? 'images/frasco-despertar.webp';
$cor = (string) ($p['cor_destaque'] ?? '#173A2C');
$link = url('/produto/' . $p['slug']);
$noCarrinho = ($p['compra_tipo'] ?? 'carrinho') === 'carrinho' && !produto_digital($p);
?>
<article class="product-card" style="--c:<?= e($cor) ?>" data-nevoa="<?= e($cor) ?>">
    <a class="thumb" href="<?= e($link) ?>" tabindex="-1" aria-hidden="true">
        <img class="vt-frasco" data-vt="frasco-<?= (int) $p['id'] ?>" src="<?= e(asset($img)) ?>" alt="" width="400" height="500" loading="lazy" decoding="async">
    </a>
    <div class="product-info">
        <h3 class="rotulo"><a href="<?= e($link) ?>"><?= e($p['nome']) ?></a></h3>
        <?php if (!empty($p['aroma'])): ?>
            <p class="product-aroma"><?= e($p['aroma']) ?>, <?= e($p['volume'] ?? '120 ml') ?></p>
        <?php endif; ?>
        <p class="price">
            <?php if (!empty($p['preco_promocional'])): ?>
                <?= e(money($p['preco_promocional'])) ?>
                <small class="price-was"><?= e(money($p['preco'])) ?></small>
            <?php else: ?>
                <?= e(money($p['preco'])) ?>
            <?php endif; ?>
        </p>
        <?php if ($noCarrinho && (int) ($p['estoque'] ?? 0) > 0): ?>
            <form method="post" action="<?= e(url('/carrinho/adicionar')) ?>" class="card-add">
                <?= csrf_field() ?>
                <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
                <button type="submit" data-icone aria-label="Adicionar <?= e($p['nome']) ?> à sacola">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                </button>
            </form>
        <?php elseif ($noCarrinho): ?>
            <p class="card-add card-sold">Esgotado</p>
        <?php endif; ?>
    </div>
</article>
