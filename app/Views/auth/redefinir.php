<?php \App\Core\View::partial('partials/flash'); $token = $token ?? ''; ?>
<section class="page-hero container">
    <p class="eyebrow">Conta</p>
    <h1>Nova senha</h1>
    <p class="lede lede-center">Escolha uma senha com ao menos oito caracteres.</p>
</section>
<section class="container" style="max-width:420px">
    <form class="form" method="post" action="<?= e(url('/recuperar-senha/' . $token)) ?>">
        <?= csrf_field() ?>
        <label><span>Nova senha</span><input type="password" name="senha" required minlength="8"></label>
        <label><span>Confirmar</span><input type="password" name="senha_confirmation" required minlength="8"></label>
        <button class="btn btn-gold" type="submit">Guardar senha</button>
    </form>
</section>
