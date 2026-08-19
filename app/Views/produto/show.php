<?php
use App\Core\View;
View::partial('partials/flash');
$produto = $produto ?? [];
$imagens = $imagens ?: [['caminho' => 'images/frasco-despertar.webp', 'alt' => $produto['nome']]];
$principal = $imagens[0];
$cor = $produto['cor_destaque'] ?? '#1B4332';
$estoque = (int) ($produto['estoque'] ?? 0);
$disponivel = $estoque > 0;
$compraTipo = $produto['compra_tipo'] ?? 'carrinho';
?>
<section class="container product-page">
    <div class="gallery">
        <div class="gallery-main">
            <img src="<?= e(asset($principal['caminho'])) ?>" alt="<?= e($principal['alt'] ?? $produto['nome']) ?>">
        </div>
        <?php if (count($imagens) > 1): ?>
            <div class="thumbs">
                <?php foreach ($imagens as $i => $img): ?>
                    <button type="button" class="<?= $i === 0 ? 'is-on' : '' ?>" data-thumb="<?= e(asset($img['caminho'])) ?>">
                        <img src="<?= e(asset($img['caminho'])) ?>" alt="">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div>
        <p class="eyebrow"><?= e($produto['colecao'] ?? $produto['categoria_nome'] ?? '') ?></p>
        <p class="product-band" style="background:<?= e((string) $cor) ?>"><?= e($produto['nome']) ?></p>
        <?php if (!empty($produto['aroma'])): ?>
            <p class="product-aroma">Aroma <?= e($produto['aroma']) ?></p>
        <?php endif; ?>
        <?php if (!empty($produto['citacao'])): ?>
            <p class="lede">“<?= e($produto['citacao']) ?>”</p>
        <?php endif; ?>
        <p class="price price-lg">
            <?php if (!empty($produto['preco_promocional'])): ?>
                <?= e(money($produto['preco_promocional'])) ?>
                <small class="price-was"><?= e(money($produto['preco'])) ?></small>
            <?php else: ?>
                <?= e(money($produto['preco'])) ?>
            <?php endif; ?>
        </p>
        <p class="product-stock <?= $disponivel ? 'is-on' : 'is-off' ?>">
            <?= $disponivel ? 'Disponível · envio em até 3 dias úteis' : 'Esgotado neste momento' ?>
        </p>
        <p><?= nl2br(e($produto['descricao'] ?? '')) ?></p>

        <div class="notes">
            <div class="note"><small>Notas de saída</small><p><?= e($produto['notas_topo'] ?? '—') ?></p></div>
            <div class="note"><small>Notas de corpo</small><p><?= e($produto['notas_coracao'] ?? '—') ?></p></div>
            <div class="note"><small>Notas de fundo</small><p><?= e($produto['notas_fundo'] ?? '—') ?></p></div>
        </div>

        <ul class="facts">
            <li>Vidro · <?= e($produto['volume'] ?? '120 ml') ?></li>
            <li>Atomizador dourado</li>
            <li>Névoa em leque fino</li>
        </ul>

        <?php if ($compraTipo === 'carrinho' && $disponivel): ?>
            <form method="post" action="<?= e(url('/carrinho/adicionar')) ?>" class="form buy-form">
                <?= csrf_field() ?>
                <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">
                <label><span>Quantidade</span>
                    <input type="number" name="quantidade" value="1" min="1" max="<?= min(20, max(1, $estoque)) ?>">
                </label>
                <button class="btn btn-gold" type="submit">Adicionar à sacola</button>
            </form>
        <?php elseif ($compraTipo === 'carrinho'): ?>
            <p class="pay-safe">Avise-nos pelo WhatsApp quando quiser ser avisada do retorno.</p>
            <a class="btn btn-ghost" href="<?= e(whatsapp_url('Olá, quero ser avisada quando ' . ($produto['nome'] ?? 'este aroma') . ' voltar.')) ?>" target="_blank" rel="noopener">Conversar</a>
        <?php else: ?>
            <?php
            $urlCompra = match ($compraTipo) {
                'shopee' => shopee_url(),
                'amazon' => $produto['url_amazon'] ?? '',
                default => $produto['url_mercadolivre'] ?? '',
            };
            $rotulo = match ($compraTipo) {
                'shopee' => 'Comprar na vitrine da Geo',
                'amazon' => 'Comprar na Amazon',
                default => 'Comprar no Mercado Livre',
            };
            ?>
            <?php if ($urlCompra): ?>
                <a class="btn btn-gold" href="<?= e($urlCompra) ?>" target="_blank" rel="noopener"><?= e($rotulo) ?></a>
            <?php endif; ?>
        <?php endif; ?>

        <div class="ritual">
            <p class="eyebrow">O ritual</p>
            <ol>
                <li>Três névoas no ar, em leque.</li>
                <li>Deixe assentar no cômodo.</li>
                <li>Habite o espaço com o olfato acordado.</li>
            </ol>
            <?php if (!empty($produto['modo_usar'])): ?>
                <p><?= e($produto['modo_usar']) ?></p>
            <?php endif; ?>
        </div>

        <div class="tech">
            <p><strong>Spray de ambiente</strong> · <?= e($produto['volume'] ?? '120 ml') ?></p>
            <?php if (!empty($produto['precaucoes'])): ?>
                <p><strong>Precauções.</strong> <?= e($produto['precaucoes']) ?></p>
            <?php endif; ?>
            <p><?= nl2br(e($produto['ficha_tecnica'] ?? '')) ?></p>
        </div>
    </div>
</section>

<?php if (!empty($depoimentos)): ?>
<section class="container">
    <h2>Quem escolheu este aroma</h2>
    <div class="quotes">
        <?php foreach ($depoimentos as $d): ?>
            <blockquote class="quote">
                <div class="stars"><?= str_repeat('★', (int) $d['nota']) ?></div>
                <p>“<?= e($d['texto']) ?>”</p>
                <footer><?= e($d['nome']) ?></footer>
            </blockquote>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($relacionados)): ?>
<section class="container">
    <div class="section-head"><p class="eyebrow">Continuar</p><h2>Na mesma coleção</h2></div>
    <div class="product-grid">
        <?php foreach ($relacionados as $p): View::partial('partials/product-card', ['p' => $p]); endforeach; ?>
    </div>
</section>
<?php endif; ?>
