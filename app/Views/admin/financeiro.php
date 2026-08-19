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
