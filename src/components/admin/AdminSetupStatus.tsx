"use client";

import { useEffect, useState } from "react";

type ChecklistItem = { id: string; ok: boolean; label: string };

export function AdminSetupStatus() {
  const [items, setItems] = useState<ChecklistItem[] | null>(null);

  useEffect(() => {
    fetch("/api/admin/status")
      .then((r) => (r.ok ? r.json() : null))
      .then((data) => {
        if (data?.checklist) setItems(data.checklist);
      })
      .catch(() => undefined);
  }, []);

  if (!items) return null;

  const done = items.filter((i) => i.ok).length;

  return (
    <div className="mb-10 border border-elomiah-green/10 bg-white/40 p-5">
      <div className="flex flex-wrap items-baseline justify-between gap-2">
        <p className="section-label">Setup produção</p>
        <p className="text-xs text-elomiah-muted">
          {done}/{items.length} prontos
        </p>
      </div>
      <ul className="mt-4 grid gap-2 sm:grid-cols-2">
        {items.map((item) => (
          <li
            key={item.id}
            className="flex items-start gap-2 text-sm text-elomiah-muted"
          >
            <span
              className={
                item.ok ? "text-elomiah-green" : "text-elomiah-gold/80"
              }
              aria-hidden
            >
              {item.ok ? "●" : "○"}
            </span>
            <span className={item.ok ? "text-elomiah-green" : undefined}>
              {item.label}
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}
