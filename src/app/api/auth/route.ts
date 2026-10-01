import { NextRequest, NextResponse } from "next/server";
import {
  ADMIN_COOKIE,
  getAdminPassword,
  getAdminSecret,
  isAdminConfigured,
} from "@/lib/auth";
import { rateLimit } from "@/lib/rate-limit";

export async function POST(req: NextRequest) {
  try {
    const ip =
      req.headers.get("x-forwarded-for")?.split(",")[0]?.trim() || "local";
    const limited = rateLimit(`auth:${ip}`, 8, 60_000);
    if (!limited.ok) {
      return NextResponse.json(
        { ok: false, error: "Muitas tentativas. Aguarde um momento." },
        { status: 429 }
      );
    }

    const body = await req.json();
    const { password, action } = body as { password?: string; action?: string };

    if (action === "logout") {
      const res = NextResponse.json({ ok: true });
      res.cookies.set(ADMIN_COOKIE, "", { httpOnly: true, path: "/", maxAge: 0 });
      return res;
    }

    if (!isAdminConfigured() && process.env.NODE_ENV === "production") {
      return NextResponse.json(
        { ok: false, error: "Admin não configurado no servidor" },
        { status: 503 }
      );
    }

    const adminPassword = getAdminPassword();
    if (adminPassword && password === adminPassword) {
      const res = NextResponse.json({ ok: true });
      res.cookies.set(ADMIN_COOKIE, getAdminSecret(), {
        httpOnly: true,
        path: "/",
        sameSite: "lax",
        secure:
          process.env.NODE_ENV === "production" &&
          process.env.COOKIE_SECURE !== "false",
        maxAge: 60 * 60 * 24 * 7,
      });
      return res;
    }

    return NextResponse.json({ ok: false, error: "Senha incorreta" }, { status: 401 });
  } catch {
    return NextResponse.json({ ok: false, error: "Requisição inválida" }, { status: 400 });
  }
}

export async function GET() {
  const { isAuthenticated } = await import("@/lib/auth");
  return NextResponse.json({ authenticated: isAuthenticated() });
}
