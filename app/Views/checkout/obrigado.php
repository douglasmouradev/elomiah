<?php
\App\Core\View::partial('partials/flash');
$pedido = $pedido ?? [];
$metodo = (string) ($pedido['metodo_pagamento'] ?? 'pix');
$status = (string) ($pedido['status'] ?? 'pendente');
$ehCartao = $metodo === 'cartao';
$ehPix = $metodo === 'pix';
$pago = $status === 'pago';
$pendente = $status === 'pendente';
$link = (string) ($pedido['mp_init_point'] ?? '');
$pixPayload = (string) ($pixPayload ?? '');
$pixChave = (string) ($pixChave ?? '');
$pixQrBase64 = (string) ($pixQrBase64 ?? '');
$pixAutomatico = !empty($pedido['mp_payment_id']);
$mpPublicKey = (string) ($mpPublicKey ?? '');
$cartaoNoSite = $pendente && $ehCartao && $mpPublicKey !== '';
$digital = pedido_so_digital($pedido);
$cursoUrl = (string) ($cursoUrl ?? '');
$pagoComAcesso = $pago && $digital;
?>
<section class="page-hero container">
    <h1>Pedido <?= e($pedido['codigo'] ?? '') ?></h1>
    <p><span class="status-pill status-<?= e($status) ?>"><?= e(pedido_status_rotulo($status, $digital)) ?></span></p>

    <?php if ($status !== 'cancelado'): ?>
        <ol class="order-track">
            <?php
            $passos = $digital
                ? ['pendente' => 'Pedido', 'pago' => 'Acesso']
                : ['pendente' => 'Pedido', 'pago' => 'Pago', 'enviado' => 'Enviado', 'entregue' => 'Entregue'];
            $ordem = array_keys($passos);
            $idx = array_search($status === 'enviado' || $status === 'entregue' ? 'pago' : $status, $ordem, true);
            if ($digital && in_array($status, ['enviado', 'entregue'], true)) {
                $idx = array_search('pago', $ordem, true);
            }
            foreach ($passos as $chave => $rotulo):
                $i = array_search($chave, $ordem, true);
                $classe = ($idx !== false && $i < $idx) ? 'is-done' : (($idx !== false && $i === $idx) ? 'is-on' : '');
            ?>
                <li class="<?= $classe ?>"><?= e($rotulo) ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <?php if ($pagoComAcesso): ?>
        <p class="lede">Pagamento confirmado. A formação está nesta conta. O recibo foi para o seu e-mail.</p>
        <p>
            <?php if ($cursoUrl !== ''): ?>
                <a class="btn btn-gold" href="<?= e($cursoUrl) ?>" target="_blank" rel="noopener">Abrir as aulas</a>
            <?php endif; ?>
            <a class="btn btn-ghost" href="<?= e(url('/conta/curso')) ?>">Ver na conta</a>
        </p>
    <?php elseif ($pago && $ehCartao): ?>
        <p class="lede">
            Cartão <?= e(\App\Support\CartaoCredito::bandeiraRotulo((string) ($pedido['cartao_bandeira'] ?? ''))) ?>
            <?php if (!empty($pedido['cartao_final'])): ?>final <?= e($pedido['cartao_final']) ?><?php endif; ?>
            <?php if (!empty($pedido['parcelas'])): ?> · <?= (int) $pedido['parcelas'] ?>x<?php endif; ?>.
            Confirmação e o recibo de compra foram para <?= e($pedido['email_cliente'] ?? '') ?>.
        </p>
    <?php elseif ($pago): ?>
        <p class="lede">Pix confirmado. O recibo de compra foi para o seu e-mail. O pedido sai do ateliê em até 3 dias úteis.</p>
    <?php elseif ($status === 'enviado'): ?>
        <p class="lede">
            Seu pedido saiu do ateliê<?= !empty($pedido['transportadora']) ? ' pelos ' . e($pedido['transportadora']) : '' ?>.
            <?php if (!empty($pedido['codigo_rastreio'])): ?>
                Rastreio: <strong><?= e($pedido['codigo_rastreio']) ?></strong>.
            <?php endif; ?>
        </p>
    <?php elseif ($status === 'entregue'): ?>
        <p class="lede">Pedido entregue. Se algo não estiver certo, você tem 7 dias para pedir a troca pelo WhatsApp.</p>
    <?php elseif ($pendente && $ehPix && $pixPayload !== ''): ?>
        <p class="lede">Pague <?= e(money($pedido['total'] ?? 0)) ?> no Pix<?php if ((float) ($pedido['desconto'] ?? 0) > 0): ?>, já com <?= e(money($pedido['desconto'])) ?> de desconto<?php endif; ?>. <?= $pixAutomatico ? 'Esta página confirma sozinha quando o banco autorizar.' : 'O ateliê confere na Nubank e marca como pago. Você recebe um e-mail — esta página não confirma sozinha pelo banco.' ?></p>
        <div class="pix-box">
            <?php if ($pixQrBase64 !== ''): ?>
                <img class="pix-qr" src="data:image/png;base64,<?= e($pixQrBase64) ?>" alt="QR Code Pix" width="220" height="220">
            <?php else: ?>
                <div id="pix-qr" class="pix-qr" role="img" aria-label="QR Code Pix"></div>
            <?php endif; ?>
            <?php if (!$pixAutomatico && $pixChave !== ''): ?>
                <p class="pix-label">Chave aleatória</p>
                <p class="pix-key" id="pix-chave"><?= e($pixChave) ?></p>
            <?php endif; ?>
            <div class="pix-actions">
                <?php if (!$pixAutomatico && $pixChave !== ''): ?>
                    <button class="btn btn-ghost" type="button" data-copy="pix-chave">Copiar chave</button>
                <?php endif; ?>
                <button class="btn btn-gold" type="button" data-copy="pix-cola">Copiar Pix</button>
            </div>
            <textarea id="pix-cola" class="visually-hidden" readonly><?= e($pixPayload) ?></textarea>
            <p class="pay-safe"><?= $pixAutomatico ? 'Abra o app do banco, leia o QR ou cole o Pix. Não feche esta aba: ela detecta o pagamento e confirma.' : 'Abra o app do banco, leia o QR ou cole o Pix. Quando a Geo marcar o recebimento no ateliê, esta página atualiza e o e-mail parte.' ?></p>
            <p class="pix-wait" id="pix-wait"><?= $pixAutomatico ? 'Aguardando o Pix…' : 'Aguardando o ateliê confirmar o Pix…' ?></p>
        </div>
    <?php elseif ($pendente && $ehCartao && $cartaoNoSite): ?>
        <p class="lede">Pague <?= e(money($pedido['total'] ?? 0)) ?> no cartão. O número do cartão vai direto ao Mercado Pago.</p>
        <div class="card-brick">
            <div id="cardPaymentBrick_container"></div>
            <p class="card-brick-err" id="card-brick-err" hidden></p>
        </div>
    <?php elseif ($pendente): ?>
        <p class="lede">O pedido está reservado. Conclua o <?= e(pagamento_rotulo($metodo)) ?> para confirmar.</p>
    <?php else: ?>
        <p class="lede">Este pagamento não foi concluído. Se ainda quiser o pedido, volte à loja e tente outra vez.</p>
    <?php endif; ?>

    <p>Total <?= e(money($pedido['total'] ?? 0)) ?> · <?= e(pagamento_rotulo($metodo)) ?> · <?= e(pedido_status_rotulo($status)) ?></p>
    <p>
        <?php if ($pendente && $ehCartao && $link !== ''): ?>
            <a class="btn btn-gold" href="<?= e($link) ?>">Concluir no cartão</a>
        <?php endif; ?>
        <a class="btn btn-ghost" href="<?= e(url('/loja')) ?>">Continuar na loja</a>
        <?php if (\App\Core\Auth::check()): ?>
            <a class="btn btn-ghost" href="<?= e(url('/conta')) ?>">Ver meus pedidos</a>
        <?php endif; ?>
    </p>
