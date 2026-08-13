import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import { getAchadinhos, saveAchadinhos } from "@/lib/data";
import { revalidateCatalog } from "@/lib/revalidate";
import { achadinhoSchema } from "@/lib/validation";

interface Params {
  params: { id: string };
}

export const dynamic = "force-dynamic";

export async function PUT(req: NextRequest, { params }: Params) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const parsed = achadinhoSchema.safeParse(await req.json());
  if (!parsed.success) {
    return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
  }

  const items = await getAchadinhos();
  const index = items.findIndex((i) => i.id === params.id);
  if (index === -1) {
    return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
  }

  items[index] = { ...items[index], ...parsed.data, id: items[index].id };
  await saveAchadinhos(items);
  await revalidateCatalog();
  return NextResponse.json(items[index]);
}

export async function DELETE(_req: NextRequest, { params }: Params) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const items = await getAchadinhos();
  const next = items.filter((i) => i.id !== params.id);
  if (next.length === items.length) {
    return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
  }
  await saveAchadinhos(next);
  await revalidateCatalog();
  return NextResponse.json({ ok: true });
}
