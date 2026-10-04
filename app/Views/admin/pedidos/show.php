<?php
$pedido = $pedido ?? [];
$status = (string) ($pedido['status'] ?? 'pendente');
$digital = pedido_so_digital($pedido);
$acao = pedido_status_acao($status, $digital);
$passos = $digital ? ['pendente', 'pago'] : ['pendente', 'pago', 'enviado', 'entregue'];
$idx = array_search($status, $passos, true);
$msgEnvio = 'Olá, ' . ($pedido['nome_cliente'] ?? '') . '. Seu pedido ' . ($pedido['codigo'] ?? '') . ' da Elomiah foi enviado'
    . (!empty($pedido['codigo_rastreio']) ? '. Rastreio: ' . $pedido['codigo_rastreio'] : '')
    . '.';
?>
    <p class="pedido-kicker">
    <span class="status-pill status-<?= e($status) ?>"><?= e(pedido_status_rotulo($status, $digital)) ?></span>
    <?= e($pedido['codigo']) ?> · <?= e($pedido['created_at']) ?>
</p>

<ol class="pedido-pipeline" aria-label="Andamento do pedido">
    <?php foreach ($passos as $i => $passo): ?>
        <?php
        $classe = '';
        if ($status === 'cancelado') {
            $classe = $i === 0 ? 'is-done' : '';
        } elseif ($idx !== false && $i < $idx) {
            $classe = 'is-done';
        } elseif ($idx !== false && $i === $idx) {
            $classe = 'is-on';
        }
        ?>
        <li class="<?= $classe ?>"><?= e(pedido_status_rotulo($passo, $digital)) ?></li>
    <?php endforeach; ?>
</ol>

<p><?= e($pedido['nome_cliente']) ?> · <?= e($pedido['email_cliente']) ?> · <?= e($pedido['telefone_cliente']) ?></p>
<?php if (!empty($pedido['endereco'])): $end = $pedido['endereco']; ?>
    <p><?= e($end['logradouro']) ?>, <?= e($end['numero']) ?> <?= e($end['complemento'] ?? '') ?> — <?= e($end['bairro']) ?>, <?= e($end['cidade']) ?>/<?= e($end['estado']) ?> · CEP <?= e(cep_format($end['cep'] ?? '')) ?></p>
<?php endif; ?>
<?php if (!empty($pedido['observacoes'])): ?>
    <p>Observações: <?= e($pedido['observacoes']) ?></p>
<?php endif; ?>

<table class="table">
    <thead><tr><th>Item</th><th>Qtd</th><th>Unitário</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php foreach ($pedido['itens'] ?? [] as $i): ?>
        <tr>
            <td><?= e($i['nome_produto']) ?></td>
            <td><?= (int) $i['quantidade'] ?></td>
            <td><?= e(money($i['preco_unitario'])) ?></td>
            <td><?= e(money($i['subtotal'])) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php $freteInfo = $digital ? ['nome' => 'Acesso digital', 'prazo' => 'Sem despacho'] : frete(); ?>
<p>Subtotal <?= e(money($pedido['subtotal'])) ?> · <?= e($freteInfo['nome']) ?> <?= e(money($pedido['frete'])) ?><?php if ((float) ($pedido['desconto'] ?? 0) > 0): ?> · Desconto Pix − <?= e(money($pedido['desconto'])) ?><?php endif; ?> · <strong>Total <?= e(money($pedido['total'])) ?></strong></p>
<?php if (!$digital): ?>
<p class="field-hint"><?= e($freteInfo['prazo']) ?></p>
<?php endif; ?>
<p>
    Pagamento: <?= e(pagamento_rotulo($pedido['metodo_pagamento'] ?? '')) ?>
    <?php if (($pedido['metodo_pagamento'] ?? '') === 'cartao'): ?>
        · <?= e(\App\Support\CartaoCredito::bandeiraRotulo((string) ($pedido['cartao_bandeira'] ?? ''))) ?>
        <?php if (!empty($pedido['cartao_final'])): ?>final <?= e($pedido['cartao_final']) ?><?php endif; ?>
        <?php if (!empty($pedido['parcelas'])): ?> · <?= (int) $pedido['parcelas'] ?>x<?php endif; ?>
    <?php endif; ?>
    <?php if (!empty($pedido['mp_payment_id'])): ?>
        · MP #<?= e($pedido['mp_payment_id']) ?>
        <?php if (!empty($pedido['mp_status'])): ?>(<?= e($pedido['mp_status']) ?>)<?php endif; ?>
    <?php endif; ?>
