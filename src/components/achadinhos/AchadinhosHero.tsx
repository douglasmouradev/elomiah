import Image from "next/image";
import Link from "next/link";
import { ExternalLink } from "lucide-react";
import { ACHADINHOS_TAGLINE, COLLSHP_GEO_URL } from "@/lib/achadinhos";
import { cn } from "@/lib/utils";

interface AchadinhosHeroProps {
  variant?: "full" | "compact";
  className?: string;
}

export function AchadinhosHero({
  variant = "full",
  className,
}: AchadinhosHeroProps) {
  const compact = variant === "compact";

  return (
    <div className={cn("relative", className)}>
      <Link
        href={COLLSHP_GEO_URL}
        target="_blank"
        rel="noopener noreferrer"
        className="group relative block overflow-hidden rounded-sm shadow-soft"
        aria-label="Abrir vitrine Achadinhos da Geo na Collshop"
      >
        <Image
          src="/images/achadinhos-banner.png"
          alt="Achadinhos da Geo — curadoria pessoal na Collshop"
          width={1400}
          height={520}
          priority={!compact}
          className={cn(
            "h-auto w-full object-cover transition-transform duration-700 group-hover:scale-[1.015]",
            compact ? "max-h-[220px] md:max-h-[280px]" : "max-h-[420px] md:max-h-none"
          )}
        />
        <span className="absolute inset-0 bg-black/0 transition-colors duration-300 group-hover:bg-black/10" />
      </Link>

      <div
        className={cn(
          "flex flex-col gap-5",
          compact ? "mt-6 md:flex-row md:items-center md:justify-between" : "mt-8"
        )}
      >
        <p
          className={cn(
            "max-w-2xl leading-relaxed text-elomiah-muted",
            compact ? "text-sm md:text-base" : "text-base md:text-lg"
          )}
        >
          {ACHADINHOS_TAGLINE}
        </p>

        <Link
          href={COLLSHP_GEO_URL}
          target="_blank"
          rel="noopener noreferrer"
          data-no-spray
          className={cn(
            "btn-primary shrink-0",
            compact && "w-full md:w-auto"
          )}
        >
          Ver vitrine na Collshop
          <ExternalLink size={15} strokeWidth={1.5} />
        </Link>
      </div>
    </div>
  );
}
