<?php

declare(strict_types=1);

/**
 * Instala o banco Elomiah a partir do .env.
 * Uso (na raiz do projeto): php database/install.php
 */

require dirname(__DIR__) . '/bootstrap.php';

$cfg = require CONFIG_PATH . DIRECTORY_SEPARATOR . 'database.php';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $cfg['host'], $cfg['port']),
        $cfg['user'],
        $cfg['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "Não conectou no MySQL. Confira DB_HOST/DB_USER/DB_PASS no .env (XAMPP/Laragon).\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$runFile = static function (PDO $pdo, string $path): void {
    $sql = file_get_contents($path);
    if ($sql === false) {
        throw new RuntimeException('SQL não encontrado: ' . $path);
    }
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    $parts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($parts as $part) {
        if ($part === '' || str_starts_with(strtoupper($part), 'SET NAMES')) {
            continue;
        }
        $pdo->exec($part);
    }
};

$runFile($pdo, __DIR__ . DIRECTORY_SEPARATOR . 'schema.sql');
$runFile($pdo, __DIR__ . DIRECTORY_SEPARATOR . 'seed.sql');

echo "Banco '{$cfg['name']}' criado e alimentado.\n";
echo "Ateliê: http://localhost:8000/admin — e-mail do seed. Troque a senha em /admin/conta.\n";
echo "Suba o site: php -S localhost:8000 -t public public/router.php\n";
