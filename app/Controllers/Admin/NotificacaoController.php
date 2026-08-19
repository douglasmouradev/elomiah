<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Notificacao;

final class NotificacaoController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $lista = [];
        foreach (Notificacao::recentes() as $n) {
            $lista[] = [
                'id' => (int) $n['id'],
                'titulo' => $n['titulo'],
                'mensagem' => $n['mensagem'],
                'link' => url((string) $n['link']),
                'lida' => (int) $n['lida'] === 1,
                'quando' => $n['created_at'],
            ];
        }
        $this->json([
            'ok' => true,
            'nao_lidas' => Notificacao::naoLidas(),
            'itens' => $lista,
        ]);
    }

    public function lida(Request $request, array $params = []): never
    {
        Notificacao::marcarLida((int) ($params['id'] ?? 0));
        $this->json(['ok' => true, 'nao_lidas' => Notificacao::naoLidas()]);
    }

    public function todas(Request $request, array $params = []): never
    {
        Notificacao::marcarTodas();
        $this->json(['ok' => true, 'nao_lidas' => 0]);
    }
}
