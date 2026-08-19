<?php
$pedido = $pedido ?? [];
$status = (string) ($pedido['status'] ?? 'pendente');
$rastreioLink = rastreio_url($pedido['codigo_rastreio'] ?? null, $pedido['transportadora'] ?? null);
$passos = ['pendente' => 'Pedido', 'pago' => 'Pago', 'enviado' => 'Enviado', 'entregue' => 'Entregue'];
$ordem = array_keys($passos);
$idx = array_search($status, $ordem, true);
\App\Core\View::partial('partials/flash');
?>
<section class="page-hero container">
    <p class="eyebrow">Pedido</p>
    <h1><?= e($pedido['codigo'] ?? '') ?></h1>
    <p class="lede lede-center"><?= e(pedido_status_rotulo($status)) ?></p>
</section>
<section class="container account-page">
    <?php if ($status !== 'cancelado'): ?>
        <ol class="order-track">
            <?php foreach ($passos as $chave => $rotulo): ?>
                <?php
                $i = array_search($chave, $ordem, true);
                $classe = ($idx !== false && $i < $idx) ? 'is-done' : (($idx !== false && $i === $idx) ? 'is-on' : '');
                ?>
                <li class="<?= $classe ?>"><?= e($rotulo) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <ul class="account-items account-items-lg">
        <?php foreach ($pedido['itens'] ?? [] as $item): ?>
            <li>
                <img src="<?= e(asset($item['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="">
                <span>
                    <strong><?= e($item['nome_produto']) ?></strong>
                    <small><?= (int) $item['quantidade'] ?> × <?= e(money($item['preco_unitario'])) ?> · <?= e(money($item['subtotal'])) ?></small>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <p class="account-total">Total <?= e(money($pedido['total'] ?? 0)) ?> · <?= e(pagamento_rotulo($pedido['metodo_pagamento'] ?? '')) ?></p>
    <?php $freteInfo = frete(); ?>
    <p class="field-hint"><?= e($freteInfo['nome']) ?> <?= e(money($pedido['frete'] ?? $freteInfo['valor'])) ?> · <?= e($freteInfo['prazo']) ?></p>

    <?php if (!empty($pedido['codigo_rastreio'])): ?>
        <div class="account-rastreio">
            <p class="eyebrow">Rastreio</p>
            <p><?= e($pedido['transportadora'] ?: 'Correios') ?> · <strong><?= e($pedido['codigo_rastreio']) ?></strong></p>
            <?php if ($rastreioLink): ?>
                <p><a class="btn btn-gold" href="<?= e($rastreioLink) ?>" target="_blank" rel="noopener">Acompanhar nos Correios</a></p>
            <?php endif; ?>
        </div>
    <?php elseif ($status === 'pago'): ?>
        <p class="pay-safe">O pagamento entrou. Quando o ateliê despachar, o código de rastreio aparece aqui.</p>
    <?php elseif ($status === 'pendente'): ?>
        <p><a class="btn btn-gold" href="<?= e(url('/pedido/' . $pedido['codigo'])) ?>">Concluir pagamento</a></p>
    <?php endif; ?>

    <?php if (!empty($pedido['endereco'])): $end = $pedido['endereco']; ?>
        <p class="field-hint">
            Entrega: <?= e($end['logradouro']) ?>, <?= e($end['numero']) ?>
            <?= e($end['complemento'] ?? '') ?> — <?= e($end['bairro']) ?>, <?= e($end['cidade']) ?>/<?= e($end['estado']) ?>
        </p>
    <?php endif; ?>

    <p><a class="btn btn-ghost" href="<?= e(url('/conta')) ?>">Todos os pedidos</a></p>
</section>
