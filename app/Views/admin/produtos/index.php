<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
    <p>Catálogo do ateliê</p>
    <a class="btn btn-gold" href="<?= e(url('/admin/produtos/novo')) ?>">+ Adicionar novo produto</a>
</div>
<table class="table">
    <thead>
    <tr><th></th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Estoque</th><th>Status</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($produtos ?? [] as $p): ?>
        <tr>
            <td>
                <?php if (!empty($p['imagem'])): ?>
                    <img class="thumb-sm" src="<?= e(asset($p['imagem'])) ?>" alt="">
                <?php endif; ?>
            </td>
            <td><?= e($p['nome']) ?><?php if ($p['destaque']): ?> · destaque<?php endif; ?><?php if ($p['achadinho_geo']): ?> · achadinho<?php endif; ?></td>
            <td><?= e($p['categoria_nome'] ?? '') ?></td>
            <td><?= e(money($p['preco'])) ?></td>
            <td><?= (int) $p['estoque'] ?></td>
            <td><?= e($p['status']) ?></td>
            <td>
                <a href="<?= e(url('/admin/produtos/' . $p['id'] . '/editar')) ?>">Editar</a>
                <form method="post" action="<?= e(url('/admin/produtos/' . $p['id'] . '/excluir')) ?>" style="display:inline" onsubmit="return confirm('Excluir este produto?')">
                    <?= csrf_field() ?>
                    <button type="submit">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
