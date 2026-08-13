import { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { whatsappLink } from "@/lib/site";
import { getTestimonials } from "@/lib/data";
import { InitialsAvatar } from "@/components/ui/InitialsAvatar";

export const metadata: Metadata = {
  title: "Curso de Aulas",
  description:
    "Curso online de aromaterapia e perfumaria artesanal com a Elomiah.",
};

const modules = [
  {
    n: "01",
    title: "Fundamentos do olfato",
    desc: "Pirâmide olfativa, famílias aromáticas e como ler uma essência.",
    hours: "Aula 1",
  },
  {
    n: "02",
    title: "Ritual e intenção",
    desc: "Como criar atmosferas sagradas no cotidiano com aroma e gesto.",
    hours: "Aula 2",
  },
  {
    n: "03",
    title: "Composição artesanal",
    desc: "Bases, diluições e primeiros passos para criar sua própria fórmula.",
    hours: "Aula 3",
  },
  {
    n: "04",
    title: "Difusão e ambiente",
    desc: "Sprays, difusores e técnicas para espaços íntimos e acolhedores.",
    hours: "Aula 4",
  },
];

const includes = [
  "4 módulos em vídeo, no seu ritmo",
  "Acesso por 12 meses",
  "Lista de materiais opcionais",
  "Comunidade e suporte via WhatsApp",
];

const faqs = [
  {
    q: "Para quem é o curso?",
    a: "Para quem deseja aprofundar o universo olfativo — iniciantes e entusiastas de aromaterapia e perfumaria artesanal.",
  },
  {
    q: "As aulas ficam disponíveis?",
    a: "Sim. Após a matrícula, o conteúdo permanece disponível por 12 meses para você estudar no seu ritmo.",
  },
  {
    q: "Preciso de materiais?",
    a: "Uma lista de materiais opcionais é enviada no módulo 3. Nada é obrigatório para acompanhar a teoria.",
  },
];

const enrollHref = whatsappLink(
  "Olá! Gostaria de me matricular no curso O Ritual da Essência."
);

export default async function CursoPage() {
  const testimonials = await getTestimonials();
  const alumni = testimonials.filter((t) =>
    (t.role || "").toLowerCase().includes("aluna")
  );
  const quotes = (alumni.length ? alumni : testimonials).slice(0, 2);

  return (
    <div className="pt-28">
      <section className="section-padding relative overflow-hidden pb-8">
        <div className="container-wide grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
          <div className="lg:col-span-6">
            <SectionHeader
              label="Formação online"
              title="O ritual da essência"
              description="Aromaterapia e perfumaria artesanal para quem deseja criar atmosferas com consciência — do primeiro aroma ao altar doméstico."
            />
            <div className="mt-10 flex flex-wrap items-end gap-8">
              <div>
                <p className="font-display text-4xl text-elomiah-green md:text-5xl">
                  R$ 497
                </p>
                <p className="mt-1 text-sm text-elomiah-muted">
                  ou 6× de R$ 89,90 sem juros
                </p>
              </div>
              <a
                href={enrollHref}
                target="_blank"
                rel="noopener noreferrer"
                className="btn-primary"
              >
                Solicitar matrícula
              </a>
            </div>
          </div>

          <div className="relative aspect-[4/5] overflow-hidden bg-elomiah-surface lg:col-span-5 lg:col-start-8">
            <Image
              src="/images/produtos/colecao-refugio-hero.jpg"
              alt="Universo olfativo Elomiah — referência visual do curso"
              fill
              priority
              sizes="(max-width: 1024px) 100vw, 40vw"
              className="object-cover"
            />
          </div>
        </div>
      </section>

      <section className="section-padding pt-8">
        <div className="container-wide grid gap-12 md:grid-cols-2">
          <div className="border-t border-elomiah-green/[0.08] pt-8">
            <p className="section-label">Para quem é</p>
            <p className="mt-5 font-display text-2xl leading-snug text-elomiah-green md:text-3xl">
              Quem quer transformar o cotidiano em ritual — sem jargão, com
              prática.
            </p>
            <p className="mt-4 text-[15px] leading-relaxed text-elomiah-muted">
              Iniciantes, curiosas pelo olfato e quem já usa sprays Elomiah e
              deseja ir além da borrifada.
            </p>
          </div>
          <div className="border-t border-elomiah-green/[0.08] pt-8">
            <p className="section-label">O que está incluso</p>
            <ul className="mt-5 space-y-3">
              {includes.map((item) => (
                <li
                  key={item}
                  className="flex gap-3 text-[15px] leading-relaxed text-elomiah-muted"
                >
                  <span
                    className="mt-2 h-px w-6 shrink-0 bg-elomiah-gold/70"
                    aria-hidden
                  />
                  {item}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </section>

      <section className="section-padding surface-panel">
        <div className="container-wide">
          <h2 className="heading-display text-3xl md:text-4xl">Módulos</h2>
          <div className="gold-rule mt-6" />
          <div className="mt-14 grid gap-6 md:grid-cols-2 md:gap-8">
            {modules.map((m) => (
              <article
                key={m.n}
                className="border-t border-elomiah-green/[0.08] bg-elomiah-cream/80 p-8 md:p-10"
              >
                <div className="flex items-baseline justify-between gap-4">
                  <p className="font-display text-4xl text-elomiah-gold/50">
                    {m.n}
                  </p>
                  <p className="section-label">{m.hours}</p>
                </div>
                <h3 className="mt-4 font-display text-2xl text-elomiah-green">
                  {m.title}
                </h3>
                <p className="mt-4 text-[15px] leading-relaxed text-elomiah-muted">
                  {m.desc}
                </p>
              </article>
            ))}
          </div>
        </div>
      </section>

      {quotes.length > 0 && (
        <section className="section-padding">
          <div className="container-wide">
            <h2 className="heading-display text-3xl md:text-4xl">
              Palavras de alunas
            </h2>
            <div className="gold-rule mt-6" />
            <div className="mt-12 grid gap-10 md:grid-cols-2">
              {quotes.map((t) => (
                <article
                  key={t.id}
                  className="border-t border-elomiah-green/[0.08] pt-8"
                >
                  <div className="mb-5 flex items-center gap-3">
                    <div className="relative h-11 w-11 overflow-hidden">
                      {t.photo ? (
                        <Image
                          src={t.photo}
                          alt={t.name}
                          fill
                          className="object-cover"
                          sizes="44px"
                        />
                      ) : (
                        <InitialsAvatar
                          name={t.name}
                          className="h-full w-full text-sm"
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
                  <blockquote className="font-display text-xl leading-[1.5] text-elomiah-green md:text-2xl">
                    &ldquo;{t.text}&rdquo;
                  </blockquote>
                </article>
              ))}
            </div>
            <div className="mt-10">
              <Link href="/depoimentos" className="btn-outline">
                Ver mais depoimentos
              </Link>
            </div>
          </div>
        </section>
      )}

      <section className="section-padding bg-elomiah-green text-elomiah-cream">
        <div className="container-wide max-w-3xl">
          <h2 className="heading-display text-3xl text-elomiah-cream md:text-4xl">
            Perguntas frequentes
          </h2>
          <div className="mt-10 space-y-8">
            {faqs.map((f) => (
              <div key={f.q} className="border-t border-elomiah-cream/10 pt-8">
                <h3 className="font-display text-xl text-elomiah-gold">{f.q}</h3>
                <p className="mt-3 text-[15px] leading-relaxed text-elomiah-cream/65">
                  {f.a}
                </p>
              </div>
            ))}
          </div>
          <a
            href={enrollHref}
            target="_blank"
            rel="noopener noreferrer"
            className="btn-gold mt-12"
          >
            Quero me matricular
          </a>
        </div>
      </section>
    </div>
  );
}
