<p class="field-hint">Altere a senha padrão no primeiro acesso. Não a deixe no README nem em conversas.</p>
<form class="form" method="post" action="<?= e(url('/admin/conta')) ?>" style="max-width:420px">
    <?= csrf_field() ?>
    <label><span>Senha atual</span><input type="password" name="senha_atual" required autocomplete="current-password"></label>
    <label><span>Nova senha</span><input type="password" name="senha" required minlength="8" autocomplete="new-password"></label>
    <label><span>Confirmar</span><input type="password" name="senha_confirmation" required minlength="8" autocomplete="new-password"></label>
    <button class="btn btn-gold" type="submit">Guardar senha</button>
</form>
