<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Curso extends Model
{
    protected static string $table = 'cursos';

    /** Garante um produto comprável com o mesmo slug e preço do curso. */
    public static function garantirProduto(?array $curso): ?array
    {
        if (!$curso) {
            return null;
        }

        $slug = (string) ($curso['slug'] ?? 'o-ritual-das-essencias');
        $cat = Categoria::firstWhere('slug', 'formacao');
        if (!$cat) {
            $catId = Categoria::create([
                'nome' => 'Formação',
                'slug' => 'formacao',
                'descricao' => 'Cursos com a Geo',
                'ordem' => 9,
            ]);
            $cat = Categoria::find($catId);
        }

        $dados = [
            'categoria_id' => (int) ($cat['id'] ?? 0),
            'nome' => (string) ($curso['titulo'] ?? 'O Ritual das Essências'),
            'slug' => $slug,
            'descricao' => (string) ($curso['descricao'] ?? ''),
            'descricao_curta' => 'Formação com a Geo. Acesso por 12 meses.',
            'preco' => (float) ($curso['preco'] ?? 0),
            'preco_promocional' => null,
            'estoque' => 999,
            'sku' => 'ELO-CURSO',
            'volume' => 'Acesso 12 meses',
            'colecao' => 'Formação',
            'cor_destaque' => '#1B4332',
            'destaque' => 0,
            'achadinho_geo' => 0,
            'status' => (($curso['status'] ?? 'ativo') === 'ativo') ? 'ativo' : 'inativo',
            'compra_tipo' => 'carrinho',
        ];

        $produto = self::produtoDoCurso($slug);
        if ($produto) {
            Produto::updateById((int) $produto['id'], $dados);
            $produto = Produto::find((int) $produto['id']);
        } else {
            $id = Produto::create($dados);
            $produto = Produto::find($id);
        }

        if ($produto && empty(Produto::imagens((int) $produto['id']))) {
            ImagemProduto::create([
                'produto_id' => (int) $produto['id'],
                'caminho' => (string) ($curso['imagem'] ?: 'images/still-sagrado.webp'),
                'alt' => (string) ($curso['titulo'] ?? 'Curso Elomiah'),
                'ordem' => 1,
            ]);
        }

        return $produto;
    }

    public static function produtoDoCurso(string $slug): ?array
    {
        return Database::fetch(
            'SELECT p.*, c.slug AS categoria_slug FROM produtos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.slug = :slug LIMIT 1',
            ['slug' => $slug]
        );
    }
}
