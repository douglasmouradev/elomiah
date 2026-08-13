"use client";

import { useEffect, useRef, useState } from "react";
import Link from "next/link";
import { ArrowLeft, ArrowRight } from "lucide-react";
import { Product } from "@/lib/types";
import { ProductCard } from "@/components/ui/ProductCard";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { cn } from "@/lib/utils";

const COLLECTIONS = ["Coleção Refúgio", "Coleção ELO", "Kits"] as const;

export function FeaturedProducts({ products }: { products: Product[] }) {
  const trackRef = useRef<HTMLDivElement>(null);
  const [canPrev, setCanPrev] = useState(false);
  const [canNext, setCanNext] = useState(true);
  const [activeCollection, setActiveCollection] = useState<string>(
    COLLECTIONS[0]
  );

  const list = products.filter((p) => p.collection === activeCollection);

  const updateArrows = () => {
    const el = trackRef.current;
    if (!el) return;
    setCanPrev(el.scrollLeft > 8);
    setCanNext(el.scrollLeft + el.clientWidth < el.scrollWidth - 8);
  };

  useEffect(() => {
    const el = trackRef.current;
    if (!el) return;
    el.scrollLeft = 0;
    updateArrows();
    el.addEventListener("scroll", updateArrows, { passive: true });
    window.addEventListener("resize", updateArrows);
    return () => {
      el.removeEventListener("scroll", updateArrows);
      window.removeEventListener("resize", updateArrows);
    };
  }, [list.length, activeCollection]);

  const scrollByCard = (dir: -1 | 1) => {
    const el = trackRef.current;
    if (!el) return;
    const card = el.querySelector<HTMLElement>("[data-featured-card]");
    const amount = card ? card.offsetWidth + 32 : el.clientWidth * 0.7;
    el.scrollBy({ left: dir * amount, behavior: "smooth" });
  };

  return (
    <section className="section-padding overflow-hidden">
      <div className="container-wide mb-12 md:mb-16">
        <div className="flex flex-col gap-10 lg:flex-row lg:items-end lg:justify-between">
          <SectionHeader
            index="01"
            label="Vitrine"
            title="Essências em destaque"
          />

          <div className="flex flex-col gap-6 sm:flex-row sm:items-center">
            <div className="flex gap-6 border-b border-elomiah-green/10">
              {COLLECTIONS.map((col) => (
                <button
                  key={col}
                  type="button"
                  onClick={() => setActiveCollection(col)}
                  className={cn(
                    "chip pb-3",
                    activeCollection === col && "chip-active"
                  )}
                >
                  {col === "Kits" ? "Kits" : col.replace("Coleção ", "")}
                </button>
              ))}
            </div>
            <Link href="/loja" className="btn-link hidden sm:inline-flex">
              Ver catálogo completo
            </Link>
          </div>
        </div>
      </div>

      {list.length === 0 ? (
        <div className="container-wide">
          <p className="text-elomiah-muted">Nenhuma essência nesta coleção.</p>
        </div>
      ) : (
        <div className="relative">
          <div
            ref={trackRef}
            className="hide-scrollbar flex snap-x snap-mandatory gap-8 overflow-x-auto px-5 pb-2 md:gap-10 md:px-12 lg:px-[max(3rem,calc((100vw-88rem)/2+3rem))]"
          >
            {list.map((product, i) => (
              <div
                key={product.id}
                data-featured-card
                className="w-[min(78vw,280px)] shrink-0 snap-start sm:w-[280px] md:w-[300px]"
              >
                <ProductCard product={product} index={i} variant="featured" />
              </div>
            ))}
          </div>

          <div className="container-wide mt-8 flex items-center justify-end gap-3 px-5 md:px-12">
            <button
              type="button"
              aria-label="Anterior"
              disabled={!canPrev}
              onClick={() => scrollByCard(-1)}
              className="flex h-11 w-11 items-center justify-center border border-elomiah-green/10 text-elomiah-green transition-all duration-300 hover:border-elomiah-green disabled:opacity-25"
            >
              <ArrowLeft size={16} strokeWidth={1.25} />
            </button>
            <button
              type="button"
              aria-label="Próximo"
              disabled={!canNext}
              onClick={() => scrollByCard(1)}
              className="flex h-11 w-11 items-center justify-center border border-elomiah-green/10 text-elomiah-green transition-all duration-300 hover:border-elomiah-green disabled:opacity-25"
            >
              <ArrowRight size={16} strokeWidth={1.25} />
            </button>
          </div>
        </div>
      )}
    </section>
  );
}
