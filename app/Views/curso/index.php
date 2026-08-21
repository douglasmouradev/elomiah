<?php
use App\Core\View;
View::partial('partials/flash');
$curso = $curso ?? [];
?>
<section class="page-hero container">
    <p class="eyebrow">Formação com a Geo</p>
    <h1><?= e($curso['titulo'] ?? 'O Ritual das Essências') ?></h1>
    <p class="lede" style="margin-inline:auto"><?= e($curso['descricao'] ?? '') ?></p>
</section>

<section class="container split-2">
    <?php
    $cursoImg = (string) ($curso['imagem'] ?? '');
    if ($cursoImg === '' || str_contains($cursoImg, 'geo-real') || str_contains($cursoImg, 'banner-achadinhos')) {
        $cursoImg = 'images/colecao-refugio.webp';
    }
    ?>
    <img src="<?= e(asset($cursoImg)) ?>" alt="Coleção Refúgio Elomiah" style="width:100%;height:520px;object-fit:cover">
    <div>
        <p class="eyebrow">O convite</p>
        <h2>Aprender a habitar o cheiro.</h2>
        <p class="lede">Não é um curso para virar perfumista. É um curso para devolver ao olfato o lugar que a pressa tirou — e construir um ritual que caiba na sua casa.</p>
        <p style="font-family:Playfair Display,serif;font-size:2rem;color:var(--dourado);margin:1.2rem 0"><?= e(money($curso['preco'] ?? 0)) ?></p>
        <form method="post" action="<?= e(url('/curso/matricular')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-gold" type="submit">Quero me matricular</button>
        </form>
    </div>
</section>

<section class="container">
    <div class="section-head"><h2>Módulos</h2></div>
    <div class="modulos">
        <?php foreach ($modulos ?? [] as $i => $m): ?>
            <article class="modulo">
                <div class="n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></div>
                <div>
                    <h3><?= e($m['titulo']) ?></h3>
                    <p><?= e($m['descricao']) ?></p>
                </div>
                <span><?= e($m['duracao']) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="container">
    <div class="section-head"><h2>Alunas</h2></div>
    <div class="quotes">
        <?php foreach ($depoimentos ?? [] as $d): ?>
            <blockquote class="quote">
                <div class="stars"><?= str_repeat('★', (int) $d['nota']) ?></div>
                <p>“<?= e($d['texto']) ?>”</p>
                <footer><?= e($d['nome']) ?></footer>
            </blockquote>
        <?php endforeach; ?>
    </div>
</section>

<section class="container faq">
    <div class="section-head"><h2>Perguntas</h2></div>
    <?php foreach ($faqs ?? [] as $f): ?>
        <details>
            <summary><?= e($f['pergunta']) ?></summary>
            <p><?= e($f['resposta']) ?></p>
        </details>
    <?php endforeach; ?>
    <p style="text-align:center;margin-top:2.5rem">
        <form method="post" action="<?= e(url('/curso/matricular')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-gold" type="submit">Matricular-me agora</button>
        </form>
    </p>
</section>
