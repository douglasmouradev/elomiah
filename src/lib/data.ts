import { Achadinho, Product, Testimonial } from "@/lib/types";
import { promises as fs } from "fs";
import path from "path";
import { initialProducts } from "./seed";
import { isSupabaseEnabled, storeRead, storeWrite } from "./supabase";

const dataDir = path.join(process.cwd(), "data");
const productsPath = path.join(dataDir, "products.json");
const achadinhosPath = path.join(dataDir, "achadinhos.json");
const testimonialsPath = path.join(dataDir, "testimonials.json");

async function ensureFile(filePath: string, fallback: unknown) {
  try {
    await fs.access(filePath);
  } catch {
    await fs.mkdir(dataDir, { recursive: true });
    await fs.writeFile(filePath, JSON.stringify(fallback, null, 2));
  }
}

async function readJson<T>(filePath: string, fallback: T): Promise<T> {
  await ensureFile(filePath, fallback);
  const raw = await fs.readFile(filePath, "utf-8");
  return JSON.parse(raw) as T;
}

async function writeJson(filePath: string, data: unknown) {
  await fs.mkdir(dataDir, { recursive: true });
  await fs.writeFile(filePath, JSON.stringify(data, null, 2));
}

export async function getProducts(): Promise<Product[]> {
  if (isSupabaseEnabled()) {
    return storeRead<Product[]>("products", initialProducts);
  }
  return readJson(productsPath, initialProducts);
}

export async function saveProducts(products: Product[]): Promise<void> {
  if (isSupabaseEnabled()) {
    await storeWrite("products", products);
    return;
  }
  await writeJson(productsPath, products);
}

export async function getProductBySlug(slug: string): Promise<Product | undefined> {
  const products = await getProducts();
  return products.find((p) => p.slug === slug);
}

export async function getFeaturedProducts(): Promise<Product[]> {
  const products = await getProducts();
  return products.filter((p) => p.featured);
}

export async function getAchadinhos(): Promise<Achadinho[]> {
  if (isSupabaseEnabled()) {
    return storeRead<Achadinho[]>("achadinhos", await readJson(achadinhosPath, []));
  }
  return readJson(achadinhosPath, []);
}

export async function saveAchadinhos(items: Achadinho[]): Promise<void> {
  if (isSupabaseEnabled()) {
    await storeWrite("achadinhos", items);
    return;
  }
  await writeJson(achadinhosPath, items);
}

export async function getTestimonials(): Promise<Testimonial[]> {
  if (isSupabaseEnabled()) {
    return storeRead<Testimonial[]>(
      "testimonials",
      await readJson(testimonialsPath, [])
    );
  }
  return readJson(testimonialsPath, []);
}

export async function saveTestimonials(items: Testimonial[]): Promise<void> {
  if (isSupabaseEnabled()) {
    await storeWrite("testimonials", items);
    return;
  }
  await writeJson(testimonialsPath, items);
}

export function slugify(text: string): string {
  return text
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "");
}
