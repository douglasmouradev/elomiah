<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="app-base" content="<?= e(rtrim((string) (config('app')['url'] ?? ''), '/')) ?>">
    <title><?= e($title ?? 'Ateliê Elomiah') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/admin-base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">
<?php
$avisosNaoLidos = \App\Models\Notificacao::naoLidas();
$avisos = \App\Models\Notificacao::recentes(8);
$contatosNovos = \App\Models\MensagemContato::count('lido = 0');
$lgpdPendentes = \App\Models\SolicitacaoLgpd::count('status = :s', ['s' => 'pendente']);
?>
<aside class="admin-side">
    <a class="admin-brand" href="<?= e(url('/admin')) ?>">ELOMIAH</a>
    <nav class="admin-nav">
        <a class="<?= is_active('/admin') && current_path() === '/admin' ? 'is-on' : '' ?>" href="<?= e(url('/admin')) ?>">Painel</a>
        <a class="<?= is_active('/admin/produtos') ? 'is-on' : '' ?>" href="<?= e(url('/admin/produtos')) ?>">Produtos</a>
        <a class="<?= is_active('/admin/pedidos') ? 'is-on' : '' ?>" href="<?= e(url('/admin/pedidos')) ?>">
            Pedidos
            <?php if ($avisosNaoLidos > 0): ?>
                <span class="nav-badge"><?= (int) $avisosNaoLidos ?></span>
            <?php endif; ?>
        </a>
        <a class="<?= is_active('/admin/depoimentos') ? 'is-on' : '' ?>" href="<?= e(url('/admin/depoimentos')) ?>">Depoimentos</a>
        <a class="<?= is_active('/admin/curso') ? 'is-on' : '' ?>" href="<?= e(url('/admin/curso')) ?>">Curso</a>
        <a class="<?= is_active('/admin/contato') ? 'is-on' : '' ?>" href="<?= e(url('/admin/contato')) ?>">
            Contato
            <?php if ($contatosNovos > 0): ?>
                <span class="nav-badge"><?= (int) $contatosNovos ?></span>
            <?php endif; ?>
        </a>
        <a class="<?= is_active('/admin/lgpd') ? 'is-on' : '' ?>" href="<?= e(url('/admin/lgpd')) ?>">
            Dados
            <?php if ($lgpdPendentes > 0): ?>
                <span class="nav-badge"><?= (int) $lgpdPendentes ?></span>
            <?php endif; ?>
        </a>
        <a class="<?= is_active('/admin/pagamento') ? 'is-on' : '' ?>" href="<?= e(url('/admin/pagamento')) ?>">Pagamento</a>
        <a class="<?= is_active('/admin/conta') ? 'is-on' : '' ?>" href="<?= e(url('/admin/conta')) ?>">Senha</a>
        <a class="<?= is_active('/admin/logs') ? 'is-on' : '' ?>" href="<?= e(url('/admin/logs')) ?>">Auditoria</a>
        <a href="<?= e(url('/')) ?>">Ver o site</a>
        <a href="<?= e(url('/admin/sair')) ?>">Sair</a>
    </nav>
</aside>
<div class="admin-main">
    <div class="admin-top">
        <h1 style="font-family:Playfair Display,serif;font-size:1.6rem;margin:0"><?= e($title ?? '') ?></h1>
        <div class="admin-top-end">
            <div class="admin-notify" data-notify>
                <button class="admin-notify-btn" type="button" aria-label="Notificações" data-notify-toggle>
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                        <path d="M6 9a6 6 0 1 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/>
                        <path d="M10 19a2 2 0 0 0 4 0"/>
                    </svg>
                    <span class="admin-notify-count<?= $avisosNaoLidos ? ' is-on' : '' ?>" data-notify-count><?= $avisosNaoLidos ? (int) $avisosNaoLidos : '' ?></span>
                </button>
                <div class="admin-notify-panel" data-notify-panel hidden>
                    <div class="admin-notify-head">
                        <strong>Avisos</strong>
                        <button type="button" class="admin-notify-clear" data-notify-all>Marcar lidas</button>
                    </div>
                    <div data-notify-list>
                        <?php if (!$avisos): ?>
                            <p class="admin-notify-empty">Nenhum aviso por agora.</p>
                        <?php else: ?>
                            <?php foreach ($avisos as $aviso): ?>
                                <a class="admin-notify-item<?= empty($aviso['lida']) ? ' is-new' : '' ?>" href="<?= e(url((string) $aviso['link'])) ?>" data-notify-id="<?= (int) $aviso['id'] ?>">
                                    <strong><?= e($aviso['titulo']) ?></strong>
                                    <span><?= e((string) $aviso['mensagem']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <span><?= e(\App\Core\Auth::user()['nome'] ?? '') ?></span>
        </div>
    </div>
    <?php if ($msg = flash('success')): ?><div class="alert alert-ok"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('error')): ?><div class="alert alert-err"><?= e($msg) ?></div><?php endif; ?>
    <?= $content ?? '' ?>
</div>
<script src="<?= e(asset('js/admin.js')) ?>"></script>
</body>
</html>
