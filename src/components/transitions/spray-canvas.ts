import {
  SPRAY_DURATION_MS,
  SPRAY_NAV_AT,
  easeExpand,
  easeOutSoft,
  easePage,
  maxSprayReach,
  type SprayOrigin,
} from "@/lib/spray-transition";

type Droplet = {
  x: number;
  y: number;
  vx: number;
  vy: number;
  size: number;
  grow: number;
  life: number;
  max: number;
  gold: boolean;
};

export type SprayCanvasOptions = {
  origin: SprayOrigin;
  duration?: number;
  navAt?: number;
  mobile?: boolean;
  onNavigate?: () => void;
  onComplete?: () => void;
  onPhase?: (phase: "spray" | "fill" | "reveal", progress: number) => void;
};

export function runSprayCanvas(
  canvas: HTMLCanvasElement,
  options: SprayCanvasOptions
): () => void {
  const {
    origin,
    duration = SPRAY_DURATION_MS,
    navAt = SPRAY_NAV_AT,
    mobile = false,
    onNavigate,
    onComplete,
    onPhase,
  } = options;

  const ctx = canvas.getContext("2d", { alpha: true });
  if (!ctx) {
    onNavigate?.();
    onComplete?.();
    return () => {};
  }

  const dpr = Math.min(window.devicePixelRatio || 1, mobile ? 1.25 : 1.5);
  const maxDrops = mobile ? 110 : 200;
  const drops: Droplet[] = [];
  let w = 0;
  let h = 0;
  let reach = 0;
  let running = true;
  let raf = 0;
  let navigated = false;
  let lastPhase: "spray" | "fill" | "reveal" = "spray";
  const start = performance.now();
  const emitUntil = duration * 0.48;

  const resize = () => {
    w = window.innerWidth;
    h = window.innerHeight;
    canvas.width = Math.floor(w * dpr);
    canvas.height = Math.floor(h * dpr);
    canvas.style.width = `${w}px`;
    canvas.style.height = `${h}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    reach = maxSprayReach(origin, w, h);
  };
  resize();

  const drawBlob = (
    x: number,
    y: number,
    radius: number,
    alpha: number,
    gold = false
  ) => {
    const g = ctx.createRadialGradient(x, y, 0, x, y, radius);
    if (gold) {
      g.addColorStop(0, `rgba(255, 248, 230, ${alpha * 0.8})`);
      g.addColorStop(0.5, `rgba(201, 162, 75, ${alpha * 0.22})`);
      g.addColorStop(1, "rgba(201, 162, 75, 0)");
    } else {
      g.addColorStop(0, `rgba(255, 255, 255, ${alpha * 0.88})`);
      g.addColorStop(0.35, `rgba(255, 252, 248, ${alpha * 0.32})`);
      g.addColorStop(0.7, `rgba(253, 251, 246, ${alpha * 0.08})`);
      g.addColorStop(1, "rgba(255, 255, 255, 0)");
    }
    ctx.fillStyle = g;
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, Math.PI * 2);
    ctx.fill();
  };

  /** Gotículas em todas as direções — névoa orgânica, sem leque */
  const emitBurst = (n: number, intensity: number) => {
    for (let i = 0; i < n; i++) {
      if (drops.length >= maxDrops) drops.shift();

      const a = Math.random() * Math.PI * 2;
      const speed =
        (0.35 + Math.random() * 1.6 + intensity * 1.8) * (mobile ? 0.75 : 1);
      const close = Math.random() > 0.32;

      drops.push({
        x: origin.x + (Math.random() - 0.5) * 6,
        y: origin.y + (Math.random() - 0.5) * 6,
        vx: Math.cos(a) * speed,
        vy: Math.sin(a) * speed,
        size: close ? 1.2 + Math.random() * 2.8 : 0.4 + Math.random() * 1.4,
        grow: close ? 0.03 + Math.random() * 0.05 : 0.015 + Math.random() * 0.03,
        life: 0,
        max: close ? 70 + Math.random() * 60 : 45 + Math.random() * 50,
        gold: Math.random() > 0.92,
      });
    }
  };

  /** Núcleo suave no bico — só um ponto luminoso, sem forma geométrica */
  const drawNozzleGlow = (expand: number, dissolve: number) => {
    const r = 6 + expand * 14;
    const alpha = (1 - dissolve) * (0.35 + expand * 0.35);
    drawBlob(origin.x, origin.y, r, alpha);
  };

  /** Nuvens difusas sobrepostas — preenchem a tela organicamente */
  const drawCloudLayers = (expand: number, dissolve: number) => {
    const layers = mobile ? 6 : 10;
    const fade = (1 - dissolve) * expand;

    for (let i = 0; i < layers; i++) {
      const t = i / layers;
      const angle = i * 2.399963 + expand * 0.8;
      const dist = reach * expand * (0.08 + t * 0.72);
      const wobble = 0.38 + (i % 4) * 0.06;
      const cx = origin.x + Math.cos(angle) * dist * wobble;
      const cy = origin.y + Math.sin(angle) * dist * wobble;
      const radius = 18 + expand * (35 + i * 18);
      const alpha = fade * 0.07 * (1 - t * 0.65);
      drawBlob(cx, cy, radius, alpha);
    }
  };

  const frame = (now: number) => {
    if (!running) return;

    const elapsed = now - start;
    const t = Math.min(1, elapsed / duration);

    const expandT = Math.min(1, elapsed / (duration * 0.58));
    const expand = easeExpand(expandT);

    const fillT = Math.max(0, (t - 0.15) / 0.55);
    const fill = easeOutSoft(Math.min(1, fillT));

    const dissolveT = Math.max(0, (t - navAt) / (1 - navAt));
    const dissolve = easePage(dissolveT);

    const phase: "spray" | "fill" | "reveal" =
      t < 0.35 ? "spray" : t < navAt ? "fill" : "reveal";

    if (phase !== lastPhase) {
      lastPhase = phase;
      onPhase?.(phase, t);
    }

    if (!navigated && t >= navAt) {
      navigated = true;
      onNavigate?.();
    }

    ctx.globalCompositeOperation = "source-over";
    ctx.fillStyle = `rgba(255, 255, 255, ${0.022 + dissolve * 0.04})`;
    ctx.fillRect(0, 0, w, h);

    if (elapsed < emitUntil) {
      emitBurst(mobile ? 3 : 4, expand);
    }

    ctx.globalCompositeOperation = "lighter";
    drawCloudLayers(fill, dissolve);
    drawNozzleGlow(expand, dissolve);

    const drift = 0.4 + expand * 0.5;
    const particleAlpha = (1 - dissolve * 0.92) * (0.2 + expand * 0.32);

    for (let i = drops.length - 1; i >= 0; i--) {
      const d = drops[i];
      d.x += d.vx * drift;
      d.y += d.vy * drift;
      d.vx *= 0.981;
      d.vy *= 0.982;
      d.vy -= 0.002;
      d.vx += (Math.random() - 0.5) * 0.03;
      d.vy += (Math.random() - 0.5) * 0.025;
      d.life += 1;

      const lifeT = d.life / d.max;
      if (lifeT >= 1) {
        drops.splice(i, 1);
        continue;
      }

      const dist = Math.hypot(d.x - origin.x, d.y - origin.y);
      const distFade = 1 - (dist / reach) * 0.5;
      const alpha = particleAlpha * (1 - lifeT * lifeT) * distFade;
      if (alpha < 0.003) continue;

      drawBlob(d.x, d.y, d.size + d.grow * d.life, alpha, d.gold);
    }

    ctx.globalCompositeOperation = "source-over";

    if (t >= 1) {
      running = false;
      onComplete?.();
      return;
    }

    raf = requestAnimationFrame(frame);
  };

  raf = requestAnimationFrame(frame);

  const onResize = () => resize();
  window.addEventListener("resize", onResize, { passive: true });

  return () => {
    running = false;
    cancelAnimationFrame(raf);
    window.removeEventListener("resize", onResize);
  };
}
