import type { MetadataRoute } from "next";
import { getProducts } from "@/lib/data";
import { siteConfig } from "@/lib/site";

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const base = siteConfig.url.replace(/\/$/, "");
  const products = await getProducts();

  const staticPages = [
    "",
    "/loja",
    "/curso",
    "/achadinhos",
    "/sobre",
    "/depoimentos",
    "/contato",
    "/faq",
    "/privacidade",
    "/termos",
    "/trocas-devolucoes",
  ].map((path) => ({
    url: `${base}${path}`,
    lastModified: new Date(),
    changeFrequency: "weekly" as const,
    priority: path === "" ? 1 : 0.8,
  }));

  const productPages = products.map((p) => ({
    url: `${base}/produtos/${p.slug}`,
    lastModified: new Date(p.updatedAt),
    changeFrequency: "weekly" as const,
    priority: 0.7,
  }));

  return [...staticPages, ...productPages];
}
