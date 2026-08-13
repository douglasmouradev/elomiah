import { Sale, SaleChannel, SaleItem, SaleStatus } from "@/lib/types";

export type SalesMetrics = {
  totalOrders: number;
  confirmedOrders: number;
  startedOrders: number;
  cancelledOrders: number;
  revenue: number;
  averageTicket: number;
  conversionRate: number;
  byDay: { date: string; revenue: number; orders: number }[];
  byProduct: { name: string; quantity: number; revenue: number }[];
  byChannel: { channel: SaleChannel; revenue: number; orders: number }[];
  byStatus: { status: SaleStatus; count: number }[];
};

function dayKey(iso: string) {
  return iso.slice(0, 10);
}

function inRange(iso: string, from?: string, to?: string) {
  const d = dayKey(iso);
  if (from && d < from) return false;
  if (to && d > to) return false;
  return true;
}

export function filterSales(
  sales: Sale[],
  opts: { from?: string; to?: string; status?: SaleStatus | "all" } = {}
) {
  return sales
    .filter((s) => inRange(s.createdAt, opts.from, opts.to))
    .filter((s) =>
      !opts.status || opts.status === "all" ? true : s.status === opts.status
    )
    .sort(
      (a, b) =>
        new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
    );
}

export function computeSalesMetrics(
  sales: Sale[],
  opts: { from?: string; to?: string } = {}
): SalesMetrics {
  const filtered = filterSales(sales, opts);
  const confirmed = filtered.filter((s) => s.status === "confirmado");
  const started = filtered.filter((s) => s.status === "iniciado");
  const cancelled = filtered.filter((s) => s.status === "cancelado");
  const revenue = confirmed.reduce((sum, s) => sum + s.total, 0);
  const conversionBase = confirmed.length + started.length;

  const dayMap = new Map<string, { revenue: number; orders: number }>();
  const spanDays = 30;
  const end = opts.to ? new Date(`${opts.to}T12:00:00`) : new Date();
  for (let i = spanDays - 1; i >= 0; i--) {
    const d = new Date(end);
    d.setDate(d.getDate() - i);
    const key = d.toISOString().slice(0, 10);
    if (opts.from && key < opts.from) continue;
    if (opts.to && key > opts.to) continue;
    dayMap.set(key, { revenue: 0, orders: 0 });
  }
  for (const s of confirmed) {
    const key = dayKey(s.createdAt);
    const row = dayMap.get(key) || { revenue: 0, orders: 0 };
    row.revenue += s.total;
    row.orders += 1;
    dayMap.set(key, row);
  }

  const productMap = new Map<
    string,
    { name: string; quantity: number; revenue: number }
  >();
  for (const s of confirmed) {
    for (const item of s.items) {
      const cur = productMap.get(item.productId) || {
        name: item.productName,
        quantity: 0,
        revenue: 0,
      };
      cur.quantity += item.quantity;
      cur.revenue += item.unitPrice * item.quantity;
      productMap.set(item.productId, cur);
    }
  }

  const channelMap = new Map<
    SaleChannel,
    { channel: SaleChannel; revenue: number; orders: number }
  >();
  for (const s of confirmed) {
    const cur = channelMap.get(s.channel) || {
      channel: s.channel,
      revenue: 0,
      orders: 0,
    };
    cur.revenue += s.total;
    cur.orders += 1;
    channelMap.set(s.channel, cur);
  }

  return {
    totalOrders: filtered.length,
    confirmedOrders: confirmed.length,
    startedOrders: started.length,
    cancelledOrders: cancelled.length,
    revenue,
    averageTicket: confirmed.length ? revenue / confirmed.length : 0,
    conversionRate: conversionBase
      ? (confirmed.length / conversionBase) * 100
      : 0,
    byDay: Array.from(dayMap.entries()).map(([date, v]) => ({
      date,
      ...v,
    })),
    byProduct: Array.from(productMap.values())
      .sort((a, b) => b.revenue - a.revenue)
      .slice(0, 8),
    byChannel: Array.from(channelMap.values()).sort(
      (a, b) => b.revenue - a.revenue
    ),
    byStatus: [
      { status: "iniciado", count: started.length },
      { status: "confirmado", count: confirmed.length },
      { status: "cancelado", count: cancelled.length },
    ],
  };
}

export function salesToCsv(sales: Sale[]): string {
  const header = [
    "id",
    "data",
    "status",
    "canal",
    "cliente",
    "whatsapp",
    "cidade",
    "cep",
    "itens",
    "subtotal",
    "embalagem_presente",
    "total",
    "observacoes",
  ];
  const rows = sales.map((s) => {
    const items = s.items
      .map(
        (i) =>
          `${i.productName} x${i.quantity} (${i.unitPrice.toFixed(2)})`
      )
      .join(" | ");
    return [
      s.id,
      s.createdAt,
      s.status,
      s.channel,
      s.customerName,
      s.customerPhone || "",
      s.city || "",
      s.cep || "",
      items,
      s.subtotal.toFixed(2),
      s.giftWrap.toFixed(2),
      s.total.toFixed(2),
      (s.notes || "").replace(/\n/g, " "),
    ]
      .map((cell) => `"${String(cell).replace(/"/g, '""')}"`)
      .join(";");
  });
  return `\uFEFF${[header.join(";"), ...rows].join("\n")}`;
}

export function buildSale(input: {
  channel: SaleChannel;
  status?: SaleStatus;
  customerName: string;
  customerPhone?: string;
  city?: string;
  cep?: string;
  items: SaleItem[];
  giftWrap?: number;
  notes?: string;
  createdAt?: string;
}): Sale {
  const now = input.createdAt || new Date().toISOString();
  const subtotal = input.items.reduce(
    (sum, i) => sum + i.unitPrice * i.quantity,
    0
  );
  const giftWrap = input.giftWrap || 0;
  return {
    id: crypto.randomUUID(),
    createdAt: now,
    updatedAt: now,
    status: input.status || "iniciado",
    channel: input.channel,
    customerName: input.customerName,
    customerPhone: input.customerPhone,
    city: input.city,
    cep: input.cep,
    items: input.items,
    subtotal,
    giftWrap,
    total: subtotal + giftWrap,
    notes: input.notes,
  };
}