</p>
<?php if ($status === 'pendente' && ($pedido['metodo_pagamento'] ?? '') === 'pix' && empty($pedido['mp_payment_id'])): ?>
    <p class="field-hint">Pix direto para a chave da Elomiah. Confira na Nubank e marque como <strong>pago</strong>.</p>
<?php elseif ($status === 'pendente'): ?>
    <p class="field-hint">O Mercado Pago confirma sozinho quando o banco autorizar. Use <strong>Marcar como pago</strong> só se o valor já entrou e o site ainda não atualizou.</p>
<?php endif; ?>
<?php if ($digital && in_array($status, ['pago', 'enviado', 'entregue'], true)): ?>
    <?php $urlAulas = \App\Models\Curso::urlAcesso(); ?>
    <p class="field-hint">Formação digital. <?= $urlAulas !== '' ? 'O acesso já está no e-mail e na conta da aluna.' : 'Coloque o link das aulas em Curso para a aluna abrir as aulas.' ?></p>
<?php endif; ?>
<?php if (!$digital && (!empty($pedido['codigo_rastreio']) || !empty($pedido['transportadora']))): ?>
    <p>Envio: <?= e($pedido['transportadora'] ?: 'Correios') ?><?php if (!empty($pedido['codigo_rastreio'])): ?> · rastreio <?= e($pedido['codigo_rastreio']) ?><?php endif; ?></p>
<?php endif; ?>

<section class="pedido-ops">
    <h2>Atualizar status</h2>
    <?php if ($acao): ?>
        <form method="post" action="<?= e(url('/admin/pedidos/' . $pedido['id'] . '/status')) ?>" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="<?= e($acao['status']) ?>">
            <?php if ($acao['status'] === 'enviado'): ?>
                <label><span>Transportadora</span>
                    <input type="text" name="transportadora" value="<?= e((string) ($pedido['transportadora'] ?? 'Correios')) ?>" placeholder="Correios">
                </label>
                <label><span>Código de rastreio</span>
                    <input type="text" name="codigo_rastreio" value="<?= e((string) ($pedido['codigo_rastreio'] ?? '')) ?>" placeholder="AA123456789BR">
                </label>
            <?php endif; ?>
            <button class="btn btn-gold" type="submit"><?= e($acao['rotulo']) ?></button>
        </form>
    <?php endif; ?>

    <form method="post" action="<?= e(url('/admin/pedidos/' . $pedido['id'] . '/status')) ?>" class="form pedido-ops-more">
        <?= csrf_field() ?>
        <label><span>Ou escolher outro status</span>
            <select name="status">
                <?php foreach (($digital ? ['pendente','pago','cancelado'] : ['pendente','pago','enviado','entregue','cancelado']) as $s): ?>
                    <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(pedido_status_rotulo($s, $digital)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php if (!$digital): ?>
        <label><span>Transportadora</span>
            <input type="text" name="transportadora" value="<?= e((string) ($pedido['transportadora'] ?? '')) ?>">
        </label>
        <label><span>Código de rastreio</span>
            <input type="text" name="codigo_rastreio" value="<?= e((string) ($pedido['codigo_rastreio'] ?? '')) ?>">
        </label>
        <?php endif; ?>
        <button class="btn btn-ghost" type="submit">Guardar status</button>
    </form>

    <?php if ($status !== 'cancelado'): ?>
        <form method="post" action="<?= e(url('/admin/pedidos/' . $pedido['id'] . '/status')) ?>" onsubmit="return confirm('Cancelar este pedido e devolver o estoque?')">
            <?= csrf_field() ?>
            <input type="hidden" name="status" value="cancelado">
            <button class="btn btn-ghost" type="submit">Cancelar pedido</button>
        </form>
    <?php endif; ?>

    <?php if (in_array($status, ['pago', 'enviado', 'entregue'], true)): ?>
        <p>
            <a class="btn btn-ghost" href="<?= e(url('/pedido/' . ($pedido['codigo'] ?? '') . '/nota')) ?>">Ver o recibo</a>
            <a class="btn btn-ghost" href="<?= e(pedido_whatsapp_cliente($pedido['telefone_cliente'] ?? '', $status === 'enviado' ? $msgEnvio : 'Olá, ' . ($pedido['nome_cliente'] ?? '') . '. Sobre o pedido ' . ($pedido['codigo'] ?? '') . ' da Elomiah.')) ?>" target="_blank" rel="noopener">
                Avisar no WhatsApp
            </a>
        </p>
    <?php endif; ?>
</section>
