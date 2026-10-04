<?php
use App\Core\View;
View::partial('partials/flash');
$produto = $produto ?? [];
$imagens = $imagens ?: [['caminho' => 'images/frasco-despertar.webp', 'alt' => $produto['nome']]];
$principal = $imagens[0];
$cor = (string) ($produto['cor_destaque'] ?? '#173A2C');
$estoque = (int) ($produto['estoque'] ?? 0);
$disponivel = $estoque > 0;
$compraTipo = $produto['compra_tipo'] ?? 'carrinho';
$volume = (string) ($produto['volume'] ?? '120 ml');
$colecao = (string) ($produto['colecao'] ?? $produto['categoria_nome'] ?? '');
?>
<article class="container product-page" data-nevoa-base="<?= e($cor) ?>" style="--c:<?= e($cor) ?>">
    <div class="gallery">
        <div class="gallery-main" tabindex="0" role="button" aria-label="Ampliar foto">
            <img class="vt-frasco" style="view-transition-name:frasco-<?= (int) $produto['id'] ?>" src="<?= e(asset($principal['caminho'])) ?>" alt="<?= e($principal['alt'] ?? ('Frasco ' . $produto['nome'])) ?>" width="800" height="1000" fetchpriority="high" decoding="async">
        </div>
        <?php if (count($imagens) > 1): ?>
            <div class="thumbs">
                <?php foreach ($imagens as $i => $img): ?>
                    <button type="button" class="<?= $i === 0 ? 'is-on' : '' ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Foto <?= $i + 1 ?> de <?= count($imagens) ?>" data-thumb="<?= e(asset($img['caminho'])) ?>">
                        <img src="<?= e(asset($img['caminho'])) ?>" alt="" width="72" height="90" loading="lazy">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="product-summary">
        <nav aria-label="Você está em">
            <ol class="breadcrumb">
                <li><a href="<?= e(url('/loja')) ?>">Aromas</a></li>
                <?php if (!empty($produto['categoria_slug'])): ?>
                    <li><a href="<?= e(url('/loja?categoria=' . $produto['categoria_slug'])) ?>"><?= e($colecao) ?></a></li>
                <?php endif; ?>
                <li aria-current="page"><?= e($produto['nome']) ?></li>
            </ol>
        </nav>
        <h1 class="rotulo"><?= e($produto['nome']) ?></h1>
        <p class="product-line"><?= !empty($produto['aroma']) ? 'Aroma ' . e(mb_strtolower((string) $produto['aroma'])) . ', ' : '' ?>spray de ambiente, <?= e($volume) ?></p>

        <p class="price price-lg">
            <?php if (!empty($produto['preco_promocional'])): ?>
                <?= e(money($produto['preco_promocional'])) ?>
                <small class="price-was"><?= e(money($produto['preco'])) ?></small>
            <?php else: ?>
                <?= e(money($produto['preco'])) ?>
            <?php endif; ?>
        </p>
        <?php $pagamento = preco_pagamento((float) ($produto['preco_promocional'] ?: $produto['preco'])); ?>
        <?php if ($pagamento['pix'] !== null || $pagamento['parcelas']): ?>
            <p class="price-terms">
                <?php if ($pagamento['pix'] !== null): ?><span><strong><?= e(money($pagamento['pix'])) ?></strong> no Pix (<?= e(pct(desconto_pix())) ?> de desconto)</span><?php endif; ?>
                <?php if ($pagamento['parcelas']): ?><span>ou <?= (int) $pagamento['parcelas'] ?>x de <?= e(money($pagamento['parcela'])) ?> sem juros no cartão</span><?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($compraTipo === 'carrinho' && $disponivel): ?>
            <form method="post" action="<?= e(url('/carrinho/adicionar')) ?>" class="buy-form">
                <?= csrf_field() ?>
                <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">
                <div class="stepper" data-stepper>
                    <button type="button" data-passo="-1" aria-label="Diminuir quantidade">−</button>
                    <label class="visually-hidden" for="qtd">Quantidade</label>
                    <input id="qtd" type="number" name="quantidade" value="1" min="1" max="<?= min(20, max(1, $estoque)) ?>" inputmode="numeric">
                    <button type="button" data-passo="1" aria-label="Aumentar quantidade">+</button>
                </div>
                <button class="btn btn-gold" type="submit">Adicionar à sacola</button>
            </form>
            <ul class="product-assurance">
                <li>Sai do ateliê em até 3 dias úteis</li>
                <li><?= e(formas_pagamento()) ?> · troca em 7 dias</li>
                <?php if ($estoque <= 5): ?><li>Restam <?= $estoque ?> unidades deste lote</li><?php endif; ?>
            </ul>
        <?php elseif ($compraTipo === 'carrinho'): ?>
            <p class="product-stock is-off">Esgotado neste lote.</p>
            <p>Mande uma mensagem e a Geo avisa você quando o próximo lote ficar pronto.</p>
            <p><a class="btn btn-gold" href="<?= e(whatsapp_url('Olá! Quero ser avisada quando ' . ($produto['nome'] ?? 'este aroma') . ' voltar.')) ?>" target="_blank" rel="noopener">Avise-me pelo WhatsApp</a></p>
        <?php else: ?>
            <?php
            $urlCompra = match ($compraTipo) {
                'shopee' => shopee_url(),
                'amazon' => $produto['url_amazon'] ?? '',
                default => $produto['url_mercadolivre'] ?? '',
            };
            $rotulo = match ($compraTipo) {
                'shopee' => 'Comprar na Shopee',
                'amazon' => 'Comprar na Amazon',
                default => 'Comprar no Mercado Livre',
            };
            ?>
            <?php if ($urlCompra): ?>
                <p><a class="btn btn-gold" href="<?= e($urlCompra) ?>" target="_blank" rel="noopener"><?= e($rotulo) ?></a></p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="notes">
            <div class="note"><small>Saída</small><p><?= e($produto['notas_topo'] ?: '—') ?></p></div>
            <div class="note"><small>Corpo</small><p><?= e($produto['notas_coracao'] ?: '—') ?></p></div>
            <div class="note"><small>Fundo</small><p><?= e($produto['notas_fundo'] ?: '—') ?></p></div>
        </div>

        <?php if (!empty($produto['citacao'])): ?>
            <p class="product-quote">“<?= e($produto['citacao']) ?>”</p>
        <?php endif; ?>
        <?php if (!empty($produto['descricao'])): ?>
            <div class="product-desc"><p><?= nl2br(e($produto['descricao'])) ?></p></div>
        <?php endif; ?>

        <details class="accordion">
            <summary>Como usar</summary>
            <div class="accordion-body">
                <p><?= e($produto['modo_usar'] ?: 'Borrife no ambiente, a cerca de 20 cm, e espere o aroma assentar.') ?></p>
            </div>
        </details>
        <details class="accordion">
            <summary>Ficha e cuidados</summary>
            <div class="accordion-body">
                <p>Frasco de vidro com atomizador dourado, <?= e($volume) ?>.</p>
                <?php if (!empty($produto['ficha_tecnica'])): ?>
                    <p><?= nl2br(e($produto['ficha_tecnica'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($produto['precaucoes'])): ?>
                    <p><strong>Cuidados:</strong> <?= e($produto['precaucoes']) ?></p>
                <?php endif; ?>
            </div>
        </details>
    </div>
</article>

<?php if (!empty($depoimentos)): ?>
<section class="container">
    <h2>Quem usa o <?= e($produto['nome']) ?></h2>
    <div class="quotes" style="--c:<?= e($cor) ?>">
        <?php foreach ($depoimentos as $d): ?>
            <figure class="quote">
                <blockquote><p>“<?= e($d['texto']) ?>”</p></blockquote>
                <figcaption><strong><?= e($d['nome']) ?></strong></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($relacionados)): ?>
<section class="container">
    <div class="section-head"><h2>Outros aromas da <?= e($colecao) ?></h2></div>
    <div class="product-grid">
        <?php foreach ($relacionados as $p): View::partial('partials/product-card', ['p' => $p]); endforeach; ?>
    </div>
</section>
<?php endif; ?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $produto['nome'] ?? '',
    'description' => $produto['descricao_curta'] ?? '',
    'image' => $ogImage ?? asset($principal['caminho'] ?? ''),
    'brand' => ['@type' => 'Brand', 'name' => 'Elomiah'],
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'BRL',
        'price' => (string) ($produto['preco_promocional'] ?: $produto['preco'] ?? '0'),
        'availability' => !empty($disponivel) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
