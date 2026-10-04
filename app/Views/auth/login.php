<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <h1>Entrar</h1>
    <p class="lede">Entre para comprar e acompanhar o rastreio das suas peças.</p>
</section>
<section class="container narrow-sm">
    <form class="form" method="post" action="<?= e(url('/entrar')) ?>">
        <?= csrf_field() ?>
        <label><span>E-mail</span><input type="email" name="email" required></label>
        <label><span>Senha</span><input type="password" name="senha" required></label>
        <button class="btn btn-gold" type="submit">Entrar</button>
    </form>
        <p class="auth-links">Ainda sem cadastro? <a href="<?= e(url('/cadastro')) ?>">Criar conta</a></p>
        <p><a href="<?= e(url('/recuperar-senha')) ?>">Esqueci a senha</a></p>
</section>
