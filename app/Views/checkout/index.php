<?php
\App\Core\View::partial('partials/flash');
$user = $user ?? null;
$carrinho = $carrinho ?? ['items' => []];
$mpPronto = !empty($mpPronto);
$endereco = $endereco ?? null;
$requerEnvio = $requerEnvio ?? carrinho_requer_envio($carrinho);
$freteInfo = $freteInfo ?? frete();
?>
<section class="page-hero container">
    <h1>Finalizar compra</h1>
    <p class="lede"><?= $requerEnvio ? 'Dois passos: para onde enviar e como pagar. Depois você acompanha o envio em Minha conta.' : 'Dois passos: seus dados e o pagamento. O acesso ao curso libera nesta conta.' ?></p>
</section>
<section class="container checkout-layout">
    <form class="form" method="post" action="<?= e(url('/checkout')) ?>" data-checkout>
        <?= csrf_field() ?>
        <ol class="checkout-steps" aria-label="Etapas">
            <li data-step-label="1"><?= $requerEnvio ? 'Destino' : 'Seus dados' ?></li>
            <li data-step-label="2">Pagamento</li>
        </ol>

        <div data-step="1">
            <h2><?= $requerEnvio ? 'Quem recebe' : 'Quem se matricula' ?></h2>
            <label><span>Nome</span><input type="text" name="nome" value="<?= e((string) old('nome', $user['nome'] ?? '')) ?>" required></label>
            <label><span>E-mail</span><input type="email" name="email" value="<?= e((string) old('email', $user['email'] ?? '')) ?>" required></label>
            <label><span>Telefone</span><input type="tel" name="telefone" value="<?= e((string) old('telefone', $user['telefone'] ?? '')) ?>" required></label>

            <?php if ($requerEnvio): ?>
            <h2 class="form-block">Endereço</h2>
            <?php if ($endereco): ?>
                <p class="field-hint">Preenchemos com o último endereço desta conta. Altere se o envio for para outro lugar.</p>
            <?php endif; ?>
            <label><span>CEP</span>
                <input type="text" name="cep" id="cep" maxlength="9" value="<?= e((string) old('cep', cep_format($endereco['cep'] ?? ''))) ?>" required autocomplete="postal-code">
            </label>
            <p id="cep-hint" class="field-hint">Ao informar o CEP, rua, bairro, cidade e estado se preenchem sozinhos.</p>
            <label><span>Rua</span><input type="text" name="logradouro" id="logradouro" value="<?= e((string) old('logradouro', $endereco['logradouro'] ?? '')) ?>" required></label>
            <div class="split-2">
                <label><span>Número</span><input type="text" name="numero" id="numero" value="<?= e((string) old('numero', $endereco['numero'] ?? '')) ?>" required></label>
                <label><span>Complemento</span><input type="text" name="complemento" value="<?= e((string) old('complemento', $endereco['complemento'] ?? '')) ?>"></label>
            </div>
            <label><span>Bairro</span><input type="text" name="bairro" id="bairro" value="<?= e((string) old('bairro', $endereco['bairro'] ?? '')) ?>" required></label>
            <div class="split-2">
                <label><span>Cidade</span><input type="text" name="cidade" id="cidade" value="<?= e((string) old('cidade', $endereco['cidade'] ?? '')) ?>" required></label>
                <label><span>Estado</span><input type="text" name="estado" id="estado" maxlength="2" value="<?= e((string) old('estado', $endereco['estado'] ?? '')) ?>" required></label>
            </div>
            <?php else: ?>
            <p class="field-hint">Formação digital: sem despacho. O acesso libera nesta conta depois do pagamento.</p>
            <?php endif; ?>
            <label><span>Observações</span><textarea name="observacoes"><?= e((string) old('observacoes')) ?></textarea></label>
            <button class="btn btn-gold" type="button" data-to-pay>Continuar para o pagamento</button>
        </div>

        <div data-step="2">
            <button class="text-back" type="button" data-to-dest><?= $requerEnvio ? 'Voltar ao destino' : 'Voltar aos dados' ?></button>
            <h2>Pagamento</h2>
            <?php
            $metodo = (string) old('metodo_pagamento', 'pix');
            if (empty($mpPronto)) {
                $metodo = 'pix';
            }
            ?>
            <div class="pay-methods" role="radiogroup" aria-label="Forma de pagamento">
                <label class="pay-option">
                    <input type="radio" name="metodo_pagamento" value="pix" <?= $metodo !== 'cartao' ? 'checked' : '' ?>>
                    <span class="pay-option-title">Pix</span>
                    <span class="pay-option-hint">QR Code na próxima tela</span>
                </label>
                <label class="pay-option">
                    <input type="radio" name="metodo_pagamento" value="cartao" <?= $metodo === 'cartao' ? 'checked' : '' ?><?= empty($mpPronto) ? ' disabled' : '' ?>>
                    <span class="pay-option-title">Cartão de crédito</span>
                    <span class="pay-option-hint"><?= !empty($mpPronto) ? 'Visa, Mastercard, Elo · até 6x' : 'Indisponível no momento' ?></span>
                </label>
            </div>
            <p class="pay-safe"><?= !empty($mpPronto) ? 'O número do cartão vai direto ao Mercado Pago e não fica salvo no site.' : 'O Pix é pago na chave da Elomiah e confirmado na tela do pedido.' ?></p>
            <label class="check">
                <input type="checkbox" name="lgpd" value="1" required>
                <span>Li e aceito a <a href="<?= e(url('/privacidade')) ?>" target="_blank">Política de Privacidade</a> e os <a href="<?= e(url('/termos')) ?>" target="_blank">Termos de Uso</a>. Autorizo o uso dos meus dados para processar este pedido.</span>
            </label>
            <button class="btn btn-gold" type="submit">Confirmar pedido e pagar</button>
        </div>
    </form>
    <aside class="checkout-summary">
        <h2>Resumo</h2>
        <ul>
            <?php foreach ($carrinho['items'] as $item): $p = $item['produto']; ?>
                <li>
                    <img src="<?= e(asset($p['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="" width="52" height="65">
                    <span>
                        <?= e($p['nome']) ?> × <?= (int) $item['qty'] ?>
                        <small><?= e(money($item['subtotal'])) ?></small>
                    </span>
                </li>
            <?php endforeach; ?>
        </ul>
        <dl class="totals">
            <div><dt>Subtotal</dt><dd><?= e(money($carrinho['total'])) ?></dd></div>
            <div><dt><?= e($freteInfo['nome']) ?></dt><dd><?= e(money($freteInfo['valor'])) ?></dd></div>
            <div class="totals-total"><dt>Total</dt><dd><?= e(money($total ?? 0)) ?></dd></div>
        </dl>
        <p class="field-hint"><?= e($freteInfo['prazo']) ?></p>
        <p class="field-hint">Pix ou cartão · troca em 7 dias.</p>
    </aside>
</section>
