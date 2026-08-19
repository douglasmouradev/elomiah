(() => {
  const header = document.querySelector('.site-header');
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.nav-main');

  const onScroll = () => {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 12);
  };
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  toggle?.addEventListener('click', () => nav?.classList.toggle('is-open'));

  const banner = document.querySelector('.cookie-banner');
  if (banner && !document.cookie.includes('elomiah_cookies=')) {
    banner.classList.add('is-on');
  }
  banner?.querySelectorAll('[data-cookie]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const escolha = btn.getAttribute('data-cookie');
      const token = document.querySelector('meta[name="csrf-token"]')?.content;
      await fetch('/cookies/consentimento', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': token || '',
        },
        body: `_csrf=${encodeURIComponent(token || '')}&escolha=${encodeURIComponent(escolha)}`,
      });
      banner.classList.remove('is-on');
    });
  });

  document.querySelectorAll('.gallery-main').forEach((el) => {
    el.addEventListener('click', () => el.classList.toggle('is-zoom'));
  });
  document.querySelectorAll('[data-thumb]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const src = btn.getAttribute('data-thumb');
      const main = document.querySelector('.gallery-main img');
      if (main && src) main.src = src;
      btn.parentElement?.querySelectorAll('button').forEach((b) => b.classList.remove('is-on'));
      btn.classList.add('is-on');
    });
  });

  const splash = document.getElementById('splash');
  const skipSplash = document.documentElement.classList.contains('splash-skip');
  const revelar = () => {
    document.documentElement.classList.add('splash-ready');
    if (!splash) return;
    splash.classList.add('is-done');
    try { sessionStorage.setItem('elomiah_splash', '1'); } catch (e) {}
    setTimeout(() => splash.remove(), 750);
  };

  if (skipSplash) {
    document.documentElement.classList.add('splash-ready');
    splash?.remove();
  } else {
    const minimo = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 200 : 1600;
    const inicio = Date.now();
    const terminar = () => {
      const resto = Math.max(0, minimo - (Date.now() - inicio));
      setTimeout(revelar, resto);
    };
    if (document.readyState === 'complete') terminar();
    else window.addEventListener('load', terminar);
  }

  const checkout = document.querySelector('[data-checkout]');
  if (checkout) {
    checkout.classList.add('is-stepped');
    const passo1 = checkout.querySelector('[data-step="1"]');
    const ir = checkout.querySelector('[data-to-pay]');
    const voltar = checkout.querySelector('[data-to-dest]');
    ir?.addEventListener('click', () => {
      const campos = passo1?.querySelectorAll('input, textarea, select');
      if (campos) {
        for (const el of campos) {
          if (typeof el.checkValidity === 'function' && !el.checkValidity()) {
            el.reportValidity();
            return;
          }
        }
      }
      checkout.classList.add('is-pay');
      checkout.querySelector('[data-step="2"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    voltar?.addEventListener('click', () => checkout.classList.remove('is-pay'));
    checkout.addEventListener('submit', (e) => {
      if (!checkout.classList.contains('is-pay')) {
        e.preventDefault();
        ir?.click();
        return;
      }
      if (!checkout.querySelector('button[type="submit"]')) {
        e.preventDefault();
      }
    });
  }
})();
