import Image from "next/image";
import Link from "next/link";

export function ColecaoEloBanner() {
  return (
    <section className="px-5 py-16 md:px-12 lg:px-20">
      <div className="container-wide">
        <Link
          href="/produtos/colecao-elo"
          className="group grid overflow-hidden bg-elomiah-surface md:grid-cols-2"
        >
          <div className="relative aspect-[4/3] md:aspect-auto">
            <Image
              src="/images/produtos/thumbs/colecao-elo.jpg"
              alt="Coleção ELO — sprays infantis Elomiah"
              fill
              sizes="(max-width: 768px) 100vw, 50vw"
              quality={88}
              className="object-contain object-center p-8 transition-transform duration-700 ease-premium group-hover:scale-[1.02]"
            />
          </div>
          <div className="flex flex-col justify-center px-8 py-10 md:px-12">
            <p className="section-label">Coleção ELO</p>
            <h2 className="heading-display mt-4 text-3xl md:text-4xl">
              Delicadeza para os pequenos
            </h2>
            <p className="mt-4 max-w-sm text-sm leading-relaxed text-elomiah-muted md:text-base">
              Aromas suaves para perfumar ambientes infantis com acolhimento.
            </p>
            <span className="btn-link mt-8">Conhecer ELO</span>
          </div>
        </Link>
      </div>
    </section>
  );
}
