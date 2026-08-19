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

        $this->view('loja/index', [
            'title' => 'A loja — Elomiah',
            'categorias' => Categoria::all('ordem ASC'),
            'produtos' => Produto::ativos([
                'categoria' => $categoria,
                'ordenar' => $ordenar,
            ]),
            'categoriaAtual' => $categoria,
            'ordenar' => $ordenar,
        ]);
    }
}
