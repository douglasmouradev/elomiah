"use client";

import { FormEvent, useCallback, useEffect, useMemo, useState } from "react";
import {
  Download,
  Plus,
  RefreshCw,
  Trash2,
  TrendingUp,
} from "lucide-react";
import { formatPrice } from "@/lib/format";
import {
  Product,
  Sale,
  SaleChannel,
  SALE_CHANNEL_LABELS,
  SaleStatus,
  SALE_STATUS_LABELS,
} from "@/lib/types";
import type { SalesMetrics } from "@/lib/sales";
import { cn } from "@/lib/utils";

type RangePreset = "7d" | "30d" | "90d" | "all";

function toInputDate(d: Date) {
  return d.toISOString().slice(0, 10);
}

function rangeFromPreset(preset: RangePreset): { from?: string; to?: string } {
  if (preset === "all") return {};
  const to = new Date();
  const from = new Date();
  const days = preset === "7d" ? 7 : preset === "30d" ? 30 : 90;
  from.setDate(from.getDate() - (days - 1));
  return { from: toInputDate(from), to: toInputDate(to) };
}

function formatDayLabel(iso: string) {
  const [, m, d] = iso.split("-");
  return `${d}/${m}`;
}

function LineChart({
  data,
}: {
  data: { date: string; revenue: number; orders: number }[];
}) {
  const w = 640;
  const h = 220;
  const pad = { t: 16, r: 12, b: 28, l: 44 };
  const max = Math.max(...data.map((d) => d.revenue), 1);
  const innerW = w - pad.l - pad.r;
  const innerH = h - pad.t - pad.b;

  const points = data.map((d, i) => {
    const x =
      pad.l + (data.length <= 1 ? innerW / 2 : (i / (data.length - 1)) * innerW);
    const y = pad.t + innerH - (d.revenue / max) * innerH;
    return { x, y, ...d };
  });

  const path = points
    .map((p, i) => `${i === 0 ? "M" : "L"} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`)
    .join(" ");

  const area =
    points.length > 0
      ? `${path} L ${points[points.length - 1].x} ${pad.t + innerH} L ${points[0].x} ${pad.t + innerH} Z`
      : "";

  const ticks = [0, 0.5, 1].map((t) => ({
    y: pad.t + innerH - t * innerH,
    label: formatPrice(max * t),
  }));

  const labelEvery = Math.max(1, Math.ceil(data.length / 7));

  return (
    <svg viewBox={`0 0 ${w} ${h}`} className="h-auto w-full" role="img">
      <title>Receita confirmada por dia</title>
      {ticks.map((t) => (
        <g key={t.y}>
          <line
            x1={pad.l}
            x2={w - pad.r}
            y1={t.y}
            y2={t.y}
            stroke="rgba(21,41,33,0.08)"
          />
          <text
            x={pad.l - 8}
            y={t.y + 3}
            textAnchor="end"
            className="fill-elomiah-muted"
            style={{ fontSize: 9 }}
          >
            {t.label}
          </text>
        </g>
      ))}
      {area && (
        <path d={area} fill="rgba(184,149,107,0.16)" stroke="none" />
      )}
      {path && (
        <path
          d={path}
          fill="none"
          stroke="#b8956b"
          strokeWidth="2.2"
          strokeLinejoin="round"
          strokeLinecap="round"
        />
      )}
      {points.map((p, i) =>
        i % labelEvery === 0 || i === points.length - 1 ? (
          <text
            key={p.date}
            x={p.x}
            y={h - 8}
            textAnchor="middle"
            className="fill-elomiah-muted"
            style={{ fontSize: 9 }}
          >
            {formatDayLabel(p.date)}
          </text>
        ) : null
      )}
    </svg>
  );
}

