"use client";

import Image from "next/image";
import Link from "next/link";
import { Achadinho } from "@/lib/types";
import { formatPrice } from "@/lib/format";
import { motion } from "framer-motion";

const marketplaceLabels: Record<string, string> = {
  shopee: "Ver na Shopee",
  amazon: "Ver na Amazon",
  mercadolivre: "Ver no Mercado Livre",
  collshop: "Ver na Collshop",
};

interface AchadinhosGridProps {
  items: Achadinho[];
}

export function AchadinhosGrid({ items }: AchadinhosGridProps) {
  return (
    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
      {items.map((item, index) => (
        <motion.article
          key={item.id}
          initial={{ opacity: 0, y: 18 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.65, delay: index * 0.06 }}
          className="group relative border border-elomiah-green/10 bg-elomiah-cream shadow-soft"
        >
          <div className="absolute left-3 top-3 z-10 bg-elomiah-green px-2.5 py-1 text-[9px] uppercase tracking-soft text-elomiah-cream">
            Achadinho da Geo
          </div>
          <div className="relative aspect-square overflow-hidden bg-elomiah-surface">
            {item.image ? (
              <Image
                src={item.image}
                alt={item.name}
                fill
                sizes="(max-width: 768px) 100vw, 33vw"
                className="object-cover transition-transform duration-700 group-hover:scale-[1.03]"
              />
            ) : (
              <div className="absolute inset-0 flex flex-col justify-end bg-[radial-gradient(ellipse_at_30%_20%,rgba(184,149,107,0.18),transparent_55%)] p-6">
                <p className="font-display text-2xl leading-tight text-elomiah-green/80">
                  {item.name}
                </p>
                <p className="mt-2 text-[11px] tracking-[0.18em] text-elomiah-gold uppercase">
                  Curadoria Geo
                </p>
              </div>
            )}
          </div>
          <div className="space-y-3 p-5">
            <h3 className="font-display text-xl text-elomiah-green">
              {item.name}
            </h3>
            <p className="text-sm leading-relaxed text-elomiah-muted">
              {item.description}
            </p>
            <div className="flex items-center justify-between pt-2">
              <span className="font-display text-lg text-elomiah-gold">
                {formatPrice(item.price)}
              </span>
              <Link
                href={item.url}
                target="_blank"
                rel="noopener noreferrer"
                className="text-[11px] uppercase tracking-soft text-elomiah-green underline-offset-4 transition-colors hover:text-elomiah-gold hover:underline"
              >
                {marketplaceLabels[item.marketplace] || "Ver na Collshop"}
              </Link>
            </div>
          </div>
        </motion.article>
      ))}
    </div>
  );
}
