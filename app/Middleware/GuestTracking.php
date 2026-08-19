<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;

final class GuestTracking
{
    public function handle(Request $request): void
    {
        $path = $request->path();
        if (
            str_starts_with($path, '/admin')
            || str_starts_with($path, '/assets')
            || str_starts_with($path, '/webhooks/')
            || str_ends_with($path, '/status')
        ) {
            return;
        }
        if ($request->method() !== 'GET') {
            return;
        }

        try {
            Database::insert('visitantes', [
                'sessao' => session_id() ?: 'anon',
                'usuario_id' => Auth::id(),
                'pagina' => substr($path, 0, 180),
                'ip_hash' => hash('sha256', $request->ip() . (config('app')['key'] ?? '')),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // tracking nunca deve quebrar a navegação
        }
    }
}