function BarChart({
  data,
}: {
  data: { name: string; quantity: number; revenue: number }[];
}) {
  const max = Math.max(...data.map((d) => d.revenue), 1);
  if (!data.length) {
    return (
      <p className="py-10 text-center text-sm text-elomiah-muted">
        Sem vendas confirmadas no período.
      </p>
    );
  }
  return (
    <div className="space-y-3">
      {data.map((row) => (
        <div key={row.name}>
          <div className="mb-1 flex items-baseline justify-between gap-3 text-sm">
            <span className="truncate text-elomiah-green">{row.name}</span>
            <span className="shrink-0 text-elomiah-muted">
              {formatPrice(row.revenue)} · {row.quantity} un.
            </span>
          </div>
          <div className="h-2 overflow-hidden bg-elomiah-green/10">
            <div
              className="h-full bg-elomiah-gold transition-all duration-500"
              style={{ width: `${(row.revenue / max) * 100}%` }}
            />
          </div>
        </div>
      ))}
    </div>
  );
}

function DonutChart({
  data,
}: {
  data: { channel: SaleChannel; revenue: number; orders: number }[];
}) {
  const total = data.reduce((s, d) => s + d.revenue, 0) || 1;
  const colors = ["#152921", "#b8956b", "#5c5c56", "#3d5a4c", "#c9a878"];
  const size = 160;
  const r = 54;
  const c = 2 * Math.PI * r;
  let offset = 0;

  if (!data.length) {
    return (
      <p className="py-10 text-center text-sm text-elomiah-muted">
        Sem receita por canal no período.
      </p>
    );
  }

  return (
    <div className="flex flex-wrap items-center gap-6">
      <svg width={size} height={size} viewBox={`0 0 ${size} ${size}`}>
        <circle
          cx={size / 2}
          cy={size / 2}
          r={r}
          fill="none"
          stroke="rgba(21,41,33,0.08)"
          strokeWidth="18"
        />
        {data.map((d, i) => {
          const len = (d.revenue / total) * c;
          const el = (
            <circle
              key={d.channel}
              cx={size / 2}
              cy={size / 2}
              r={r}
              fill="none"
              stroke={colors[i % colors.length]}
              strokeWidth="18"
              strokeDasharray={`${len} ${c - len}`}
              strokeDashoffset={-offset}
              transform={`rotate(-90 ${size / 2} ${size / 2})`}
            />
          );
          offset += len;
          return el;
        })}
        <text
          x={size / 2}
          y={size / 2 - 4}
          textAnchor="middle"
          className="fill-elomiah-green"
          style={{ fontSize: 11, fontWeight: 600 }}
        >
          Canais
        </text>
        <text
          x={size / 2}
          y={size / 2 + 12}
          textAnchor="middle"
          className="fill-elomiah-muted"
          style={{ fontSize: 9 }}
        >
          {formatPrice(total)}
        </text>
      </svg>
      <ul className="space-y-2 text-sm">
        {data.map((d, i) => (
          <li key={d.channel} className="flex items-center gap-2">
            <span
              className="h-2.5 w-2.5 shrink-0"
              style={{ background: colors[i % colors.length] }}
            />
            <span className="text-elomiah-green">
              {SALE_CHANNEL_LABELS[d.channel]}
            </span>
            <span className="text-elomiah-muted">
              {formatPrice(d.revenue)} ({d.orders})
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}

const emptyManual = {
  customerName: "",
  customerPhone: "",
  city: "",
  channel: "manual" as SaleChannel,
  productId: "",
  quantity: "1",
  notes: "",
  status: "confirmado" as SaleStatus,
};

export function AdminSales() {
  const [sales, setSales] = useState<Sale[]>([]);
  const [metrics, setMetrics] = useState<SalesMetrics | null>(null);
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  const [preset, setPreset] = useState<RangePreset>("30d");
  const [from, setFrom] = useState(() => rangeFromPreset("30d").from || "");
  const [to, setTo] = useState(() => rangeFromPreset("30d").to || "");
  const [statusFilter, setStatusFilter] = useState<SaleStatus | "all">("all");
  const [manual, setManual] = useState(emptyManual);
  const [showManual, setShowManual] = useState(false);
  const [saving, setSaving] = useState(false);

  const query = useMemo(() => {
    const q = new URLSearchParams();
    if (from) q.set("from", from);
    if (to) q.set("to", to);
    if (statusFilter !== "all") q.set("status", statusFilter);
    return q.toString();
  }, [from, to, statusFilter]);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const [salesRes, prodRes] = await Promise.all([
        fetch(`/api/sales?${query}`),
        fetch("/api/products"),
      ]);
      if (salesRes.ok) {
        const data = await salesRes.json();
        setSales(data.sales || []);
        setMetrics(data.metrics || null);
      }
      if (prodRes.ok) setProducts(await prodRes.json());
    } finally {
      setLoading(false);
    }
  }, [query]);

  useEffect(() => {
    load();
  }, [load]);

  const applyPreset = (p: RangePreset) => {
    setPreset(p);
    const r = rangeFromPreset(p);
    setFrom(r.from || "");
    setTo(r.to || "");
  };

  const exportCsv = () => {
    const q = new URLSearchParams(query);
    q.set("format", "csv");
    window.open(`/api/sales?${q.toString()}`, "_blank");
  };

  const updateStatus = async (id: string, status: SaleStatus) => {
    const res = await fetch(`/api/sales/${id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ status }),
    });
    if (res.ok) {
      const data = await res.json();
      if (data.becameConfirmed) {
        const { trackPurchase } = await import("@/lib/analytics");
        trackPurchase({
          id: data.id,
          total: data.total,
          items: data.items || [],
        });
      }
      load();
    }
  };

  const removeSale = async (id: string) => {
    if (!window.confirm("Excluir este registro de venda?")) return;
    const res = await fetch(`/api/sales/${id}`, { method: "DELETE" });
    if (res.ok) load();
  };

  const saveManual = async (e: FormEvent) => {
    e.preventDefault();
    const product = products.find((p) => p.id === manual.productId);
    if (!product) return;
    setSaving(true);
    try {
      const res = await fetch("/api/sales", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          channel: manual.channel,
          status: manual.status,
          customerName: manual.customerName,
          customerPhone: manual.customerPhone || undefined,
          city: manual.city || undefined,
          notes: manual.notes || undefined,
          items: [
            {
              productId: product.id,
              productName: product.name,
              quantity: Number(manual.quantity) || 1,
              unitPrice: product.price,
            },
          ],
        }),
      });
      if (res.ok) {
        setManual(emptyManual);
        setShowManual(false);
        load();
      }
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-end justify-between gap-4">
        <div>
          <h2 className="font-display text-2xl text-elomiah-green">
            Vendas & métricas
          </h2>
          <p className="mt-1 text-sm text-elomiah-muted">
            Pedidos via WhatsApp, marketplaces e lançamentos manuais.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <button type="button" onClick={load} className="btn-outline">
            <RefreshCw size={15} /> Atualizar
          </button>
          <button type="button" onClick={exportCsv} className="btn-outline">
            <Download size={15} /> Exportar CSV
          </button>
          <button
            type="button"
            onClick={() => setShowManual((v) => !v)}
            className="btn-primary"
          >
            <Plus size={15} /> Lançar venda
          </button>
        </div>
      </div>

      <div className="flex flex-wrap items-end gap-4 border border-elomiah-green/10 bg-white/40 p-4">
        <div className="flex flex-wrap gap-2">
          {(
            [
              ["7d", "7 dias"],
              ["30d", "30 dias"],
              ["90d", "90 dias"],
              ["all", "Tudo"],
            ] as const
          ).map(([id, label]) => (
            <button
              key={id}
              type="button"
              onClick={() => applyPreset(id)}
              className={cn(
                "px-3 py-1.5 text-xs tracking-wide uppercase",
                preset === id
                  ? "bg-elomiah-green text-elomiah-cream"
                  : "bg-elomiah-green/5 text-elomiah-muted hover:text-elomiah-green"
              )}
            >
              {label}
            </button>
          ))}
        </div>
        <label className="text-xs text-elomiah-muted">
          De
          <input
            type="date"
            value={from}
            onChange={(e) => {
              setPreset("all");
              setFrom(e.target.value);
            }}
            className="mt-1 block border-b border-elomiah-green/20 bg-transparent py-1 text-sm text-elomiah-green outline-none"
          />
        </label>
        <label className="text-xs text-elomiah-muted">
          Até
          <input
            type="date"
            value={to}
            onChange={(e) => {
              setPreset("all");
              setTo(e.target.value);
            }}
            className="mt-1 block border-b border-elomiah-green/20 bg-transparent py-1 text-sm text-elomiah-green outline-none"
          />
        </label>
        <label className="text-xs text-elomiah-muted">
          Status na tabela
          <select
            value={statusFilter}
            onChange={(e) =>
              setStatusFilter(e.target.value as SaleStatus | "all")
            }
            className="mt-1 block border-b border-elomiah-green/20 bg-transparent py-1 text-sm text-elomiah-green outline-none"
          >
            <option value="all">Todos</option>
            <option value="iniciado">Iniciados</option>
            <option value="confirmado">Confirmados</option>
            <option value="cancelado">Cancelados</option>
          </select>
        </label>
      </div>

      {showManual && (
        <form
          onSubmit={saveManual}
          className="grid gap-3 border border-elomiah-green/10 bg-white/50 p-5 md:grid-cols-3"
        >
          <h3 className="font-display text-xl text-elomiah-green md:col-span-3">
            Novo lançamento
          </h3>
          <input
            required
            placeholder="Cliente"
            value={manual.customerName}
            onChange={(e) =>
              setManual((m) => ({ ...m, customerName: e.target.value }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          />
          <input
            placeholder="WhatsApp"
            value={manual.customerPhone}
            onChange={(e) =>
              setManual((m) => ({ ...m, customerPhone: e.target.value }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          />
          <input
            placeholder="Cidade"
            value={manual.city}
            onChange={(e) => setManual((m) => ({ ...m, city: e.target.value }))}
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          />
          <select
            value={manual.productId}
            required
            onChange={(e) =>
              setManual((m) => ({ ...m, productId: e.target.value }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          >
            <option value="">Produto</option>
            {products.map((p) => (
              <option key={p.id} value={p.id}>
                {p.name} — {formatPrice(p.price)}
              </option>
            ))}
          </select>
          <input
            type="number"
            min={1}
            max={99}
            value={manual.quantity}
            onChange={(e) =>
              setManual((m) => ({ ...m, quantity: e.target.value }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          />
          <select
            value={manual.channel}
            onChange={(e) =>
              setManual((m) => ({
                ...m,
                channel: e.target.value as SaleChannel,
              }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          >
            {Object.entries(SALE_CHANNEL_LABELS).map(([id, label]) => (
              <option key={id} value={id}>
                {label}
              </option>
            ))}
          </select>
          <select
            value={manual.status}
            onChange={(e) =>
              setManual((m) => ({
                ...m,
                status: e.target.value as SaleStatus,
              }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none"
          >
            {Object.entries(SALE_STATUS_LABELS).map(([id, label]) => (
              <option key={id} value={id}>
                {label}
              </option>
            ))}
          </select>
          <input
            placeholder="Observações"
            value={manual.notes}
            onChange={(e) =>
              setManual((m) => ({ ...m, notes: e.target.value }))
            }
            className="border-b border-elomiah-green/20 bg-transparent py-2 text-sm outline-none md:col-span-2"
          />
          <button
            type="submit"
            disabled={saving}
            className="btn-primary md:col-span-3"
          >
            {saving ? "Salvando…" : "Salvar venda"}
          </button>
        </form>
      )}

      {loading && !metrics ? (
        <p className="text-sm text-elomiah-muted">Carregando métricas…</p>
      ) : metrics ? (
        <>
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {[
              {
                label: "Receita confirmada",
                value: formatPrice(metrics.revenue),
                hint: `${metrics.confirmedOrders} pedidos`,
              },
              {
                label: "Ticket médio",
                value: formatPrice(metrics.averageTicket),
                hint: "somente confirmados",
              },
              {
                label: "Pedidos no período",
                value: String(metrics.totalOrders),
                hint: `${metrics.startedOrders} iniciados · ${metrics.cancelledOrders} cancelados`,
              },
              {
                label: "Conversão",
                value: `${metrics.conversionRate.toFixed(0)}%`,
                hint: "iniciado → confirmado",
              },
            ].map((card) => (
              <div
                key={card.label}
                className="border border-elomiah-green/10 bg-white/50 p-5"
              >
                <div className="flex items-center gap-2 text-xs tracking-[0.18em] text-elomiah-gold uppercase">
                  <TrendingUp size={14} />
                  {card.label}
                </div>
                <p className="mt-3 font-display text-3xl text-elomiah-green">
                  {card.value}
                </p>
                <p className="mt-1 text-xs text-elomiah-muted">{card.hint}</p>
              </div>
            ))}
          </div>

          <div className="grid gap-6 lg:grid-cols-5">
            <div className="border border-elomiah-green/10 bg-white/50 p-5 lg:col-span-3">
              <h3 className="mb-4 font-display text-xl text-elomiah-green">
                Receita por dia
              </h3>
              <LineChart data={metrics.byDay} />
            </div>
            <div className="border border-elomiah-green/10 bg-white/50 p-5 lg:col-span-2">
              <h3 className="mb-4 font-display text-xl text-elomiah-green">
                Por canal
              </h3>
              <DonutChart data={metrics.byChannel} />
            </div>
          </div>

          <div className="border border-elomiah-green/10 bg-white/50 p-5">
            <h3 className="mb-4 font-display text-xl text-elomiah-green">
              Produtos mais vendidos
            </h3>
            <BarChart data={metrics.byProduct} />
          </div>
        </>
      ) : null}

      <div className="border border-elomiah-green/10 bg-white/40">
        <div className="border-b border-elomiah-green/10 px-5 py-4">
          <h3 className="font-display text-xl text-elomiah-green">
            Pedidos ({sales.length})
          </h3>
        </div>
        {sales.length === 0 ? (
          <p className="px-5 py-10 text-sm text-elomiah-muted">
            Nenhum pedido neste filtro. Os checkouts WhatsApp aparecem como
            &quot;Iniciado&quot; — confirme aqui após o pagamento.
          </p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full min-w-[720px] text-left text-sm">
              <thead className="border-b border-elomiah-green/10 text-xs tracking-wide text-elomiah-muted uppercase">
                <tr>
                  <th className="px-4 py-3 font-medium">Data</th>
                  <th className="px-4 py-3 font-medium">Cliente</th>
                  <th className="px-4 py-3 font-medium">Itens</th>
                  <th className="px-4 py-3 font-medium">Canal</th>
                  <th className="px-4 py-3 font-medium">Total</th>
                  <th className="px-4 py-3 font-medium">Status</th>
                  <th className="px-4 py-3 font-medium" />
                </tr>
              </thead>
              <tbody>
                {sales.map((s) => (
                  <tr
                    key={s.id}
                    className="border-b border-elomiah-green/[0.06]"
                  >
                    <td className="px-4 py-3 text-elomiah-muted">
                      {new Date(s.createdAt).toLocaleString("pt-BR", {
                        day: "2-digit",
                        month: "2-digit",
                        year: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                      })}
                    </td>
                    <td className="px-4 py-3">
                      <div className="text-elomiah-green">{s.customerName}</div>
                      <div className="text-xs text-elomiah-muted">
                        {[s.customerPhone, s.city].filter(Boolean).join(" · ")}
                      </div>
                    </td>
                    <td className="max-w-[220px] truncate px-4 py-3 text-elomiah-muted">
                      {s.items
                        .map((i) => `${i.productName} ×${i.quantity}`)
                        .join(", ")}
                    </td>
                    <td className="px-4 py-3 text-elomiah-muted">
                      {SALE_CHANNEL_LABELS[s.channel]}
                    </td>
                    <td className="px-4 py-3 text-elomiah-green">
                      {formatPrice(s.total)}
                    </td>
                    <td className="px-4 py-3">
                      <select
                        value={s.status}
                        onChange={(e) =>
                          updateStatus(s.id, e.target.value as SaleStatus)
                        }
                        className={cn(
                          "border-b bg-transparent py-1 outline-none",
                          s.status === "confirmado" &&
                            "border-elomiah-gold text-elomiah-green",
                          s.status === "iniciado" &&
                            "border-elomiah-green/20 text-elomiah-muted",
                          s.status === "cancelado" &&
                            "border-red-300 text-red-700/80"
                        )}
                      >
                        {Object.entries(SALE_STATUS_LABELS).map(
                          ([id, label]) => (
                            <option key={id} value={id}>
                              {label}
                            </option>
                          )
                        )}
                      </select>
                    </td>
                    <td className="px-4 py-3">
                      <button
                        type="button"
                        onClick={() => removeSale(s.id)}
                        className="text-elomiah-muted hover:text-red-700"
                        aria-label="Excluir"
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
    </div>
  );
}
