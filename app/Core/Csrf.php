<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf', $token);
        }
        return $token;
    }

    public static function verify(?string $token): bool
    {
        $session = Session::get('_csrf');
        if (!is_string($session) || !is_string($token) || $token === '') {
            return false;
        }
        return hash_equals($session, $token);
    }
}