</section>
<?php if ($pendente && $ehPix && $pixPayload !== ''): ?>
<?php if ($pixQrBase64 === ''): ?>
<script src="<?= e(asset('js/qrcode.min.js')) ?>"></script>
<?php endif; ?>
<script>
(() => {
  const payload = <?= json_encode($pixPayload, JSON_UNESCAPED_SLASHES) ?>;
  const box = document.getElementById('pix-qr');
  if (box && typeof QRCode === 'function') {
    new QRCode(box, {
      text: payload,
      width: 220,
      height: 220,
      colorDark: '#173A2C',
      colorLight: '#FBFAF6',
      correctLevel: QRCode.CorrectLevel.M
    });
  }
  document.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const el = document.getElementById(btn.getAttribute('data-copy'));
      const text = el?.value || el?.textContent || '';
      try {
        await navigator.clipboard.writeText(text.trim());
        btn.textContent = 'Copiado';
        setTimeout(() => { btn.textContent = btn.getAttribute('data-copy') === 'pix-cola' ? 'Copiar Pix' : 'Copiar chave'; }, 1600);
      } catch (_) {}
    });
  });

  const statusUrl = <?= json_encode(url('/pedido/' . ($pedido['codigo'] ?? '') . '/status'), JSON_UNESCAPED_SLASHES) ?>;
  const wait = document.getElementById('pix-wait');
  const consultar = async () => {
    try {
      const res = await fetch(statusUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        cache: 'no-store',
      });
      const data = await res.json();
      if (data && data.pago) {
        if (wait) wait.textContent = 'Pix recebido. Confirmando…';
        window.location.reload();
      }
    } catch (_) {}
  };
  consultar();
  setInterval(consultar, 2500);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) consultar();
  });
})();
</script>
<?php endif; ?>
<?php if ($cartaoNoSite): ?>
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
(() => {
  const publicKey = <?= json_encode($mpPublicKey, JSON_UNESCAPED_SLASHES) ?>;
  const amount = <?= json_encode((float) ($pedido['total'] ?? 0)) ?>;
  const email = <?= json_encode((string) ($pedido['email_cliente'] ?? ''), JSON_UNESCAPED_UNICODE) ?>;
  const pagarUrl = <?= json_encode(url('/pedido/' . ($pedido['codigo'] ?? '') . '/pagar'), JSON_UNESCAPED_SLASHES) ?>;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const err = document.getElementById('card-brick-err');
  const mp = new MercadoPago(publicKey, { locale: 'pt-BR' });
  mp.bricks().create('cardPayment', 'cardPaymentBrick_container', {
    initialization: {
      amount,
      payer: { email },
    },
    customization: {
      visual: { style: { theme: 'default' } },
      paymentMethods: { maxInstallments: 6, minInstallments: 1 },
    },
    callbacks: {
      onReady: () => {},
      onError: (error) => {
        if (err) {
          err.hidden = false;
          err.textContent = 'Não foi possível abrir o cartão. Recarregue a página.';
        }
        console.error(error);
      },
      onSubmit: (cardFormData) => fetch(pagarUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify(cardFormData),
      }).then(async (res) => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) {
          throw new Error(data.message || 'O cartão não autorizou.');
        }
        window.location.reload();
      }).catch((e) => {
        if (err) {
          err.hidden = false;
          err.textContent = e.message || 'O cartão não autorizou.';
        }
        throw e;
      }),
    },
  });
})();
</script>
<?php endif; ?>
