<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <h1>Recuperar senha</h1>
    <p class="lede">Informe o e-mail da conta. Se ele existir neste refúgio, enviamos o caminho.</p>
</section>
<section class="container narrow-sm">
    <form class="form" method="post" action="<?= e(url('/recuperar-senha')) ?>">
        <?= csrf_field() ?>
        <label><span>E-mail</span><input type="email" name="email" required></label>
        <button class="btn btn-gold" type="submit">Enviar o caminho</button>
    </form>
    <p class="auth-links"><a href="<?= e(url('/entrar')) ?>">Voltar a entrar</a></p>
</section>
