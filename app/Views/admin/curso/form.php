<?php $curso = $curso ?? []; ?>
<form class="form" method="post" action="<?= e(url('/admin/curso')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) ($curso['id'] ?? 0) ?>">
    <label><span>Título</span><input type="text" name="titulo" value="<?= e($curso['titulo'] ?? '') ?>"></label>
    <label><span>Preço</span><input type="text" name="preco" value="<?= e((string) ($curso['preco'] ?? '')) ?>"></label>
    <label><span>Status</span>
        <select name="status">
            <option value="ativo" <?= ($curso['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="inativo" <?= ($curso['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
    </label>
    <label><span>Descrição</span><textarea name="descricao"><?= e($curso['descricao'] ?? '') ?></textarea></label>
    <label><span>Link das aulas (https)</span>
        <input type="url" name="acesso_url" value="<?= e((string) ($curso['acesso_url'] ?? '')) ?>" placeholder="https://…" inputmode="url">
    </label>
    <p class="field-hint">Depois do pagamento, a aluna recebe este link por e-mail e o botão aparece na conta. Memberkit, YouTube privado ou Drive.</p>
    <h2>Módulos</h2>
    <?php foreach ($modulos ?? [] as $m): ?>
        <input type="hidden" name="modulo_id[]" value="<?= (int) $m['id'] ?>">
        <label><span>Título</span><input type="text" name="modulo_titulo[]" value="<?= e($m['titulo']) ?>"></label>
        <label><span>Descrição</span><textarea name="modulo_desc[]"><?= e($m['descricao']) ?></textarea></label>
        <label><span>Duração</span><input type="text" name="modulo_duracao[]" value="<?= e($m['duracao']) ?>"></label>
        <hr style="border:0;border-top:1px solid var(--linha)">
    <?php endforeach; ?>
    <p>Novo módulo</p>
    <input type="hidden" name="modulo_id[]" value="0">
    <label><span>Título</span><input type="text" name="modulo_titulo[]"></label>
    <label><span>Descrição</span><textarea name="modulo_desc[]"></textarea></label>
    <label><span>Duração</span><input type="text" name="modulo_duracao[]"></label>
    <button class="btn btn-gold" type="submit">Guardar curso</button>
</form>
