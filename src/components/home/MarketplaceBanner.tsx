import { MarketplaceLinks } from "@/components/ui/MarketplaceLinks";
import { hasConfiguredMarketplaces } from "@/lib/site";

export function MarketplaceBanner() {
  if (!hasConfiguredMarketplaces()) return null;

  return (
    <section className="section-padding">
      <div className="container-wide">
        <div className="relative overflow-hidden bg-elomiah-green px-8 py-16 md:px-16 md:py-24">
          <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-elomiah-gold/10 blur-3xl" aria-hidden />
          <div className="relative grid gap-10 md:grid-cols-2 md:items-center md:gap-16">
            <div>
              <p className="section-label text-elomiah-gold/90">
                Marketplaces
              </p>
              <h2 className="heading-display mt-6 text-3xl text-elomiah-cream md:text-4xl lg:text-[2.75rem]">
                Encontre a Elomiah onde você já compra
              </h2>
            </div>
            <div>
              <p className="text-base leading-relaxed text-elomiah-cream/60 md:text-[17px]">
                Shopee, Amazon e Mercado Livre — a mesma essência, o mesmo
                cuidado, no marketplace da sua preferência.
              </p>
              <div className="mt-8">
                <MarketplaceLinks variant="dark" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
