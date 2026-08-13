import { Metadata } from "next";
import { getAchadinhos, getFeaturedProducts, getTestimonials } from "@/lib/data";
import { HomeOpening } from "@/components/home/HomeOpening";
import { Hero } from "@/components/home/Hero";
import { ColecaoEloBanner } from "@/components/home/ColecaoEloBanner";
import { FeaturedProducts } from "@/components/home/FeaturedProducts";
import { HomeTestimonials } from "@/components/home/HomeTestimonials";
import { HomeAchadinhos } from "@/components/home/HomeAchadinhos";
import { MarketplaceBanner } from "@/components/home/MarketplaceBanner";
import { HomeScrollEffects } from "@/components/effects/HomeScrollEffects";

export const metadata: Metadata = {
  title: "Início",
  description:
    "Elomiah — refúgio de aromatizantes e perfumes. Coleções Refúgio e ELO, curadoria Achadinhos da Geo.",
};

export const revalidate = 60;

export default async function HomePage() {
  const [featured, testimonials, achadinhos] = await Promise.all([
    getFeaturedProducts(),
    getTestimonials(),
    getAchadinhos(),
  ]);

  return (
    <>
      <HomeScrollEffects />
      <HomeOpening />
      <Hero />
      <FeaturedProducts products={featured} />
      <div data-fade-scroll>
        <ColecaoEloBanner />
      </div>
      <div data-fade-scroll>
        <HomeTestimonials testimonials={testimonials} />
      </div>
      <HomeAchadinhos items={achadinhos} />
      <MarketplaceBanner />
    </>
  );
}
