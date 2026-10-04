<?php
\App\Core\View::partial('partials/flash');
$usuario = $usuario ?? [];
$pedidos = $pedidos ?? [];
?>
<section class="page-hero container">
    <h1>Minha conta</h1>
    <p class="lede">Pedidos, rastreio, endereço e senha.</p>
</section>
<section class="container account-page">
    <p class="account-hello">Olá, <?= e((string) ($usuario['nome'] ?? '')) ?>.</p>

    <?php if (!empty($cursoAcesso)): ?>
        <article class="account-address">
            <h2>Curso O Ritual das Essências</h2>
            <p class="lede">O Ritual das Essências está liberado nesta conta.</p>
            <?php if (!empty($cursoUrl)): ?>
                <p><a class="btn btn-gold" href="<?= e((string) $cursoUrl) ?>" target="_blank" rel="noopener">Abrir as aulas</a></p>
            <?php else: ?>
                <p class="field-hint">O pagamento entrou. O caminho das aulas aparece aqui quando a Geo publicar o link.</p>
            <?php endif; ?>
        </article>
    <?php endif; ?>

    <article class="account-address">
        <h2>Seus dados</h2>
        <form class="form" method="post" action="<?= e(url('/conta/perfil')) ?>">
            <?= csrf_field() ?>
            <label><span>Nome</span><input type="text" name="nome" value="<?= e((string) ($usuario['nome'] ?? '')) ?>" required></label>
            <label><span>Telefone</span><input type="tel" name="telefone" value="<?= e((string) ($usuario['telefone'] ?? '')) ?>" required></label>
            <button class="btn btn-ghost" type="submit">Guardar dados</button>
        </form>
        <form class="form" method="post" action="<?= e(url('/conta/senha')) ?>" style="margin-top:var(--s-3)">
            <?= csrf_field() ?>
            <label><span>Senha atual</span><input type="password" name="senha_atual" required autocomplete="current-password"></label>
            <label><span>Nova senha</span><input type="password" name="senha" required minlength="8" autocomplete="new-password"></label>
            <label><span>Confirmar</span><input type="password" name="senha_confirmation" required minlength="8" autocomplete="new-password"></label>
            <button class="btn btn-ghost" type="submit">Trocar senha</button>
        </form>
    </article>

    <?php $endereco = $endereco ?? null; $freteInfo = frete(); ?>
    <article class="account-address">
        <h2>Endereço de envio</h2>
        <p class="field-hint"><?= e($freteInfo['nome']) ?> · <?= e($freteInfo['prazo']) ?></p>
        <form class="form" method="post" action="<?= e(url('/conta/endereco')) ?>">
            <?= csrf_field() ?>
            <label><span>CEP</span>
                <input type="text" name="cep" id="cep" maxlength="9" value="<?= e(cep_format($endereco['cep'] ?? '')) ?>" required autocomplete="postal-code">
            </label>
            <p id="cep-hint" class="field-hint">Ao informar o CEP, rua, bairro, cidade e estado se preenchem sozinhos.</p>
            <label><span>Rua</span><input type="text" name="logradouro" id="logradouro" value="<?= e((string) ($endereco['logradouro'] ?? '')) ?>" required></label>
            <div class="split-2">
                <label><span>Número</span><input type="text" name="numero" id="numero" value="<?= e((string) ($endereco['numero'] ?? '')) ?>" required></label>
                <label><span>Complemento</span><input type="text" name="complemento" value="<?= e((string) ($endereco['complemento'] ?? '')) ?>"></label>
            </div>
            <label><span>Bairro</span><input type="text" name="bairro" id="bairro" value="<?= e((string) ($endereco['bairro'] ?? '')) ?>" required></label>
            <div class="split-2">
                <label><span>Cidade</span><input type="text" name="cidade" id="cidade" value="<?= e((string) ($endereco['cidade'] ?? '')) ?>" required></label>
                <label><span>Estado</span><input type="text" name="estado" id="estado" maxlength="2" value="<?= e((string) ($endereco['estado'] ?? '')) ?>" required></label>
            </div>
            <button class="btn btn-ghost" type="submit">Guardar endereço</button>
        </form>
    </article>

    <?php if (!$pedidos): ?>
        <div class="cart-empty">
            <p>Você ainda não fez pedidos. Quando fizer, o andamento e o código de rastreio aparecem aqui.</p>
            <p><a class="btn btn-gold" href="<?= e(url('/loja')) ?>">Ver os aromas</a></p>
        </div>
    <?php else: ?>
        <div class="account-orders">
            <?php foreach ($pedidos as $pedido): ?>
                <?php $rastreioLink = rastreio_url($pedido['codigo_rastreio'] ?? null, $pedido['transportadora'] ?? null); ?>
                <article class="account-order">
                    <header>
                        <h3><?= e($pedido['codigo']) ?></h3>
                        <?php $digitalPedido = pedido_so_digital($pedido); ?>
                    <span class="status-pill status-<?= e($pedido['status']) ?>"><?= e(pedido_status_rotulo($pedido['status'], $digitalPedido)) ?></span>
                    </header>
                    <ul class="account-items">
                        <?php foreach ($pedido['itens'] ?? [] as $item): ?>
                            <li>
                                <img src="<?= e(asset($item['imagem'] ?? 'images/frasco-despertar.webp')) ?>" alt="">
                                <span>
                                    <strong><?= e($item['nome_produto']) ?></strong>
                                    <small><?= (int) $item['quantidade'] ?> × <?= e(money($item['preco_unitario'])) ?></small>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="account-total">Total <?= e(money($pedido['total'])) ?></p>
                    <?php if (!empty($pedido['codigo_rastreio'])): ?>
                        <p class="account-track">
                            <?= e($pedido['transportadora'] ?: 'Correios') ?> · <?= e($pedido['codigo_rastreio']) ?>
                            <?php if ($rastreioLink): ?>
                                <a href="<?= e($rastreioLink) ?>" target="_blank" rel="noopener">Rastrear envio</a>
                            <?php endif; ?>
                        </p>
                    <?php elseif (($pedido['status'] ?? '') === 'pago' && $digitalPedido): ?>
                        <p class="account-track">Pago. A formação está nesta conta.
                            <a href="<?= e(url('/conta/curso')) ?>">Abrir as aulas</a>
                        </p>
                    <?php elseif (($pedido['status'] ?? '') === 'pago'): ?>
                        <p class="account-track">Pago. Aguardando o despacho do ateliê.</p>
                    <?php elseif (($pedido['status'] ?? '') === 'pendente'): ?>
                        <p class="account-track">Aguardando o pagamento.</p>
                    <?php endif; ?>
                    <p>
                        <a class="btn btn-ghost" href="<?= e(url('/conta/pedidos/' . $pedido['codigo'])) ?>">Ver pedido</a>
                        <?php if (\App\Support\NotaCompra::liberada($pedido)): ?>
                            <a class="btn btn-ghost" href="<?= e(url('/pedido/' . $pedido['codigo'] . '/nota')) ?>">Ver recibo</a>
                        <?php endif; ?>
                    </p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
