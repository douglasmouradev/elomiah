<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Categoria;
use App\Models\Produto;

final class LojaController extends Controller
{
    public function index(Request $request, array $params = []): never
    {
        $categoria = preg_replace('/[^a-z0-9\-]/', '', (string) $request->input('categoria', '')) ?: null;
        $ordenar = (string) $request->input('ordenar', 'lancamento');
        if (!in_array($ordenar, ['lancamento', 'preco_asc', 'preco_desc', 'nome'], true)) {
            $ordenar = 'lancamento';
        }
        $busca = mb_substr(trim((string) $request->input('q', '')), 0, 60);

        $todos = Produto::ativos();
        $contagem = [];
        foreach ($todos as $p) {
            $slug = (string) ($p['categoria_slug'] ?? '');
            $contagem[$slug] = ($contagem[$slug] ?? 0) + 1;
        }

        $categorias = [];
        foreach (Categoria::all('ordem ASC') as $c) {
            $total = $contagem[$c['slug'] ?? ''] ?? 0;
            if (($c['slug'] ?? '') !== 'formacao' && $total > 0) {
                $c['total'] = $total;
                $categorias[] = $c;
            }
        }

        $this->view('loja/index', [
            'title' => $busca !== '' ? 'Busca: ' . $busca . ' — Elomiah' : 'A loja — Elomiah',
            'categorias' => $categorias,
            'totalGeral' => count($todos),
            'produtos' => Produto::ativos([
                'categoria' => $categoria,
                'ordenar' => $ordenar,
                'busca' => $busca,
            ]),
            'categoriaAtual' => $categoria,
            'ordenar' => $ordenar,
            'busca' => $busca,
        ]);
    }
}
