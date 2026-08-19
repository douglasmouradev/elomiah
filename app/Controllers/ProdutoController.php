<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Depoimento;
use App\Models\Produto;

final class ProdutoController extends Controller
{
    public function show(Request $request, array $params = []): never
    {
        $slug = (string) ($params['slug'] ?? '');
        $produto = Produto::bySlug($slug);
        if (!$produto || $produto['status'] !== 'ativo') {
            Response::abort(404, 'Este aroma não está disponível.');
        }
        if (produto_digital($produto)) {
            redirect('/curso');
        }

        $imagens = Produto::imagens((int) $produto['id']);
        $capa = $imagens[0]['caminho'] ?? Produto::capa($produto);
        $relacionados = array_filter(
            Produto::ativos(['categoria' => $produto['categoria_slug'] ?? '']),
            static fn ($p) => (int) $p['id'] !== (int) $produto['id']
        );

        $this->view('produto/show', [
            'title' => $produto['nome'] . ' — Elomiah',
            'metaDescription' => (string) ($produto['descricao_curta'] ?? $produto['citacao'] ?? ''),
            'ogTitle' => $produto['nome'] . ' — Elomiah',
            'ogDescription' => (string) ($produto['descricao_curta'] ?: 'Névoa Elomiah em vidro, 120 ml.'),
            'ogImage' => asset($capa),
            'produto' => $produto,
            'imagens' => $imagens,
            'relacionados' => array_slice($relacionados, 0, 3),
            'depoimentos' => array_filter(
                Depoimento::where('status', 'aprovado'),
                static fn ($d) => (int) ($d['produto_id'] ?? 0) === (int) $produto['id']
            ),
        ]);
    }
}
