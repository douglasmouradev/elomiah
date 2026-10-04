<?php
use App\Core\View;
View::partial('partials/flash');
$categorias = $categorias ?? [];
$produtos = $produtos ?? [];
$busca = (string) ($busca ?? '');
$ordenar = (string) ($ordenar ?? 'lancamento');
$categoriaAtual = $categoriaAtual ?? null;
$linkCategoria = static function (?string $slug, bool $comBusca = true) use ($ordenar, $busca): string {
    $q = array_filter(['categoria' => $slug, 'ordenar' => $ordenar !== 'lancamento' ? $ordenar : null, 'q' => $comBusca && $busca !== '' ? $busca : null]);
    return url('/loja' . ($q ? '?' . http_build_query($q) : ''));
};
$total = count($produtos);
?>
<section class="page-hero container">
    <h1>Aromas</h1>
    <p class="lede">Sprays de ambiente em vidro de 120 ml. Busque por uma nota que você gosta ou filtre pela coleção.</p>
</section>
<section class="container">
    <form class="shop-bar" method="get" action="<?= e(url('/loja')) ?>" role="search">
        <div class="chip-row" aria-label="Coleções">
            <a class="chip <?= empty($categoriaAtual) ? 'is-on' : '' ?>" href="<?= e($linkCategoria(null)) ?>" <?= empty($categoriaAtual) ? 'aria-current="true"' : '' ?>>Todos <small><?= (int) ($totalGeral ?? 0) ?></small></a>
            <?php foreach ($categorias as $c): $on = $categoriaAtual === $c['slug']; ?>
                <a class="chip <?= $on ? 'is-on' : '' ?>" href="<?= e($linkCategoria($c['slug'])) ?>" <?= $on ? 'aria-current="true"' : '' ?>><?= e($c['nome']) ?> <small><?= (int) $c['total'] ?></small></a>
            <?php endforeach; ?>
        </div>
        <label class="shop-search">
            <span class="visually-hidden">Buscar por nome, aroma ou nota</span>
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.4-4.4"/></svg>
            <input type="search" id="busca" name="q" value="<?= e($busca) ?>" placeholder="Buscar: figo, lavanda, âmbar…" autocomplete="off" enterkeyhint="search">
        </label>
        <label class="shop-sort">
            <span class="visually-hidden">Ordenar</span>
            <select name="ordenar" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                <option value="lancamento" <?= $ordenar === 'lancamento' ? 'selected' : '' ?>>Mais recentes</option>
                <option value="preco_asc" <?= $ordenar === 'preco_asc' ? 'selected' : '' ?>>Menor preço</option>
                <option value="preco_desc" <?= $ordenar === 'preco_desc' ? 'selected' : '' ?>>Maior preço</option>
                <option value="nome" <?= $ordenar === 'nome' ? 'selected' : '' ?>>Nome, A a Z</option>
            </select>
        </label>
        <?php if (!empty($categoriaAtual)): ?>
            <input type="hidden" name="categoria" value="<?= e($categoriaAtual) ?>">
        <?php endif; ?>
        <button class="visually-hidden" type="submit">Buscar</button>
    </form>

    <?php if ($busca !== '' && $total > 0): ?>
        <p class="shop-count" role="status"><?= $total ?> <?= $total === 1 ? 'aroma encontrado' : 'aromas encontrados' ?> para “<?= e($busca) ?>”. <a href="<?= e($linkCategoria($categoriaAtual, false)) ?>">Limpar busca</a></p>
    <?php endif; ?>

    <?php if ($produtos): ?>
        <h2 class="visually-hidden">Aromas disponíveis</h2>
        <div class="product-grid">
            <?php foreach ($produtos as $p): ?>
                <?php View::partial('partials/product-card', ['p' => $p]); ?>
            <?php endforeach; ?>
        </div>
    <?php elseif ($busca !== ''): ?>
        <div class="empty-state" role="status">
            <h2>Nenhum aroma com “<?= e($busca) ?>”</h2>
            <p>A busca olha nome, aroma e notas. Tente uma nota como figo, lavanda, bergamota ou âmbar, ou fale com a Geo para uma indicação.</p>
            <a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver todos os aromas</a>
            <a class="btn btn-ghost" href="<?= e(whatsapp_url('Olá! Procuro um aroma com ' . $busca . '.')) ?>" target="_blank" rel="noopener">Pedir indicação no WhatsApp</a>
        </div>
    <?php else: ?>
        <div class="empty-state" role="status">
            <h2>Esta coleção está sem frascos agora</h2>
            <p>Os aromas voltam a cada novo lote. Enquanto isso, veja os que estão disponíveis.</p>
            <a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver todos os aromas</a>
        </div>
    <?php endif; ?>
</section>
