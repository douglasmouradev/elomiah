<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Usuario;

final class RateLimiter
{
    public static function tooMany(string $action, ?string $ip = null): bool
    {
        $cfg = config('app')['rate_limit'][$action] ?? ['max' => 8, 'window' => 600];
        $ip = $ip ?? client_ip();
        $windowStart = date('Y-m-d H:i:s', time() - (int) $cfg['window']);

        Database::query(
            'DELETE FROM rate_limits WHERE created_at < :lim',
            ['lim' => date('Y-m-d H:i:s', time() - 86400)]
        );

        $row = Database::fetch(
            'SELECT COUNT(*) AS total FROM rate_limits WHERE acao = :a AND ip = :ip AND created_at >= :w',
            ['a' => $action, 'ip' => $ip, 'w' => $windowStart]
        );

        return (int) ($row['total'] ?? 0) >= (int) $cfg['max'];
    }

    public static function hit(string $action, ?string $ip = null): void
    {
        Database::insert('rate_limits', [
            'acao' => $action,
            'ip' => $ip ?? client_ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function accountLocked(string $email): bool
    {
        $user = Usuario::firstWhere('email', strtolower(trim($email)));
        if (!$user || empty($user['locked_until'])) {
            return false;
        }
        return strtotime((string) $user['locked_until']) > time();
    }

    public static function registerFailure(string $email): void
    {
        $user = Usuario::firstWhere('email', strtolower(trim($email)));
        if (!$user) {
            return;
        }
        $attempts = (int) $user['failed_login_attempts'] + 1;
        $data = ['failed_login_attempts' => $attempts];
        if ($attempts >= 5) {
            $data['locked_until'] = date('Y-m-d H:i:s', time() + 900);
        }
        Usuario::updateById((int) $user['id'], $data);
    }

    public static function clearFailures(int $userId): void
    {
        Usuario::updateById($userId, [
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }
}
