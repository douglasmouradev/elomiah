"use client";

import { useMemo, useState } from "react";
import { Product } from "@/lib/types";
import { ProductCard } from "@/components/ui/ProductCard";
import { cn } from "@/lib/utils";

type SortKey = "name" | "price-asc" | "price-desc";

const COLLECTIONS = ["Todas", "Coleção Refúgio", "Coleção ELO"] as const;

export function LojaClient({ products }: { products: Product[] }) {
  const [collection, setCollection] =
    useState<(typeof COLLECTIONS)[number]>("Todas");
  const [query, setQuery] = useState("");
  const [sort, setSort] = useState<SortKey>("name");

  const filtered = useMemo(() => {
    let list = [...products];

    if (collection !== "Todas") {
      list = list.filter((p) => p.collection === collection);
    }

    const q = query.trim().toLowerCase();
    if (q) {
      list = list.filter(
        (p) =>
          p.name.toLowerCase().includes(q) ||
          p.aroma.toLowerCase().includes(q) ||
          p.collection.toLowerCase().includes(q)
      );
    }

    list.sort((a, b) => {
      if (sort === "name") return a.name.localeCompare(b.name, "pt-BR");
      if (sort === "price-asc") return a.price - b.price;
      return b.price - a.price;
    });

    return list;
  }, [products, collection, query, sort]);

  return (
    <div>
      <div className="mb-14 flex flex-col gap-8 border-b border-elomiah-green/[0.08] pb-10">
        <input
          type="search"
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          placeholder="Buscar por nome, aroma ou coleção…"
          className="input-brand max-w-xl"
          aria-label="Buscar produtos"
        />

        <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
          <div className="flex gap-6 border-b border-elomiah-green/[0.06]">
            {COLLECTIONS.map((label) => (
              <button
                key={label}
                type="button"
                onClick={() => setCollection(label)}
                className={cn(
                  "chip pb-3",
                  collection === label && "chip-active"
                )}
              >
                {label === "Todas" ? "Todas" : label.replace("Coleção ", "")}
              </button>
            ))}
          </div>
          <select
            value={sort}
            onChange={(e) => setSort(e.target.value as SortKey)}
            className="border-0 border-b border-elomiah-green/15 bg-transparent py-2 text-sm text-elomiah-green outline-none focus:border-elomiah-gold"
            aria-label="Ordenar produtos"
          >
            <option value="name">Nome</option>
            <option value="price-asc">Preço: menor</option>
            <option value="price-desc">Preço: maior</option>
          </select>
        </div>
      </div>

      {filtered.length === 0 ? (
        <p className="text-elomiah-muted">Nenhuma essência encontrada.</p>
      ) : (
        <div className="grid gap-12 sm:grid-cols-2 lg:grid-cols-3">
          {filtered.map((product, i) => (
            <ProductCard key={product.id} product={product} index={i} />
          ))}
        </div>
      )}
    </div>
  );
}
