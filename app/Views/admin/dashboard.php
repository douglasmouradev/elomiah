<?php
$metricas = $metricas ?? ['qtd' => 0, 'faturamento' => 0, 'ticket' => 0, 'grafico' => [], 'status' => []];
$statusMap = [];
foreach ($metricas['status'] as $s) {
    $statusMap[$s['status']] = (int) $s['qtd'];
}
$labels = array_column($metricas['grafico'], 'dia');
$valores = array_map('floatval', array_column($metricas['grafico'], 'valor'));
?>
<div class="period-tabs" style="margin-bottom:1rem">
    <a class="chip <?= ($periodo ?? '') === 'dia' ? 'is-on' : '' ?>" href="<?= e(url('/admin?periodo=dia')) ?>">Dia</a>
    <a class="chip <?= ($periodo ?? '') === 'semana' ? 'is-on' : '' ?>" href="<?= e(url('/admin?periodo=semana')) ?>">Semana</a>
    <a class="chip <?= ($periodo ?? '') === 'mes' ? 'is-on' : '' ?>" href="<?= e(url('/admin?periodo=mes')) ?>">Mês</a>
</div>
<div class="metrics">
    <div class="metric"><span>Vendas</span><b><?= (int) $metricas['qtd'] ?></b></div>
    <div class="metric"><span>Faturamento</span><b><?= e(money($metricas['faturamento'])) ?></b></div>
    <div class="metric"><span>Ticket médio</span><b><?= e(money($metricas['ticket'])) ?></b></div>
    <div class="metric"><span>Conversão (30d)</span><b><?= e((string) ($conversao ?? 0)) ?>%</b></div>
    <div class="metric"><span>Visitantes (30d)</span><b><?= (int) ($visitantes ?? 0) ?></b></div>
    <div class="metric"><span>Pedidos pendentes</span><b><?= (int) ($pendentes ?? 0) ?></b></div>
    <div class="metric"><span>Pagos</span><b><?= (int) ($statusMap['pago'] ?? 0) ?></b></div>
    <div class="metric"><span>Depoimentos à moderar</span><b><?= (int) ($depoimentosPendentes ?? 0) ?></b></div>
</div>
<div class="chart-wrap">
    <canvas id="chartFaturamento" height="110"></canvas>
</div>
<div class="split-2">
    <div>
        <h2>Mais vendidos</h2>
        <table class="table">
            <thead><tr><th>Produto</th><th>Qtd</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($maisVendidos ?? [] as $row): ?>
                <tr>
                    <td><?= e($row['nome']) ?></td>
                    <td><?= (int) $row['qtd'] ?></td>
                    <td><?= e(money($row['total'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div>
        <h2>Pedidos recentes</h2>
        <table class="table">
            <thead><tr><th>Código</th><th>Status</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($pedidosRecentes ?? [] as $p): ?>
                <tr>
                    <td><a href="<?= e(url('/admin/pedidos/' . $p['id'])) ?>"><?= e($p['codigo']) ?></a></td>
                    <td><span class="status-pill status-<?= e($p['status']) ?>"><?= e(pedido_status_rotulo($p['status'])) ?></span></td>
                    <td><?= e(money($p['total'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
(() => {
  const el = document.getElementById('chartFaturamento');
  if (!el || typeof Chart === 'undefined') return;
  const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
  const data = <?= json_encode($valores) ?>;
  new Chart(el, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Faturamento',
        data,
        borderColor: '#C9A24B',
        backgroundColor: 'rgba(201,162,75,.12)',
        tension: 0.35,
        fill: true,
        pointRadius: 3
      }]
    },
    options: {
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#4A4A45' }, grid: { color: 'rgba(201,162,75,.15)' } },
        y: { ticks: { color: '#4A4A45' }, grid: { color: 'rgba(201,162,75,.15)' } }
      }
    }
  });
})();
</script>
