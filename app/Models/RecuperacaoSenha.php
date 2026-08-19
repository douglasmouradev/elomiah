<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class RecuperacaoSenha extends Model
{
    protected static string $table = 'recuperacao_senhas';

    public static function garantirTabela(): void
    {
        static $feito = false;
        if ($feito) {
            return;
        }
        Database::connection()->exec(
            'CREATE TABLE IF NOT EXISTS `recuperacao_senhas` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `email` VARCHAR(180) NOT NULL,
                `token_hash` CHAR(64) NOT NULL,
                `expires_at` DATETIME NOT NULL,
                `used_at` DATETIME DEFAULT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uk_recup_token` (`token_hash`),
                KEY `idx_recup_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $feito = true;
    }

    public static function emitir(string $email): string
    {
        self::garantirTabela();
        $email = strtolower(trim($email));
        $token = bin2hex(random_bytes(32));
        Database::insert('recuperacao_senhas', [
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $token;
    }

    public static function encontrar(string $token): ?array
    {
        self::garantirTabela();
        if ($token === '' || strlen($token) !== 64) {
            return null;
        }
        $row = Database::fetch(
            'SELECT * FROM recuperacao_senhas
             WHERE token_hash = :h AND used_at IS NULL AND expires_at > :agora
             LIMIT 1',
            ['h' => hash('sha256', $token), 'agora' => now()]
        );

        return $row ?: null;
    }

    public static function gastar(int $id): void
    {
        self::updateById($id, ['used_at' => now()]);
    }
}
