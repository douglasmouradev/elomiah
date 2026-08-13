import Link from "next/link";
import { ExternalLink } from "lucide-react";
import { Achadinho } from "@/lib/types";
import { AchadinhosGrid } from "@/components/ui/AchadinhosGrid";
import { AchadinhosHero } from "@/components/achadinhos/AchadinhosHero";
import { COLLSHP_GEO_URL } from "@/lib/achadinhos";

export function HomeAchadinhos({ items }: { items: Achadinho[] }) {
  return (
    <section className="section-padding bg-elomiah-green/[0.03]">
      <div className="container-brand">
        <AchadinhosHero variant="compact" className="mb-12" />

        <AchadinhosGrid items={items.slice(0, 3)} />

        <div className="mt-12 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
          <Link href="/achadinhos" className="btn-gold">
            Ver todos os achadinhos
          </Link>
          <Link
            href={COLLSHP_GEO_URL}
            target="_blank"
            rel="noopener noreferrer"
            data-no-spray
            className="inline-flex items-center gap-2 text-sm tracking-soft text-elomiah-green underline-offset-4 transition-colors hover:text-elomiah-gold hover:underline"
          >
            Collshop da Geo
            <ExternalLink size={14} strokeWidth={1.5} />
          </Link>
        </div>
      </div>
    </section>
  );
}
