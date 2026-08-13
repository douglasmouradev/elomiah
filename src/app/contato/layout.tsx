import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Contato",
  description:
    "Fale com a Elomiah pelo WhatsApp, e-mail ou Instagram. Dúvidas sobre essências, pedidos e o curso.",
  openGraph: {
    title: "Contato | Elomiah",
    description:
      "Fale com a Elomiah pelo WhatsApp, e-mail ou Instagram.",
  },
};

export default function ContatoLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return children;
}
