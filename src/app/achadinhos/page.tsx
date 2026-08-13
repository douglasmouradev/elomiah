import { Metadata } from "next";
import { getAchadinhos } from "@/lib/data";
import { AchadinhosGrid } from "@/components/ui/AchadinhosGrid";
import { AchadinhosHero } from "@/components/achadinhos/AchadinhosHero";
import { SectionHeader } from "@/components/ui/SectionHeader";

export const metadata: Metadata = {
  title: "Achadinhos da Geo",
  description:
    "Curadoria pessoal da Geo na Collshop — produtos que ela realmente usa e indica.",
};

export default async function AchadinhosPage() {
  const items = await getAchadinhos();

  return (
    <div className="section-padding pt-28 md:pt-32">
      <div className="container-wide">
        <AchadinhosHero />

        <div className="mt-20 border-t border-elomiah-green/[0.08] pt-16">
          <SectionHeader
            label="Curadoria"
            title="Destaques da Geo"
            className="mb-12"
          />
          <AchadinhosGrid items={items} />
        </div>
      </div>
    </div>
  );
}
