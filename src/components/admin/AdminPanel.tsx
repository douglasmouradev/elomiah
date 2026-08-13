"use client";

import { FormEvent, useEffect, useState } from "react";
import { Product, ProductCategory, Marketplace, CATEGORY_LABELS } from "@/lib/types";
import { formatPrice } from "@/lib/format";
import { Pencil, Plus, Trash2, Upload, LogOut } from "lucide-react";
import { AdminAchadinhos } from "@/components/admin/AdminAchadinhos";
import { AdminTestimonials } from "@/components/admin/AdminTestimonials";
import { AdminSetupStatus } from "@/components/admin/AdminSetupStatus";
import { AdminSales } from "@/components/admin/AdminSales";
import { AdminWaitlist } from "@/components/admin/AdminWaitlist";
import { cn } from "@/lib/utils";

const emptyForm = {
  name: "",
  aroma: "",
  collection: "Coleção Refúgio",
  accentColor: "#C9A24B",
  quote: "",
  category: "spray-ambiente" as ProductCategory,
  price: "",
  stock: "",
  description: "",
  shortDescription: "",
  top: "",
  heart: "",
  base: "",
  usage: "",
  precautions: "",
  technical: "",
  images: "" as string,
  benefits: "" as string,
  featured: false,
  marketplace: "interno" as Marketplace,
  marketplaceUrl: "",
};

