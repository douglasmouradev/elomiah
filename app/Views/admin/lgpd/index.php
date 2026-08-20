<?php $solicitacoes = $solicitacoes ?? []; ?>
<p class="field-hint">Pedidos de exportar ou excluir dados feitos em /meus-dados. Prazo: 15 dias.</p>
<?php if (!$solicitacoes): ?>
    <p class="field-hint">Nenhuma solicitação ainda.</p>
<?php else: ?>
<table class="table">
    <thead><tr><th>Quando</th><th>De</th><th>Pedido</th><th>Mensagem</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($solicitacoes as $s): ?>
        <tr<?= ($s['status'] ?? '') === 'pendente' ? ' class="is-new"' : '' ?>>
            <td><?= e((string) ($s['created_at'] ?? '')) ?></td>
            <td>
                <?= e((string) ($s['nome'] ?? '')) ?><br>
                <a href="mailto:<?= e((string) ($s['email'] ?? '')) ?>"><?= e((string) ($s['email'] ?? '')) ?></a>
            </td>
            <td><?= ($s['tipo'] ?? '') === 'excluir' ? 'Excluir dados' : 'Exportar dados' ?></td>
            <td><?= nl2br(e((string) ($s['mensagem'] ?? ''))) ?></td>
            <td>
                <?php if (($s['status'] ?? '') === 'pendente'): ?>
                    <form method="post" action="<?= e(url('/admin/lgpd/' . (int) $s['id'] . '/atender')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost" type="submit">Marcar atendida</button>
                    </form>
                <?php else: ?>
                    <span class="field-hint">Atendida</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
