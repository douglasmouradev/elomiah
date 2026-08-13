import { Metadata } from "next";
import { getProducts } from "@/lib/data";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { LojaClient } from "@/components/loja/LojaClient";

export const metadata: Metadata = {
  title: "Loja",
  description: "Catálogo completo de aromatizantes e perfumes Elomiah.",
};

export const revalidate = 60;

export default async function LojaPage() {
  const products = await getProducts();

  return (
    <div className="section-padding pt-32">
      <div className="container-wide">
        <SectionHeader
          label="Catálogo"
          title="Todas as essências"
          description="Sprays de ambiente das coleções Refúgio e ELO — cada fórmula pensada para criar atmosfera e ritual."
          className="mb-14 md:mb-16"
        />
        <LojaClient products={products} />
      </div>
    </div>
  );
}
