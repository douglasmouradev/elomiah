<?php
/** @var array $p */
$p = $p ?? $produto ?? [];
$img = $p['imagem'] ?? 'images/frasco-despertar.webp';
$cor = $p['cor_destaque'] ?? '#1B4332';
$dado = $p['volume'] ?? $p['colecao'] ?? $p['categoria_nome'] ?? '';
?>
<article class="product-card">
    <a class="thumb" href="<?= e(url('/produto/' . $p['slug'])) ?>">
        <?php if (!empty($p['achadinho_geo'])): ?>
            <span class="badge-geo">Achadinho da Geo</span>
        <?php endif; ?>
        <img src="<?= e(asset($img)) ?>" alt="<?= e($p['nome']) ?>" loading="lazy">
    </a>
    <p class="product-band" style="background:<?= e((string) $cor) ?>"><?= e($p['nome']) ?></p>
    <?php if (!empty($p['aroma'])): ?>
        <p class="product-aroma">Aroma <?= e($p['aroma']) ?></p>
    <?php endif; ?>
    <div class="product-meta">
        <span><?= e((string) $dado) ?></span>
        <span class="price">
            <?php if (!empty($p['preco_promocional'])): ?>
                <?= e(money($p['preco_promocional'])) ?>
                <small class="price-was"><?= e(money($p['preco'])) ?></small>
            <?php else: ?>
                <?= e(money($p['preco'])) ?>
            <?php endif; ?>
        </span>
    </div>
    <?php if (($p['compra_tipo'] ?? 'carrinho') === 'carrinho' && (int) ($p['estoque'] ?? 0) > 0 && !produto_digital($p)): ?>
        <form method="post" action="<?= e(url('/carrinho/adicionar')) ?>" class="card-add">
            <?= csrf_field() ?>
            <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
            <button type="submit">À sacola</button>
        </form>
    <?php elseif (($p['compra_tipo'] ?? 'carrinho') === 'carrinho' && !produto_digital($p)): ?>
        <p class="card-add card-sold">Esgotado</p>
    <?php endif; ?>
</article>
