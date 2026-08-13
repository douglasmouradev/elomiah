import { Metadata } from "next";
import Image from "next/image";
import { promises as fs } from "fs";
import path from "path";
import { SectionHeader } from "@/components/ui/SectionHeader";

export const metadata: Metadata = {
  title: "Sobre",
  description: "A história da Elomiah e da Geo, fundadora do refúgio.",
};

const GEO_CANDIDATES = ["geo.jpg", "geo.jpeg", "geo.png", "geo.webp"] as const;

async function resolveGeoImage(): Promise<{
  src: string;
  alt: string;
  caption: string;
}> {
  const dir = path.join(process.cwd(), "public", "images");
  for (const file of GEO_CANDIDATES) {
    try {
      await fs.access(path.join(dir, file));
      return {
        src: `/images/${file}`,
        alt: "Geo, fundadora da Elomiah",
        caption: "Geo — fundadora da Elomiah.",
      };
    } catch {
      /* next candidate */
    }
  }
  return {
    src: "/images/produtos/colecao-refugio-hero.jpg",
    alt: "Coleção Refúgio Elomiah",
    caption:
      "Coleção Refúgio. Coloque a foto da Geo em public/images/geo.jpg",
  };
}

export default async function SobrePage() {
  const geo = await resolveGeoImage();

  return (
    <div className="pt-28">
      <section className="section-padding">
        <div className="container-wide grid items-start gap-16 lg:grid-cols-12 lg:gap-20">
          <div className="lg:col-span-5 lg:sticky lg:top-32">
            <div className="relative aspect-[4/5] overflow-hidden bg-elomiah-surface">
              <Image
                src={geo.src}
                alt={geo.alt}
                fill
                className="object-cover object-[center_40%]"
                sizes="(max-width: 1024px) 100vw, 40vw"
                priority
              />
            </div>
            <p className="mt-4 font-display text-sm italic text-elomiah-muted">
              {geo.caption}
            </p>
          </div>
          <div className="lg:col-span-6 lg:col-start-7">
            <SectionHeader label="Sobre" title="Geo & Elomiah" />
            <div className="mt-10 space-y-6 text-base leading-[1.85] text-elomiah-muted md:text-[17px]">
              <p>
                A Elomiah nasceu de uma escuta íntima: a de Geo, que encontrou
                no aroma uma linguagem para o sagrado cotidiano. Entre
                experimentações, estudos olfativos e o desejo de criar refúgio
                para outras pessoas, a marca ganhou forma — verde profundo,
                dourado discreto, essências que pedem presença.
              </p>
              <p>
                Não se trata de moda aromática. Trata-se de ritual: a borrifada
                que abre o dia, o difusor que acolhe a noite, o perfume que
                carrega memória na pele. Cada fórmula é pensada como um convite
                à quietude.
              </p>
              <p>
                Hoje, a Elomiah habita a casa, os marketplaces e o curso online —
                sempre com a mesma intenção: onde o sagrado encontra a essência.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section className="section-padding bg-elomiah-green text-elomiah-cream">
        <div className="container-wide">
          <h2 className="heading-display text-3xl text-elomiah-cream md:text-4xl">
            Valores que sustentam
          </h2>
          <div className="gold-rule mt-6 bg-elomiah-gold/60" />
          <div className="mt-14 grid gap-12 md:grid-cols-3 md:gap-10">
            {[
              {
                t: "Presença",
                d: "Cada aroma é um chamado ao agora — sem pressa, sem excesso.",
              },
              {
                t: "Cuidado",
                d: "Ingredientes selecionados, fórmulas honestas, embalagens com intenção.",
              },
              {
                t: "Refúgio",
                d: "Criar espaços olfativos onde o corpo e a alma possam descansar.",
              },
            ].map((v) => (
              <div key={v.t} className="border-t border-elomiah-cream/10 pt-8">
                <h3 className="font-display text-2xl text-elomiah-gold">{v.t}</h3>
                <p className="mt-4 text-[15px] leading-relaxed text-elomiah-cream/65">
                  {v.d}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
