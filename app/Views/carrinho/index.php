<?php
\App\Core\View::partial('partials/flash');
$carrinho = $carrinho ?? ['items' => [], 'total' => 0];
?>
<section class="page-hero container">
    <p class="eyebrow">Sacola</p>
    <h1>O que você escolheu</h1>
</section>
<section class="container">
    <?php if (empty($carrinho['items'])): ?>
        <div class="cart-empty">
            <p class="lede lede-center">A sacola está em silêncio.</p>
            <p><a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ir à coleção</a></p>
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr><th>Peça</th><th>Qtd</th><th>Subtotal</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($carrinho['items'] as $item): $p = $item['produto']; ?>
                <tr>
                    <td>
                        <a class="cart-piece" href="<?= e(url('/produto/' . $p['slug'])) ?>">
                            <img src="<?= e(asset($p['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="">
                            <span>
                                <strong><?= e($p['nome']) ?></strong>
                                <small><?= e(trim(($p['aroma'] ?? '') . ' · ' . ($p['volume'] ?? '120 ml'), ' ·')) ?></small>
                            </span>
                        </a>
                    </td>
                    <td>
                        <form method="post" action="<?= e(url('/carrinho/atualizar')) ?>" class="qty-step">
                            <?= csrf_field() ?>
                            <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
                            <button type="submit" name="quantidade" value="<?= max(1, (int) $item['qty'] - 1) ?>" aria-label="Diminuir">−</button>
                            <span><?= (int) $item['qty'] ?></span>
                            <button type="submit" name="quantidade" value="<?= min(20, (int) $item['qty'] + 1) ?>" aria-label="Aumentar">+</button>
                        </form>
                    </td>
                    <td><?= e(money($item['subtotal'])) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('/carrinho/remover')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="produto_id" value="<?= (int) $p['id'] ?>">
                            <button class="cart-remove" type="submit">Retirar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="cart-footer">
            <?php
            $freteInfo = $freteInfo ?? frete();
            $estimado = $estimado ?? (($carrinho['total'] ?? 0) + $freteInfo['valor']);
            ?>
            <div class="cart-totals">
                <p>Peças <?= e(money($carrinho['total'])) ?></p>
                <p><?= e($freteInfo['nome']) ?> <?= e(money($freteInfo['valor'])) ?></p>
                <p class="field-hint"><?= e($freteInfo['prazo']) ?></p>
                <p class="price price-lg">Estimativa <?= e(money($estimado)) ?></p>
            </div>
            <?php if (!\App\Core\Auth::check()): ?>
                <p class="field-hint">O checkout pede conta. A sacola permanece se você entrar ou se cadastrar agora.</p>
            <?php endif; ?>
            <a class="btn btn-gold" href="<?= e(url('/checkout')) ?>"><?= \App\Core\Auth::check() ? 'Seguir para o checkout' : 'Entrar e concluir' ?></a>
        </div>
    <?php endif; ?>
</section>
