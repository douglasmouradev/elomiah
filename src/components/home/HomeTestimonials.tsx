import Link from "next/link";
import { Testimonial } from "@/lib/types";
import { TestimonialCarousel } from "@/components/ui/TestimonialCarousel";
import { SectionHeader } from "@/components/ui/SectionHeader";

export function HomeTestimonials({
  testimonials,
}: {
  testimonials: Testimonial[];
}) {
  return (
    <section className="section-padding surface-panel">
      <div className="container-wide">
        <SectionHeader
          index="02"
          label="Depoimentos"
          title="Quem vive o refúgio"
          align="center"
          className="mx-auto mb-14 md:mb-20"
        />
        <TestimonialCarousel testimonials={testimonials} />
        <div className="mt-14 text-center">
          <Link href="/depoimentos" className="btn-outline">
            Ler mais histórias
          </Link>
        </div>
      </div>
    </section>
  );
}
