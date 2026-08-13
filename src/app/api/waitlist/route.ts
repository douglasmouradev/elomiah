import { NextRequest, NextResponse } from "next/server";
import { z } from "zod";
import { isAuthenticated } from "@/lib/auth";
import { getWaitlist, saveWaitlist } from "@/lib/data";
import { rateLimit } from "@/lib/rate-limit";
import { WaitlistEntry } from "@/lib/types";

export const dynamic = "force-dynamic";

const schema = z.object({
  productSlug: z.string().min(1).max(120),
  productName: z.string().min(1).max(120),
  name: z.string().min(2).max(80),
  contact: z.string().min(5).max(120),
});

export async function GET() {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }
  const list = await getWaitlist();
  return NextResponse.json(
    [...list].sort(
      (a, b) =>
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
    )
  );
}

export async function POST(req: NextRequest) {
  try {
    const ip =
      req.headers.get("x-forwarded-for")?.split(",")[0]?.trim() || "unknown";
    const limited = rateLimit(`waitlist:${ip}`, 8, 60_000);
    if (!limited.ok) {
      return NextResponse.json(
        { error: "Muitas tentativas. Aguarde um minuto." },
        { status: 429 }
      );
    }

    const body = await req.json();
    const parsed = schema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
    }

    const list = await getWaitlist();
    const entry: WaitlistEntry = {
      id: crypto.randomUUID(),
      ...parsed.data,
      createdAt: new Date().toISOString(),
    };
    list.push(entry);
    await saveWaitlist(list);

    return NextResponse.json({ ok: true, id: entry.id }, { status: 201 });
  } catch {
    return NextResponse.json({ error: "Falha ao salvar" }, { status: 500 });
  }
}

export async function DELETE(req: NextRequest) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const id = new URL(req.url).searchParams.get("id");
  if (!id) {
    return NextResponse.json({ error: "id obrigatório" }, { status: 400 });
  }

  const list = await getWaitlist();
  const next = list.filter((e) => e.id !== id);
  if (next.length === list.length) {
    return NextResponse.json({ error: "Não encontrado" }, { status: 404 });
  }
  await saveWaitlist(next);
  return NextResponse.json({ ok: true });
}
