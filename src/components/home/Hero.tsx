"use client";

import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";

const ease = [0.22, 1, 0.36, 1] as const;

export function Hero() {
  return (
    <section className="relative min-h-[100svh] overflow-hidden bg-[#f3efe8]">
      {/* Full-bleed: frascos à direita, espaço editorial à esquerda */}
      <motion.div
        initial={{ opacity: 0, scale: 1.04 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ duration: 1.7, ease }}
        className="absolute inset-0"
      >
        <Image
          src="/images/produtos/colecao-refugio-hero.jpg"
          alt="Coleção Refúgio — Recomeço, Encontro e Equilíbrio"
          fill
          sizes="100vw"
          quality={94}
          priority
          className="hidden object-cover object-right md:block"
        />
        <Image
          src="/images/produtos/colecao-refugio-hero-mobile.jpg"
          alt="Coleção Refúgio — frascos Elomiah"
          fill
          sizes="100vw"
          quality={92}
          priority
          className="object-cover object-[78%_42%] md:hidden"
        />
      </motion.div>

      {/* Véu: legibilidade do texto sem esconder os frascos */}
      <div
        className="pointer-events-none absolute inset-0"
        style={{
          background: `
            linear-gradient(90deg,
              #f3efe8 0%,
              #f3efe8 22%,
              rgba(243,239,232,0.92) 36%,
              rgba(243,239,232,0.45) 52%,
              rgba(243,239,232,0.08) 68%,
              transparent 82%
            ),
            linear-gradient(180deg,
              rgba(243,239,232,0.55) 0%,
              transparent 22%,
              transparent 78%,
              rgba(243,239,232,0.5) 100%
            )
          `,
        }}
        aria-hidden
      />
      {/* Mobile: mais véu embaixo onde fica o texto */}
      <div
        className="pointer-events-none absolute inset-0 md:hidden"
        style={{
          background: `
            linear-gradient(180deg,
              rgba(243,239,232,0.35) 0%,
              transparent 28%,
              rgba(243,239,232,0.55) 58%,
              #f3efe8 100%
            )
          `,
        }}
        aria-hidden
      />

      <div className="container-wide relative z-10 flex min-h-[100svh] items-end px-5 pb-16 pt-28 md:items-center md:px-12 md:pb-24 md:pt-32">
        <div className="max-w-[22rem] sm:max-w-md md:max-w-lg">
          <motion.p
            initial={{ opacity: 0, y: 18 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 1, delay: 0.2, ease }}
            className="font-display text-[clamp(3.25rem,9vw,5.75rem)] font-medium leading-[0.92] tracking-[0.04em] text-elomiah-green"
          >
            ELOMIAH
          </motion.p>

          <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.85, delay: 0.45, ease }}
            className="mt-5 flex items-center gap-3"
          >
            <span className="h-px w-8 bg-elomiah-gold md:w-12" />
            <span className="font-body text-[10px] font-medium tracking-[0.28em] text-elomiah-gold uppercase md:text-[11px]">
              Coleção Refúgio
            </span>
          </motion.div>

          <motion.h1
            initial={{ opacity: 0, y: 16 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.95, delay: 0.55, ease }}
            className="mt-8 font-display text-[clamp(1.65rem,3.5vw,2.35rem)] font-medium leading-[1.2] tracking-tight text-elomiah-green"
          >
            O sagrado mora no{" "}
            <span className="italic font-normal text-elomiah-gold">aroma</span>.
          </motion.h1>

          <motion.p
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.85, delay: 0.7, ease }}
            className="mt-5 max-w-sm text-[15px] leading-[1.75] text-elomiah-muted md:text-[16px]"
          >
            Cinco essências para presença, quietude e ritual no cotidiano.
          </motion.p>

          <motion.div
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8, delay: 0.85, ease }}
            className="mt-10 flex flex-wrap items-center gap-5"
          >
            <Link href="/loja" className="btn-primary">
              Explorar essências
            </Link>
            <Link href="/sobre" className="btn-link">
              A história da marca
            </Link>
          </motion.div>
        </div>
      </div>

      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        transition={{ delay: 1.4, duration: 1 }}
        className="pointer-events-none absolute bottom-6 left-1/2 hidden -translate-x-1/2 md:block"
        aria-hidden
      >
        <span className="block h-8 w-px bg-gradient-to-b from-elomiah-gold/60 to-transparent" />
      </motion.div>
    </section>
  );
}
