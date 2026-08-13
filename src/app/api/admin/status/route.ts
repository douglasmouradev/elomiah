import { NextResponse } from "next/server";
import { promises as fs } from "fs";
import path from "path";
import { isAuthenticated } from "@/lib/auth";
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

  const geo = await hasGeoPhoto();

  return NextResponse.json({
    supabase: isSupabaseEnabled(),
    whatsapp: Boolean(siteConfig.whatsapp),
    ga: Boolean(siteConfig.gaId),
    marketplaces: configuredMarketplaces().map((m) => m.key),
    hasMarketplaces: hasConfiguredMarketplaces(),
    siteUrl: siteConfig.url,
    checklist: [
      {
        id: "whatsapp",
        ok: Boolean(siteConfig.whatsapp),
        label: "WhatsApp configurado",
      },
      {
        id: "supabase",
        ok: isSupabaseEnabled(),
        label: "Supabase (persistência em produção)",
      },
      {
        id: "admin",
        ok: Boolean(process.env.ADMIN_PASSWORD && process.env.ADMIN_SECRET),
        label: "ADMIN_PASSWORD e ADMIN_SECRET",
      },
      {
        id: "geo",
        ok: geo,
        label: "Foto da Geo em public/images/geo.jpg",
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
    ],
  });
}
