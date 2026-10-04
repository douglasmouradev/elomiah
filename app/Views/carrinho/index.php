<?php
\App\Core\View::partial('partials/flash');
$carrinho = $carrinho ?? ['items' => [], 'total' => 0];
$logado = \App\Core\Auth::check();
?>
<section class="page-hero container">
    <h1>Sacola</h1>
</section>
<section class="container">
    <?php if (empty($carrinho['items'])): ?>
        <div class="empty-state">
            <h2>Sua sacola está vazia</h2>
            <p>Os aromas ficam guardados aqui enquanto você navega, mesmo se fechar a página.</p>
            <a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver os aromas</a>
        </div>
    <?php else: ?>
        <?php
        $freteInfo = $freteInfo ?? frete();
        $estimado = $estimado ?? (($carrinho['total'] ?? 0) + $freteInfo['valor']);
        ?>
        <div class="cart-layout">
            <ul class="cart-list">
                <?php foreach ($carrinho['items'] as $item): $p = $item['produto']; $qty = (int) $item['qty']; ?>
                    <li class="cart-row" style="--c:<?= e((string) ($p['cor_destaque'] ?? '#A9853B')) ?>">
                        <img src="<?= e(asset($p['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="" width="88" height="110" loading="lazy">
                        <div>
                            <h2 class="rotulo"><a href="<?= e(url('/produto/' . $p['slug'])) ?>"><?= e($p['nome']) ?></a></h2>
                            <p class="cart-detail"><?= e(trim(($p['aroma'] ?? '') . ', ' . ($p['volume'] ?? '120 ml'), ', ')) ?>. <?= e(money($item['preco'])) ?> cada</p>
                            <div class="cart-controls">
                                <form method="post" action="<?= e(url('/carrinho/atualizar')) ?>" class="qty-step" aria-label="Quantidade de <?= e($p['nome']) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
                                    <button type="submit" name="quantidade" value="<?= max(1, $qty - 1) ?>" aria-label="Diminuir" <?= $qty <= 1 ? 'disabled' : '' ?>>−</button>
                                    <span><?= $qty ?></span>
                                    <button type="submit" name="quantidade" value="<?= min(20, $qty + 1) ?>" aria-label="Aumentar">+</button>
                                </form>
                                <form method="post" action="<?= e(url('/carrinho/remover')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
                                    <button class="cart-remove" type="submit">Remover</button>
                                </form>
                            </div>
                        </div>
                        <span class="cart-sub"><?= e(money($item['subtotal'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <aside class="cart-totals" aria-labelledby="resumo-titulo">
                <h2 id="resumo-titulo">Resumo</h2>
                <?php if (!empty($freteGratis['ativo'])): ?>
                    <div class="frete-gratis<?= $freteGratis['atingido'] ? ' is-ok' : '' ?>">
                        <p><?php if ($freteGratis['atingido']): ?>Você ganhou <strong>frete grátis</strong>.<?php else: ?>Faltam <strong><?= e(money($freteGratis['falta'])) ?></strong> para o frete grátis.<?php endif; ?></p>
                        <span class="frete-barra" aria-hidden="true"><i style="--pct:<?= (int) $freteGratis['pct'] ?>"></i></span>
                    </div>
                <?php endif; ?>
                <dl class="totals">
                    <div><dt>Aromas</dt><dd><?= e(money($carrinho['total'])) ?></dd></div>
                    <div><dt><?= e($freteInfo['nome']) ?></dt><dd><?= e(money($freteInfo['valor'])) ?></dd></div>
                    <div class="totals-total"><dt>Total</dt><dd><?= e(money($estimado)) ?></dd></div>
                </dl>
                <a class="btn btn-gold" href="<?= e(url('/checkout')) ?>"><?= $logado ? 'Finalizar compra' : 'Entrar e finalizar' ?></a>
                <p class="field-hint"><?= e(rtrim((string) $freteInfo['prazo'], '. ')) ?>.<?= $logado ? '' : ' Para finalizar você entra ou cria uma conta; a sacola continua aqui.' ?></p>
                <p class="field-hint"><?= e(formas_pagamento()) ?> · troca em 7 dias.</p>
            </aside>
        </div>
        <p><a class="text-back" href="<?= e(url('/loja')) ?>">Continuar escolhendo</a></p>
    <?php endif; ?>
</section>
