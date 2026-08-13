import { Metadata } from "next";
import Link from "next/link";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { whatsappLink, isConfiguredWhatsApp } from "@/lib/site";

export const metadata: Metadata = {
  title: "Perguntas Frequentes",
  description: "Dúvidas sobre sprays Elomiah, pedidos, frete e cuidados.",
};

const faqs = [
  {
    q: "Como usar os sprays de ambiente?",
    a: "Borrife no ar, a cerca de 30 cm de distância das superfícies. Evite aplicar diretamente em tecidos delicados, pele, alimentos ou animais.",
  },
  {
    q: "Qual a duração do aroma?",
    a: "A permanência varia conforme ventilação e tamanho do ambiente. Em média, o rastro olfativo dura de 2 a 4 horas.",
  },
  {
    q: "Como finalizo minha compra?",
    a: "Adicione os produtos ao carrinho, informe nome e CEP e finalize pelo WhatsApp. Confirmamos disponibilidade, frete e pagamento por lá.",
  },
  {
    q: "Vocês entregam para todo o Brasil?",
    a: "Sim. O prazo e valor do frete são informados no WhatsApp após confirmação do pedido.",
  },
  {
    q: "Posso trocar ou devolver?",
    a: "Produtos lacrados podem ser trocados em até 7 dias conforme o CDC. Veja a página de Trocas e devoluções.",
  },
];

export default function FaqPage() {
  return (
    <div className="section-padding pt-32">
      <div className="container-wide max-w-3xl">
        <SectionHeader
          label="Ajuda"
          title="Perguntas frequentes"
          className="mb-14"
        />

        <dl className="space-y-0">
          {faqs.map((item) => (
            <div
              key={item.q}
              className="border-t border-elomiah-green/[0.08] py-8 first:border-t-0 first:pt-0"
            >
              <dt className="font-display text-xl text-elomiah-green md:text-2xl">
                {item.q}
              </dt>
              <dd className="mt-4 leading-[1.75] text-elomiah-muted">
                {item.q === "Posso trocar ou devolver?" ? (
                  <>
                    Produtos lacrados podem ser trocados em até 7 dias conforme o
                    CDC. Veja{" "}
                    <Link
                      href="/trocas-devolucoes"
                      className="text-elomiah-green underline-offset-4 hover:underline"
                    >
                      Trocas e devoluções
                    </Link>
                    .
                  </>
                ) : (
                  item.a
                )}
              </dd>
            </div>
          ))}
        </dl>

        {isConfiguredWhatsApp() && (
          <div className="mt-14 surface-panel p-8 md:p-10">
            <p className="text-elomiah-muted">
              Ainda com dúvidas? Fale conosco pelo WhatsApp.
            </p>
            <a
              href={whatsappLink("Olá! Tenho uma dúvida sobre os produtos Elomiah.")}
              target="_blank"
              rel="noopener noreferrer"
              data-no-spray
              className="btn-primary mt-6 inline-flex"
            >
              Chamar no WhatsApp
            </a>
          </div>
        )}

        <Link href="/loja" className="btn-link mt-12">
          Explorar a loja
        </Link>
      </div>
    </div>
  );
}
