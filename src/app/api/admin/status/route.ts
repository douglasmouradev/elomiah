import { NextResponse } from "next/server";
import { promises as fs } from "fs";
import path from "path";
import { isAuthenticated, isAdminConfigured } from "@/lib/auth";
import { getAchadinhos, getTestimonials, getWaitlist } from "@/lib/data";
import { isSupabaseEnabled } from "@/lib/supabase";
import {
  hasConfiguredMarketplaces,
  configuredMarketplaces,
  siteConfig,
} from "@/lib/site";

async function hasGeoPhoto() {
  const dir = path.join(process.cwd(), "public", "images");
  for (const file of ["geo.jpg", "geo.jpeg", "geo.png", "geo.webp"]) {
    try {
      await fs.access(path.join(dir, file));
      return true;
    } catch {
      /* continue */
    }
  }
  return false;
}

/** Status de setup para o admin — sem expor segredos */
export async function GET() {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const [geo, achadinhos, testimonials, waitlist] = await Promise.all([
    hasGeoPhoto(),
    getAchadinhos(),
    getTestimonials(),
    getWaitlist(),
  ]);

  const achadinhosWithImage = achadinhos.filter((a) => Boolean(a.image?.trim()));
  const testimonialsWithPhoto = testimonials.filter((t) =>
    Boolean(t.photo?.trim())
  );
  const siteUrlOk =
    Boolean(siteConfig.url) &&
    !siteConfig.url.includes("localhost") &&
    siteConfig.url.startsWith("http");

  return NextResponse.json({
    supabase: isSupabaseEnabled(),
    whatsapp: Boolean(siteConfig.whatsapp),
    ga: Boolean(siteConfig.gaId),
    marketplaces: configuredMarketplaces().map((m) => m.key),
    hasMarketplaces: hasConfiguredMarketplaces(),
    siteUrl: siteConfig.url,
    waitlistCount: waitlist.length,
    checklist: [
      {
        id: "whatsapp",
        ok: Boolean(siteConfig.whatsapp),
        label: "WhatsApp configurado",
      },
      {
        id: "site-url",
        ok: siteUrlOk,
        label: "SITE_URL pública (não localhost)",
      },
      {
        id: "supabase",
        ok: isSupabaseEnabled(),
        label: "Supabase (produtos, vendas, waitlist, uploads)",
      },
      {
        id: "admin",
        ok: isAdminConfigured(),
        label: "ADMIN_PASSWORD e ADMIN_SECRET",
      },
      {
        id: "geo",
        ok: geo,
        label: "Foto da Geo em public/images/geo.jpg",
      },
      {
        id: "achadinhos",
        ok: achadinhos.length > 0 && achadinhosWithImage.length === achadinhos.length,
        label: `Achadinhos com imagem (${achadinhosWithImage.length}/${achadinhos.length})`,
      },
      {
        id: "depoimentos",
        ok:
          testimonials.length > 0 &&
          testimonialsWithPhoto.length >= Math.min(3, testimonials.length),
        label: `Depoimentos com foto (${testimonialsWithPhoto.length}/${testimonials.length})`,
      },
      {
        id: "marketplaces",
        ok: hasConfiguredMarketplaces(),
        label: "URLs Shopee / Amazon / Mercado Livre",
      },
      {
        id: "ga",
        ok: Boolean(siteConfig.gaId),
        label: "GA4 (NEXT_PUBLIC_GA_ID)",
      },
      {
        id: "waitlist-store",
        ok: isSupabaseEnabled() || process.env.NODE_ENV !== "production",
        label: "Waitlist persistente (Supabase em produção)",
      },
    ],
  });
}
