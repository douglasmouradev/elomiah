import { Metadata } from "next";
import { siteConfig } from "@/lib/site";

export const metadata: Metadata = {
  title: "Termos de uso",
  description: "Condições de uso do site e das compras Elomiah.",
};

export default function TermosPage() {
  return (
    <div className="section-padding pt-32">
      <div className="container-wide max-w-3xl">
        <p className="section-label">Legal</p>
        <h1 className="heading-display mt-4 text-4xl md:text-5xl">
          Termos de uso
        </h1>
        <p className="mt-8 leading-[1.85] text-elomiah-muted">
          Ao acessar o site da {siteConfig.name}, você concorda com estes termos.
          Pedidos são confirmados via WhatsApp após a seleção no carrinho.
        </p>

        <section className="mt-12 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Pedidos</h2>
          <p>
            O carrinho no site é um pré-pedido. A venda só se efetiva quando
            nossa equipe confirma disponibilidade, frete e forma de pagamento
            pelo WhatsApp. Preços e estoque podem variar até essa confirmação.
          </p>
        </section>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">
            Conteúdo do site
          </h2>
          <p>
            Textos, imagens e identidade visual da Elomiah são protegidos.
            É vedada a reprodução comercial sem autorização.
          </p>
        </section>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Contato</h2>
          <p>
            Dúvidas:{" "}
            <a
              href={`mailto:${siteConfig.email}`}
              className="text-elomiah-green underline-offset-4 hover:underline"
            >
              {siteConfig.email}
            </a>
          </p>
        </section>

        <p className="mt-12 text-sm text-elomiah-muted/80">
          Última atualização: agosto de 2026.
        </p>
      </div>
    </div>
  );
}
