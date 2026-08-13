"use client";

import { useCallback, useEffect, useState } from "react";
import { RefreshCw, Trash2 } from "lucide-react";
import type { WaitlistEntry } from "@/lib/types";

export function AdminWaitlist() {
  const [entries, setEntries] = useState<WaitlistEntry[]>([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const res = await fetch("/api/waitlist");
      if (res.ok) setEntries(await res.json());
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const remove = async (id: string) => {
    if (!window.confirm("Remover este aviso da lista?")) return;
    const res = await fetch(`/api/waitlist?id=${encodeURIComponent(id)}`, {
      method: "DELETE",
    });
    if (res.ok) load();
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h2 className="font-display text-2xl text-elomiah-green">
            Avise-me
          </h2>
          <p className="mt-1 text-sm text-elomiah-muted">
            Leads de produtos esgotados. Em produção, persistem no Supabase.
          </p>
        </div>
        <button type="button" onClick={load} className="btn-outline">
          <RefreshCw size={15} /> Atualizar
        </button>
      </div>

      {loading ? (
        <p className="text-sm text-elomiah-muted">Carregando…</p>
      ) : entries.length === 0 ? (
        <p className="border border-elomiah-green/10 bg-white/40 px-5 py-10 text-sm text-elomiah-muted">
          Nenhuma solicitação ainda.
        </p>
      ) : (
        <div className="overflow-x-auto border border-elomiah-green/10 bg-white/40">
          <table className="w-full min-w-[640px] text-left text-sm">
            <thead className="border-b border-elomiah-green/10 text-xs tracking-wide text-elomiah-muted uppercase">
              <tr>
                <th className="px-4 py-3 font-medium">Data</th>
                <th className="px-4 py-3 font-medium">Produto</th>
                <th className="px-4 py-3 font-medium">Nome</th>
                <th className="px-4 py-3 font-medium">Contato</th>
                <th className="px-4 py-3 font-medium" />
              </tr>
            </thead>
            <tbody>
              {entries.map((e) => (
                <tr
                  key={e.id}
                  className="border-b border-elomiah-green/[0.06]"
                >
                  <td className="px-4 py-3 text-elomiah-muted">
                    {new Date(e.createdAt).toLocaleString("pt-BR", {
                      day: "2-digit",
                      month: "2-digit",
                      year: "2-digit",
                      hour: "2-digit",
                      minute: "2-digit",
                    })}
                  </td>
                  <td className="px-4 py-3 text-elomiah-green">
                    {e.productName}
                  </td>
                  <td className="px-4 py-3">{e.name}</td>
                  <td className="px-4 py-3 text-elomiah-muted">{e.contact}</td>
                  <td className="px-4 py-3">
                    <button
                      type="button"
                      onClick={() => remove(e.id)}
                      className="text-elomiah-muted hover:text-red-700"
                      aria-label="Remover"
                    >
                      <Trash2 size={15} />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
