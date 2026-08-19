<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\LogAdmin;
use App\Models\Usuario;

final class Auth
{
    public static function user(): ?array
    {
        $id = Session::get('user_id');
        if (!$id) {
            return null;
        }
        static $cached = null;
        static $cachedId = null;
        if ($cachedId === $id && is_array($cached)) {
            return $cached;
        }
        $cached = Usuario::find((int) $id);
        $cachedId = $id;
        return $cached;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user !== null && ($user['role'] ?? '') === 'admin';
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $user = Usuario::firstWhere('email', strtolower(trim($email)));
        if (!$user || ($user['status'] ?? '') !== 'ativo') {
            return false;
        }
        if (!password_verify($password, $user['senha_hash'])) {
            return false;
        }
        if (password_needs_rehash($user['senha_hash'], PASSWORD_DEFAULT)) {
            Usuario::updateById((int) $user['id'], [
                'senha_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
        }
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('user_role', $user['role']);
        return true;
    }

    public static function loginById(int $id): void
    {
        Session::regenerate();
        Session::set('user_id', $id);
        $user = Usuario::find($id);
        if ($user) {
            Session::set('user_role', $user['role'] ?? 'cliente');
        }
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::forget('user_role');
        Session::regenerate();
    }

    public static function log(string $acao, string $entidade = '', ?int $entidadeId = null, ?string $antes = null, ?string $depois = null): void
    {
        if (!self::isAdmin()) {
            return;
        }
        LogAdmin::create([
            'usuario_id' => self::id(),
            'acao' => $acao,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'dados_anteriores' => $antes,
            'dados_novos' => $depois,
            'ip' => client_ip(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    }
}
