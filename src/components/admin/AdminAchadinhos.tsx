"use client";

import { FormEvent, useEffect, useState } from "react";
import { Achadinho, AchadinhoChannel } from "@/lib/types";
import { formatPrice } from "@/lib/format";
import { Pencil, Plus, Trash2, Upload } from "lucide-react";

const empty = {
  name: "",
  price: "",
  image: "",
  marketplace: "collshop" as AchadinhoChannel,
  url: "",
  description: "",
};

export function AdminAchadinhos() {
  const [items, setItems] = useState<Achadinho[]>([]);
  const [editing, setEditing] = useState<Achadinho | null>(null);
  const [form, setForm] = useState(empty);
  const [saving, setSaving] = useState(false);

  const load = async () => {
    const res = await fetch("/api/achadinhos");
    setItems(await res.json());
  };

  useEffect(() => {
    load();
  }, []);

  const startEdit = (item: Achadinho) => {
    setEditing(item);
    setForm({
      name: item.name,
      price: String(item.price),
      image: item.image,
      marketplace: item.marketplace,
      url: item.url,
      description: item.description,
    });
  };

  const uploadImage = async (file: File) => {
    const fd = new FormData();
    fd.append("file", file);
    const res = await fetch("/api/upload", { method: "POST", body: fd });
    if (!res.ok) throw new Error("Falha no upload");
    const data = await res.json();
    setForm((f) => ({ ...f, image: data.url }));
  };

  const save = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const payload = {
      name: form.name,
      price: Number(form.price),
      image: form.image,
      marketplace: form.marketplace,
      url: form.url,
      description: form.description,
    };
    const url = editing ? `/api/achadinhos/${editing.id}` : "/api/achadinhos";
    const res = await fetch(url, {
      method: editing ? "PUT" : "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    setSaving(false);
    if (!res.ok) {
      alert("Erro ao salvar");
      return;
    }
    setEditing(null);
    setForm(empty);
    load();
  };

  const remove = async (id: string) => {
    if (!confirm("Remover este achadinho?")) return;
    await fetch(`/api/achadinhos/${id}`, { method: "DELETE" });
    load();
  };

  return (
    <div>
      <p className="mb-6 text-sm text-elomiah-muted">
        Faça upload da foto de cada item (ou deixe vazio para card tipográfico).
        Pasta local:{" "}
        <code className="text-elomiah-green">public/images/achadinhos/</code>
      </p>
      <div className="grid gap-10 lg:grid-cols-5">
        <form
          onSubmit={save}
          className="space-y-4 border border-elomiah-green/10 bg-white/50 p-6 lg:col-span-2"
        >
          <h2 className="font-display text-2xl text-elomiah-green">
            {editing ? "Editar achadinho" : "Novo achadinho"}
          </h2>
          <label className="block">
            <span className="section-label">Nome</span>
            <input
              required
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
              className="input-admin"
            />
          </label>
          <label className="block">
            <span className="section-label">Preço (R$)</span>
            <input
              required
              type="number"
              step="0.01"
              value={form.price}
              onChange={(e) => setForm({ ...form, price: e.target.value })}
              className="input-admin"
            />
          </label>
          <label className="block">
            <span className="section-label">Marketplace</span>
            <select
              value={form.marketplace}
              onChange={(e) =>
                setForm({
                  ...form,
                  marketplace: e.target.value as AchadinhoChannel,
                })
              }
              className="input-admin"
            >
              <option value="collshop">Collshop</option>
              <option value="shopee">Shopee</option>
              <option value="amazon">Amazon</option>
              <option value="mercadolivre">Mercado Livre</option>
            </select>
          </label>
          <label className="block">
            <span className="section-label">Link do produto</span>
            <input
              value={form.url}
              onChange={(e) => setForm({ ...form, url: e.target.value })}
              className="input-admin"
            />
          </label>
          <label className="block">
            <span className="section-label">Descrição</span>
            <textarea
              rows={3}
              value={form.description}
              onChange={(e) =>
                setForm({ ...form, description: e.target.value })
              }
              className="input-admin"
            />
          </label>
          <label className="block">
            <span className="section-label">Imagem (URL)</span>
            <input
              value={form.image}
              onChange={(e) => setForm({ ...form, image: e.target.value })}
              className="input-admin"
            />
            <span className="mt-2 inline-flex cursor-pointer items-center gap-2 text-xs text-elomiah-green">
              <Upload size={14} />
              Upload
              <input
                type="file"
                accept="image/*"
                className="hidden"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file) uploadImage(file);
                }}
              />
            </span>
          </label>
          <div className="flex gap-2">
            <button
              type="submit"
              disabled={saving}
              className="btn-primary flex-1"
            >
              {saving ? "Salvando…" : editing ? "Atualizar" : "Criar"}
            </button>
            {editing && (
              <button
                type="button"
                onClick={() => {
                  setEditing(null);
                  setForm(empty);
                }}
                className="btn-outline"
              >
                <Plus size={16} /> Novo
              </button>
            )}
          </div>
        </form>

        <div className="space-y-3 lg:col-span-3">
          {items.map((item) => (
            <div
              key={item.id}
              className="flex flex-wrap items-center justify-between gap-3 border border-elomiah-green/10 bg-white/40 px-4 py-4"
            >
              <div>
                <p className="font-display text-xl text-elomiah-green">
                  {item.name}
                </p>
                <p className="text-xs text-elomiah-muted">
                  {formatPrice(item.price)} · {item.marketplace}
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => startEdit(item)}
                  className="p-2 text-elomiah-green hover:text-elomiah-gold"
                  aria-label="Editar"
                >
                  <Pencil size={16} />
                </button>
                <button
                  type="button"
                  onClick={() => remove(item.id)}
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
    </div>
  );
}
