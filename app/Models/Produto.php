<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Produto extends Model
{
    protected static string $table = 'produtos';

    public static function ativos(array $filters = []): array
    {
        $sql = 'SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug,
                (SELECT caminho FROM imagens_produto i WHERE i.produto_id = p.id ORDER BY i.ordem ASC, i.id ASC LIMIT 1) AS imagem
                FROM produtos p
                LEFT JOIN categorias c ON c.id = p.categoria_id
                WHERE p.status = :status';
        $params = ['status' => 'ativo'];

        if (!empty($filters['categoria'])) {
            if ($filters['categoria'] === 'formacao') {
                return [];
            }
            $sql .= ' AND c.slug = :cat';
            $params['cat'] = $filters['categoria'];
        } else {
            $sql .= ' AND (c.slug IS NULL OR c.slug <> :formacao)';
            $params['formacao'] = 'formacao';
        }
        if (!empty($filters['destaque'])) {
            $sql .= ' AND p.destaque = 1';
        }
        $busca = trim((string) ($filters['busca'] ?? ''));
        if ($busca !== '') {
            $campos = ['p.nome', 'p.aroma', 'p.notas_topo', 'p.notas_coracao', 'p.notas_fundo', 'p.descricao_curta', 'c.nome'];
            $partes = [];
            foreach ($campos as $i => $campo) {
                $partes[] = $campo . ' LIKE :q' . $i;
                $params['q' . $i] = '%' . addcslashes($busca, '%_\\') . '%';
            }
            $sql .= ' AND (' . implode(' OR ', $partes) . ')';
        }

        $order = match ($filters['ordenar'] ?? 'lancamento') {
            'preco_asc' => 'COALESCE(NULLIF(p.preco_promocional, 0), p.preco) ASC',
            'preco_desc' => 'COALESCE(NULLIF(p.preco_promocional, 0), p.preco) DESC',
            'nome' => 'p.nome ASC',
            default => 'p.created_at DESC',
        };

        $sql .= ' ORDER BY ' . $order;
        return Database::fetchAll($sql, $params);
    }

    public static function bySlug(string $slug): ?array
    {
        return Database::fetch(
            'SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug
             FROM produtos p
             LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.slug = :slug LIMIT 1',
            ['slug' => $slug]
        );
    }

    public static function imagens(int $produtoId): array
    {
        return Database::fetchAll(
            'SELECT * FROM imagens_produto WHERE produto_id = :id ORDER BY ordem ASC, id ASC',
            ['id' => $produtoId]
        );
    }

    public static function capa(?array $produto): string
    {
        if (!$produto) {
            return 'images/frasco-despertar.webp';
        }
        if (!empty($produto['imagem'])) {
            return (string) $produto['imagem'];
        }
        $id = (int) ($produto['id'] ?? 0);
        if ($id < 1) {
            return 'images/frasco-despertar.webp';
        }
        $row = Database::fetch(
            'SELECT caminho FROM imagens_produto WHERE produto_id = :id ORDER BY ordem ASC, id ASC LIMIT 1',
            ['id' => $id]
        );

        return (string) ($row['caminho'] ?? 'images/frasco-despertar.webp');
    }

    public static function maisVendidos(int $limit = 5): array
    {
        return Database::fetchAll(
            'SELECT p.nome, SUM(i.quantidade) AS qtd, SUM(i.subtotal) AS total
             FROM itens_pedido i
             INNER JOIN produtos p ON p.id = i.produto_id
             INNER JOIN pedidos o ON o.id = i.pedido_id
             WHERE o.status IN ("pago","enviado","entregue")
             GROUP BY p.id, p.nome
             ORDER BY qtd DESC
             LIMIT ' . (int) $limit
        );
    }
}
