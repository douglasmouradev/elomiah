import type { Metadata } from "next";
import { Cormorant_Garamond, DM_Sans } from "next/font/google";
import "./globals.css";
import { Header } from "@/components/layout/Header";
import { Footer } from "@/components/layout/Footer";
import { CartDrawer } from "@/components/layout/CartDrawer";
import { CartSync } from "@/components/layout/CartSync";
import { SprayTransitionProvider } from "@/components/transitions";
import { CookieConsent } from "@/components/layout/CookieConsent";
import { siteConfig } from "@/lib/site";

const cormorant = Cormorant_Garamond({
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
  variable: "--font-cormorant",
  display: "swap",
});

const dmSans = DM_Sans({
  subsets: ["latin"],
  weight: ["400", "500", "600"],
  variable: "--font-dm-sans",
  display: "swap",
});

export const metadata: Metadata = {
  metadataBase: new URL(siteConfig.url),
  title: {
    default: "Elomiah — Onde o sagrado encontra a essência",
    template: "%s | Elomiah",
  },
  description:
    "Refúgio de aromatizantes e perfumes de alto padrão. Elomiah: onde o sagrado encontra a essência.",
  openGraph: {
    title: "Elomiah",
    description: "Onde o sagrado encontra a essência.",
    locale: "pt_BR",
    type: "website",
    siteName: "Elomiah",
    images: [
      {
        url: "/images/produtos/colecao-refugio-hero.jpg",
        width: 1740,
        height: 1220,
        alt: "Elomiah — Coleção Refúgio",
      },
    ],
  },
  twitter: {
    card: "summary_large_image",
    title: "Elomiah",
    description: "Onde o sagrado encontra a essência.",
    images: ["/images/produtos/colecao-refugio-hero.jpg"],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pt-BR">
      <body
        className={`${cormorant.variable} ${dmSans.variable} linen-bg antialiased`}
      >
        <SprayTransitionProvider>
          <a
            href="#conteudo"
            className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[90] focus:bg-elomiah-green focus:px-4 focus:py-2 focus:text-elomiah-cream"
          >
            Pular para o conteúdo
          </a>
          <CartSync />
          <Header />
          <main id="conteudo" className="min-h-screen">
            {children}
          </main>
          <Footer />
          <CartDrawer />
          <CookieConsent />
        </SprayTransitionProvider>
      </body>
    </html>
  );
}
