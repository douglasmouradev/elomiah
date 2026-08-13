"use client";

import Image from "next/image";
import Link from "next/link";
import { Product } from "@/lib/types";
import { formatPrice } from "@/lib/format";
import { cn } from "@/lib/utils";

interface ProductCardProps {
  product: Product;
  index?: number;
  variant?: "card" | "featured";
}

export function ProductCard({
  product,
  variant = "card",
}: ProductCardProps) {
  const featured = variant === "featured";
  const outOfStock = product.stock <= 0;
  const lowStock = !outOfStock && product.stock <= 5;

  return (
    <article className={cn("group min-w-0", featured && "h-full")}>
      <Link href={`/produtos/${product.slug}`} className="flex h-full min-w-0 flex-col">
        <div className="relative aspect-[3/4] overflow-hidden bg-elomiah-surface shadow-card transition-shadow duration-500 group-hover:shadow-soft">
          <div
            className="absolute inset-x-0 top-0 z-10 h-0.5 opacity-80"
            style={{ backgroundColor: product.accentColor }}
            aria-hidden
          />
          {(outOfStock || lowStock) && (
            <span
              className={cn(
                "absolute left-3 top-3 z-10 px-2.5 py-1 text-[11px] font-medium tracking-soft",
                outOfStock
                  ? "bg-elomiah-dark text-elomiah-cream"
                  : "bg-elomiah-cream/95 text-elomiah-green"
              )}
            >
              {outOfStock ? "Esgotado" : `Últimas ${product.stock}`}
            </span>
          )}
          <Image
            src={product.images[0]}
            alt={`${product.name} — Aroma ${product.aroma}`}
            fill
            sizes={
              featured
                ? "(max-width: 768px) 78vw, 300px"
                : "(max-width: 768px) 100vw, 33vw"
            }
            quality={90}
            className={cn(
              "object-contain object-center p-4 transition-transform duration-700 ease-premium group-hover:scale-[1.03]",
              outOfStock && "opacity-55"
            )}
          />
        </div>

        <div className="mt-5 flex flex-1 flex-col gap-1">
          <div className="flex items-baseline justify-between gap-3">
            <h3 className="font-display text-2xl leading-tight text-elomiah-green transition-colors duration-300 group-hover:text-elomiah-gold">
              {product.name}
            </h3>
            <p className="shrink-0 font-body text-sm font-medium text-elomiah-green">
              {formatPrice(product.price)}
            </p>
          </div>
          <p className="text-xs tracking-wide text-elomiah-muted">
            {product.collection} · Aroma {product.aroma}
          </p>
          {!featured && (
            <p className="mt-2 line-clamp-2 text-sm leading-relaxed text-elomiah-muted/90">
              {product.shortDescription}
            </p>
          )}
        </div>
      </Link>
    </article>
  );
}
