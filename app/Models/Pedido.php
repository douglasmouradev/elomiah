<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Pedido extends Model
{
    protected static string $table = 'pedidos';

    public static function withItems(int $id): ?array
    {
        $pedido = self::find($id);
        if (!$pedido) {
            return null;
        }
        $pedido['itens'] = Database::fetchAll(
            'SELECT * FROM itens_pedido WHERE pedido_id = :id',
            ['id' => $id]
        );
        $pedido['usuario'] = $pedido['usuario_id']
            ? Database::fetch('SELECT id, nome, email, telefone FROM usuarios WHERE id = :id', ['id' => $pedido['usuario_id']])
            : null;
        $pedido['endereco'] = $pedido['endereco_id']
            ? Database::fetch('SELECT * FROM enderecos WHERE id = :id', ['id' => $pedido['endereco_id']])
            : null;
        return $pedido;
    }

    public static function statuses(): array
    {
        return ['pendente', 'pago', 'enviado', 'entregue', 'cancelado'];
    }

    public static function garantirColunas(): void
    {
        static $feito = false;
        if ($feito) {
            return;
        }

        $cols = Database::fetchAll('SHOW COLUMNS FROM pedidos');
        $nomes = array_column($cols, 'Field');
        $novas = [
            'codigo_rastreio' => 'VARCHAR(80) DEFAULT NULL',
            'transportadora' => 'VARCHAR(80) DEFAULT NULL',
            'pago_em' => 'DATETIME DEFAULT NULL',
            'enviado_em' => 'DATETIME DEFAULT NULL',
            'entregue_em' => 'DATETIME DEFAULT NULL',
            'cancelado_em' => 'DATETIME DEFAULT NULL',
        ];
        foreach ($novas as $coluna => $def) {
            if (!in_array($coluna, $nomes, true)) {
                Database::connection()->exec("ALTER TABLE pedidos ADD COLUMN `{$coluna}` {$def}");
            }
        }
        $feito = true;
    }

    public static function mudarStatus(int $id, string $novo, array $extra = []): bool
    {
        self::garantirColunas();
        $pedido = self::find($id);
        if (!$pedido || !in_array($novo, self::statuses(), true)) {
            return false;
        }

        $atual = (string) $pedido['status'];
        $agora = now();
        $dados = ['status' => $novo];

        if ($novo === 'pago' && empty($pedido['pago_em'])) {
            $dados['pago_em'] = $agora;
        }
        if ($novo === 'enviado') {
            if (empty($pedido['enviado_em']) || $atual !== 'enviado') {
                $dados['enviado_em'] = $agora;
            }
            if (array_key_exists('codigo_rastreio', $extra)) {
                $rastreio = trim((string) $extra['codigo_rastreio']);
                $dados['codigo_rastreio'] = $rastreio !== '' ? $rastreio : null;
            }
            if (array_key_exists('transportadora', $extra)) {
                $via = trim((string) $extra['transportadora']);
                $dados['transportadora'] = $via !== '' ? $via : null;
            }
        }
        if ($novo === 'entregue' && empty($pedido['entregue_em'])) {
            $dados['entregue_em'] = $agora;
        }
        if ($novo === 'cancelado' && $atual !== 'cancelado') {
            self::devolverEstoque($id);
            $dados['cancelado_em'] = $agora;
        }

        self::updateById($id, $dados);
        return true;
    }

    public static function doCliente(int $usuarioId): array
    {
        self::garantirColunas();
        if ($usuarioId < 1) {
            return [];
        }

        $pedidos = Database::fetchAll(
            'SELECT * FROM pedidos WHERE usuario_id = :id ORDER BY id DESC',
            ['id' => $usuarioId]
        );
        foreach ($pedidos as &$pedido) {
            $pedido['itens'] = self::itensComImagem((int) $pedido['id']);
        }
        unset($pedido);

        return $pedidos;
    }

    public static function doClientePorCodigo(int $usuarioId, string $codigo): ?array
    {
        self::garantirColunas();
        $pedido = Database::fetch(
            'SELECT * FROM pedidos WHERE codigo = :codigo AND usuario_id = :uid LIMIT 1',
            ['codigo' => $codigo, 'uid' => $usuarioId]
        );
        if (!$pedido) {
            return null;
        }
        $pedido['itens'] = self::itensComImagem((int) $pedido['id']);
        $pedido['endereco'] = $pedido['endereco_id']
            ? Database::fetch('SELECT * FROM enderecos WHERE id = :id', ['id' => $pedido['endereco_id']])
            : null;

        return $pedido;
    }

    private static function itensComImagem(int $pedidoId): array
    {
        return Database::fetchAll(
            'SELECT i.*,
                    (SELECT caminho FROM imagens_produto img
                     WHERE img.produto_id = i.produto_id
                     ORDER BY img.ordem ASC, img.id ASC LIMIT 1) AS imagem
             FROM itens_pedido i
             WHERE i.pedido_id = :id',
            ['id' => $pedidoId]
        );
    }

    private static function devolverEstoque(int $pedidoId): void
    {
        $itens = Database::fetchAll(
            'SELECT produto_id, quantidade FROM itens_pedido WHERE pedido_id = :id',
            ['id' => $pedidoId]
        );
        foreach ($itens as $item) {
            $produtoId = (int) ($item['produto_id'] ?? 0);
            if ($produtoId < 1) {
                continue;
            }
            $produto = Produto::find($produtoId);
            if (produto_digital($produto)) {
                continue;
            }
            Database::query(
                'UPDATE produtos SET estoque = estoque + :q WHERE id = :id',
                ['q' => (int) $item['quantidade'], 'id' => $produtoId]
            );
        }
    }

    public static function metricas(string $periodo = 'mes'): array
    {
        $since = match ($periodo) {
            'dia' => date('Y-m-d 00:00:00'),
            'semana' => date('Y-m-d 00:00:00', strtotime('-7 days')),
            default => date('Y-m-01 00:00:00'),
        };

        $vendas = Database::fetch(
            'SELECT COUNT(*) AS qtd, COALESCE(SUM(total),0) AS faturamento, COALESCE(AVG(total),0) AS ticket
             FROM pedidos WHERE status IN ("pago","enviado","entregue") AND created_at >= :s',
            ['s' => $since]
        );

        $status = Database::fetchAll(
            'SELECT status, COUNT(*) AS qtd FROM pedidos GROUP BY status'
        );

        $grafico = Database::fetchAll(
            'SELECT DATE(created_at) AS dia, COALESCE(SUM(total),0) AS valor
             FROM pedidos
             WHERE status IN ("pago","enviado","entregue") AND created_at >= :s
             GROUP BY DATE(created_at)
             ORDER BY dia ASC',
            ['s' => date('Y-m-d 00:00:00', strtotime('-30 days'))]
        );

        return [
            'qtd' => (int) ($vendas['qtd'] ?? 0),
            'faturamento' => (float) ($vendas['faturamento'] ?? 0),
            'ticket' => (float) ($vendas['ticket'] ?? 0),
            'status' => $status,
            'grafico' => $grafico,
        ];
    }
}
