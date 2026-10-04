<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <h1>Cadastro</h1>
    <p class="lede">Crie a conta para comprar no site e ver status, peças e código de rastreio.</p>
</section>
<section class="container narrow">
    <form class="form" method="post" action="<?= e(url('/cadastro')) ?>">
        <?= csrf_field() ?>
        <label><span>Nome</span><input type="text" name="nome" value="<?= e((string) old('nome')) ?>" required></label>
        <label><span>E-mail</span><input type="email" name="email" value="<?= e((string) old('email')) ?>" required></label>
        <label><span>Telefone</span><input type="tel" name="telefone" value="<?= e((string) old('telefone')) ?>"></label>
        <label><span>Senha</span><input type="password" name="senha" required minlength="8"></label>
        <label><span>Confirmar senha</span><input type="password" name="senha_confirmation" required></label>
        <label class="check">
            <input type="checkbox" name="lgpd" value="1" required>
            <span>Li e aceito a <a href="<?= e(url('/privacidade')) ?>">Política de Privacidade</a>.</span>
        </label>
        <button class="btn btn-gold" type="submit">Criar conta</button>
    </form>
</section>
