import { NextRequest, NextResponse } from "next/server";
import { promises as fs } from "fs";
import path from "path";
import { z } from "zod";
import { revalidatePath } from "next/cache";

const waitlistPath = path.join(process.cwd(), "data", "waitlist.json");

const schema = z.object({
  productSlug: z.string().min(1).max(120),
  productName: z.string().min(1).max(120),
  name: z.string().min(2).max(80),
  contact: z.string().min(5).max(120),
});

async function readWaitlist() {
  try {
    const raw = await fs.readFile(waitlistPath, "utf-8");
    return JSON.parse(raw) as unknown[];
  } catch {
    return [];
  }
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const parsed = schema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
    }

    const list = await readWaitlist();
    const entry = {
      id: `w_${Date.now()}`,
      ...parsed.data,
      createdAt: new Date().toISOString(),
    };
    list.push(entry);
    await fs.mkdir(path.dirname(waitlistPath), { recursive: true });
    await fs.writeFile(waitlistPath, JSON.stringify(list, null, 2));
    revalidatePath("/admin");
    return NextResponse.json({ ok: true });
  } catch {
    return NextResponse.json({ error: "Falha ao salvar" }, { status: 500 });
  }
}
