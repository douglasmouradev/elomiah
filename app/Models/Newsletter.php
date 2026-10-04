<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;

final class Newsletter extends Model
{
    protected static string $table = 'newsletter_inscritos';

    public static function garantir(): void
    {
        static $ok = false;
        if ($ok) {
            return;
        }
        Database::connection()->exec(
            "CREATE TABLE IF NOT EXISTS `newsletter_inscritos` (
              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
              `email` VARCHAR(180) NOT NULL,
              `ip` VARCHAR(45) DEFAULT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_newsletter_email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $ok = true;
    }

    public static function inscrever(string $email, string $ip): void
    {
        self::garantir();
        $stmt = Database::connection()->prepare(
            'INSERT IGNORE INTO `newsletter_inscritos` (`email`, `ip`) VALUES (:email, :ip)'
        );
        $stmt->execute(['email' => $email, 'ip' => $ip]);
    }

    /** @return list<array<string, mixed>> */
    public static function todos(): array
    {
        self::garantir();
        return Database::connection()
            ->query('SELECT id, email, created_at FROM `newsletter_inscritos` ORDER BY id DESC')
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function remover(int $id): void
    {
        self::garantir();
        Database::connection()->prepare('DELETE FROM `newsletter_inscritos` WHERE id = :id')->execute(['id' => $id]);
    }
}
