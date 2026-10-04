<?php
use App\Core\View;
View::partial('partials/flash');
$curso = $curso ?? [];
$cursoImg = (string) ($curso['imagem'] ?? '');
if ($cursoImg === '' || str_contains($cursoImg, 'geo-real') || str_contains($cursoImg, 'banner-achadinhos')) {
    $cursoImg = 'images/still-sagrado.webp';
}
$modulos = $modulos ?? [];
?>
<section class="page-hero container">
    <p class="eyebrow">Curso online com a Geo</p>
    <h1><?= e($curso['titulo'] ?? 'O Ritual das Essências') ?></h1>
    <p class="lede"><?= e($curso['descricao'] ?? '') ?></p>
</section>

<section class="container split-2">
    <img class="curso-img" src="<?= e(asset($cursoImg)) ?>" alt="" width="800" height="1000" loading="lazy">
    <div>
        <h2>Para quem é</h2>
        <p>Para quem quer entender como o cheiro muda um cômodo e montar um ritual de casa com o que já tem. Não é preciso saber nada de perfumaria.</p>
        <ul class="product-assurance">
            <li><?= count($modulos) ?: 6 ?> módulos gravados</li>
            <li>Acesso por 12 meses nesta conta</li>
            <li>Pagamento por Pix ou cartão</li>
        </ul>
        <p class="curso-preco"><?= e(money($curso['preco'] ?? 0)) ?></p>
        <form method="post" action="<?= e(url('/curso/matricular')) ?>">
            <?= csrf_field() ?>
            <button class="btn btn-gold" type="submit">Fazer minha matrícula</button>
        </form>
    </div>
</section>

<?php if ($modulos): ?>
<section class="container">
    <div class="section-head"><h2>Programa</h2></div>
    <div class="modulos">
        <?php foreach ($modulos as $i => $m): ?>
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
<?php endif; ?>

<?php if (!empty($depoimentos)): ?>
<section class="container">
    <div class="section-head"><h2>Quem fez o curso</h2></div>
    <div class="quotes">
        <?php foreach ($depoimentos as $d): ?>
            <figure class="quote">
                <blockquote><p>“<?= e($d['texto']) ?>”</p></blockquote>
                <figcaption><strong><?= e($d['nome']) ?></strong></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($faqs)): ?>
<section class="container faq">
    <div class="section-head"><h2>Perguntas frequentes</h2></div>
    <?php foreach ($faqs as $f): ?>
        <details>
            <summary><?= e($f['pergunta']) ?></summary>
            <p><?= e($f['resposta']) ?></p>
        </details>
    <?php endforeach; ?>
    <form method="post" action="<?= e(url('/curso/matricular')) ?>" class="section-cta">
        <?= csrf_field() ?>
        <button class="btn btn-gold" type="submit">Fazer minha matrícula, <?= e(money($curso['preco'] ?? 0)) ?></button>
    </form>
</section>
<?php endif; ?>
