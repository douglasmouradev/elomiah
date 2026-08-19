<?php \App\Core\View::partial('partials/flash'); ?>
<section class="page-hero container">
    <p class="eyebrow">Relatos</p>
    <h1>Depoimentos</h1>
    <p class="lede" style="margin-inline:auto">Quem entrou no refúgio e quis deixar um traço. Os textos passam por leitura antes de aparecer aqui.</p>
</section>
<section class="container">
    <div class="quotes">
        <?php foreach ($depoimentos ?? [] as $d): ?>
            <blockquote class="quote">
                <?php if (!empty($d['foto'])): ?>
                    <img src="<?= e(asset($d['foto'])) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:50%;margin-bottom:1rem">
                <?php endif; ?>
                <div class="stars"><?= str_repeat('★', (int) $d['nota']) ?></div>
                <p>“<?= e($d['texto']) ?>”</p>
                <footer><?= e($d['nome']) ?></footer>
            </blockquote>
        <?php endforeach; ?>
    </div>
</section>
<section class="container" style="max-width:640px">
    <h2>Deixar um relato</h2>
    <form class="form" method="post" action="<?= e(url('/depoimentos')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label><span>Nome</span><input type="text" name="nome" value="<?= e((string) old('nome')) ?>" required></label>
        <label><span>Nota</span>
            <select name="nota">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>"><?= $i ?> estrelas</option>
                <?php endfor; ?>
            </select>
        </label>
        <label><span>Texto</span><textarea name="texto" required><?= e((string) old('texto')) ?></textarea></label>
        <label><span>Foto (opcional)</span><input type="file" name="foto" accept="image/jpeg,image/png,image/webp"></label>
        <button class="btn btn-gold" type="submit">Enviar para revisão</button>
    </form>
</section>
