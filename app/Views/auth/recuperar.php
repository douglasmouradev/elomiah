<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <p class="eyebrow">Conta</p>
    <h1>Recuperar senha</h1>
    <p class="lede lede-center">Informe o e-mail da conta. Se ele existir neste refúgio, enviamos o caminho.</p>
</section>
<section class="container" style="max-width:420px">
    <form class="form" method="post" action="<?= e(url('/recuperar-senha')) ?>">
        <?= csrf_field() ?>
        <label><span>E-mail</span><input type="email" name="email" required></label>
        <button class="btn btn-gold" type="submit">Enviar o caminho</button>
    </form>
    <p style="margin-top:1.2rem"><a href="<?= e(url('/entrar')) ?>">Voltar a entrar</a></p>
</section>
