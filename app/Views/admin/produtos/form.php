<?php
$p = $produto ?? [];
$old = fn(string $k, $d = '') => old($k, $p[$k] ?? $d);
?>
<form class="form grid-form" method="post" enctype="multipart/form-data"
      action="<?= e($p ? url('/admin/produtos/' . $p['id']) : url('/admin/produtos')) ?>">
    <?= csrf_field() ?>
    <label class="full"><span>Nome</span><input type="text" name="nome" value="<?= e((string) $old('nome')) ?>" required></label>
    <label><span>Slug (vazio = automático)</span><input type="text" name="slug" value="<?= e((string) $old('slug')) ?>"></label>
    <label><span>Categoria</span>
        <select name="categoria_id" required>
            <?php foreach ($categorias ?? [] as $c): ?>
                <option value="<?= (int) $c['id'] ?>" <?= (int) $old('categoria_id') === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label><span>Preço</span><input type="text" name="preco" value="<?= e((string) $old('preco')) ?>" required></label>
    <label><span>Preço promocional</span><input type="text" name="preco_promocional" value="<?= e((string) $old('preco_promocional')) ?>"></label>
    <label><span>Estoque</span><input type="number" name="estoque" value="<?= e((string) $old('estoque', '0')) ?>"></label>
    <label><span>SKU</span><input type="text" name="sku" value="<?= e((string) $old('sku')) ?>"></label>
    <label><span>Volume</span><input type="text" name="volume" value="<?= e((string) $old('volume')) ?>"></label>
    <label class="full"><span>Descrição curta</span><input type="text" name="descricao_curta" value="<?= e((string) $old('descricao_curta')) ?>"></label>
    <label class="full"><span>Descrição</span><textarea name="descricao"><?= e((string) $old('descricao')) ?></textarea></label>
    <label><span>Notas de topo</span><input type="text" name="notas_topo" value="<?= e((string) $old('notas_topo')) ?>"></label>
    <label><span>Notas de coração</span><input type="text" name="notas_coracao" value="<?= e((string) $old('notas_coracao')) ?>"></label>
    <label><span>Notas de fundo</span><input type="text" name="notas_fundo" value="<?= e((string) $old('notas_fundo')) ?>"></label>
    <label><span>Aroma</span><input type="text" name="aroma" value="<?= e((string) $old('aroma')) ?>"></label>
    <label><span>Coleção</span><input type="text" name="colecao" value="<?= e((string) $old('colecao', 'Coleção Refúgio')) ?>"></label>
    <label><span>Cor da faixa (#hex)</span><input type="text" name="cor_destaque" value="<?= e((string) $old('cor_destaque', '#1B4332')) ?>"></label>
    <label class="full"><span>Citação do rótulo</span><input type="text" name="citacao" value="<?= e((string) $old('citacao')) ?>"></label>
    <label class="full"><span>Ficha técnica</span><textarea name="ficha_tecnica"><?= e((string) $old('ficha_tecnica')) ?></textarea></label>
    <label class="full"><span>Modo de usar</span><textarea name="modo_usar"><?= e((string) $old('modo_usar')) ?></textarea></label>
    <label class="full"><span>Precauções</span><textarea name="precaucoes"><?= e((string) $old('precaucoes')) ?></textarea></label>
    <label><span>Status</span>
        <select name="status">
            <option value="ativo" <?= $old('status', 'ativo') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="inativo" <?= $old('status') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
    </label>
    <label><span>Botão de compra</span>
        <select name="compra_tipo">
            <?php foreach (['carrinho' => 'Sacola interna', 'shopee' => 'Shopee', 'amazon' => 'Amazon', 'mercadolivre' => 'Mercado Livre'] as $k => $lab): ?>
                <option value="<?= $k ?>" <?= $old('compra_tipo', 'carrinho') === $k ? 'selected' : '' ?>><?= e($lab) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="full"><span>URL Shopee</span><input type="url" name="url_shopee" value="<?= e((string) $old('url_shopee')) ?>"></label>
    <label class="full"><span>URL Amazon</span><input type="url" name="url_amazon" value="<?= e((string) $old('url_amazon')) ?>"></label>
    <label class="full"><span>URL Mercado Livre</span><input type="url" name="url_mercadolivre" value="<?= e((string) $old('url_mercadolivre')) ?>"></label>
    <label><span><input type="checkbox" name="destaque" <?= $old('destaque') ? 'checked' : '' ?>> Destacar na home</span></label>
    <label class="full"><span>Imagens (múltiplas)</span>
        <input type="file" name="imagens[]" id="imagens" accept="image/jpeg,image/png,image/webp" multiple>
        <div class="previews" id="previews">
            <?php foreach ($imagens ?? [] as $img): ?>
                <img src="<?= e(asset($img['caminho'])) ?>" alt="">
            <?php endforeach; ?>
        </div>
    </label>
    <div class="full">
        <button class="btn btn-gold" type="submit"><?= $p ? 'Guardar alterações' : 'Criar produto' ?></button>
        <a class="btn btn-ghost" href="<?= e(url('/admin/produtos')) ?>">Voltar</a>
    </div>
</form>
