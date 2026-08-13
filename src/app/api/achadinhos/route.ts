import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import { getAchadinhos, saveAchadinhos } from "@/lib/data";
import { revalidateCatalog } from "@/lib/revalidate";
import { Achadinho } from "@/lib/types";
import { achadinhoSchema } from "@/lib/validation";

export const dynamic = "force-dynamic";

export async function GET() {
  const items = await getAchadinhos();
  return NextResponse.json(items);
}

export async function POST(req: NextRequest) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const parsed = achadinhoSchema.safeParse(await req.json());
  if (!parsed.success) {
    return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
  }

  const body = parsed.data;
  const items = await getAchadinhos();
  const item: Achadinho = {
    id: crypto.randomUUID(),
    name: body.name || "Novo achadinho",
    price: Number(body.price) || 0,
    image: body.image || "/logo-elomiah.jpg",
    marketplace: body.marketplace || "shopee",
    url: body.url || "",
    description: body.description || "",
  };

  items.push(item);
  await saveAchadinhos(items);
  await revalidateCatalog();
  return NextResponse.json(item, { status: 201 });
}
