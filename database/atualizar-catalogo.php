<?php

declare(strict_types=1);

/**
 * Atualiza o banco já instalado com o catálogo oficial (fotos e textos Elomiah).
 * Uso: php database/atualizar-catalogo.php
 */

require dirname(__DIR__) . '/bootstrap.php';

$pdo = App\Core\Database::connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cols = $pdo->query('SHOW COLUMNS FROM produtos')->fetchAll(PDO::FETCH_COLUMN);
$add = [
    'cor_destaque' => "VARCHAR(7) NOT NULL DEFAULT '#1B4332'",
    'aroma' => 'VARCHAR(80) DEFAULT NULL',
    'citacao' => 'VARCHAR(320) DEFAULT NULL',
    'colecao' => "VARCHAR(80) DEFAULT 'Coleção Refúgio'",
    'modo_usar' => 'TEXT',
    'precaucoes' => 'TEXT',
];
foreach ($add as $col => $def) {
    if (!in_array($col, $cols, true)) {
        $pdo->exec("ALTER TABLE produtos ADD COLUMN `{$col}` {$def}");
        echo "Coluna {$col} adicionada.\n";
    }
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('DELETE FROM imagens_produto');
$pdo->exec('DELETE FROM itens_pedido');
$pdo->exec('DELETE FROM depoimentos');
$pdo->exec('DELETE FROM produtos');
$pdo->exec('ALTER TABLE produtos AUTO_INCREMENT = 1');
$pdo->exec("UPDATE categorias SET nome = 'Coleção Refúgio', slug = 'colecao-refugio', descricao = 'Sprays de ambiente 120 ml' WHERE id = 1");
$pdo->exec("UPDATE categorias SET nome = 'Coleção Elo', slug = 'colecao-elo', descricao = 'Home spray para os primeiros capítulos' WHERE id = 2");
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

$seed = file_get_contents(__DIR__ . '/seed.sql');
if ($seed === false) {
    throw new RuntimeException('seed.sql não encontrado');
}

$blocks = [
    'INSERT INTO `produtos`',
    'INSERT INTO `imagens_produto`',
    'INSERT INTO `depoimentos`',
];

foreach ($blocks as $start) {
    $pos = strpos($seed, $start);
    if ($pos === false) {
        throw new RuntimeException('Bloco não encontrado: ' . $start);
    }
    $rest = substr($seed, $pos);
    $end = strpos($rest, ';');
    $sql = substr($rest, 0, $end + 1);
    $pdo->exec($sql);
    echo "OK: {$start}\n";
}

$pdo->exec("INSERT INTO itens_pedido (pedido_id, produto_id, nome_produto, quantidade, preco_unitario, subtotal, created_at, updated_at)
SELECT 1, 1, 'Despertar', 1, 89.90, 89.90, NOW(), NOW() FROM pedidos WHERE id = 1
UNION ALL SELECT 2, 2, 'Recomeço', 1, 89.90, 89.90, NOW(), NOW() FROM pedidos WHERE id = 2
UNION ALL SELECT 3, 5, 'Silêncio', 1, 89.90, 89.90, NOW(), NOW() FROM pedidos WHERE id = 3
UNION ALL SELECT 4, 6, 'Elo', 1, 89.90, 89.90, NOW(), NOW() FROM pedidos WHERE id = 4");

echo "Catálogo oficial aplicado. Recarregue o site.\n";
