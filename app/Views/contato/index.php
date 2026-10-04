<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <h1>Contato</h1>
    <p class="lede">Dúvidas sobre aromas, pedidos ou o curso. Pelo WhatsApp a resposta costuma ser mais rápida.</p>
</section>
<section class="container split-2">
    <form class="form" method="post" action="<?= e(url('/contato')) ?>">
        <?= csrf_field() ?>
        <label><span>Nome</span><input type="text" name="nome" value="<?= e((string) old('nome')) ?>" required autocomplete="name"></label>
        <label><span>E-mail</span><input type="email" name="email" value="<?= e((string) old('email')) ?>" required autocomplete="email"></label>
        <label><span>Telefone (opcional)</span><input type="tel" name="telefone" value="<?= e((string) old('telefone')) ?>" autocomplete="tel"></label>
        <label><span>Assunto</span><input type="text" name="assunto" value="<?= e((string) old('assunto')) ?>" placeholder="Ex.: troca, pedido, indicação de aroma"></label>
        <label><span>Mensagem</span><textarea name="mensagem" required><?= e((string) old('mensagem')) ?></textarea></label>
        <button class="btn btn-gold" type="submit">Enviar mensagem</button>
    </form>
    <aside>
        <h2>Outros canais</h2>
        <p><a class="btn btn-ghost" href="<?= e(whatsapp_url('Olá, vim pelo site da Elomiah.')) ?>" target="_blank" rel="noopener">Conversar no WhatsApp</a></p>
        <p><a href="https://instagram.com/elomiah" target="_blank" rel="noopener">Instagram @elomiah</a></p>
        <p class="field-hint">Para pedir acesso, correção ou exclusão dos seus dados pessoais, use a página <a href="<?= e(url('/meus-dados')) ?>">Seus dados</a>.</p>
    </aside>
</section>
