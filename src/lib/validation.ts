import { z } from "zod";

export const productSchema = z.object({
  name: z.string().min(1).max(120).optional(),
  slug: z.string().max(120).optional(),
  aroma: z.string().max(80).optional(),
  collection: z.string().max(80).optional(),
  accentColor: z.string().max(20).optional(),
  quote: z.string().max(300).optional(),
  category: z
    .enum(["spray-ambiente", "perfume", "difusor", "kits"])
    .optional(),
  price: z.number().min(0).optional(),
  stock: z.number().int().min(0).optional(),
  description: z.string().max(5000).optional(),
  shortDescription: z.string().max(300).optional(),
  notes: z
    .object({ top: z.string(), heart: z.string(), base: z.string() })
    .optional(),
  usage: z.string().max(2000).optional(),
  precautions: z.string().max(2000).optional(),
  technical: z.string().max(500).optional(),
  images: z.array(z.string()).optional(),
  benefits: z.array(z.string().max(80)).max(8).optional(),
  featured: z.boolean().optional(),
  marketplace: z
    .enum(["shopee", "amazon", "mercadolivre", "interno"])
    .optional(),
  marketplaceUrl: z.string().optional(),
});

export const ALLOWED_UPLOAD_TYPES = [
  "image/jpeg",
  "image/png",
  "image/webp",
  "image/gif",
] as const;

export const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;

export const achadinhoSchema = z.object({
  name: z.string().min(1).max(120).optional(),
  price: z.number().min(0).optional(),
  image: z.string().max(500).optional(),
  marketplace: z
    .enum(["shopee", "amazon", "mercadolivre", "collshop"])
    .optional(),
  url: z.string().max(500).optional(),
  description: z.string().max(500).optional(),
});

export const testimonialSchema = z.object({
  name: z.string().min(1).max(80).optional(),
  photo: z.string().max(500).optional(),
  rating: z.number().int().min(1).max(5).optional(),
  text: z.string().min(1).max(800).optional(),
  role: z.string().max(80).optional(),
});
