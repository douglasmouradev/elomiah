<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <p class="eyebrow">Presença</p>
    <h1>Contato</h1>
    <p class="lede" style="margin-inline:auto">Escreva com calma. Respondemos no tempo de quem formula, não no de quem dispara cupom.</p>
</section>
<section class="container split-2">
    <form class="form" method="post" action="<?= e(url('/contato')) ?>">
        <?= csrf_field() ?>
        <label><span>Nome</span><input type="text" name="nome" value="<?= e((string) old('nome')) ?>" required></label>
        <label><span>E-mail</span><input type="email" name="email" value="<?= e((string) old('email')) ?>" required></label>
        <label><span>Telefone</span><input type="tel" name="telefone" value="<?= e((string) old('telefone')) ?>"></label>
        <label><span>Assunto</span><input type="text" name="assunto" value="<?= e((string) old('assunto')) ?>"></label>
        <label><span>Mensagem</span><textarea name="mensagem" required><?= e((string) old('mensagem')) ?></textarea></label>
        <button class="btn btn-gold" type="submit">Enviar</button>
    </form>
    <aside>
        <h2>Outros caminhos</h2>
        <p><a class="btn btn-ghost" href="<?= e(whatsapp_url('Olá, vim pelo site da Elomiah.')) ?>" target="_blank" rel="noopener">WhatsApp</a></p>
        <p><a href="https://instagram.com/elomiah" target="_blank" rel="noopener">Instagram @elomiah</a></p>
        <p><a href="<?= e(achadinhos_url()) ?>" target="_blank" rel="noopener noreferrer">Vitrine da Geo</a></p>
        <p style="margin-top:2rem;color:var(--cinza)">Para exercer seus direitos de titular (acesso, correção, exclusão, portabilidade), use também a página <a href="<?= e(url('/meus-dados')) ?>">Seus dados</a>.</p>
    </aside>
</section>
