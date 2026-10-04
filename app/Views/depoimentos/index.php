<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <h1>Depoimentos</h1>
    <p class="lede">O que clientes contaram depois de usar os aromas. Cada relato é lido pela equipe antes de ser publicado.</p>
</section>
<section class="container">
    <div class="quotes">
        <?php foreach ($depoimentos ?? [] as $d): ?>
            <figure class="quote">
                <?php if (!empty($d['foto'])): ?>
                    <img class="quote-foto" src="<?= e(asset($d['foto'])) ?>" alt="" width="56" height="56" loading="lazy">
                <?php endif; ?>
                <blockquote><p>“<?= e($d['texto']) ?>”</p></blockquote>
                <figcaption><strong><?= e($d['nome']) ?></strong></figcaption>
            </figure>
        <?php endforeach; ?>
    </div>
</section>
<section class="container narrow">
    <h2>Contar a sua experiência</h2>
    <form class="form" method="post" action="<?= e(url('/depoimentos')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label><span>Nome (como quer que apareça)</span><input type="text" name="nome" value="<?= e((string) old('nome')) ?>" required autocomplete="name"></label>
        <label><span>Nota</span>
            <select name="nota">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?> de 5</option>
                <?php endfor; ?>
            </select>
        </label>
        <label><span>Seu relato</span><textarea name="texto" required placeholder="Qual aroma, em que cômodo, o que mudou"><?= e((string) old('texto')) ?></textarea></label>
        <label><span>Foto (opcional, JPG, PNG ou WebP)</span><input type="file" name="foto" accept="image/jpeg,image/png,image/webp"></label>
        <button class="btn btn-gold" type="submit">Enviar relato</button>
    </form>
</section>