export function AdminPanel() {
  const [auth, setAuth] = useState<boolean | null>(null);
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [products, setProducts] = useState<Product[]>([]);
  const [editing, setEditing] = useState<Product | null>(null);
  const [form, setForm] = useState(emptyForm);
  const [saving, setSaving] = useState(false);
  const [tab, setTab] = useState<
    "vendas" | "avise-me" | "produtos" | "achadinhos" | "depoimentos"
  >("vendas");

  useEffect(() => {
    const run = async () => {
      const res = await fetch("/api/auth");
      const data = await res.json();
      setAuth(data.authenticated);
      if (data.authenticated) {
        const prodRes = await fetch("/api/products");
        setProducts(await prodRes.json());
      }
    };
    run();
  }, []);

  const loadProducts = async () => {
    const res = await fetch("/api/products");
    setProducts(await res.json());
  };

  const login = async (e: FormEvent) => {
    e.preventDefault();
    setError("");
    const res = await fetch("/api/auth", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ password }),
    });
    if (!res.ok) {
      setError("Senha incorreta");
      return;
    }
    setAuth(true);
    loadProducts();
  };

  const logout = async () => {
    await fetch("/api/auth", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "logout" }),
    });
    setAuth(false);
  };

  const startEdit = (p: Product) => {
    setEditing(p);
    setForm({
      name: p.name,
      aroma: p.aroma,
      collection: p.collection,
      accentColor: p.accentColor,
      quote: p.quote,
      category: p.category,
      price: String(p.price),
      stock: String(p.stock),
      description: p.description,
      shortDescription: p.shortDescription,
      top: p.notes.top,
      heart: p.notes.heart,
      base: p.notes.base,
      usage: p.usage,
      precautions: p.precautions,
      technical: p.technical,
      images: p.images.join("\n"),
      benefits: (p.benefits || []).join("\n"),
      featured: p.featured,
      marketplace: p.marketplace,
      marketplaceUrl: p.marketplaceUrl || "",
    });
  };

  const startCreate = () => {
    setEditing(null);
    setForm(emptyForm);
  };

  const uploadImage = async (file: File) => {
    const fd = new FormData();
    fd.append("file", file);
    const res = await fetch("/api/upload", { method: "POST", body: fd });
    if (!res.ok) throw new Error("Falha no upload");
    const data = await res.json();
    setForm((f) => ({
      ...f,
      images: f.images ? `${f.images}\n${data.url}` : data.url,
    }));
  };

  const save = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const payload = {
      name: form.name,
      aroma: form.aroma,
      collection: form.collection,
      accentColor: form.accentColor,
      quote: form.quote,
      category: form.category,
      price: Number(form.price),
      stock: Number(form.stock),
      description: form.description,
      shortDescription: form.shortDescription,
      notes: { top: form.top, heart: form.heart, base: form.base },
      usage: form.usage,
      precautions: form.precautions,
      technical: form.technical,
      images: form.images
        .split("\n")
        .map((s) => s.trim())
        .filter(Boolean),
      benefits: form.benefits
        .split("\n")
        .map((s) => s.trim())
        .filter(Boolean),
      featured: form.featured,
      marketplace: form.marketplace,
      marketplaceUrl: form.marketplaceUrl || undefined,
    };

    const url = editing ? `/api/products/${editing.id}` : "/api/products";
    const method = editing ? "PUT" : "POST";
    const res = await fetch(url, {
      method,
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    setSaving(false);
    if (!res.ok) {
      alert("Erro ao salvar");
      return;
    }
    startCreate();
    loadProducts();
  };

  const remove = async (id: string) => {
    if (!confirm("Remover este produto?")) return;
    await fetch(`/api/products/${id}`, { method: "DELETE" });
    loadProducts();
  };

  if (auth === null) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-elomiah-cream">
        <p className="text-elomiah-muted">Carregando…</p>
      </div>
    );
  }

  if (!auth) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-elomiah-cream px-6">
        <form
          onSubmit={login}
          className="w-full max-w-sm border border-elomiah-green/10 bg-white/40 p-8 shadow-soft"
        >
          <p className="font-display text-3xl text-elomiah-green">Elomiah</p>
          <p className="mt-1 text-xs tracking-soft text-elomiah-gold">
            Painel administrativo
          </p>
          <label className="mt-8 block text-[11px] uppercase tracking-soft text-elomiah-gold">
            Senha
          </label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="mt-2 w-full border-b border-elomiah-green/20 bg-transparent py-2 outline-none focus:border-elomiah-gold"
            required
          />
          {error && <p className="mt-3 text-sm text-red-700">{error}</p>}
          <button type="submit" className="btn-primary mt-8 w-full">
            Entrar
          </button>
        </form>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-elomiah-cream px-4 py-10 md:px-8">
      <div className="mx-auto max-w-6xl">
        <div className="mb-10 flex flex-wrap items-center justify-between gap-4">
          <div>
            <h1 className="font-display text-4xl text-elomiah-green">Admin</h1>
            <p className="text-sm text-elomiah-muted">
              Vendas, avise-me, produtos, achadinhos e depoimentos
            </p>
          </div>
          <div className="flex gap-3">
            {tab === "produtos" && (
              <button type="button" onClick={startCreate} className="btn-outline">
                <Plus size={16} /> Novo
              </button>
            )}
            <button type="button" onClick={logout} className="btn-outline">
              <LogOut size={16} /> Sair
            </button>
          </div>
        </div>

        <AdminSetupStatus />

        <div className="mb-10 flex gap-6 border-b border-elomiah-green/10">
          {(
            [
              ["vendas", "Vendas"],
              ["avise-me", "Avise-me"],
              ["produtos", "Produtos"],
              ["achadinhos", "Achadinhos"],
              ["depoimentos", "Depoimentos"],
            ] as const
          ).map(([id, label]) => (
            <button
              key={id}
              type="button"
              onClick={() => setTab(id)}
              className={cn("chip pb-3", tab === id && "chip-active")}
            >
              {label}
            </button>
          ))}
        </div>

        {tab === "vendas" && <AdminSales />}
        {tab === "avise-me" && <AdminWaitlist />}
        {tab === "achadinhos" && <AdminAchadinhos />}
        {tab === "depoimentos" && <AdminTestimonials />}
        {tab === "produtos" && (
        <div className="grid gap-10 lg:grid-cols-5">
          <form
            onSubmit={save}
            className="space-y-4 border border-elomiah-green/10 bg-white/50 p-6 lg:col-span-2"
          >
            <h2 className="font-display text-2xl text-elomiah-green">
              {editing ? "Editar produto" : "Novo produto"}
            </h2>

            <Field label="Nome">
              <input
                required
                value={form.name}
                onChange={(e) => setForm({ ...form, name: e.target.value })}
                className="input-admin"
              />
            </Field>

            <div className="grid grid-cols-2 gap-3">
              <Field label="Aroma">
                <input
                  value={form.aroma}
                  onChange={(e) => setForm({ ...form, aroma: e.target.value })}
                  className="input-admin"
                />
              </Field>
              <Field label="Coleção">
                <input
                  value={form.collection}
                  onChange={(e) =>
                    setForm({ ...form, collection: e.target.value })
                  }
                  className="input-admin"
                />
              </Field>
            </div>

            <Field label="Citação / frase do rótulo">
              <textarea
                rows={2}
                value={form.quote}
                onChange={(e) => setForm({ ...form, quote: e.target.value })}
                className="input-admin"
              />
            </Field>

            <Field label="Cor de destaque (hex)">
              <input
                value={form.accentColor}
                onChange={(e) =>
                  setForm({ ...form, accentColor: e.target.value })
                }
                className="input-admin"
                placeholder="#C9A24B"
              />
            </Field>

            <div className="grid grid-cols-2 gap-3">
              <Field label="Categoria">
                <select
                  value={form.category}
                  onChange={(e) =>
                    setForm({
                      ...form,
                      category: e.target.value as ProductCategory,
                    })
                  }
                  className="input-admin"
                >
                  {(Object.keys(CATEGORY_LABELS) as ProductCategory[]).map(
                    (k) => (
                      <option key={k} value={k}>
                        {CATEGORY_LABELS[k]}
                      </option>
                    )
                  )}
                </select>
              </Field>
              <Field label="Destaque">
                <label className="flex items-center gap-2 py-2 text-sm">
                  <input
                    type="checkbox"
                    checked={form.featured}
                    onChange={(e) =>
                      setForm({ ...form, featured: e.target.checked })
                    }
                  />
                  Exibir na home
                </label>
              </Field>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <Field label="Preço (R$)">
                <input
                  required
                  type="number"
                  step="0.01"
                  value={form.price}
                  onChange={(e) => setForm({ ...form, price: e.target.value })}
                  className="input-admin"
                />
              </Field>
              <Field label="Estoque">
                <input
                  required
                  type="number"
                  value={form.stock}
                  onChange={(e) => setForm({ ...form, stock: e.target.value })}
                  className="input-admin"
                />
              </Field>
            </div>

            <Field label="Resumo curto">
              <input
                value={form.shortDescription}
                onChange={(e) =>
                  setForm({ ...form, shortDescription: e.target.value })
                }
                className="input-admin"
              />
            </Field>

            <Field label="Descrição">
              <textarea
                rows={3}
                value={form.description}
                onChange={(e) =>
                  setForm({ ...form, description: e.target.value })
                }
                className="input-admin"
              />
            </Field>

            <Field label="Notas — topo / coração / fundo">
              <input
                placeholder="Topo"
                value={form.top}
                onChange={(e) => setForm({ ...form, top: e.target.value })}
                className="input-admin mb-2"
              />
              <input
                placeholder="Coração"
                value={form.heart}
                onChange={(e) => setForm({ ...form, heart: e.target.value })}
                className="input-admin mb-2"
              />
              <input
                placeholder="Fundo"
                value={form.base}
                onChange={(e) => setForm({ ...form, base: e.target.value })}
                className="input-admin"
              />
            </Field>

            <Field label="Modo de uso">
              <textarea
                rows={2}
                value={form.usage}
                onChange={(e) => setForm({ ...form, usage: e.target.value })}
                className="input-admin"
              />
            </Field>

            <Field label="Precauções">
              <textarea
                rows={2}
                value={form.precautions}
                onChange={(e) =>
                  setForm({ ...form, precautions: e.target.value })
                }
                className="input-admin"
              />
            </Field>

            <Field label="Ficha técnica">
              <input
                value={form.technical}
                onChange={(e) =>
                  setForm({ ...form, technical: e.target.value })
                }
                className="input-admin"
              />
            </Field>

            <Field label="Benefícios (um por linha)">
              <textarea
                rows={3}
                value={form.benefits}
                onChange={(e) => setForm({ ...form, benefits: e.target.value })}
                className="input-admin"
              />
            </Field>

            <Field label="Canal de venda">
              <select
                value={form.marketplace}
                onChange={(e) =>
                  setForm({
                    ...form,
                    marketplace: e.target.value as Marketplace,
                  })
                }
                className="input-admin"
              >
                <option value="interno">Carrinho interno</option>
                <option value="shopee">Shopee</option>
                <option value="amazon">Amazon</option>
                <option value="mercadolivre">Mercado Livre</option>
              </select>
            </Field>

            {form.marketplace !== "interno" && (
              <Field label="Link do marketplace">
                <input
                  value={form.marketplaceUrl}
                  onChange={(e) =>
                    setForm({ ...form, marketplaceUrl: e.target.value })
                  }
                  className="input-admin"
                  placeholder="https://..."
                />
              </Field>
            )}

            <Field label="Imagens (URL, uma por linha)">
              <textarea
                rows={3}
                value={form.images}
                onChange={(e) => setForm({ ...form, images: e.target.value })}
                className="input-admin"
              />
              <label className="mt-2 inline-flex cursor-pointer items-center gap-2 text-xs text-elomiah-green">
                <Upload size={14} />
                Upload de imagem
                <input
                  type="file"
                  accept="image/*"
                  className="hidden"
                  onChange={(e) => {
                    const file = e.target.files?.[0];
                    if (file) uploadImage(file);
                  }}
                />
              </label>
            </Field>

            <button type="submit" disabled={saving} className="btn-primary w-full">
              {saving ? "Salvando…" : editing ? "Atualizar" : "Criar produto"}
            </button>
          </form>

          <div className="space-y-3 lg:col-span-3">
            {products.map((p) => (
              <div
                key={p.id}
                className="flex flex-wrap items-center justify-between gap-3 border border-elomiah-green/10 bg-white/40 px-4 py-4"
              >
                <div>
                  <p className="font-display text-xl text-elomiah-green">
                    {p.name}
                  </p>
                  <p className="text-xs text-elomiah-muted">
                    {CATEGORY_LABELS[p.category]} · {formatPrice(p.price)} ·
                    estoque {p.stock}
                    {p.featured ? " · destaque" : ""} · {p.marketplace}
                  </p>
                </div>
                <div className="flex gap-2">
                  <button
                    type="button"
                    onClick={() => startEdit(p)}
                    className="p-2 text-elomiah-green hover:text-elomiah-gold"
                    aria-label="Editar"
                  >
                    <Pencil size={16} />
                  </button>
                  <button
                    type="button"
                    onClick={() => remove(p.id)}
                    className="p-2 text-elomiah-muted hover:text-red-700"
                    aria-label="Remover"
                  >
                    <Trash2 size={16} />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
        )}
      </div>
    </div>
  );
}

function Field({
  label,
  children,
}: {
  label: string;
  children: React.ReactNode;
}) {
  return (
    <div>
      <label className="text-[11px] uppercase tracking-soft text-elomiah-gold">
        {label}
      </label>
      {children}
    </div>
  );
}
