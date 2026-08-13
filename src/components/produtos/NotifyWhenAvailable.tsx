"use client";

import { FormEvent, useState } from "react";
import { isConfiguredWhatsApp, whatsappLink } from "@/lib/site";

export function NotifyWhenAvailable({
  productSlug,
  productName,
}: {
  productSlug: string;
  productName: string;
}) {
  const [name, setName] = useState("");
  const [contact, setContact] = useState("");
  const [status, setStatus] = useState<"idle" | "ok" | "error">("idle");
  const [saving, setSaving] = useState(false);

  const onSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setStatus("idle");
    try {
      const res = await fetch("/api/waitlist", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          productSlug,
          productName,
          name: name.trim(),
          contact: contact.trim(),
        }),
      });
      setStatus(res.ok ? "ok" : "error");
      if (res.ok) {
        setName("");
        setContact("");
      }
    } catch {
      setStatus("error");
    } finally {
      setSaving(false);
    }
  };

  const waHref = isConfiguredWhatsApp()
    ? whatsappLink(
        `Olá! Quero ser avisada quando "${productName}" voltar ao estoque.`
      )
    : null;

  return (
    <div className="mt-8 border border-elomiah-green/10 bg-elomiah-surface/40 p-6">
      <p className="section-label">Esgotado no momento</p>
      <p className="mt-3 text-sm leading-relaxed text-elomiah-muted">
        Deixe seu contato e avisamos quando esta essência voltar.
      </p>
      <form onSubmit={onSubmit} className="mt-5 space-y-3">
        <input
          required
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Seu nome"
          className="input-brand"
          autoComplete="name"
        />
        <input
          required
          value={contact}
          onChange={(e) => setContact(e.target.value)}
          placeholder="E-mail ou WhatsApp"
          className="input-brand"
          autoComplete="email"
        />
        <button
          type="submit"
          disabled={saving}
          className="btn-outline w-full disabled:opacity-50"
        >
          {saving ? "Enviando…" : "Avise-me"}
        </button>
      </form>
      {status === "ok" && (
        <p className="mt-3 text-sm text-elomiah-green" role="status">
          Pronto — você está na lista.
        </p>
      )}
      {status === "error" && (
        <p className="mt-3 text-sm text-red-700" role="status">
          Não foi possível salvar. Tente pelo WhatsApp.
        </p>
      )}
      {waHref && (
        <a
          href={waHref}
          target="_blank"
          rel="noopener noreferrer"
          className="btn-link mt-4"
        >
          Ou avisar pelo WhatsApp
        </a>
      )}
    </div>
  );
}
