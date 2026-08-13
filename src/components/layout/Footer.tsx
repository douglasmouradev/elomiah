"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { siteConfig, hasConfiguredMarketplaces } from "@/lib/site";
import { MarketplaceLinks } from "@/components/ui/MarketplaceLinks";
import { Instagram, Mail } from "lucide-react";

const navLinks = [
  ["/loja", "Produtos"],
  ["/curso", "Curso"],
  ["/achadinhos", "Achadinhos"],
  ["/sobre", "Sobre"],
  ["/depoimentos", "Depoimentos"],
  ["/contato", "Contato"],
  ["/faq", "FAQ"],
] as const;

export function Footer() {
  const pathname = usePathname();
  if (pathname?.startsWith("/admin")) return null;

  return (
    <footer className="relative mt-24 overflow-hidden bg-elomiah-dark text-elomiah-cream">
      <div className="absolute inset-0 bg-grain opacity-40" aria-hidden />

      <div className="container-wide relative px-5 py-20 md:px-12 md:py-24">
        <div className="grid gap-16 lg:grid-cols-12 lg:gap-8">
          <div className="lg:col-span-5">
            <p className="font-display text-3xl tracking-tight md:text-4xl">
              {siteConfig.tagline}
            </p>
            <p className="mt-6 max-w-sm text-sm leading-relaxed text-elomiah-cream/55">
              Aromatizantes e perfumes de nicho, criados com intenção para
              transformar ambientes em refúgio.
            </p>
          </div>

          <div className="grid gap-12 sm:grid-cols-2 lg:col-span-4 lg:col-start-7">
            <div>
              <p className="section-label text-elomiah-gold/80">Navegação</p>
              <ul className="mt-5 space-y-3">
                {navLinks.map(([href, label]) => (
                  <li key={href}>
                    <Link
                      href={href}
                      className="font-body text-sm text-elomiah-cream/65 transition-colors duration-300 hover:text-elomiah-cream"
                    >
                      {label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>

            <div>
              {hasConfiguredMarketplaces() && (
                <>
                  <p className="section-label text-elomiah-gold/80">Onde comprar</p>
                  <div className="mt-5">
                    <MarketplaceLinks variant="dark" />
                  </div>
                </>
              )}
              <div className={hasConfiguredMarketplaces() ? "mt-8 flex gap-5" : "flex gap-5"}>
                {siteConfig.instagram && (
                  <a
                    href={siteConfig.instagram}
                    target="_blank"
                    rel="noopener noreferrer"
                    data-no-spray
                    aria-label="Instagram"
                    className="text-elomiah-cream/50 transition-colors hover:text-elomiah-gold"
                  >
                    <Instagram size={18} strokeWidth={1.25} />
                  </a>
                )}
                <a
                  href={`mailto:${siteConfig.email}`}
                  aria-label="E-mail"
                  className="text-elomiah-cream/50 transition-colors hover:text-elomiah-gold"
                >
                  <Mail size={18} strokeWidth={1.25} />
                </a>
              </div>
            </div>
          </div>
        </div>

        <div className="mt-20 border-t border-elomiah-cream/[0.08] pt-10">
          <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
            <p className="font-display text-[clamp(3.5rem,12vw,8rem)] leading-[0.85] tracking-tight text-elomiah-cream/[0.07]">
              Elomiah
            </p>
            <div className="flex flex-col gap-2 text-xs text-elomiah-cream/40 md:text-right">
              <p>© {new Date().getFullYear()} Elomiah. Todos os direitos reservados.</p>
              <div className="flex flex-wrap gap-x-4 gap-y-1 md:justify-end">
                <Link href="/privacidade" className="transition-colors hover:text-elomiah-gold">
                  Privacidade
                </Link>
                <Link href="/termos" className="transition-colors hover:text-elomiah-gold">
                  Termos
                </Link>
                <Link href="/trocas-devolucoes" className="transition-colors hover:text-elomiah-gold">
                  Trocas
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
