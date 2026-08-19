<?php
$rotulos = [
    'pendente' => 'Aguardando',
    'pago' => 'Pagos',
    'enviado' => 'Enviados',
    'entregue' => 'Entregues',
    'cancelado' => 'Cancelados',
];
?>
<div class="chip-row" style="margin-bottom:1rem">
    <a class="chip <?= ($status ?? '') === '' ? 'is-on' : '' ?>" href="<?= e(url('/admin/pedidos')) ?>">Todos</a>
    <?php foreach ($rotulos as $s => $label): ?>
        <a class="chip <?= ($status ?? '') === $s ? 'is-on' : '' ?>" href="<?= e(url('/admin/pedidos?status=' . $s)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>
<?php if (empty($pedidos)): ?>
    <p>Nenhum pedido neste filtro.</p>
<?php else: ?>
<table class="table">
    <thead>
    <tr>
        <th>Código</th>
        <th>Cliente</th>
        <th>Status</th>
        <th>Pagamento</th>
        <th>Total</th>
        <th>Data</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($pedidos as $p): ?>
        <?php $acao = pedido_status_acao($p['status'] ?? ''); ?>
        <tr>
            <td><a href="<?= e(url('/admin/pedidos/' . $p['id'])) ?>"><?= e($p['codigo']) ?></a></td>
            <td><?= e($p['nome_cliente'] ?? '') ?></td>
            <td><span class="status-pill status-<?= e($p['status']) ?>"><?= e(pedido_status_rotulo($p['status'])) ?></span></td>
            <td><?= e(pagamento_rotulo($p['metodo_pagamento'] ?? '')) ?></td>
            <td><?= e(money($p['total'])) ?></td>
            <td><?= e($p['created_at']) ?></td>
            <td class="pedido-acoes">
                <?php if ($acao && $acao['status'] === 'enviado'): ?>
                    <a class="btn btn-gold btn-compact" href="<?= e(url('/admin/pedidos/' . $p['id'])) ?>">Registrar envio</a>
                <?php elseif ($acao): ?>
                    <form method="post" action="<?= e(url('/admin/pedidos/' . $p['id'] . '/status')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="<?= e($acao['status']) ?>">
                        <input type="hidden" name="voltar" value="lista">
                        <button class="btn btn-gold btn-compact" type="submit"><?= e($acao['rotulo']) ?></button>
                    </form>
                <?php endif; ?>
                <a href="<?= e(url('/admin/pedidos/' . $p['id'])) ?>">Abrir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
