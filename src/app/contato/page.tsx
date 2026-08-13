"use client";

import { FormEvent, useState } from "react";
import { Instagram, Mail, MessageCircle } from "lucide-react";
import { MarketplaceLinks } from "@/components/ui/MarketplaceLinks";
import { SectionHeader } from "@/components/ui/SectionHeader";
import { siteConfig, whatsappLink, isConfiguredWhatsApp, formatWhatsAppDisplay, hasConfiguredMarketplaces } from "@/lib/site";

export default function ContatoPage() {
  const [sent, setSent] = useState(false);

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const name = String(form.get("name") || "");
    const email = String(form.get("email") || "");
    const message = String(form.get("message") || "");
    const text = `Olá, sou ${name} (${email}).\n\n${message}`;
    if (isConfiguredWhatsApp()) {
      window.open(whatsappLink(text), "_blank", "noopener,noreferrer");
    }
    setSent(true);
  };

  return (
    <div className="section-padding pt-32">
      <div className="container-wide grid gap-16 lg:grid-cols-12 lg:gap-20">
        <div className="lg:col-span-5">
          <SectionHeader
            label="Contato"
            title="Vamos conversar"
            description="Dúvidas sobre essências, pedidos ou o curso — estamos à escuta. WhatsApp é o canal mais direto."
          />

          <div className="mt-12 space-y-5">
            {isConfiguredWhatsApp() && (
              <a
                href={whatsappLink("Olá! Gostaria de falar com a Elomiah.")}
                target="_blank"
                rel="noopener noreferrer"
                data-no-spray
                className="flex items-center gap-4 text-elomiah-green transition-colors duration-300 hover:text-elomiah-gold"
              >
                <MessageCircle size={18} strokeWidth={1.25} />
                <span className="text-sm">
                  WhatsApp · {formatWhatsAppDisplay()}
                </span>
              </a>
            )}
            <a
              href={`mailto:${siteConfig.email}`}
              className="flex items-center gap-4 text-elomiah-green transition-colors duration-300 hover:text-elomiah-gold"
            >
              <Mail size={18} strokeWidth={1.25} />
              <span className="text-sm">{siteConfig.email}</span>
            </a>
            {siteConfig.instagram && (
              <a
                href={siteConfig.instagram}
                target="_blank"
                rel="noopener noreferrer"
                data-no-spray
                className="flex items-center gap-4 text-elomiah-green transition-colors duration-300 hover:text-elomiah-gold"
              >
                <Instagram size={18} strokeWidth={1.25} />
                <span className="text-sm">Instagram</span>
              </a>
            )}
          </div>

          {hasConfiguredMarketplaces() && (
            <div className="mt-14">
              <p className="section-label mb-4">Marketplaces</p>
              <MarketplaceLinks />
            </div>
          )}
        </div>

        <div className="surface-panel p-8 md:p-12 lg:col-span-7">
          <form onSubmit={onSubmit} className="space-y-8">
            <div>
              <label className="section-label">Nome</label>
              <input name="name" required className="input-brand mt-2" />
            </div>
            <div>
              <label className="section-label">E-mail</label>
              <input name="email" type="email" required className="input-brand mt-2" />
            </div>
            <div>
              <label className="section-label">Mensagem</label>
              <textarea
                name="message"
                required
                rows={5}
                className="mt-2 w-full resize-none border-0 border-b border-elomiah-green/15 bg-transparent py-3 text-sm outline-none focus:border-elomiah-gold"
              />
            </div>
            <button type="submit" className="btn-primary">
              Enviar pelo WhatsApp
            </button>
            {sent && (
              <p className="text-sm text-elomiah-muted">
                Abrimos o WhatsApp com sua mensagem. Se não abriu, verifique o
                bloqueador de pop-ups.
              </p>
            )}
          </form>
        </div>
      </div>
    </div>
  );
}
