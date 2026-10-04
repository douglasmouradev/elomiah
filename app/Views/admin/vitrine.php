<?php
$vitrine = $vitrine ?? \App\Models\Configuracao::vitrine();
$faq = $faq ?? [];
$inscritos = $inscritos ?? [];
$mpPronto = !empty($mpPronto);
$dinheiro = static fn (float $v) => $v > 0 ? number_format($v, 2, ',', '') : '';
$faqLinhas = array_pad($faq, 8, ['p' => '', 'r' => '']);
?>
<p class="field-hint">Cada item só aparece no site depois de preenchido. Deixe em branco para esconder.</p>

<form class="form" method="post" action="<?= e(url('/admin/vitrine')) ?>" style="max-width:640px">
    <?= csrf_field() ?>

    <h2>Ofertas</h2>
    <label><span>Frete grátis acima de (R$)</span>
        <input type="text" name="frete_gratis_acima" inputmode="decimal" value="<?= e($dinheiro($vitrine['frete_gratis'])) ?>" placeholder="Ex.: 250,00">
    </label>
    <p class="field-hint">A sacola mostra quanto falta, e o checkout zera o despacho ao atingir.</p>
    <label><span>Desconto no Pix (%)</span>
        <input type="text" name="desconto_pix" inputmode="decimal" value="<?= $vitrine['desconto_pix'] > 0 ? e(str_replace('.', ',', (string) $vitrine['desconto_pix'])) : '' ?>" placeholder="Ex.: 5 — máximo 30">
    </label>
    <p class="field-hint">Vale sobre o total do pedido, frete incluído. É descontado de verdade no valor do Pix.</p>
    <label><span>Parcelas sem juros no cartão</span>
        <select name="parcelas_sem_juros">
            <option value="0">Não mostrar</option>
            <?php for ($n = 2; $n <= 6; $n++): ?>
                <option value="<?= $n ?>" <?= $vitrine['parcelas'] === $n ? 'selected' : '' ?>>Até <?= $n ?>x sem juros</option>
            <?php endfor; ?>
        </select>
    </label>
    <p class="field-hint"><?= $mpPronto ? 'Configure o mesmo número de parcelas sem juros na sua conta do Mercado Pago: é lá que os juros são absorvidos.' : 'Só aparece no site quando o Mercado Pago estiver ligado em Pagamento.' ?></p>
    <div class="split-2">
        <label><span>Brinde acima de (R$)</span>
            <input type="text" name="brinde_acima" inputmode="decimal" value="<?= e($dinheiro($vitrine['brinde_acima'])) ?>" placeholder="Ex.: 200,00">
        </label>
        <label><span>Qual é o brinde</span>
            <input type="text" name="brinde_texto" maxlength="80" value="<?= e($vitrine['brinde_texto']) ?>" placeholder="Ex.: uma amostra de 10 ml">
        </label>
    </div>
    <p class="field-hint">Aparece como “Ganhe uma amostra de 10 ml nas compras acima de R$ 200,00”. O pedido que atingir sai com “Brinde: …” nas observações.</p>

    <h2 style="margin-top:2rem">Faixa do topo e atendimento</h2>
    <label><span>Avisos extras da faixa (um por linha, até 3)</span>
        <textarea name="faixa_avisos" rows="3" placeholder="Ex.: Lote de outubro disponível"><?= e(implode("\n", $vitrine['avisos'])) ?></textarea>
    </label>
    <p class="field-hint">Frete grátis, desconto no Pix e brinde entram sozinhos na faixa quando configurados.</p>
    <label><span>Horário de atendimento</span>
        <input type="text" name="atendimento_horario" maxlength="120" value="<?= e($vitrine['atendimento']) ?>" placeholder="Ex.: Seg a sex, 9h às 18h">
    </label>

    <h2 style="margin-top:2rem">Redes sociais</h2>
    <?php foreach (\App\Models\Configuracao::REDES as $rede => $nome): ?>
        <label><span><?= e($nome) ?></span>
            <input type="url" name="redes_<?= e($rede) ?>" value="<?= e($vitrine['redes'][$rede] ?? '') ?>" placeholder="https://…">
        </label>
    <?php endforeach; ?>
    <p class="field-hint">Use o endereço completo, começando com https://. Os ícones aparecem no rodapé.</p>

    <h2 style="margin-top:2rem">Perguntas frequentes da home</h2>
    <p class="field-hint">Até 8. Pergunta sem resposta é ignorada. Apague as duas para remover.</p>
    <?php foreach ($faqLinhas as $i => $item): ?>
        <fieldset style="border:1px solid #ddd;border-radius:8px;padding:.8rem 1rem;margin:0 0 .8rem">
            <label><span>Pergunta <?= $i + 1 ?></span>
                <input type="text" name="faq_p[]" maxlength="160" value="<?= e($item['p']) ?>">
            </label>
            <label><span>Resposta</span>
                <textarea name="faq_r[]" rows="2" maxlength="800"><?= e($item['r']) ?></textarea>
            </label>
        </fieldset>
    <?php endforeach; ?>

    <p><button class="btn btn-gold" type="submit">Guardar vitrine</button></p>
</form>

<h2 id="newsletter" style="margin-top:2.4rem">Newsletter</h2>
<p class="field-hint"><?= count($inscritos) ?> e-mail(s) com aceite registrado. O site guarda a lista; o envio das campanhas é feito na sua ferramenta de e-mail.</p>
<?php if ($inscritos): ?>
    <p><a class="btn btn-ghost" href="<?= e(url('/admin/newsletter.csv')) ?>">Baixar lista (CSV)</a></p>
    <table class="table">
        <thead><tr><th>E-mail</th><th>Inscrito em</th><th></th></tr></thead>
        <tbody>
            <?php foreach (array_slice($inscritos, 0, 100) as $i): ?>
                <tr>
                    <td><?= e($i['email']) ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime((string) $i['created_at']))) ?></td>
                    <td>
                        <form method="post" action="<?= e(url('/admin/newsletter/' . (int) $i['id'] . '/remover')) ?>" onsubmit="return confirm('Remover este e-mail da lista?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-ghost" type="submit">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
