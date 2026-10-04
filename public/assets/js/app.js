(() => {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const reduz = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const token = () => $('meta[name="csrf-token"]')?.content || '';
  const base = ($('meta[name="app-base"]')?.content || '').replace(/\/$/, '');
  const caminho = location.pathname.replace(new URL(base || location.origin).pathname.replace(/\/$/, ''), '') || '/';
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  /* Abertura: some sozinha; clique ou tecla adianta */
  const abertura = $('.abertura');
  if (abertura && document.documentElement.classList.contains('com-abertura')) {
    const pular = () => abertura.classList.add('is-fora');
    abertura.addEventListener('click', pular);
    document.addEventListener('keydown', pular, { once: true });
    abertura.addEventListener('animationend', (e) => {
      if (e.target === abertura) abertura.remove();
    });
  } else {
    abertura?.remove();
  }

  /* Frasco que acompanha a navegação até a página do produto */
  window.addEventListener('pageswap', (e) => {
    if (!e.viewTransition || !e.activation?.entry?.url) return;
    $$('.vt-frasco[data-vt]').forEach((img) => { img.style.viewTransitionName = ''; });
    const destino = new URL(e.activation.entry.url).pathname;
    if (!destino.includes('/produto/')) return;
    const bloco = $$('.product-card, .shelf-item, .elo-row').find((b) =>
      $$('a[href]', b).some((a) => new URL(a.href).pathname === destino));
    const img = bloco && $('.vt-frasco[data-vt]', bloco);
    if (img) img.style.viewTransitionName = img.dataset.vt;
  });

  /* Faixa de avisos */
  const faixa = $('[data-faixa]');
  const avisos = faixa ? $$('li', faixa) : [];
  if (avisos.length > 1 && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let atual = 0;
    let parado = false;
    faixa.addEventListener('mouseenter', () => { parado = true; });
    faixa.addEventListener('mouseleave', () => { parado = false; });
    setInterval(() => {
      if (parado || document.hidden) return;
      avisos[atual].classList.remove('is-on');
      atual = (atual + 1) % avisos.length;
      avisos[atual].classList.add('is-on');
    }, 5000);
  }

  /* Topo */
  const header = $('.site-header');
  const onScroll = () => header?.classList.toggle('is-scrolled', window.scrollY > 8);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  const toggle = $('.nav-toggle');
  const nav = $('.nav-main');
  const fecharMenu = () => {
    nav?.classList.remove('is-open');
    toggle?.setAttribute('aria-expanded', 'false');
  };
  toggle?.addEventListener('click', () => {
    const aberto = nav?.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') fecharMenu(); });
  document.addEventListener('click', (e) => {
    if (nav?.classList.contains('is-open') && !nav.contains(e.target) && !toggle?.contains(e.target)) fecharMenu();
  });

  /* Cookies */
  const banner = $('.cookie-banner');
  $$('[data-cookie]', banner || document).forEach((btn) => {
    btn.addEventListener('click', async () => {
      const escolha = btn.getAttribute('data-cookie') || 'essenciais';
      banner?.classList.remove('is-on');
      try {
        await fetch(base + '/cookies/consentimento', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token(),
          },
          body: `_csrf=${encodeURIComponent(token())}&escolha=${encodeURIComponent(escolha)}`,
        });
      } catch (_) {}
    });
  });

  /* Névoa de cor */
  const camadas = $$('.nevoa i');
  let ativa = 0;
  let corAtual = '';
  const nevoa = (cor) => {
    if (camadas.length < 2 || cor === corAtual) return;
    corAtual = cor;
    const proxima = camadas[1 - ativa];
    if (cor) proxima.style.setProperty('--c', cor);
    camadas[ativa].classList.remove('is-on');
    if (cor) proxima.classList.add('is-on');
    ativa = 1 - ativa;
  };
  const corBase = $('[data-nevoa-base]')?.getAttribute('data-nevoa-base') || '';
  if (corBase) nevoa(corBase);
  $$('[data-nevoa]').forEach((el) => {
    const cor = el.getAttribute('data-nevoa');
    el.addEventListener('pointerenter', () => nevoa(cor));
    el.addEventListener('focusin', () => nevoa(cor));
    el.addEventListener('pointerleave', () => nevoa(corBase));
    el.addEventListener('focusout', () => nevoa(corBase));
  });

  /* Busca: /loja#busca foca o campo */
  const busca = $('#busca');
  if (busca && location.hash === '#busca') busca.focus({ preventScroll: false });

  /* Galeria */
  const galeria = $('.gallery-main');
  const fotoPrincipal = $('.gallery-main img');
  if (galeria && fotoPrincipal) {
    const mover = (e) => {
      const r = fotoPrincipal.getBoundingClientRect();
      galeria.style.setProperty('--zx', `${((e.clientX - r.left) / r.width) * 100}%`);
      galeria.style.setProperty('--zy', `${((e.clientY - r.top) / r.height) * 100}%`);
    };
    galeria.addEventListener('click', (e) => { mover(e); galeria.classList.toggle('is-zoom'); });
    galeria.addEventListener('pointermove', (e) => { if (galeria.classList.contains('is-zoom')) mover(e); });
    galeria.addEventListener('pointerleave', () => galeria.classList.remove('is-zoom'));
    galeria.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); galeria.classList.toggle('is-zoom'); }
    });
  }
  $$('[data-thumb]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const src = btn.getAttribute('data-thumb');
      if (!fotoPrincipal || !src || fotoPrincipal.getAttribute('src') === src) return;
      $$('button', btn.parentElement).forEach((b) => { b.classList.remove('is-on'); b.setAttribute('aria-pressed', 'false'); });
      btn.classList.add('is-on');
      btn.setAttribute('aria-pressed', 'true');
      galeria?.classList.remove('is-zoom');
      if (reduz) { fotoPrincipal.src = src; return; }
      fotoPrincipal.classList.add('is-trocando');
      const img = new Image();
      img.onload = img.onerror = () => {
        setTimeout(() => {
          fotoPrincipal.src = src;
          fotoPrincipal.classList.remove('is-trocando');
        }, 160);
      };
      img.src = src;
    });
  });

  /* Quantidade com − e + */
  $$('[data-stepper]').forEach((box) => {
    const input = $('input', box);
    $$('[data-passo]', box).forEach((b) => {
      b.addEventListener('click', () => {
        const min = Number(input.min || 1);
        const max = Number(input.max || 20);
        const v = Math.min(max, Math.max(min, (Number(input.value) || min) + Number(b.getAttribute('data-passo'))));
        input.value = String(v);
      });
    });
  });

  /* Checkout em duas etapas */
  const checkout = $('[data-checkout]');
  if (checkout) {
    checkout.classList.add('is-stepped');
    const passo1 = $('[data-step="1"]', checkout);
    const ir = $('[data-to-pay]', checkout);
    const voltar = $('[data-to-dest]', checkout);
    ir?.addEventListener('click', () => {
      for (const el of $$('input, textarea, select', passo1 || checkout)) {
        if (typeof el.checkValidity === 'function' && !el.checkValidity()) {
          el.reportValidity();
          return;
        }
      }
      checkout.classList.add('is-pay');
      $('.checkout-steps', checkout)?.scrollIntoView({ behavior: reduz ? 'auto' : 'smooth', block: 'start' });
      $('[data-step="2"] input[type="radio"]:checked', checkout)?.focus({ preventScroll: true });
    });
    voltar?.addEventListener('click', () => {
      checkout.classList.remove('is-pay');
      $('input', passo1)?.focus();
    });
    checkout.addEventListener('submit', (e) => {
      if (!checkout.classList.contains('is-pay')) {
        e.preventDefault();
        ir?.click();
        return;
      }
      if (!$('button[type="submit"]', checkout)) {
        e.preventDefault();
        return;
      }
      const btn = $('button[type="submit"]', checkout);
      btn.setAttribute('aria-disabled', 'true');
      btn.textContent = 'Gerando o pedido…';
    });
  }

  /* Sacola lateral */
  const drawer = $('#sacola');
  const corpo = $('.drawer-body', drawer || document);
  const rodape = $('.drawer-foot', drawer || document);
  const aviso = $('.drawer-msg', drawer || document);
  const contador = $('.cart-count');
  const semDrawer = !drawer || typeof drawer.showModal !== 'function' || ['/carrinho', '/checkout'].includes(caminho);

  const atualizarContador = (n) => {
    if (!contador || String(n) === contador.textContent.trim()) return;
    contador.textContent = String(n);
    contador.classList.remove('is-tick');
    void contador.offsetWidth;
    contador.classList.add('is-tick');
  };

  const postar = async (url, dados) => {
    const fd = dados instanceof FormData ? dados : new FormData();
    if (!(dados instanceof FormData)) Object.entries(dados).forEach(([k, v]) => fd.append(k, v));
    if (!fd.has('_csrf')) fd.append('_csrf', token());
    const res = await fetch(url, {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token(), Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const json = await res.json().catch(() => null);
    if (!json) throw new Error('resposta');
    return json;
  };

  let pctAnterior = 0;
  const barraFrete = (fg) => {
    if (!fg || !fg.ativo) return '';
    const texto = fg.atingido
      ? 'Você ganhou <strong>frete grátis</strong>.'
      : `Faltam <strong>${esc(fg.falta)}</strong> para o frete grátis.`;
    return `<div class="frete-gratis${fg.atingido ? ' is-ok' : ''}" role="status"><p>${texto}</p><span class="frete-barra" aria-hidden="true"><i style="--pct:${pctAnterior}" data-pct="${Number(fg.pct) || 0}"></i></span></div>`;
  };

  const desenhar = (estado) => {
    if (!corpo || !rodape) return;
    atualizarContador(estado.count ?? 0);
    if (!estado.itens || !estado.itens.length) {
      corpo.innerHTML = `<div class="drawer-empty"><p>A sacola está vazia.</p><p class="field-hint">Escolha um aroma na loja. Se tiver dúvida, a Geo indica pelo WhatsApp.</p><a class="btn btn-ghost" href="${esc(base)}/loja">Ver os aromas</a></div>`;
      rodape.hidden = true;
      return;
    }
    corpo.innerHTML = `<ul class="cart-list">${estado.itens.map((i) => `
      <li class="cart-row" style="--c:${esc(i.cor)}">
        <img src="${esc(i.imagem)}" alt="" width="64" height="80">
        <div>
          <h3><a href="${esc(i.url)}">${esc(i.nome)}</a></h3>
          <p class="cart-detail">${esc(i.detalhe)}</p>
          <div class="cart-controls">
            <div class="qty-step" role="group" aria-label="Quantidade de ${esc(i.nome)}">
              <button type="button" data-qtd="${i.id}" data-valor="${i.qty - 1}" aria-label="Diminuir" ${i.qty <= 1 ? 'disabled' : ''}>−</button>
              <span aria-live="polite">${i.qty}</span>
              <button type="button" data-qtd="${i.id}" data-valor="${i.qty + 1}" aria-label="Aumentar" ${i.qty >= i.teto ? 'disabled' : ''}>+</button>
            </div>
            <button class="cart-remove" type="button" data-remover="${i.id}">Remover</button>
          </div>
        </div>
        <span class="cart-sub">${esc(i.subtotal)}</span>
      </li>`).join('')}</ul>`;
    rodape.hidden = false;
    rodape.innerHTML = `${barraFrete(estado.freteGratis)}
      <dl class="totals">
        <div><dt>Aromas</dt><dd>${esc(estado.pecas)}</dd></div>
        <div><dt>${esc(estado.frete.nome)}</dt><dd>${esc(estado.frete.valor)}</dd></div>
        <div class="totals-total"><dt>Total</dt><dd>${esc(estado.total)}</dd></div>
      </dl>
      <a class="btn btn-gold" href="${esc(base)}/checkout">${estado.logado ? 'Finalizar compra' : 'Entrar e finalizar'}</a>
      <a class="btn btn-ghost" href="${esc(base)}/carrinho">Ver sacola completa</a>`;
    const barra = rodape.querySelector('.frete-barra i');
    if (barra) {
      requestAnimationFrame(() => requestAnimationFrame(() => { barra.style.setProperty('--pct', barra.dataset.pct); }));
      pctAnterior = barra.dataset.pct;
    }
  };

  const mostrarAviso = (texto, erro = false) => {
    if (!aviso) return;
    aviso.hidden = !texto;
    aviso.textContent = texto || '';
    aviso.classList.toggle('is-err', erro);
  };

  const abrir = () => {
    if (!drawer || drawer.open) return;
    fecharMenu();
    drawer.showModal();
    $('[data-fechar-sacola]', drawer)?.focus();
  };
  const fechar = () => { if (drawer?.open) drawer.close(); };

  if (drawer && !semDrawer) {
    $$('[data-abrir-sacola]').forEach((a) => {
      a.addEventListener('click', async (e) => {
        e.preventDefault();
        mostrarAviso('');
        try {
          const res = await fetch(base + '/carrinho/resumo', { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
          desenhar(await res.json());
          abrir();
        } catch (_) {
          location.href = a.href;
        }
      });
    });
    $('[data-fechar-sacola]', drawer)?.addEventListener('click', fechar);
    drawer.addEventListener('click', (e) => { if (e.target === drawer) fechar(); });

    drawer.addEventListener('click', async (e) => {
      const qtd = e.target.closest('[data-qtd]');
      const rem = e.target.closest('[data-remover]');
      if (!qtd && !rem) return;
      drawer.classList.add('is-busy');
      try {
        const estado = qtd
          ? await postar(base + '/carrinho/atualizar', { produto_id: qtd.dataset.qtd, quantidade: qtd.dataset.valor })
          : await postar(base + '/carrinho/remover', { produto_id: rem.dataset.remover });
        mostrarAviso(estado.ok === false ? estado.message : '', estado.ok === false);
        desenhar(estado);
      } catch (_) {
        mostrarAviso('Não deu para atualizar agora. Verifique a conexão e tente de novo.', true);
      } finally {
        drawer.classList.remove('is-busy');
      }
    });

    $$('form[action$="/carrinho/adicionar"]').forEach((form) => {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = $('button[type="submit"], button:not([type])', form);
        if (btn?.getAttribute('aria-disabled') === 'true') return;
        btn?.setAttribute('aria-disabled', 'true');
        try {
          const estado = await postar(form.action, new FormData(form));
          if (estado.redirect) { location.href = estado.redirect; return; }
          desenhar(estado);
          mostrarAviso(estado.message, estado.ok === false);
          if (estado.ok && btn) {
            const original = btn.innerHTML;
            btn.classList.add('is-done');
            btn.innerHTML = btn.hasAttribute('data-icone')
              ? '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>'
              : 'Adicionado ✓';
            setTimeout(() => { btn.classList.remove('is-done'); btn.innerHTML = original; }, 1200);
          }
          abrir();
        } catch (_) {
          form.submit();
        } finally {
          btn?.removeAttribute('aria-disabled');
        }
      });
    });
  }
})();
