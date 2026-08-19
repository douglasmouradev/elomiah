(() => {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const mobile = window.matchMedia('(max-width: 800px)').matches;
  if (reduced) return;

  const spray = document.querySelector('[data-spray]');
  const canvas = document.querySelector('#mist-canvas');
  if (typeof gsap === 'undefined') return;

  if (window.ScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
  }

  gsap.utils.toArray('[data-parallax]').forEach((el) => {
    const depth = parseFloat(el.getAttribute('data-parallax') || '0.12');
    gsap.to(el, {
      y: () => window.innerHeight * depth * (el.getAttribute('data-dir') === 'up' ? -1 : 1),
      ease: 'none',
      scrollTrigger: {
        trigger: el.closest('section, .hero') || el,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true,
      },
    });
  });

  if (spray) {
    gsap.fromTo(spray, { y: 30, scale: 0.96 }, {
      y: 0,
      scale: 1,
      duration: 1.4,
      ease: 'power2.out',
    });
    gsap.to(spray, {
      y: -40,
      rotation: 1.2,
      ease: 'none',
      scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: 0.6,
      },
    });
  }

  if (!canvas || !canvas.getContext) return;
  const ctx = canvas.getContext('2d');
  const particles = [];
  const max = mobile ? 28 : 70;

  const resize = () => {
    canvas.width = canvas.offsetWidth * devicePixelRatio;
    canvas.height = canvas.offsetHeight * devicePixelRatio;
    ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
  };
  resize();
  window.addEventListener('resize', resize);

  const emit = (burst = 12) => {
    const origin = spray?.getBoundingClientRect();
    const parent = canvas.getBoundingClientRect();
    const ox = origin ? origin.left - parent.left + origin.width * 0.62 : parent.width * 0.55;
    const oy = origin ? origin.top - parent.top + origin.height * 0.16 : parent.height * 0.28;
    for (let i = 0; i < burst && particles.length < max; i += 1) {
      const angle = (-Math.PI / 2) + (Math.random() * 0.9 - 0.45);
      const speed = 0.6 + Math.random() * 1.4;
      particles.push({
        x: ox,
        y: oy,
        vx: Math.cos(angle) * speed,
        vy: Math.sin(angle) * speed - 0.4,
        r: 1.2 + Math.random() * 3.2,
        a: 0.18 + Math.random() * 0.22,
        life: 0,
        max: 90 + Math.random() * 50,
        gold: Math.random() > 0.45,
      });
    }
  };

  let running = false;
  let last = 0;
  const tick = (t) => {
    if (!running) return;
    if (t - last > (mobile ? 80 : 40)) {
      emit(mobile ? 2 : 4);
      last = t;
    }
    ctx.clearRect(0, 0, canvas.offsetWidth, canvas.offsetHeight);
    for (let i = particles.length - 1; i >= 0; i -= 1) {
      const p = particles[i];
      p.x += p.vx;
      p.y += p.vy;
      p.vx *= 0.99;
      p.vy -= 0.008;
      p.life += 1;
      const fade = 1 - p.life / p.max;
      ctx.beginPath();
      ctx.fillStyle = p.gold
        ? `rgba(201,162,75,${p.a * fade})`
        : `rgba(253,251,246,${p.a * fade})`;
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fill();
      if (p.life >= p.max) particles.splice(i, 1);
    }
    requestAnimationFrame(tick);
  };

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (e.isIntersecting && !running) {
        running = true;
        requestAnimationFrame(tick);
      }
      if (!e.isIntersecting) running = false;
    });
  }, { threshold: 0.2 });
  io.observe(canvas);

  if (window.ScrollTrigger) {
    ScrollTrigger.create({
      trigger: '.hero',
      start: 'top center',
      onEnter: () => emit(mobile ? 10 : 22),
      onEnterBack: () => emit(mobile ? 8 : 16),
    });
  }
})();
