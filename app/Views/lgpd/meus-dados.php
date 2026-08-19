<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <p class="eyebrow">LGPD</p>
    <h1>Seus dados</h1>
    <p class="lede" style="margin-inline:auto">Solicite a exportação ou a exclusão das informações que mantemos sobre você. O titular dos dados responderá em até 15 dias.</p>
</section>
<section class="container" style="max-width:560px">
    <form class="form" method="post" action="<?= e(url('/meus-dados')) ?>">
        <?= csrf_field() ?>
        <label><span>Nome</span><input type="text" name="nome" value="<?= e($user['nome'] ?? '') ?>" required></label>
        <label><span>E-mail</span><input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" required></label>
        <label><span>Tipo</span>
            <select name="tipo">
                <option value="exportar">Exportar meus dados</option>
                <option value="excluir">Excluir meus dados</option>
            </select>
        </label>
        <label><span>Mensagem</span><textarea name="mensagem" placeholder="Contexto opcional"></textarea></label>
        <button class="btn btn-gold" type="submit">Enviar solicitação</button>
    </form>
</section>
