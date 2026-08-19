<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

final class LogController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $logs = Database::fetchAll(
            'SELECT l.*, u.nome AS admin_nome
             FROM logs_admin l
             LEFT JOIN usuarios u ON u.id = l.usuario_id
             ORDER BY l.id DESC
             LIMIT 200'
        );

        $this->view('admin/logs/index', [
            'title' => 'Auditoria — Ateliê',
            'logs' => $logs,
        ], 'layouts/admin');
    }
}
