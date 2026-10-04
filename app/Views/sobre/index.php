<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <h1>Sobre a Elomiah</h1>
    <p class="lede">Sprays de ambiente criados e formulados pela Geo.</p>
</section>
<section class="container">
    <div class="geo">
        <figure>
            <img src="<?= e(asset('images/geo-fundadora.webp')) ?>" alt="Geo, fundadora da Elomiah" width="1536" height="1024" fetchpriority="high">
        </figure>
        <div>
            <h2>A Geo</h2>
            <p>Geo é mineira e aprendeu perfumaria testando: anos de ensaio em linho, madeira e pele até entender como um aroma se comporta num cômodo ao longo do dia.</p>
            <p>A Elomiah nasceu desse trabalho. O nome guarda a ideia de refúgio, um lugar da casa para onde se volta. O slogan veio junto: <em>onde o sagrado encontra a essência.</em></p>
            <p>Hoje o ateliê formula em pequenos lotes. No curso O Ritual das Essências, a Geo ensina o que aprendeu sobre olfato e ambiente.</p>
        </div>
    </div>
</section>
<section class="container media-wide">
    <img src="<?= e(asset('images/colecao-refugio.webp')) ?>" alt="Os cinco frascos da Coleção Refúgio lado a lado" width="1024" height="582" loading="lazy">
</section>
<section class="container split-2">
    <div>
        <h2>Como trabalhamos</h2>
        <p>Poucos aromas, feitos com calma. Um lançamento só chega à loja depois que a fórmula foi testada em casa por semanas.</p>
    </div>
    <div>
        <h2>O que vai no frasco</h2>
        <p>Vidro, atomizador dourado e 120 ml de spray de ambiente. Notas pensadas para durar no cômodo, não só no primeiro borrifo.</p>
        <p><a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver os aromas</a></p>
    </div>
</section>
