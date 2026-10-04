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
$avaliacoes = array_slice($depoimentos, 0, 6);
$faq = faq_loja();
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
                        <img class="vt-frasco" data-vt="frasco-<?= (int) $p['id'] ?>" src="<?= e(asset($p['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="" width="250" height="450" <?= $i < 3 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
                    </span>
                    <span class="shelf-name rotulo"><?= e($p['nome']) ?></span>
                    <span class="shelf-aroma"><?= e($p['aroma'] ?? '') ?></span>
                    <span class="shelf-aroma price"><?= e(money($p['preco_promocional'] ?: $p['preco'])) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php if (count($refugio) > 1): ?>
<section class="ritual" data-ritual style="--n:<?= count($refugio) ?>" aria-labelledby="titulo-ritual">
    <div class="ritual-palco">
        <div class="ritual-frasco" aria-hidden="true">
            <canvas class="ritual-nevoa" data-ritual-nevoa></canvas>
            <?php foreach ($refugio as $i => $p): ?>
                <img class="<?= $i === 0 ? 'is-on' : '' ?>" src="<?= e(asset($p['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="" width="250" height="450" loading="lazy" decoding="async" data-bico="<?= str_contains((string) ($p['imagem'] ?? ''), 'despertar') ? '0.58,0.09' : '0.58,0.135' ?>" data-cor="<?= e((string) ($p['cor_destaque'] ?? '#A9853B')) ?>">
            <?php endforeach; ?>
        </div>
        <div class="ritual-texto">
            <h2 class="ritual-titulo" id="titulo-ritual">Um aroma para cada momento do dia</h2>
            <ol class="ritual-passos">
                <?php foreach ($refugio as $i => $p): ?>
                    <li class="<?= $i === 0 ? 'is-on' : '' ?>" style="--c:<?= e((string) ($p['cor_destaque'] ?? '#A9853B')) ?>">
                        <span class="ritual-num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                        <h3 class="rotulo"><?= e($p['nome']) ?></h3>
                        <p><?= e($p['descricao_curta'] ?? $p['aroma'] ?? '') ?></p>
                        <a class="text-link" href="<?= e(url('/produto/' . $p['slug'])) ?>">Conhecer o <?= e($p['nome']) ?>, <?= e(money($p['preco_promocional'] ?: $p['preco'])) ?></a>
                    </li>
                <?php endforeach; ?>
            </ol>
            <div class="ritual-trilha" aria-hidden="true"><i></i></div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($elo): $corElo = (string) ($elo['cor_destaque'] ?? '#C9A24B'); ?>
<section class="container">
    <div class="elo-row" data-nevoa="<?= e($corElo) ?>">
        <figure>
            <img class="vt-frasco" data-vt="frasco-<?= (int) $elo['id'] ?>" src="<?= e(asset($elo['imagem'] ?? 'images/frasco-elo.webp')) ?>" alt="Frasco do Elo, spray de algodão e camomila" width="400" height="500" loading="lazy" decoding="async">
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

<?php if ($avaliacoes): ?>
<section class="container avaliacoes" aria-labelledby="titulo-avaliacoes">
    <div class="section-head">
        <h2 id="titulo-avaliacoes">O que dizem nossos clientes</h2>
        <a class="text-link" href="<?= e(url('/depoimentos')) ?>">Ler todos os relatos</a>
    </div>
    <ul class="avaliacoes-lista">
        <?php foreach ($avaliacoes as $d): $aroma = $porId[(int) ($d['produto_id'] ?? 0)] ?? null; $nota = max(1, min(5, (int) ($d['nota'] ?? 5))); ?>
            <li class="avaliacao" style="--c:<?= e((string) ($aroma['cor_destaque'] ?? '#A9853B')) ?>">
                <p class="avaliacao-nota" aria-label="Nota <?= $nota ?> de 5"><?= str_repeat('★', $nota) ?><span aria-hidden="true"><?= str_repeat('★', 5 - $nota) ?></span></p>
                <blockquote><p><?= e($d['texto']) ?></p></blockquote>
                <p class="avaliacao-autor">
                    <strong><?= e($d['nome']) ?></strong>
                    <time datetime="<?= e(date('Y-m-d', strtotime((string) $d['created_at']))) ?>"><?= e(date('d/m/Y', strtotime((string) $d['created_at']))) ?></time>
                </p>
                <?php if ($aroma): ?><p class="avaliacao-aroma">sobre <a href="<?= e(url('/produto/' . $aroma['slug'])) ?>"><?= e($aroma['nome']) ?></a></p><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
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

<?php if ($faq): ?>
<section class="container faq-home" aria-labelledby="titulo-faq">
    <h2 id="titulo-faq">Perguntas frequentes</h2>
    <div>
        <?php foreach ($faq as $item): ?>
            <details class="accordion">
                <summary><?= e($item['p']) ?></summary>
                <div class="accordion-body"><p><?= nl2br(e($item['r'])) ?></p></div>
            </details>
        <?php endforeach; ?>
    </div>
</section>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static fn ($i) => [
        '@type' => 'Question',
        'name' => $i['p'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['r']],
    ], $faq),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endif; ?>
