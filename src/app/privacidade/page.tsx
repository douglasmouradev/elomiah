import { Metadata } from "next";
import { siteConfig } from "@/lib/site";

export const metadata: Metadata = {
  title: "Política de Privacidade",
  description: "Como a Elomiah trata seus dados pessoais.",
};

export default function PrivacidadePage() {
  return (
    <div className="section-padding pt-32">
      <div className="container-brand max-w-3xl">
        <h1 className="font-display text-4xl text-elomiah-green">
          Política de Privacidade
        </h1>
        <p className="mt-6 leading-relaxed text-elomiah-muted">
          A {siteConfig.name} respeita sua privacidade. Esta política descreve
          como coletamos e usamos informações quando você visita nosso site ou
          entra em contato conosco.
        </p>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">
            Dados coletados
          </h2>
          <p>
            Podemos coletar nome, e-mail e mensagem quando você utiliza o
            formulário de contato. O carrinho usa armazenamento local no seu
            navegador (cookie essencial). Analytics (Google Analytics 4) só é
            carregado se você aceitar no banner de cookies.
          </p>
        </section>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Uso dos dados</h2>
          <p>
            Utilizamos seus dados para responder solicitações, processar pedidos
            via WhatsApp e melhorar a experiência no site. Não vendemos seus
            dados a terceiros.
          </p>
        </section>

        <section className="mt-10 space-y-4 text-elomiah-muted">
          <h2 className="font-display text-2xl text-elomiah-green">Seus direitos (LGPD)</h2>
          <p>
            Você pode solicitar acesso, correção ou exclusão dos seus dados
            entrando em contato:{" "}
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
