import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import { getProducts, saveProducts, slugify } from "@/lib/data";
import { revalidateCatalog } from "@/lib/revalidate";
import { Product } from "@/lib/types";
import { productSchema } from "@/lib/validation";

interface Params {
  params: { id: string };
}

export const dynamic = "force-dynamic";

export async function PUT(req: NextRequest, { params }: Params) {
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
    const index = products.findIndex((p) => p.id === params.id);
    if (index === -1) {
      return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
    }

    const current = products[index];
    const updated: Product = {
      ...current,
      ...body,
      id: current.id,
      slug: body.slug || (body.name ? slugify(body.name) : current.slug),
      price: body.price !== undefined ? Number(body.price) : current.price,
      stock: body.stock !== undefined ? Number(body.stock) : current.stock,
      featured: body.featured !== undefined ? Boolean(body.featured) : current.featured,
      notes: body.notes || current.notes,
      images: body.images || current.images,
      updatedAt: new Date().toISOString(),
    };

    products[index] = updated;
    await saveProducts(products);
    await revalidateCatalog(products);

    return NextResponse.json(updated);
  } catch {
    return NextResponse.json({ error: "Erro ao atualizar" }, { status: 500 });
  }
}

export async function DELETE(_req: NextRequest, { params }: Params) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const products = await getProducts();
  const next = products.filter((p) => p.id !== params.id);
  if (next.length === products.length) {
    return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
  }
  await saveProducts(next);
  await revalidateCatalog(next);
  return NextResponse.json({ ok: true });
}
