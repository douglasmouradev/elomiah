/** Configuração central do site — URLs e contatos via env */
export const siteConfig = {
  name: "Elomiah",
  tagline: "Onde o sagrado encontra a essência.",
  url: process.env.NEXT_PUBLIC_SITE_URL || "https://elomiah.com.br",
  whatsapp: process.env.NEXT_PUBLIC_WHATSAPP || "",
  instagram: process.env.NEXT_PUBLIC_INSTAGRAM_URL || "",
  email: process.env.NEXT_PUBLIC_CONTACT_EMAIL || "contato@elomiah.com.br",
  shopee: process.env.NEXT_PUBLIC_SHOPEE_URL || "",
  amazon: process.env.NEXT_PUBLIC_AMAZON_URL || "",
  mercadolivre: process.env.NEXT_PUBLIC_MERCADOLIVRE_URL || "",
  gaId: process.env.NEXT_PUBLIC_GA_ID || "",
} as const;

export function whatsappLink(message: string): string {
  const phone = siteConfig.whatsapp.replace(/\D/g, "");
  if (!phone) return "#";
  return `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
}

export function isConfiguredWhatsApp(): boolean {
  return siteConfig.whatsapp.replace(/\D/g, "").length >= 10;
}

/** Ex.: 5571984916767 → (71) 98491-6767 */
export function formatWhatsAppDisplay(phone = siteConfig.whatsapp): string {
  const digits = phone.replace(/\D/g, "");
  const local = digits.startsWith("55") ? digits.slice(2) : digits;
  if (local.length === 11) {
    return `(${local.slice(0, 2)}) ${local.slice(2, 7)}-${local.slice(7)}`;
  }
  if (local.length === 10) {
    return `(${local.slice(0, 2)}) ${local.slice(2, 6)}-${local.slice(6)}`;
  }
  return phone;
}

export function configuredMarketplaces() {
  return (
    [
      { key: "shopee", label: "Shopee", href: siteConfig.shopee },
      { key: "amazon", label: "Amazon", href: siteConfig.amazon },
      {
        key: "mercadolivre",
        label: "Mercado Livre",
        href: siteConfig.mercadolivre,
      },
    ] as const
  ).filter((m) => Boolean(m.href));
}

export function hasConfiguredMarketplaces() {
  return configuredMarketplaces().length > 0;
}
