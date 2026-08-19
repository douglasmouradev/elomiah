<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title ?? 'Elomiah') ?></title>
    <meta name="description" content="Elomiah — refúgio de aromatizantes e perfumes de alto padrão. Onde o sagrado encontra a essência.">
    <meta property="og:title" content="<?= e($title ?? 'Elomiah') ?>">
    <meta property="og:description" content="Elomiah — onde o sagrado encontra a essência. Cinco névoas em vidro, 120 ml.">
    <meta property="og:image" content="<?= e(asset('images/colecao-refugio.webp')) ?>">
    <meta property="og:type" content="website">
    <link rel="icon" href="<?= e(asset('images/logo.png')) ?>?v=2" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(asset('images/logo.png')) ?>?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400;1,500&family=Inter:wght@400;500&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <style>
      .splash{position:fixed;inset:0;z-index:200;display:grid;place-items:center;background:#FDFBF6;transition:opacity .7s ease,visibility .7s ease}
      .splash.is-done{opacity:0;visibility:hidden;pointer-events:none}
      html.splash-skip .splash{display:none}
    </style>
    <script>
      try { if (sessionStorage.getItem('elomiah_splash')) document.documentElement.classList.add('splash-skip'); } catch (e) {}
    </script>
</head>
<body>
<div class="splash" id="splash" role="status" aria-label="Carregando Elomiah">
    <div class="splash-inner">
        <img src="<?= e(asset('images/logo.png')) ?>?v=2" alt="Elomiah Refúgio">
        <p class="ornament">REFÚGIO</p>
        <span class="splash-line" aria-hidden="true"></span>
    </div>
</div>
<a class="skip" href="#conteudo">Ir ao conteúdo</a>
<header class="site-header">
    <div class="container">
        <a class="logo-link" href="<?= e(url('/')) ?>">
            <img src="<?= e(asset('images/logo.png')) ?>?v=2" alt="Elomiah Refúgio">
        </a>
        <nav class="nav-main" aria-label="Principal">
            <a class="<?= is_active('/loja') ? 'is-active' : '' ?>" href="<?= e(url('/loja')) ?>">Loja</a>
            <a class="<?= is_active('/curso') ? 'is-active' : '' ?>" href="<?= e(url('/curso')) ?>">Curso</a>
            <a class="<?= is_active('/achadinhos') ? 'is-active' : '' ?>" href="<?= e(url('/achadinhos')) ?>">Achadinhos</a>
            <a class="<?= is_active('/sobre') ? 'is-active' : '' ?>" href="<?= e(url('/sobre')) ?>">Sobre</a>
            <a class="<?= is_active('/depoimentos') ? 'is-active' : '' ?>" href="<?= e(url('/depoimentos')) ?>">Depoimentos</a>
            <a class="<?= is_active('/contato') ? 'is-active' : '' ?>" href="<?= e(url('/contato')) ?>">Contato</a>
        </nav>
        <div class="nav-actions">
            <a class="nav-wa" href="<?= e(whatsapp_url('Olá, vim pelo site da Elomiah.')) ?>" target="_blank" rel="noopener" aria-label="Conversar no WhatsApp">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.35" aria-hidden="true">
                    <path d="M20 12a8 8 0 0 1-11.6 7.1L4 20l.9-4.2A8 8 0 1 1 20 12Z"/>
                </svg>
            </a>
            <?php if (\App\Core\Auth::check()): ?>
                <a class="nav-text <?= is_active('/conta') ? 'is-active' : '' ?>" href="<?= e(url('/conta')) ?>">Conta</a>
                <a class="nav-text" href="<?= e(url('/sair')) ?>">Sair</a>
            <?php else: ?>
                <a class="nav-text" href="<?= e(url('/entrar')) ?>">Entrar</a>
            <?php endif; ?>
            <a class="cart-link" href="<?= e(url('/carrinho')) ?>">Sacola <span class="cart-count"><?= (int) \App\Core\Cart::count() ?></span></a>
            <button class="nav-toggle" type="button" aria-label="Abrir menu"><span></span></button>
        </div>
    </div>
</header>
<main id="conteudo">
    <?= $content ?? '' ?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <div class="footer-brand">ELOMIAH</div>
            <p class="ornament ornament-start">REFÚGIO</p>
            <p>Onde o sagrado encontra a essência.</p>
        </div>
        <div>
            <h4>O refúgio</h4>
            <ul>
                <li><a href="<?= e(url('/loja')) ?>">A loja</a></li>
                <li><a href="<?= e(url('/curso')) ?>">O Ritual das Essências</a></li>
                <li><a href="<?= e(url('/achadinhos')) ?>">Achadinhos da Geo</a></li>
                <li><a href="<?= e(url('/sobre')) ?>">A marca e a Geo</a></li>
            </ul>
        </div>
        <div>
            <h4>Cuidado</h4>
            <ul>
                <li><a href="<?= e(url('/privacidade')) ?>">Política de Privacidade</a></li>
                <li><a href="<?= e(url('/termos')) ?>">Termos de Uso</a></li>
                <li><a href="<?= e(url('/meus-dados')) ?>">Seus dados (LGPD)</a></li>
                <li><a href="<?= e(url('/conta')) ?>">Minha conta</a></li>
                <li><a href="<?= e(url('/contato')) ?>">Fale conosco</a></li>
            </ul>
        </div>
        <div>
            <h4>Presença</h4>
            <ul>
                <li><a href="https://instagram.com/elomiah" rel="noopener" target="_blank">Instagram</a></li>
                <li><a href="<?= e(whatsapp_url('Olá, vim pelo site da Elomiah.')) ?>" rel="noopener" target="_blank">WhatsApp</a></li>
                <li><a href="<?= e(achadinhos_url()) ?>" target="_blank" rel="noopener noreferrer">Vitrine da Geo</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© <?= date('Y') ?> Elomiah. Todos os direitos reservados.</span>
        <span>Feito com silêncio e precisão.</span>
    </div>
</footer>

<div class="cookie-banner" role="dialog" aria-label="Cookies">
    <p>Usamos cookies essenciais para o funcionamento da loja. Os demais — métricas discretas — só entram com o seu aceite. Leia a <a href="<?= e(url('/privacidade')) ?>">Política de Privacidade</a>.</p>
    <div class="cookie-actions">
        <button class="btn btn-gold" type="button" data-cookie="todos">Aceitar</button>
        <button class="btn btn-ghost cookie-quiet" type="button" data-cookie="essenciais">Só o essencial</button>
        <button class="cookie-text" type="button" data-cookie="recusar">Recusar</button>
    </div>
</div>

<?php if (current_path() === '/'): ?>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="<?= e(asset('js/spray.js')) ?>"></script>
<?php endif; ?>
<script src="<?= e(asset('js/app.js')) ?>"></script>
<?php if (current_path() === '/checkout'): ?>
<script src="<?= e(asset('js/cep.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
