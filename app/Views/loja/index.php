<?php
use App\Core\View;
View::partial('partials/flash');
$categorias = $categorias ?? [];
$produtos = $produtos ?? [];
?>
<section class="page-hero container">
    <p class="eyebrow">A coleção</p>
    <h1>A loja</h1>
    <p class="lede" style="margin-inline:auto">Cinco névoas em vidro, 120 ml. Filtre pelo que o cômodo pede.</p>
</section>
<section class="container" style="padding-top:1rem">
    <form class="filters" method="get" action="<?= e(url('/loja')) ?>">
        <div class="chip-row">
            <a class="chip <?= empty($categoriaAtual) ? 'is-on' : '' ?>" href="<?= e(url('/loja')) ?>">Tudo</a>
            <?php foreach ($categorias as $c): ?>
                <a class="chip <?= ($categoriaAtual ?? '') === $c['slug'] ? 'is-on' : '' ?>"
                   href="<?= e(url('/loja?categoria=' . $c['slug'] . '&ordenar=' . ($ordenar ?? 'lancamento'))) ?>">
                    <?= e($c['nome']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <label>
            <span class="visually-hidden">Ordenar</span>
            <select class="chip" name="ordenar" onchange="this.form.submit()">
                <option value="lancamento" <?= ($ordenar ?? '') === 'lancamento' ? 'selected' : '' ?>>Lançamento</option>
                <option value="preco_asc" <?= ($ordenar ?? '') === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
                <option value="preco_desc" <?= ($ordenar ?? '') === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
                <option value="nome" <?= ($ordenar ?? '') === 'nome' ? 'selected' : '' ?>>Nome</option>
            </select>
            <?php if (!empty($categoriaAtual)): ?>
                <input type="hidden" name="categoria" value="<?= e($categoriaAtual) ?>">
            <?php endif; ?>
        </label>
    </form>
    <div class="product-grid">
        <?php foreach ($produtos as $p): ?>
            <?php View::partial('partials/product-card', ['p' => $p]); ?>
        <?php endforeach; ?>
    </div>
    <?php if (!$produtos): ?>
        <p style="text-align:center;color:var(--cinza)">Nenhum item nesta prateleira por agora.</p>
    <?php endif; ?>
</section>
