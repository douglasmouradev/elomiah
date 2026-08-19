<table class="table">
    <thead><tr><th>Nome</th><th>Nota</th><th>Texto</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($depoimentos ?? [] as $d): ?>
        <tr>
            <td><?= e($d['nome']) ?></td>
            <td><?= (int) $d['nota'] ?></td>
            <td><?= e(str_limit($d['texto'], 160)) ?></td>
            <td><?= e($d['status']) ?></td>
            <td>
                <form method="post" action="<?= e(url('/admin/depoimentos/' . $d['id'])) ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="aprovado">
                    <button type="submit">Aprovar</button>
                </form>
                <form method="post" action="<?= e(url('/admin/depoimentos/' . $d['id'])) ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="reprovado">
                    <button type="submit">Reprovar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
