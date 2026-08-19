<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Depoimento;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Visitante;

final class DashboardController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $periodo = (string) $request->input('periodo', 'mes');
        if (!in_array($periodo, ['dia', 'semana', 'mes'], true)) {
            $periodo = 'mes';
        }

        $metricas = Pedido::metricas($periodo);
        $visitantes = Visitante::count('created_at >= :s', [
            's' => date('Y-m-d 00:00:00', strtotime('-30 days')),
        ]);
        $sessoes = (int) (Database::fetch(
            'SELECT COUNT(DISTINCT sessao) AS t FROM visitantes WHERE created_at >= :s',
            ['s' => date('Y-m-d 00:00:00', strtotime('-30 days'))]
        )['t'] ?? 0);
        $pedidos30 = Pedido::count('created_at >= :s', [
            's' => date('Y-m-d 00:00:00', strtotime('-30 days')),
        ]);
        $conversao = $sessoes > 0 ? round(($pedidos30 / $sessoes) * 100, 1) : 0;

        $this->view('admin/dashboard', [
            'title' => 'Painel — Elomiah',
            'periodo' => $periodo,
            'metricas' => $metricas,
            'maisVendidos' => Produto::maisVendidos(),
            'pendentes' => Pedido::count('status = :s', ['s' => 'pendente']),
            'depoimentosPendentes' => Depoimento::count('status = :s', ['s' => 'pendente']),
            'visitantes' => $visitantes,
            'conversao' => $conversao,
            'pedidosRecentes' => Database::fetchAll('SELECT * FROM pedidos ORDER BY id DESC LIMIT 8'),
        ], 'layouts/admin');
    }
}
