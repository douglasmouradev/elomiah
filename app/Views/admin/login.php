<div class="admin-card">
    <img src="<?= e(asset('images/logo.png')) ?>?v=2" alt="Elomiah" style="height:110px;margin:0 auto 1rem">
    <h1 style="font-family:Playfair Display,serif;letter-spacing:.35em;font-size:1.1rem">ATELIÊ</h1>
    <?php if ($msg = flash('error')): ?><div class="alert alert-err"><?= e($msg) ?></div><?php endif; ?>
    <form class="form" method="post" action="<?= e(url('/admin/login')) ?>" style="text-align:left;margin-top:1.5rem">
        <?= csrf_field() ?>
        <label><span>E-mail</span><input type="email" name="email" required></label>
        <label><span>Senha</span><input type="password" name="senha" required></label>
        <button class="btn btn-gold" type="submit" style="width:100%">Entrar</button>
    </form>
</div>
