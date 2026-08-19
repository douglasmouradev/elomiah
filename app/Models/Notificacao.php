<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Notificacao extends Model
{
    protected static string $table = 'notificacoes';

    public static function garantir(): void
    {
        static $ok = false;
        if ($ok) {
            return;
        }
        Database::connection()->exec(
            "CREATE TABLE IF NOT EXISTS `notificacoes` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `tipo` VARCHAR(40) NOT NULL,
              `titulo` VARCHAR(180) NOT NULL,
              `mensagem` VARCHAR(255) DEFAULT NULL,
              `link` VARCHAR(180) DEFAULT NULL,
              `pedido_id` INT UNSIGNED DEFAULT NULL,
              `lida` TINYINT(1) NOT NULL DEFAULT 0,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              KEY `idx_notificacoes_lida` (`lida`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $ok = true;
    }

    public static function pedidoNovo(int $pedidoId, string $codigo, string $nome, float $total): void
    {
        self::criar(
            'pedido_novo',
            'Novo pedido ' . $codigo,
            $nome . ' · ' . money($total),
            '/admin/pedidos/' . $pedidoId,
            $pedidoId
        );
    }

    public static function pedidoPago(int $pedidoId, string $codigo, string $nome, float $total): void
    {
        self::criar(
            'pedido_pago',
            'Pagamento confirmado ' . $codigo,
            $nome . ' · ' . money($total),
            '/admin/pedidos/' . $pedidoId,
            $pedidoId
        );
    }

    public static function criar(string $tipo, string $titulo, string $mensagem, string $link, ?int $pedidoId = null): void
    {
        self::garantir();
        if ($pedidoId) {
            $existe = Database::fetch(
                'SELECT id FROM notificacoes WHERE tipo = :tipo AND pedido_id = :pid AND lida = 0 LIMIT 1',
                ['tipo' => $tipo, 'pid' => $pedidoId]
            );
            if ($existe) {
                return;
            }
        }
        self::create([
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link,
            'pedido_id' => $pedidoId,
            'lida' => 0,
        ]);
    }

    public static function naoLidas(): int
    {
        self::garantir();
        return self::count('lida = 0');
    }

    public static function recentes(int $limit = 12): array
    {
        self::garantir();
        $limit = max(1, min(30, $limit));
        return Database::fetchAll(
            'SELECT * FROM notificacoes ORDER BY id DESC LIMIT ' . $limit
        );
    }

    public static function marcarLida(int $id): void
    {
        self::garantir();
        self::updateById($id, ['lida' => 1]);
    }

    public static function marcarTodas(): void
    {
        self::garantir();
        Database::query('UPDATE notificacoes SET lida = 1 WHERE lida = 0');
    }

    public static function marcarPedido(int $pedidoId): void
    {
        self::garantir();
        if ($pedidoId < 1) {
            return;
        }
        Database::query('UPDATE notificacoes SET lida = 1 WHERE pedido_id = :id AND lida = 0', ['id' => $pedidoId]);
    }
}
