import { Metadata } from "next";
import Image from "next/image";
import { getTestimonials } from "@/lib/data";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { InitialsAvatar } from "@/components/ui/InitialsAvatar";

export const metadata: Metadata = {
  title: "Depoimentos",
  description: "Histórias de quem vive o refúgio Elomiah.",
};

export default async function DepoimentosPage() {
  const testimonials = await getTestimonials();

  return (
    <div className="section-padding pt-32">
      <div className="container-wide">
        <SectionHeader
          label="Depoimentos"
          title="Ecos do refúgio"
          className="mb-16 md:mb-20"
        />

        <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
          {testimonials.map((t) => (
            <article key={t.id} className="surface-panel flex flex-col p-8">
              <div className="mb-6 flex items-center gap-4">
                <div className="relative h-12 w-12 shrink-0 overflow-hidden">
                  {t.photo ? (
                    <Image
                      src={t.photo}
                      alt={t.name}
                      fill
                      className="object-cover grayscale-[15%]"
                      sizes="48px"
                    />
                  ) : (
                    <InitialsAvatar
                      name={t.name}
                      className="h-full w-full text-base"
                    />
                  )}
                </div>
                <div>
                  <p className="font-display text-lg text-elomiah-green">
                    {t.name}
                  </p>
                  {t.role && (
                    <p className="text-xs text-elomiah-muted">{t.role}</p>
                  )}
                </div>
              </div>
              <p className="flex-1 text-sm leading-[1.75] text-elomiah-muted">
                &ldquo;{t.text}&rdquo;
              </p>
            </article>
          ))}
        </div>
      </div>
    </div>
  );
}
