import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import { getProducts, saveProducts, slugify } from "@/lib/data";
import { revalidateCatalog } from "@/lib/revalidate";
import { Product } from "@/lib/types";
import { productSchema } from "@/lib/validation";

export const dynamic = "force-dynamic";

export async function GET() {
  const products = await getProducts();
  return NextResponse.json(products);
}

export async function POST(req: NextRequest) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  try {
    const raw = await req.json();
    const parsed = productSchema.safeParse(raw);
    if (!parsed.success) {
      return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
    }

    const body = parsed.data;
    const products = await getProducts();
    const now = new Date().toISOString();

    const product: Product = {
      id: crypto.randomUUID(),
      slug: body.slug || slugify(body.name || "produto"),
      name: body.name || "Novo produto",
      aroma: body.aroma || "",
      collection: body.collection || "Coleção Refúgio",
      accentColor: body.accentColor || "#C9A24B",
      quote: body.quote || "",
      category: body.category || "spray-ambiente",
      price: Number(body.price) || 0,
      stock: Number(body.stock) || 0,
      description: body.description || "",
      shortDescription: body.shortDescription || "",
      notes: body.notes || { top: "", heart: "", base: "" },
      usage: body.usage || "",
      precautions: body.precautions || "",
      technical: body.technical || "",
      images: body.images?.length
        ? body.images
        : ["/images/produtos/colecao-refugio.png"],
      benefits: body.benefits?.length ? body.benefits : [],
      featured: Boolean(body.featured),
      marketplace: body.marketplace || "interno",
      marketplaceUrl: body.marketplaceUrl || undefined,
      createdAt: now,
      updatedAt: now,
    };

    let slug = product.slug;
    let i = 1;
    while (products.some((p) => p.slug === slug)) {
      slug = `${product.slug}-${i++}`;
    }
    product.slug = slug;

    products.push(product);
    await saveProducts(products);
    await revalidateCatalog(products);

    return NextResponse.json(product, { status: 201 });
  } catch {
    return NextResponse.json({ error: "Erro ao criar produto" }, { status: 500 });
  }
}
