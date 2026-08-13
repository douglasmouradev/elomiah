import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import { getSales, saveSales } from "@/lib/data";
import { rateLimit } from "@/lib/rate-limit";
import {
  buildSale,
  computeSalesMetrics,
  filterSales,
  salesToCsv,
} from "@/lib/sales";
import { SaleStatus } from "@/lib/types";
import { saleCreateSchema } from "@/lib/validation";

export const dynamic = "force-dynamic";

export async function GET(req: NextRequest) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const { searchParams } = new URL(req.url);
  const from = searchParams.get("from") || undefined;
  const to = searchParams.get("to") || undefined;
  const status = (searchParams.get("status") || "all") as SaleStatus | "all";
  const format = searchParams.get("format");

  const sales = await getSales();
  const filtered = filterSales(sales, { from, to, status });

  if (format === "csv") {
    const csv = salesToCsv(filtered);
    return new NextResponse(csv, {
      headers: {
        "Content-Type": "text/csv; charset=utf-8",
        "Content-Disposition": `attachment; filename="elomiah-vendas-${from || "inicio"}-${to || "hoje"}.csv"`,
      },
    });
  }

  const metrics = computeSalesMetrics(sales, { from, to });
  return NextResponse.json({ sales: filtered, metrics });
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const parsed = saleCreateSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
    }

    const data = parsed.data;
    const isAdmin = isAuthenticated();

    // Público: só registra intenção WhatsApp (iniciado). Admin pode criar/manual.
    if (!isAdmin) {
      const ip =
        req.headers.get("x-forwarded-for")?.split(",")[0]?.trim() ||
        "unknown";
      const limited = rateLimit(`sale:${ip}`, 12, 60_000);
      if (!limited.ok) {
        return NextResponse.json(
          { error: "Muitas tentativas. Aguarde um minuto." },
          { status: 429 }
        );
      }
      if (data.channel !== "whatsapp") {
        return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
      }
    }

    let sale = buildSale({
      channel: data.channel,
      status: isAdmin ? data.status || "confirmado" : "iniciado",
      customerName: data.customerName,
      customerPhone: data.customerPhone,
      city: data.city,
      cep: data.cep,
      items: data.items,
      giftWrap: data.giftWrap,
      notes: data.notes,
      createdAt: isAdmin ? data.createdAt : undefined,
    });

    if (isAdmin && sale.status === "confirmado") {
      const { applySaleStockChange, getProducts } = await import("@/lib/data");
      const { revalidateCatalog } = await import("@/lib/revalidate");
      const result = await applySaleStockChange(sale, "confirmado");
      sale = result.sale;
      if (result.productsChanged) {
        await revalidateCatalog(await getProducts());
      }
    }

    const sales = await getSales();
    sales.push(sale);
    await saveSales(sales);

    return NextResponse.json(sale, { status: 201 });
  } catch {
    return NextResponse.json(
      { error: "Erro ao registrar venda" },
      { status: 500 }
    );
  }
}
