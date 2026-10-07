(() => {
  const input = document.querySelector('#imagens');
  const box = document.querySelector('#previews');
  if (!input || !box) return;
  const LADO = 1800;
  const reduzir = async (file) => {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || typeof createImageBitmap !== 'function') return file;
    const bmp = await createImageBitmap(file);
    const escala = Math.min(1, LADO / Math.max(bmp.width, bmp.height));
    if (escala === 1 && file.size < 1.5 * 1024 * 1024) return file;
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bmp.width * escala);
    canvas.height = Math.round(bmp.height * escala);
    canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
    const blob = await new Promise((ok) => canvas.toBlob(ok, 'image/webp', 0.86));
    if (!blob || blob.type !== 'image/webp' || blob.size >= file.size) return file;
    return new File([blob], file.name.replace(/\.\w+$/, '') + '.webp', { type: 'image/webp' });
  };

  input.addEventListener('change', () => {
    box.innerHTML = '';
    [...input.files].forEach((file) => {
      const img = document.createElement('img');
      img.src = URL.createObjectURL(file);
      box.appendChild(img);
    });
  });

  const form = input.form;
  let pronto = false;
  form?.addEventListener('submit', async (e) => {
    if (pronto || !input.files.length || typeof DataTransfer !== 'function') return;
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const texto = btn?.textContent;
    if (btn) { btn.disabled = true; btn.textContent = 'Preparando as fotos…'; }
    try {
      const dt = new DataTransfer();
      for (const file of input.files) dt.items.add(await reduzir(file));
      input.files = dt.files;
    } catch (_) {
      // Envia as originais; o servidor também reduz.
    }
    if (btn) { btn.disabled = false; btn.textContent = 'Enviando…'; }
    pronto = true;
    if (form.requestSubmit) form.requestSubmit();
    else form.submit();
    window.addEventListener('pageshow', () => { pronto = false; if (btn) btn.textContent = texto; }, { once: true });
  });
})();

(() => {
  const root = document.querySelector('[data-notify]');
  if (!root) return;

  const toggle = root.querySelector('[data-notify-toggle]');
  const panel = root.querySelector('[data-notify-panel]');
  const list = root.querySelector('[data-notify-list]');
  const countEl = root.querySelector('[data-notify-count]');
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const appBase = (document.querySelector('meta[name="app-base"]')?.content || '').replace(/\/$/, '');
  const tituloBase = document.title.replace(/^\(\d+\)\s/, '');
  let visto = Number(countEl?.textContent || 0);

  const headers = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': token,
  };

  const setCount = (n) => {
    const qtd = Number(n) || 0;
    if (countEl) {
      countEl.textContent = qtd > 0 ? String(qtd) : '';
      countEl.classList.toggle('is-on', qtd > 0);
    }
    const badge = document.querySelector('.admin-nav a[href*="/admin/pedidos"] .nav-badge');
    if (badge) {
      badge.textContent = String(qtd);
      badge.hidden = qtd < 1;
    } else if (qtd > 0) {
      const pedidos = document.querySelector('.admin-nav a[href$="/admin/pedidos"]');
      if (pedidos && !pedidos.querySelector('.nav-badge')) {
        const span = document.createElement('span');
        span.className = 'nav-badge';
        span.textContent = String(qtd);
        pedidos.appendChild(span);
      }
    }
    document.title = qtd > 0 ? `(${qtd}) ${tituloBase}` : tituloBase;
    if (qtd > visto && typeof Notification !== 'undefined' && Notification.permission === 'granted') {
      const primeira = list?.querySelector('.is-new strong')?.textContent || 'Novo pedido na Elomiah';
      new Notification('Elomiah', { body: primeira, lang: 'pt-BR' });
    }
    visto = qtd;
  };

  const render = (itens) => {
    if (!list) return;
    if (!itens.length) {
      list.innerHTML = '<p class="admin-notify-empty">Nenhum aviso por agora.</p>';
      return;
    }
    list.innerHTML = itens.map((item) => `
      <a class="admin-notify-item${item.lida ? '' : ' is-new'}" href="${item.link}" data-notify-id="${item.id}">
        <strong></strong>
        <span></span>
      </a>
    `).join('');
    [...list.querySelectorAll('.admin-notify-item')].forEach((a, i) => {
      a.querySelector('strong').textContent = itens[i].titulo;
      a.querySelector('span').textContent = itens[i].mensagem || '';
    });
  };

  const puxar = async () => {
    try {
      const res = await fetch(appBase + '/admin/notificacoes', { headers, cache: 'no-store' });
      const data = await res.json();
      if (!data || !data.ok) return;
      render(data.itens || []);
      setCount(data.nao_lidas);
    } catch (_) {}
  };

  toggle?.addEventListener('click', () => {
    const aberto = !panel?.hidden;
    if (panel) panel.hidden = aberto;
    if (!aberto && typeof Notification !== 'undefined' && Notification.permission === 'default') {
      Notification.requestPermission();
    }
  });

  document.addEventListener('click', (e) => {
    if (!root.contains(e.target)) {
      if (panel) panel.hidden = true;
    }
  });

  list?.addEventListener('click', async (e) => {
    const item = e.target.closest('[data-notify-id]');
    if (!item) return;
    const id = item.getAttribute('data-notify-id');
    try {
      await fetch(`${appBase}/admin/notificacoes/${id}/lida`, {
        method: 'POST',
        headers: { ...headers, 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `_csrf=${encodeURIComponent(token)}`,
      });
    } catch (_) {}
  });

  root.querySelector('[data-notify-all]')?.addEventListener('click', async () => {
    await fetch(appBase + '/admin/notificacoes/lidas', {
      method: 'POST',
      headers: { ...headers, 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `_csrf=${encodeURIComponent(token)}`,
    });
    setCount(0);
    list?.querySelectorAll('.is-new').forEach((el) => el.classList.remove('is-new'));
  });

  puxar();
  setInterval(puxar, 15000);
})();
