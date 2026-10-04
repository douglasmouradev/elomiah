<?php
$v = '13';
$logado = \App\Core\Auth::check();
$wa = whatsapp_url('Olá, vim pelo site da Elomiah.');
$vitrine = \App\Models\Configuracao::vitrine();
$avisos = $vitrine['avisos'];
$automaticos = [];
if ($vitrine['frete_gratis'] > 0) {
    $automaticos[] = 'Frete grátis nas compras acima de ' . money($vitrine['frete_gratis']);
}
if (desconto_pix() > 0) {
    $automaticos[] = pct(desconto_pix()) . ' de desconto no Pix';
}
if ($vitrine['brinde_acima'] > 0 && $vitrine['brinde_texto'] !== '') {
    $automaticos[] = 'Ganhe ' . $vitrine['brinde_texto'] . ' nas compras acima de ' . money($vitrine['brinde_acima']);
}
$avisos = array_merge($automaticos, $avisos);
$loja = \App\Models\Configuracao::loja();
$cartao = \App\Support\MercadoPago::configurado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="app-base" content="<?= e(rtrim((string) (config('app')['url'] ?? ''), '/')) ?>">
    <meta name="theme-color" content="#F2F3EE">
    <title><?= e($title ?? 'Elomiah') ?></title>
    <meta name="description" content="<?= e($metaDescription ?? 'Elomiah: sprays de ambiente em vidro, 120 ml. Cinco aromas da Coleção Refúgio e o Elo, para o quarto do bebê.') ?>">
    <meta property="og:title" content="<?= e($ogTitle ?? $title ?? 'Elomiah') ?>">
    <meta property="og:description" content="<?= e($ogDescription ?? 'Onde o sagrado encontra a essência. Sprays de ambiente em vidro, 120 ml.') ?>">
    <meta property="og:image" content="<?= e($ogImage ?? asset('images/colecao-refugio.webp')) ?>">
    <meta property="og:type" content="website">
    <link rel="icon" href="<?= e(asset('images/icone-180.png')) ?>" type="image/png" sizes="180x180">
    <link rel="apple-touch-icon" href="<?= e(asset('images/icone-180.png')) ?>">
    <link rel="preload" href="<?= e(asset('fonts/marcellus-400.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/hanken-grotesk-var.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>?v=<?= $v ?>">
    <script>
      (function (d) {
        d.classList.add('js');
        try {
          if (!sessionStorage.getItem('elomiah_abertura') && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
            d.classList.add('com-abertura');
            sessionStorage.setItem('elomiah_abertura', '1');
          }
        } catch (e) {}
      })(document.documentElement);
      addEventListener('pagereveal', function (e) {
        if (!e.viewTransition || !window.navigation || !navigation.activation || !navigation.activation.from) return;
        var m = /\/produto\/[^/?#]+/.exec(navigation.activation.from.url || '');
        if (!m) return;
        var img = null;
        document.querySelectorAll('.product-card, .shelf-item, .elo-row').forEach(function (bloco) {
          if (img) return;
          var a = bloco.querySelector('a[href$="' + m[0] + '"]');
          if (a) img = bloco.querySelector('.vt-frasco[data-vt]');
        });
        if (!img) return;
        img.style.viewTransitionName = img.dataset.vt;
        e.viewTransition.finished.finally(function () { img.style.viewTransitionName = ''; });
      });
    </script>
    <link rel="expect" href="#fim-conteudo" blocking="render">
</head>
<body>
<div class="abertura" aria-hidden="true">
    <div class="abertura-marca">
        <img src="<?= e(asset('images/logo-elomiah-compacta.svg')) ?>" alt="" width="208" height="80">
        <span class="abertura-linha"></span>
        <p>Onde o sagrado encontra a essência.</p>
    </div>
</div>
<div class="nevoa" aria-hidden="true"><i></i><i></i></div>
<a class="skip" href="#conteudo">Ir ao conteúdo</a>
<?php if ($avisos): ?>
<div class="faixa" role="region" aria-label="Avisos da loja">
    <ul class="faixa-lista" data-faixa>
        <?php foreach ($avisos as $i => $aviso): ?>
            <li class="<?= $i === 0 ? 'is-on' : '' ?>"><?= e($aviso) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<header class="site-header">
    <div class="container">
        <a class="logo-link" href="<?= e(url('/')) ?>" aria-label="Elomiah, página inicial">
            <img src="<?= e(asset('images/logo-elomiah-compacta.svg')) ?>" alt="" width="104" height="40">
        </a>
        <nav class="nav-main" id="menu" aria-label="Principal">
            <a class="<?= is_active('/loja') ? 'is-active' : '' ?>" href="<?= e(url('/loja')) ?>">Aromas</a>
            <a class="<?= is_active('/curso') ? 'is-active' : '' ?>" href="<?= e(url('/curso')) ?>">Curso</a>
            <a class="<?= is_active('/sobre') ? 'is-active' : '' ?>" href="<?= e(url('/sobre')) ?>">Sobre a Geo</a>
            <a class="<?= is_active('/contato') ? 'is-active' : '' ?>" href="<?= e(url('/contato')) ?>">Contato</a>
            <a class="nav-extra" href="<?= e(url('/loja')) ?>#busca">Buscar</a>
            <?php if ($logado): ?>
                <a class="nav-extra" href="<?= e(url('/conta')) ?>">Minha conta</a>
                <a class="nav-extra" href="<?= e(url('/sair')) ?>">Sair</a>
            <?php else: ?>
                <a class="nav-extra" href="<?= e(url('/entrar')) ?>">Entrar</a>
            <?php endif; ?>
            <a class="nav-extra" href="<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a>
        </nav>
        <div class="nav-actions">
            <a class="nav-icon" href="<?= e(url('/loja')) ?>#busca" aria-label="Buscar aromas">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4"/></svg>
            </a>
            <a class="nav-wa" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Conversar no WhatsApp">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M20 12a8 8 0 0 1-11.6 7.1L4 20l.9-4.2A8 8 0 1 1 20 12Z"/></svg>
            </a>
            <?php if ($logado): ?>
                <a class="nav-text <?= is_active('/conta') ? 'is-active' : '' ?>" href="<?= e(url('/conta')) ?>">Conta</a>
            <?php else: ?>
                <a class="nav-text" href="<?= e(url('/entrar')) ?>">Entrar</a>
            <?php endif; ?>
            <a class="cart-link" href="<?= e(url('/carrinho')) ?>" data-abrir-sacola>Sacola <span class="cart-count" aria-label="itens"><?= (int) \App\Core\Cart::count() ?></span></a>
            <button class="nav-toggle" type="button" aria-label="Menu" aria-expanded="false" aria-controls="menu"><span></span></button>
        </div>
    </div>
</header>
<main id="conteudo">
    <?= $content ?? '' ?>
    <span id="fim-conteudo" hidden></span>
</main>
<footer class="site-footer">
    <div class="container footer-news" id="newsletter">
        <div>
            <h2 class="footer-news-title">Lotes novos, antes de todo mundo</h2>
            <p>Um e-mail quando sai aroma novo ou lote limitado. Sem enxurrada.</p>
        </div>
        <form class="news-form" method="post" action="<?= e(url('/newsletter')) ?>" data-newsletter>
            <?= csrf_field() ?>
            <div class="news-campos">
                <label class="visually-hidden" for="news-email">Seu e-mail</label>
                <input id="news-email" type="email" name="email" placeholder="Seu e-mail" autocomplete="email" required maxlength="180">
                <button class="btn" type="submit">Quero receber</button>
            </div>
            <input class="news-hp" type="text" name="site" tabindex="-1" autocomplete="off" aria-hidden="true">
            <label class="news-aceite">
                <input type="checkbox" name="aceite" value="1" required>
                <span>Aceito receber e-mails da Elomiah, conforme a <a href="<?= e(url('/privacidade')) ?>">Política de Privacidade</a>. Dá para sair quando quiser.</span>
            </label>
            <p class="news-msg" role="status" data-news-msg><?= e((string) (flash('newsletter') ?? '')) ?></p>
        </form>
    </div>
    <div class="container footer-grid">
        <div class="footer-brand">
            <img src="<?= e(asset('images/logo-elomiah-compacta-clara.svg')) ?>" alt="Elomiah" width="115" height="44" loading="lazy">
            <p>Sprays de ambiente em vidro, formulados em pequenos lotes pela Geo.</p>
            <?php if ($vitrine['redes']): ?>
                <ul class="redes">
                    <?php foreach ($vitrine['redes'] as $rede => $urlRede): ?>
                        <li><a href="<?= e($urlRede) ?>" rel="noopener" target="_blank" aria-label="<?= e(\App\Models\Configuracao::REDES[$rede]) ?> da Elomiah"><?php \App\Core\View::partial('partials/icone-rede', ['rede' => $rede]); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div>
            <h2 class="footer-title">Loja</h2>
            <ul>
                <li><a href="<?= e(url('/loja')) ?>">Todos os aromas</a></li>
                <li><a href="<?= e(url('/curso')) ?>">Curso O Ritual das Essências</a></li>
                <li><a href="<?= e(url('/depoimentos')) ?>">Depoimentos</a></li>
                <li><a href="<?= e(url('/sobre')) ?>">Sobre a Geo</a></li>
            </ul>
        </div>
        <div>
            <h2 class="footer-title">Ajuda</h2>
            <ul>
                <li><a href="<?= e(url('/ajuda/envios')) ?>">Envios e frete</a></li>
                <li><a href="<?= e(url('/ajuda/pagamento')) ?>">Pagamento</a></li>
                <li><a href="<?= e(url('/ajuda/trocas')) ?>">Trocas e devoluções</a></li>
                <li><a href="<?= e(url('/conta')) ?>">Meus pedidos</a></li>
                <li><a href="<?= e(url('/meus-dados')) ?>">Seus dados (LGPD)</a></li>
            </ul>
        </div>
        <div>
            <h2 class="footer-title">Atendimento</h2>
            <ul>
                <li><a href="<?= e($wa) ?>" rel="noopener" target="_blank">WhatsApp</a></li>
                <li><a href="<?= e(url('/contato')) ?>">Formulário de contato</a></li>
                <?php if ($vitrine['atendimento'] !== ''): ?><li class="footer-horario"><?= e($vitrine['atendimento']) ?></li><?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="container footer-pay">
        <h2 class="footer-title">Formas de pagamento</h2>
        <ul class="pay-badges">
            <li>Pix<?= desconto_pix() > 0 ? ' com ' . e(pct(desconto_pix())) . ' de desconto' : '' ?></li>
            <?php if ($cartao): ?>
                <li>Visa</li>
                <li>Mastercard</li>
                <li>Elo</li>
                <li><?= $vitrine['parcelas'] > 1 ? 'Até ' . (int) $vitrine['parcelas'] . 'x sem juros' : 'Até 6x no cartão' ?></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> <?= e($loja['razao']) ?><?php if ($loja['cnpj'] !== ''): ?> · CNPJ <?= e($loja['cnpj']) ?><?php endif; ?><?php if ($loja['endereco'] !== ''): ?> · <?= e($loja['endereco']) ?><?php endif; ?></span>
        <span><a href="<?= e(url('/privacidade')) ?>">Privacidade</a> &nbsp; <a href="<?= e(url('/termos')) ?>">Termos de uso</a> &nbsp; <a href="<?= e(url('/privacidade')) ?>#cookies" data-abrir-cookies>Preferências de cookies</a></span>
    </div>
</footer>

<dialog class="drawer" id="sacola" aria-labelledby="sacola-titulo">
    <div class="drawer-inner">
        <div class="drawer-head">
            <h2 id="sacola-titulo">Sacola</h2>
            <button class="drawer-close" type="button" data-fechar-sacola aria-label="Fechar sacola">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        <p class="drawer-msg" role="status" hidden></p>
        <div class="drawer-body"></div>
        <div class="drawer-foot" hidden></div>
    </div>
</dialog>

<div class="cookie-banner<?= cookie_consentimento() === '' ? ' is-on' : '' ?>" role="region" aria-label="Aviso de cookies">
    <p>Usamos cookies para manter a sacola e o login. Métricas de visita só com o seu aceite. <a href="<?= e(url('/privacidade')) ?>">Como tratamos seus dados</a>.</p>
    <div class="cookie-actions">
        <button class="btn btn-gold" type="button" data-cookie="todos">Aceitar</button>
        <button class="btn btn-ghost" type="button" data-cookie="essenciais">Só os necessários</button>
        <button class="cookie-text" type="button" data-cookie="recusar">Recusar</button>
    </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>?v=<?= $v ?>" defer></script>
<?php if (in_array(current_path(), ['/checkout', '/conta'], true)): ?>
<script src="<?= e(asset('js/cep.js')) ?>" defer></script>
<?php endif; ?>
</body>
</html>
