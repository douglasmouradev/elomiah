<?php

declare(strict_types=1);

/**
 * Cria (ou atualiza) contas só para a gravação do vídeo de demonstração.
 * Não altera a senha da Geo (admin@elomiah.com).
 */

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Models\Usuario;

$contas = [
    [
        'nome' => 'Cliente demonstração',
        'email' => 'cliente.demo@elomiah.local',
        'role' => 'cliente',
        'telefone' => '11987654321',
    ],
    [
        'nome' => 'Ateliê demonstração',
        'email' => 'atelie.demo@elomiah.local',
        'role' => 'admin',
        'telefone' => '11999990000',
    ],
];

$senha = (string) ($argv[1] ?? 'ElomiahDemo2026');
$hash = password_hash($senha, PASSWORD_DEFAULT);

foreach ($contas as $conta) {
    $atual = Usuario::firstWhere('email', $conta['email']);
    if ($atual) {
        Usuario::updateById((int) $atual['id'], [
            'senha_hash' => $hash,
            'status' => 'ativo',
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
        echo "Atualizou {$conta['email']}\n";
        continue;
    }
    Usuario::create([
        'nome' => $conta['nome'],
        'email' => $conta['email'],
        'senha_hash' => $hash,
        'telefone' => $conta['telefone'],
        'role' => $conta['role'],
        'status' => 'ativo',
        'email_verified_at' => now(),
    ]);
    echo "Criou {$conta['email']}\n";
}
