<?php
$frete = $frete ?? frete();
$vitrine = $vitrine ?? \App\Models\Configuracao::vitrine();
?>
<section class="page-hero container">
    <h1>Envios e frete</h1>
</section>
<section class="container prose">
    <h2>Prazo</h2>
    <p><?= e(rtrim($frete['prazo'], '. ')) ?>. O prazo começa a contar depois que o pagamento é confirmado.</p>
    <h2>Valor do frete</h2>
    <p><?= e($frete['nome']) ?>: <?= e(money($frete['valor'])) ?> por pedido.<?php if ($vitrine['frete_gratis'] > 0): ?> Nas compras a partir de <?= e(money($vitrine['frete_gratis'])) ?> em aromas, o frete é grátis.<?php endif; ?></p>
    <p>A sacola mostra o valor do frete antes de você finalizar.</p>
    <h2>Acompanhamento</h2>
    <p>Você acompanha o andamento do pedido em <a href="<?= e(url('/conta')) ?>">Meus pedidos</a>, e recebe um e-mail a cada etapa.</p>
    <h2>Curso</h2>
    <p>O curso O Ritual das Essências é digital e não tem frete. O acesso libera na sua conta depois do pagamento.</p>
    <p>Ficou alguma dúvida? <a href="<?= e(url('/contato')) ?>">Fale com a gente</a>.</p>
</section>
