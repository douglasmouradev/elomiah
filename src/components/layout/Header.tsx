"use client";

import Image from "next/image";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { ShoppingBag, Menu, X } from "lucide-react";
import { useCartStore } from "@/store/cart";
import { cn } from "@/lib/utils";

const links = [
  { href: "/loja", label: "Produtos" },
  { href: "/curso", label: "Curso" },
  { href: "/achadinhos", label: "Achadinhos" },
  { href: "/sobre", label: "Sobre" },
  { href: "/contato", label: "Contato" },
];

export function Header() {
  const pathname = usePathname();
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const hydrated = useCartStore((s) => s.hydrated);
  const count = useCartStore((s) =>
    s.items.reduce((acc, i) => acc + i.quantity, 0)
  );
  const openCart = useCartStore((s) => s.open);

  const lightOnDark = false;

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    setMobileOpen(false);
  }, [pathname]);

  useEffect(() => {
    if (!mobileOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") setMobileOpen(false);
    };
    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = "";
      window.removeEventListener("keydown", onKey);
    };
  }, [mobileOpen]);

  if (pathname?.startsWith("/admin")) return null;

  return (
    <>
      <header
        className={cn(
          "fixed inset-x-0 top-0 z-50 transition-all duration-700 ease-premium",
          scrolled
            ? "border-b border-elomiah-green/[0.08] bg-elomiah-cream/90 backdrop-blur-xl"
            : "bg-transparent"
        )}
      >
        <div className="container-wide grid grid-cols-[1fr_auto_1fr] items-center px-5 py-5 md:px-12 lg:py-6">
          <Link
            href="/"
            className="relative z-10 flex items-center justify-self-start overflow-visible"
          >
            <Image
              src="/logo-header.png"
              alt="Elomiah"
              width={608}
              height={268}
              className="h-12 w-auto max-w-[min(58vw,250px)] object-contain object-left md:h-14"
              priority
              unoptimized
            />
          </Link>

          <nav
            className="hidden items-center gap-8 lg:flex xl:gap-10"
            aria-label="Principal"
          >
            {links.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className={cn(
                  "relative font-body text-[13px] font-medium tracking-soft transition-colors duration-300 after:absolute after:-bottom-1 after:left-0 after:h-px after:w-0 after:bg-elomiah-gold after:transition-all after:duration-300 hover:after:w-full",
                  pathname === link.href || pathname.startsWith(link.href + "/")
                    ? cn(
                        "text-elomiah-green after:w-full after:bg-elomiah-gold",
                        lightOnDark && !scrolled && "text-white after:bg-white/80"
                      )
                    : lightOnDark
                      ? "text-white/75 hover:text-white"
                      : "text-elomiah-muted hover:text-elomiah-green"
                )}
              >
                {link.label}
              </Link>
            ))}
          </nav>

          <div className="flex items-center gap-2 justify-self-end">
            <button
              type="button"
              aria-label="Abrir carrinho"
              onClick={openCart}
              className={cn(
                "relative flex h-10 w-10 items-center justify-center transition-colors duration-300 hover:text-elomiah-gold",
                lightOnDark ? "text-white" : "text-elomiah-green"
              )}
            >
              <ShoppingBag size={19} strokeWidth={1.25} />
              {hydrated && count > 0 && (
                <span className="absolute right-0.5 top-0.5 flex h-[18px] min-w-[18px] items-center justify-center bg-elomiah-gold px-1 text-[10px] font-medium text-elomiah-dark">
                  {count}
                </span>
              )}
            </button>

            <button
              type="button"
              className={cn(
                "flex h-10 w-10 items-center justify-center lg:hidden",
                lightOnDark ? "text-white" : "text-elomiah-green"
              )}
              aria-label={mobileOpen ? "Fechar menu" : "Abrir menu"}
              aria-expanded={mobileOpen}
              onClick={() => setMobileOpen((v) => !v)}
            >
              {mobileOpen ? <X size={20} strokeWidth={1.25} /> : <Menu size={20} strokeWidth={1.25} />}
            </button>
          </div>
        </div>
      </header>

      {mobileOpen && (
        <div
          className="fixed inset-0 z-[45] bg-elomiah-dark/30 backdrop-blur-sm lg:hidden"
          onClick={() => setMobileOpen(false)}
          aria-hidden
        />
      )}

      <div
        className={cn(
          "fixed inset-y-0 right-0 z-[46] w-full max-w-sm bg-elomiah-cream shadow-lift transition-transform duration-500 ease-premium lg:hidden",
          mobileOpen ? "translate-x-0" : "translate-x-full pointer-events-none"
        )}
      >
        <nav className="flex h-full flex-col px-8 pt-28" aria-label="Mobile">
          {links.map((link, i) => (
            <Link
              key={link.href}
              href={link.href}
              className={cn(
                "border-b border-elomiah-green/[0.06] py-5 font-display text-3xl tracking-tight transition-colors",
                pathname === link.href
                  ? "text-elomiah-gold"
                  : "text-elomiah-green hover:text-elomiah-gold"
              )}
              style={{ transitionDelay: mobileOpen ? `${i * 40}ms` : "0ms" }}
            >
              {link.label}
            </Link>
          ))}
          <Link
            href="/depoimentos"
            className="mt-auto border-t border-elomiah-green/[0.06] py-6 font-body text-sm text-elomiah-muted hover:text-elomiah-green"
          >
            Depoimentos
          </Link>
        </nav>
      </div>
    </>
  );
}
