"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import Script from "next/script";
import { siteConfig } from "@/lib/site";

const STORAGE_KEY = "elomiah-cookie-consent";

type Consent = "accepted" | "rejected" | null;

export function CookieConsent() {
  const [consent, setConsent] = useState<Consent>(null);
  const [ready, setReady] = useState(false);
  const gaId = siteConfig.gaId;

  useEffect(() => {
    const stored = window.localStorage.getItem(STORAGE_KEY);
    if (stored === "accepted" || stored === "rejected") {
      setConsent(stored);
    }
    setReady(true);
  }, []);

  const choose = (value: Exclude<Consent, null>) => {
    window.localStorage.setItem(STORAGE_KEY, value);
    setConsent(value);
  };

  const loadGa = ready && Boolean(gaId) && consent === "accepted";

  return (
    <>
      {loadGa && (
        <>
          <Script
            src={`https://www.googletagmanager.com/gtag/js?id=${gaId}`}
            strategy="afterInteractive"
          />
          <Script id="ga4" strategy="afterInteractive">
            {`window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '${gaId}', { anonymize_ip: true });`}
          </Script>
        </>
      )}

      {ready && consent === null && Boolean(gaId) && (
        <div
          role="dialog"
          aria-label="Consentimento de cookies"
          className="fixed inset-x-0 bottom-0 z-[80] border-t border-elomiah-green/10 bg-elomiah-cream/95 px-5 py-5 shadow-lift backdrop-blur-md md:px-10"
        >
          <div className="container-wide flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <p className="max-w-2xl text-sm leading-relaxed text-elomiah-muted">
              Usamos cookies essenciais para o carrinho. Analytics só entra com
              o seu ok.{" "}
              <Link
                href="/privacidade"
                className="text-elomiah-green underline-offset-4 hover:underline"
              >
                Política de privacidade
              </Link>
            </p>
            <div className="flex shrink-0 flex-wrap gap-3">
              <button type="button" onClick={() => choose("rejected")} className="btn-outline">
                Só essenciais
              </button>
              <button type="button" onClick={() => choose("accepted")} className="btn-primary">
                Aceitar analytics
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
