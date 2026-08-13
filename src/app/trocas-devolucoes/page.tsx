import { Metadata } from "next";
import Link from "next/link";
import { siteConfig } from "@/lib/site";

export const metadata: Metadata = {
  title: "Trocas e devoluções",
  description: "Política de trocas e devoluções da Elomiah, conforme o CDC.",
};

export default function TrocasPage() {
  return (
    <div className="section-padding pt-32">
      <div className="container-wide max-w-3xl">
        <p className="section-label">Legal</p>
        <h1 className="heading-display mt-4 text-4xl md:text-5xl">
          Trocas e devoluções
        </h1>
        <p className="mt-8 leading-[1.85] text-elomiah-muted">
          Seguimos o Código de Defesa do Consumidor. Produtos lacrados podem ser
          devolvidos em até 7 dias corridos após o recebimento.
        </p>

        <section className="mt-12 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Como solicitar</h2>
          <p>
            Entre em contato pelo WhatsApp ou e-mail informando o número do
            pedido, o produto e o motivo. Enviaremos as instruções de envio.
          </p>
        </section>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Condições</h2>
          <p>
            O item deve estar lacrado, sem sinais de uso, com a embalagem
            original. Sprays abertos não são aceitos por questões sanitárias,
            salvo defeito de fabricação.
          </p>
        </section>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Reembolso</h2>
          <p>
            Após o recebimento e conferência, o reembolso é feito na mesma forma
            de pagamento, em até 10 dias úteis.
          </p>
        </section>

        <p className="mt-12 text-sm text-elomiah-muted">
          Contato:{" "}
          <a
            href={`mailto:${siteConfig.email}`}
            className="text-elomiah-green underline-offset-4 hover:underline"
          >
            {siteConfig.email}
          </a>
          {" · "}
          <Link href="/faq" className="underline-offset-4 hover:underline">
            FAQ
          </Link>
        </p>
      </div>
    </div>
  );
}
