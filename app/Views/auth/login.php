<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <p class="eyebrow">Conta</p>
    <h1>Entrar</h1>
    <p class="lede lede-center">Entre para comprar e acompanhar o rastreio das suas peças.</p>
</section>
<section class="container" style="max-width:420px">
    <form class="form" method="post" action="<?= e(url('/entrar')) ?>">
        <?= csrf_field() ?>
        <label><span>E-mail</span><input type="email" name="email" required></label>
        <label><span>Senha</span><input type="password" name="senha" required></label>
        <button class="btn btn-gold" type="submit">Entrar</button>
    </form>
    <p style="margin-top:1.2rem">Ainda sem cadastro? <a href="<?= e(url('/cadastro')) ?>">Criar conta</a></p>
</section>
