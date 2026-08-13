import Link from "next/link";
import { configuredMarketplaces } from "@/lib/site";
import { cn } from "@/lib/utils";

interface MarketplaceLinksProps {
  variant?: "light" | "dark";
  className?: string;
}

export function MarketplaceLinks({
  variant = "light",
  className,
}: MarketplaceLinksProps) {
  const marketplaces = configuredMarketplaces();
  if (!marketplaces.length) return null;

  return (
    <div className={cn("flex flex-wrap gap-x-6 gap-y-3", className)}>
      {marketplaces.map((m) => (
        <Link
          key={m.key}
          href={m.href}
          target="_blank"
          rel="noopener noreferrer"
          data-no-spray
          className={cn(
            "group inline-flex items-center gap-2 font-body text-[13px] font-medium tracking-soft transition-colors duration-300",
            variant === "light"
              ? "text-elomiah-green hover:text-elomiah-gold"
              : "text-elomiah-cream/70 hover:text-elomiah-gold"
          )}
        >
          <span className="border-b border-current pb-0.5 transition-all group-hover:border-elomiah-gold">
            {m.label}
          </span>
        </Link>
      ))}
    </div>
  );
}
