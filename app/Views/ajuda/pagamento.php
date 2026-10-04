<?php
$vitrine = $vitrine ?? \App\Models\Configuracao::vitrine();
$cartao = !empty($cartao);
$desconto = desconto_pix();
?>
<section class="page-hero container">
    <h1>Pagamento</h1>
</section>
<section class="container prose">
    <h2>Pix</h2>
    <p>Ao finalizar, o QR Code e o código copia e cola aparecem na tela do pedido.<?php if ($desconto > 0): ?> No Pix você tem <?= e(pct($desconto)) ?> de desconto sobre o total do pedido, frete incluído.<?php endif; ?></p>
    <?php if ($cartao): ?>
        <h2>Cartão de crédito</h2>
        <p>Visa, Mastercard e Elo, pelo Mercado Pago, <?= $vitrine['parcelas'] > 1 ? 'em até ' . (int) $vitrine['parcelas'] . 'x sem juros' : 'em até 6x' ?>. O número do cartão vai direto ao Mercado Pago e não fica salvo no site.</p>
    <?php endif; ?>
    <h2>Confirmação</h2>
    <p>Assim que o pagamento é confirmado, você recebe um e-mail e o pedido entra na fila de despacho. O recibo fica disponível em <a href="<?= e(url('/conta')) ?>">Meus pedidos</a>.</p>
    <p>Ficou alguma dúvida? <a href="<?= e(url('/contato')) ?>">Fale com a gente</a>.</p>
</section>
