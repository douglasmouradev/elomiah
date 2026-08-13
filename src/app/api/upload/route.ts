import { NextRequest, NextResponse } from "next/server";
import { writeFile, mkdir } from "fs/promises";
import path from "path";
import { isAuthenticated } from "@/lib/auth";
import { ALLOWED_UPLOAD_TYPES, MAX_UPLOAD_BYTES } from "@/lib/validation";
import { isSupabaseEnabled, uploadToSupabase } from "@/lib/supabase";

const ALLOWED_EXT = [".jpg", ".jpeg", ".png", ".webp", ".gif"];

export async function POST(req: NextRequest) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  try {
    const form = await req.formData();
    const file = form.get("file") as File | null;
    if (!file) {
      return NextResponse.json({ error: "Arquivo ausente" }, { status: 400 });
    }

    if (file.size > MAX_UPLOAD_BYTES) {
      return NextResponse.json(
        { error: "Arquivo muito grande (máx. 5 MB)" },
        { status: 400 }
      );
    }

    if (
      !ALLOWED_UPLOAD_TYPES.includes(
        file.type as (typeof ALLOWED_UPLOAD_TYPES)[number]
      )
    ) {
      return NextResponse.json(
        { error: "Tipo de arquivo não permitido" },
        { status: 400 }
      );
    }

    const ext = path.extname(file.name).toLowerCase();
    if (!ALLOWED_EXT.includes(ext)) {
      return NextResponse.json({ error: "Extensão não permitida" }, { status: 400 });
    }

    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);
    const name = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}${ext}`;

    if (isSupabaseEnabled()) {
      const url = await uploadToSupabase(buffer, name, file.type);
      return NextResponse.json({ url });
    }

    const dir = path.join(process.cwd(), "public", "uploads");
    await mkdir(dir, { recursive: true });
    await writeFile(path.join(dir, name), buffer);

    return NextResponse.json({ url: `/uploads/${name}` });
  } catch {
    return NextResponse.json({ error: "Falha no upload" }, { status: 500 });
  }
}
