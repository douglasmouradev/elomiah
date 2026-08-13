import { NextRequest, NextResponse } from "next/server";
import { isAuthenticated } from "@/lib/auth";
import { getTestimonials, saveTestimonials } from "@/lib/data";
import { revalidateCatalog } from "@/lib/revalidate";
import { Testimonial } from "@/lib/types";
import { testimonialSchema } from "@/lib/validation";

export const dynamic = "force-dynamic";

export async function GET() {
  const items = await getTestimonials();
  return NextResponse.json(items);
}

export async function POST(req: NextRequest) {
  if (!isAuthenticated()) {
    return NextResponse.json({ error: "Não autorizado" }, { status: 401 });
  }

  const parsed = testimonialSchema.safeParse(await req.json());
  if (!parsed.success) {
    return NextResponse.json({ error: "Dados inválidos" }, { status: 400 });
  }

  const body = parsed.data;
  const items = await getTestimonials();
  const item: Testimonial = {
    id: crypto.randomUUID(),
    name: body.name || "Cliente",
    photo: body.photo || "/logo-elomiah.jpg",
    rating: body.rating || 5,
    text: body.text || "",
    role: body.role || undefined,
  };

  items.push(item);
  await saveTestimonials(items);
  await revalidateCatalog();
  return NextResponse.json(item, { status: 201 });
}
