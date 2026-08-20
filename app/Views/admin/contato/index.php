<?php $mensagens = $mensagens ?? []; ?>
<?php if (!$mensagens): ?>
    <p class="field-hint">Nenhuma mensagem pelo formulário ainda.</p>
<?php else: ?>
<table class="table">
    <thead><tr><th>Quando</th><th>De</th><th>Assunto</th><th>Mensagem</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($mensagens as $m): ?>
        <tr<?= empty($m['lido']) ? ' class="is-new"' : '' ?>>
            <td><?= e((string) ($m['created_at'] ?? '')) ?></td>
            <td>
                <?= e((string) ($m['nome'] ?? '')) ?><br>
                <a href="mailto:<?= e((string) ($m['email'] ?? '')) ?>"><?= e((string) ($m['email'] ?? '')) ?></a>
                <?php if (!empty($m['telefone'])): ?><br><?= e((string) $m['telefone']) ?><?php endif; ?>
            </td>
            <td><?= e((string) ($m['assunto'] ?? '')) ?></td>
            <td><?= nl2br(e((string) ($m['mensagem'] ?? ''))) ?></td>
            <td>
                <?php if (empty($m['lido'])): ?>
                    <form method="post" action="<?= e(url('/admin/contato/' . (int) $m['id'] . '/lida')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-ghost" type="submit">Marcar lida</button>
                    </form>
                <?php else: ?>
                    <span class="field-hint">Lida</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
