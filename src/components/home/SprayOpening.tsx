"use client";

import Image from "next/image";
import { useEffect, useRef, useState } from "react";
import { motion } from "framer-motion";

type Mist = {
  x: number;
  y: number;
  vx: number;
  vy: number;
  size: number;
  life: number;
  max: number;
  alpha: number;
};

const ease = [0.22, 1, 0.36, 1] as const;
const AUTO_MS = 5200;

/**
 * Abertura editorial — marca primeiro, frasco como ritual, saída suave.
 */
export function SprayOpening({ onDismiss }: { onDismiss?: () => void }) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const bottleRef = useRef<HTMLDivElement>(null);
  const [leaving, setLeaving] = useState(false);
  const [phase, setPhase] = useState(0);

  useEffect(() => {
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, []);

  useEffect(() => {
    const timers = [
      window.setTimeout(() => setPhase(1), 200),
      window.setTimeout(() => setPhase(2), 700),
      window.setTimeout(() => setPhase(3), 1600),
      window.setTimeout(() => setPhase(4), 2800),
      window.setTimeout(() => setLeaving(true), AUTO_MS - 650),
      window.setTimeout(() => onDismiss?.(), AUTO_MS),
    ];
    return () => timers.forEach((t) => window.clearTimeout(t));
  }, [onDismiss]);

  useEffect(() => {
    const canvas = canvasRef.current;
    const bottle = bottleRef.current;
    if (!canvas || !bottle) return;

    const reduced =
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduced) return;

    const ctx = canvas.getContext("2d", { alpha: true });
    if (!ctx) return;

    const mobile = window.matchMedia("(max-width: 768px)").matches;
    const dpr = Math.min(window.devicePixelRatio || 1, mobile ? 1.2 : 1.5);
    const particles: Mist[] = [];
    const max = mobile ? 22 : 36;
    let running = true;
    let raf = 0;
    let ox = 0;
    let oy = 0;
    let w = 0;
    let h = 0;
    let spraying = false;

    const nozzle = () => {
      const rect = bottle.getBoundingClientRect();
      const parent = canvas.getBoundingClientRect();
      ox = rect.left - parent.left + rect.width * 0.55;
      oy = rect.top - parent.top + rect.height * 0.11;
    };

    const spawn = (n: number, burst = false) => {
      for (let i = 0; i < n; i++) {
        if (particles.length >= max) particles.shift();
        const speed = burst
          ? 0.45 + Math.random() * 0.9
          : 0.18 + Math.random() * 0.45;
        particles.push({
          x: ox + (Math.random() - 0.3) * 8,
          y: oy + (Math.random() - 0.5) * 5,
          vx: speed * (0.55 + Math.random() * 0.7),
          vy: -speed * (0.05 + Math.random() * 0.35),
          size: burst ? 1.1 + Math.random() * 2.2 : 0.7 + Math.random() * 1.6,
          life: 0,
          max: burst ? 70 + Math.random() * 90 : 95 + Math.random() * 100,
          alpha: burst
            ? 0.1 + Math.random() * 0.1
            : 0.05 + Math.random() * 0.07,
        });
      }
    };

    const resize = () => {
      const parent = canvas.parentElement;
      if (!parent) return;
      w = parent.clientWidth;
      h = parent.clientHeight;
      canvas.width = Math.floor(w * dpr);
      canvas.height = Math.floor(h * dpr);
      canvas.style.width = `${w}px`;
      canvas.style.height = `${h}px`;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      nozzle();
    };
    resize();

    const startSpray = window.setTimeout(() => {
      spraying = true;
      nozzle();
      spawn(mobile ? 10 : 16, true);
    }, 1750);

    let frame = 0;
    const draw = () => {
      if (!running) return;
      ctx.clearRect(0, 0, w, h);
      if (spraying && frame % (mobile ? 5 : 4) === 0) spawn(1);

      for (let i = particles.length - 1; i >= 0; i--) {
        const p = particles[i];
        p.x += p.vx;
        p.y += p.vy;
        p.vx *= 0.995;
        p.vy *= 0.994;
        p.vy -= 0.0018;
        p.life += 1;
        const t = p.life / p.max;
        if (t >= 1) {
          particles.splice(i, 1);
          continue;
        }
        const fade = Math.sin(t * Math.PI);
        const a = p.alpha * fade;
        if (a < 0.01) continue;
        const r = p.size * (1 + t * 2.8);
        const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, r * 5);
        g.addColorStop(0, `rgba(255,255,255,${a * 0.6})`);
        g.addColorStop(0.4, `rgba(247,243,236,${a * 0.22})`);
        g.addColorStop(1, "rgba(255,255,255,0)");
        ctx.fillStyle = g;
        ctx.beginPath();
        ctx.arc(p.x, p.y, r * 5, 0, Math.PI * 2);
        ctx.fill();
      }
      frame++;
      raf = requestAnimationFrame(draw);
    };
    raf = requestAnimationFrame(draw);

    window.addEventListener("resize", resize, { passive: true });
    return () => {
      running = false;
      window.clearTimeout(startSpray);
      cancelAnimationFrame(raf);
      window.removeEventListener("resize", resize);
    };
  }, []);

  const dismiss = () => {
    if (leaving) return;
    setLeaving(true);
    window.setTimeout(() => onDismiss?.(), 620);
  };

  return (
    <motion.section
      role="dialog"
      aria-modal="true"
      aria-label="Abertura Elomiah"
      onClick={dismiss}
      initial={{ opacity: 1 }}
      animate={{
        opacity: leaving ? 0 : 1,
        scale: leaving ? 1.02 : 1,
        filter: leaving ? "blur(6px)" : "blur(0px)",
      }}
      transition={{ duration: 0.62, ease }}
      className="fixed inset-0 z-[80] flex cursor-pointer items-center justify-center overflow-hidden bg-[#f3efe8]"
    >
      {/* Atmosfera */}
      <div
        className="pointer-events-none absolute inset-0"
        style={{
          background: `
            radial-gradient(ellipse 55% 45% at 50% 38%, rgba(184,149,107,0.12), transparent 62%),
            radial-gradient(ellipse 80% 70% at 50% 55%, transparent 40%, rgba(12,26,20,0.045) 100%)
          `,
        }}
        aria-hidden
      />
      <div
        className="pointer-events-none absolute inset-0 opacity-[0.28]"
        style={{
          backgroundImage:
            "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.045'/%3E%3C/svg%3E\")",
        }}
        aria-hidden
      />

      <div className="pointer-events-none relative z-10 flex w-full max-w-lg flex-col items-center px-6">
        {/* Marca — sinal hero */}
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{
            opacity: phase >= 1 ? 1 : 0,
            y: phase >= 1 ? 0 : 12,
          }}
          transition={{ duration: 1.05, ease }}
          className="text-center"
        >
          <p className="font-display text-[clamp(2.75rem,9vw,4.25rem)] font-medium leading-[0.92] tracking-[0.06em] text-elomiah-green">
            ELOMIAH
          </p>
          <motion.div
            initial={{ opacity: 0, scaleX: 0 }}
            animate={{
              opacity: phase >= 1 ? 1 : 0,
              scaleX: phase >= 1 ? 1 : 0,
            }}
            transition={{ duration: 0.85, delay: 0.2, ease }}
            className="mx-auto mt-5 flex origin-center items-center justify-center gap-3"
          >
            <span className="h-px w-7 bg-elomiah-gold/55 md:w-10" />
            <span className="font-body text-[10px] font-medium tracking-[0.34em] text-elomiah-gold uppercase">
              Coleção Refúgio
            </span>
            <span className="h-px w-7 bg-elomiah-gold/55 md:w-10" />
          </motion.div>
        </motion.div>

        {/* Frasco — ritual visual */}
        <motion.div
          initial={{ opacity: 0, y: 28, scale: 0.97 }}
          animate={{
            opacity: phase >= 2 ? 1 : 0,
            y: phase >= 2 ? 0 : 28,
            scale: phase >= 2 ? 1 : 0.97,
          }}
          transition={{ duration: 1.25, ease }}
          className="relative mt-10 w-[min(44vw,200px)] md:mt-12 md:w-[220px]"
        >
          <div
            className="pointer-events-none absolute -inset-x-12 bottom-0 h-14 rounded-[100%] bg-elomiah-green/[0.07] blur-2xl"
            aria-hidden
          />
          <motion.div
            ref={bottleRef}
            animate={
              phase >= 2 && !leaving
                ? { y: [0, -5, 0] }
                : { y: 0 }
            }
            transition={{
              duration: 4.2,
              repeat: Infinity,
              ease: "easeInOut",
            }}
            className="relative aspect-[3/4]"
          >
            <Image
              src="/images/produtos/thumbs/despertar-opening.jpg"
              alt="Spray Elomiah"
              fill
              priority
              quality={93}
              sizes="220px"
              className="object-contain object-center drop-shadow-[0_18px_40px_rgba(21,41,33,0.12)]"
            />
          </motion.div>
        </motion.div>

        {/* Slogan — uma linha só */}
        <motion.p
          initial={{ opacity: 0, y: 10 }}
          animate={{
            opacity: phase >= 3 ? 1 : 0,
            y: phase >= 3 ? 0 : 10,
          }}
          transition={{ duration: 1, ease }}
          className="mt-10 max-w-[18rem] text-center font-display text-[1.05rem] italic leading-snug text-elomiah-green/60 md:mt-12 md:text-[1.15rem]"
        >
          Onde o sagrado encontra a essência.
        </motion.p>

        <motion.p
          initial={{ opacity: 0 }}
          animate={{ opacity: phase >= 4 ? 1 : 0 }}
          transition={{ duration: 0.8, ease }}
          className="mt-8 font-body text-[9px] tracking-[0.28em] text-elomiah-muted/70 uppercase"
        >
          Toque para entrar
        </motion.p>
      </div>

      <canvas
        ref={canvasRef}
        className="pointer-events-none absolute inset-0 z-20 h-full w-full blur-[1.5px]"
        aria-hidden
      />

      <button
        type="button"
        onClick={(e) => {
          e.stopPropagation();
          dismiss();
        }}
        className="absolute right-5 top-6 z-40 cursor-pointer font-body text-[10px] tracking-[0.22em] text-elomiah-muted/75 uppercase transition-colors hover:text-elomiah-green md:right-8 md:top-8"
      >
        Pular
      </button>

      <div className="pointer-events-none absolute bottom-8 left-1/2 z-30 h-px w-20 -translate-x-1/2 overflow-hidden bg-elomiah-green/10">
        <motion.div
          initial={{ scaleX: 0 }}
          animate={{ scaleX: leaving ? 1 : 1 }}
          transition={{ duration: AUTO_MS / 1000, ease: "linear" }}
          className="h-full origin-left bg-elomiah-gold/65"
        />
      </div>
    </motion.section>
  );
}
