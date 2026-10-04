<?php
$temToken = !empty($temToken);
$temPublica = !empty($temPublica);
?>
<p class="field-hint">Pix já cai na chave da Elomiah, nesta página. O cartão confirma no próprio pedido quando o Mercado Pago estiver ligado.</p>

<form class="form" method="post" action="<?= e(url('/admin/pagamento')) ?>" style="max-width:560px">
    <?= csrf_field() ?>
    <label><span>Access Token do Mercado Pago<?= $temToken ? ' · ' . e((string) ($tokenMascara ?? '')) : '' ?></span>
        <input type="password" name="mercadopago_access_token" autocomplete="off" placeholder="<?= $temToken ? 'Deixe em branco para manter' : 'APP_USR-… ou TEST-…' ?>">
    </label>
    <label><span>Chave pública<?= $temPublica ? ' · ' . e((string) ($publicaMascara ?? '')) : '' ?></span>
        <input type="text" name="mercadopago_public_key" autocomplete="off" placeholder="<?= $temPublica ? 'Deixe em branco para manter' : 'APP_USR-… public key' ?>">
    </label>
    <p class="field-hint">Crie a aplicação em <a href="https://www.mercadopago.com.br/developers/panel/app" target="_blank" rel="noopener">developers.mercadopago.com</a>. A chave pública deixa o cartão nesta página; só o token abre o checkout do Mercado Pago.</p>
    <p>
        <button class="btn btn-gold" type="submit">Guardar</button>
    </p>
    <?php if ($temToken || $temPublica): ?>
        <label class="check">
            <input type="checkbox" name="limpar_mp" value="1">
            <span>Remover as chaves guardadas aqui</span>
        </label>
    <?php endif; ?>
</form>

<h2 style="margin-top:2.4rem">Pix</h2>
<?php $pix = $pix ?? ['chave' => '', 'nome' => 'ELOMIAH', 'cidade' => 'SAO PAULO']; ?>
<p class="field-hint">Chave que aparece no QR quando o Mercado Pago não está ligado. A cliente já vê esta chave na página do pedido.</p>
<form class="form" method="post" action="<?= e(url('/admin/pix')) ?>" style="max-width:560px">
    <?= csrf_field() ?>
    <label><span>Chave Pix</span>
        <input type="text" name="pix_chave" value="<?= e((string) ($pix['chave'] ?? '')) ?>" maxlength="80" required>
    </label>
    <label><span>Nome no Pix</span>
        <input type="text" name="pix_nome" value="<?= e((string) ($pix['nome'] ?? '')) ?>" maxlength="25" required>
    </label>
    <label><span>Cidade</span>
        <input type="text" name="pix_cidade" value="<?= e((string) ($pix['cidade'] ?? '')) ?>" maxlength="15" required>
    </label>
    <p>
        <button class="btn btn-gold" type="submit">Guardar Pix</button>
    </p>
</form>

<h2 style="margin-top:2.4rem">Emitente do recibo</h2>
<?php $loja = $loja ?? ['razao' => 'Elomiah', 'cnpj' => '', 'ie' => '', 'endereco' => '']; ?>
<p class="field-hint">Aparece no recibo de compra enviado por e-mail. CNPJ e endereço são opcionais.</p>
<form class="form" method="post" action="<?= e(url('/admin/loja')) ?>" style="max-width:560px">
    <?= csrf_field() ?>
    <label><span>Razão / nome</span>
        <input type="text" name="loja_razao" value="<?= e((string) ($loja['razao'] ?? '')) ?>" maxlength="120" required>
    </label>
    <label><span>CNPJ</span>
        <input type="text" name="loja_cnpj" value="<?= e((string) ($loja['cnpj'] ?? '')) ?>" maxlength="32">
    </label>
    <label><span>Inscrição estadual</span>
        <input type="text" name="loja_ie" value="<?= e((string) ($loja['ie'] ?? '')) ?>" maxlength="32">
    </label>
    <label><span>Endereço do ateliê</span>
        <input type="text" name="loja_endereco" value="<?= e((string) ($loja['endereco'] ?? '')) ?>" maxlength="180">
    </label>
    <p>
        <button class="btn btn-gold" type="submit">Guardar emitente</button>
    </p>
</form>

<h2 style="margin-top:2.4rem">Despacho</h2>
<?php $frete = $frete ?? frete(); ?>
<p class="field-hint">Valor, nome e prazo que aparecem no checkout das névoas. Formação digital não cobra este despacho.</p>
<form class="form" method="post" action="<?= e(url('/admin/despacho')) ?>" style="max-width:560px">
    <?= csrf_field() ?>
    <label><span>Nome</span>
        <input type="text" name="frete_nome" value="<?= e((string) ($frete['nome'] ?? '')) ?>" maxlength="80" required>
    </label>
    <label><span>Valor (R$)</span>
        <input type="text" name="frete_padrao" value="<?= e(number_format((float) ($frete['valor'] ?? 0), 2, ',', '')) ?>" required>
    </label>
    <label><span>Prazo</span>
        <input type="text" name="frete_prazo" value="<?= e((string) ($frete['prazo'] ?? '')) ?>" maxlength="180" required>
    </label>
    <p>
        <button class="btn btn-gold" type="submit">Guardar despacho</button>
    </p>
</form>

<h2 style="margin-top:2.4rem">Vitrine da loja</h2>
<?php $vitrine = $vitrine ?? ['frete_gratis' => 0, 'avisos' => [], 'atendimento' => '']; ?>
<p class="field-hint">Cada item só aparece no site depois de preenchido. Deixe em branco para esconder.</p>
<form class="form" method="post" action="<?= e(url('/admin/vitrine')) ?>" style="max-width:560px">
    <?= csrf_field() ?>
    <label><span>Frete grátis acima de (R$)</span>
        <input type="text" name="frete_gratis_acima" inputmode="decimal" value="<?= $vitrine['frete_gratis'] > 0 ? e(number_format((float) $vitrine['frete_gratis'], 2, ',', '')) : '' ?>" placeholder="Ex.: 250,00 — vazio desliga">
    </label>
    <p class="field-hint">Com valor, a sacola mostra quanto falta e o checkout zera o despacho ao atingir.</p>
    <label><span>Avisos da faixa do topo (um por linha, até 3)</span>
        <textarea name="faixa_avisos" rows="3" placeholder="Ex.: Lote de outubro disponível"><?= e(implode("\n", $vitrine['avisos'])) ?></textarea>
    </label>
    <p class="field-hint">Com frete grátis ligado, o aviso do frete entra sozinho na faixa.</p>
    <label><span>Horário de atendimento</span>
        <input type="text" name="atendimento_horario" value="<?= e((string) $vitrine['atendimento']) ?>" maxlength="120" placeholder="Ex.: Seg a sex, 9h às 18h">
    </label>
    <p class="field-hint">Razão social e CNPJ do rodapé vêm do bloco “Emitente do recibo”. O CNPJ só aparece se estiver preenchido.</p>
    <p>
        <button class="btn btn-gold" type="submit">Guardar vitrine</button>
    </p>
</form>
