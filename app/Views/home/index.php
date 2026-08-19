<?php
use App\Core\View;
$produtos = $produtos ?? [];
$depoimentos = $depoimentos ?? [];
$curso = $curso ?? null;
$depoimentosHome = array_slice($depoimentos, 0, 3);
$depoimentoDestaque = $depoimentosHome[0] ?? null;
$depoimentosApoio = array_slice($depoimentosHome, 1);
View::partial('partials/flash');
?>
<section class="hero container">
    <div class="hero-copy" data-parallax="0.08" data-dir="up">
        <p class="eyebrow">Coleção Refúgio</p>
        <h1 class="display">Elomiah</h1>
        <p class="hero-refugio"><i></i><span>REFÚGIO</span><i></i></p>
        <p class="lede">Onde o sagrado encontra a essência. Cinco névoas em vidro — Despertar, Recomeço, Encontro, Equilíbrio e Silêncio.</p>
        <div class="hero-actions">
            <a class="btn btn-gold" href="<?= e(url('/loja')) ?>">A coleção</a>
            <a class="btn btn-ghost" href="<?= e(url('/sobre')) ?>">A história</a>
        </div>
    </div>
    <div class="hero-visual">
        <div class="gold-ring" data-parallax="0.05"></div>
        <img class="spray" data-spray src="<?= e(asset('images/frasco-despertar.webp')) ?>" alt="Spray Elomiah Despertar, aroma manga verde" fetchpriority="high">
        <canvas class="hero-mist" id="mist-canvas"></canvas>
    </div>
</section>

<section class="collection-band">
    <figure class="container">
        <img src="<?= e(asset('images/colecao-refugio.webp')) ?>" alt="Coleção Refúgio Elomiah — cinco sprays de ambiente" loading="lazy">
        <figcaption>Cinco névoas · 120 ml · vidro</figcaption>
    </figure>
</section>

<section>
    <div class="container story">
        <img class="geo-portrait" src="<?= e(asset('images/geo-real.webp')) ?>" alt="Geo, fundadora da Elomiah" data-parallax="0.04">
        <div>
            <p class="eyebrow">A marca</p>
            <h2>Um refúgio, não uma vitrine.</h2>
            <p>A Elomiah nasceu da insistência da Geo em tratar o cheiro como território. Não como tendência. Como o gesto de consagrar um cômodo antes de viver nele.</p>
            <p>Os frascos são de vidro. O spray sai em leque fino. Cada nome — Despertar, Recomeço, Encontro, Equilíbrio, Silêncio — é um estado, não um slogan de prateleira.</p>
            <div class="story-note">GEO · COLEÇÃO REFÚGIO · 120 ML</div>
            <p class="story-cta"><a class="btn btn-ghost" href="<?= e(url('/sobre')) ?>">Conhecer a Geo</a></p>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">A vitrine</p>
            <div class="ornament">REFÚGIO</div>
            <h2>Coleção Refúgio</h2>
            <p class="lede lede-center">Cinco aromas, um mesmo ritual. Mais o Elo, para os primeiros capítulos.</p>
        </div>
        <div class="product-grid">
            <?php foreach ($produtos as $p): ?>
                <?php View::partial('partials/product-card', ['p' => $p]); ?>
            <?php endforeach; ?>
        </div>
        <p class="section-cta"><a class="btn btn-ghost" href="<?= e(url('/loja')) ?>">Ver a coleção</a></p>
    </div>
</section>

<?php if ($depoimentoDestaque): ?>
<section>
    <div class="container">
        <div class="section-head">
            <p class="eyebrow">Quem viveu o ritual</p>
            <h2>Depoimentos</h2>
        </div>
        <div class="quotes-editorial">
            <blockquote class="quote quote-lead">
                <div class="stars"><?= str_repeat('★', (int) $depoimentoDestaque['nota']) ?></div>
                <p>“<?= e($depoimentoDestaque['texto']) ?>”</p>
                <footer><?= e($depoimentoDestaque['nome']) ?></footer>
            </blockquote>
            <?php if ($depoimentosApoio): ?>
                <div class="quotes-side">
                    <?php foreach ($depoimentosApoio as $d): ?>
                        <blockquote class="quote">
                            <div class="stars"><?= str_repeat('★', (int) $d['nota']) ?></div>
                            <p>“<?= e($d['texto']) ?>”</p>
                            <footer><?= e($d['nome']) ?></footer>
                        </blockquote>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <p class="section-cta"><a class="btn btn-ghost" href="<?= e(url('/depoimentos')) ?>">Ler todos</a></p>
    </div>
</section>
<?php endif; ?>

<section class="course-wrap">
    <div class="course-band">
        <div class="copy">
            <p class="eyebrow">Formação</p>
            <h2><?= e($curso['titulo'] ?? 'O Ritual das Essências') ?></h2>
            <p class="lede lede-on-dark">Seis módulos com a Geo: olfato, casa e o hábito de consagrar o espaço.</p>
            <p class="course-price"><?= e(money($curso['preco'] ?? 497)) ?></p>
            <a class="btn btn-gold" href="<?= e(url('/curso')) ?>">Conhecer o curso</a>
        </div>
        <img src="<?= e(asset('images/still-sagrado.webp')) ?>" alt="Névoa Elomiah no ateliê" loading="lazy">
    </div>
</section>
