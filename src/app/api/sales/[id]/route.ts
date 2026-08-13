import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import {
  applySaleStockChange,
  getProducts,
  getSales,
  saveSales,
} from "@/lib/data";
import { revalidateCatalog } from "@/lib/revalidate";
import { saleUpdateSchema } from "@/lib/validation";

export const dynamic = "force-dynamic";

type Ctx = { params: { id: string } };

export async function PATCH(req: NextRequest, { params }: Ctx) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  try {
    const body = await req.json();
    const parsed = saleUpdateSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
    }

    const sales = await getSales();
    const idx = sales.findIndex((s) => s.id === params.id);
    if (idx < 0) {
      return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
    }

    const current = sales[idx];
    const nextStatus = parsed.data.status ?? current.status;

    let working = { ...current };
    let productsChanged = false;

    if (parsed.data.status && parsed.data.status !== current.status) {
      const result = await applySaleStockChange(current, parsed.data.status);
      working = result.sale;
      productsChanged = result.productsChanged;
    }

    sales[idx] = {
      ...working,
      ...parsed.data,
      stockApplied: working.stockApplied,
      status: nextStatus,
      updatedAt: new Date().toISOString(),
    };
    await saveSales(sales);

    if (productsChanged) {
      const products = await getProducts();
      await revalidateCatalog(products);
    }

    return NextResponse.json({
      ...sales[idx],
      stockAdjusted: productsChanged,
      becameConfirmed:
        current.status !== "confirmado" && nextStatus === "confirmado",
    });
  } catch {
    return NextResponse.json({ error: "Erro ao atualizar" }, { status: 500 });
  }
}

export async function DELETE(_req: NextRequest, { params }: Ctx) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const sales = await getSales();
  const current = sales.find((s) => s.id === params.id);
  if (!current) {
    return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
  }

  // Devolve estoque se a venda confirmada for excluída
  if (current.stockApplied && current.status === "confirmado") {
    const { productsChanged } = await applySaleStockChange(
      current,
      "cancelado"
    );
    if (productsChanged) {
      const products = await getProducts();
      await revalidateCatalog(products);
    }
  }

  const next = sales.filter((s) => s.id !== params.id);
  await saveSales(next);
  return NextResponse.json({ ok: true });
}
