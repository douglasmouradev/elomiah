<?php
use App\Core\View;
$produtos = $produtos ?? [];
$depoimentos = $depoimentos ?? [];
$curso = $curso ?? null;
$refugio = array_values(array_filter($produtos, static fn ($p) => ($p['categoria_slug'] ?? '') === 'colecao-refugio'));
$elo = array_values(array_filter($produtos, static fn ($p) => ($p['categoria_slug'] ?? '') === 'colecao-elo'))[0] ?? null;
if (!$refugio) {
    $refugio = array_slice($produtos, 0, 5);
}
usort($refugio, static fn ($a, $b) => (int) $a['id'] <=> (int) $b['id']);
$porId = [];
foreach ($produtos as $p) {
    $porId[(int) $p['id']] = $p;
}
$depoimento = null;
foreach ($depoimentos as $d) {
    if (!empty($d['produto_id']) && isset($porId[(int) $d['produto_id']])) {
        $depoimento = $d;
        break;
    }
}
$depoimento ??= $depoimentos[0] ?? null;
$aromaDepoimento = $depoimento ? ($porId[(int) ($depoimento['produto_id'] ?? 0)] ?? null) : null;
View::partial('partials/flash');
?>
<section class="shelf container" aria-labelledby="titulo-home">
    <div class="shelf-head">
        <h1 class="display" id="titulo-home">Onde o sagrado encontra a essência.</h1>
        <div>
            <p class="shelf-sub">Cinco sprays de ambiente em vidro, 120 ml. Cada um com um aroma e um momento do dia.</p>
            <a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver os aromas</a>
        </div>
    </div>
    <ul class="shelf-row">
        <?php foreach ($refugio as $i => $p): $cor = (string) ($p['cor_destaque'] ?? '#173A2C'); ?>
            <li class="shelf-item" style="--c:<?= e($cor) ?>" data-nevoa="<?= e($cor) ?>">
                <a href="<?= e(url('/produto/' . $p['slug'])) ?>">
                    <span class="shelf-photo">
                        <img src="<?= e(asset($p['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="" width="250" height="450" <?= $i < 3 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                    </span>
                    <span class="shelf-name rotulo"><?= e($p['nome']) ?></span>
                    <span class="shelf-aroma"><?= e($p['aroma'] ?? '') ?></span>
                    <span class="shelf-aroma price"><?= e(money($p['preco_promocional'] ?: $p['preco'])) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php if ($elo): $corElo = (string) ($elo['cor_destaque'] ?? '#C9A24B'); ?>
<section class="container">
    <div class="elo-row" data-nevoa="<?= e($corElo) ?>">
        <figure>
            <img src="<?= e(asset($elo['imagem'] ?? 'images/frasco-elo.webp')) ?>" alt="Frasco do Elo, spray de algodão e camomila" width="400" height="500" loading="lazy" decoding="async">
        </figure>
        <div>
            <p class="eyebrow">Coleção Elo</p>
            <h2>Elo, para o quarto do bebê</h2>
            <p class="lede">Lavanda e bergamota na saída, algodão e camomila no corpo, musk e sândalo no fundo. Borrife no ar ou em tecidos, a 20 cm, e o quarto fica com cheiro de roupa limpa.</p>
            <p><a class="btn btn-ghost" href="<?= e(url('/produto/' . $elo['slug'])) ?>">Conhecer o Elo, <?= e(money($elo['preco_promocional'] ?: $elo['preco'])) ?></a></p>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="container">
    <div class="geo">
        <figure>
            <img src="<?= e(asset('images/geo-fundadora.webp')) ?>" alt="Geo, fundadora da Elomiah, no ateliê" width="1536" height="1024" loading="lazy" decoding="async">
            <figcaption>Geo, fundadora da Elomiah.</figcaption>
        </figure>
        <div>
            <h2>Quem faz: a Geo</h2>
            <p class="lede">Antes de virar frasco, cada aroma passa semanas em teste no linho, na madeira e na pele.</p>
            <p>A Geo formula cada aroma em pequenos lotes. É também ela quem conduz o curso O Ritual das Essências.</p>
            <p><a class="btn btn-ghost" href="<?= e(url('/sobre')) ?>">A história da marca</a></p>
        </div>
    </div>
</section>

<?php if ($depoimento): ?>
<section class="container">
    <figure class="testimonial" style="--c:<?= e((string) ($aromaDepoimento['cor_destaque'] ?? '#A9853B')) ?>">
        <blockquote>
            <p>“<?= e($depoimento['texto']) ?>”</p>
        </blockquote>
        <figcaption>
            <strong><?= e($depoimento['nome']) ?></strong><?php if ($aromaDepoimento): ?>, sobre <a href="<?= e(url('/produto/' . $aromaDepoimento['slug'])) ?>"><?= e($aromaDepoimento['nome']) ?></a><?php endif; ?>
            <br><a href="<?= e(url('/depoimentos')) ?>">Ler os outros relatos</a>
        </figcaption>
    </figure>
</section>
<?php endif; ?>

<section class="container">
    <div class="course-band">
        <div class="copy">
            <p class="eyebrow lede-on-dark">Curso com a Geo</p>
            <h2><?= e($curso['titulo'] ?? 'O Ritual das Essências') ?></h2>
            <ul class="course-facts">
                <li>6 módulos gravados</li>
                <li>Acesso por 12 meses</li>
                <li><?= e(money($curso['preco'] ?? 497)) ?></li>
            </ul>
            <p class="lede lede-on-dark">Olfato, composição de ambiente e como criar o seu próprio ritual em casa.</p>
            <a class="btn btn-gold" href="<?= e(url('/curso')) ?>">Ver o programa</a>
        </div>
        <img src="<?= e(asset('images/still-sagrado.webp')) ?>" alt="" width="1536" height="1024" loading="lazy" decoding="async">
    </div>
</section>
