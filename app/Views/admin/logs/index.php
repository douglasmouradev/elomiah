<table class="table">
    <thead><tr><th>Quando</th><th>Quem</th><th>Ação</th><th>Entidade</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach ($logs ?? [] as $l): ?>
        <tr>
            <td><?= e($l['created_at']) ?></td>
            <td><?= e($l['admin_nome'] ?? '') ?></td>
            <td><?= e($l['acao']) ?></td>
            <td><?= e($l['entidade']) ?> <?= e((string) ($l['entidade_id'] ?? '')) ?></td>
            <td><?= e($l['ip'] ?? '') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
