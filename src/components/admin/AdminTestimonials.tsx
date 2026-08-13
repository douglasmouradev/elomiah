"use client";

import { FormEvent, useEffect, useState } from "react";
import { Testimonial } from "@/lib/types";
import { Pencil, Plus, Trash2, Upload } from "lucide-react";

const empty = {
  name: "",
  photo: "",
  rating: "5",
  text: "",
  role: "",
};

export function AdminTestimonials() {
  const [items, setItems] = useState<Testimonial[]>([]);
  const [editing, setEditing] = useState<Testimonial | null>(null);
  const [form, setForm] = useState(empty);
  const [saving, setSaving] = useState(false);

  const load = async () => {
    const res = await fetch("/api/testimonials");
    setItems(await res.json());
  };

  useEffect(() => {
    load();
  }, []);

  const startEdit = (item: Testimonial) => {
    setEditing(item);
    setForm({
      name: item.name,
      photo: item.photo,
      rating: String(item.rating),
      text: item.text,
      role: item.role || "",
    });
  };

  const uploadImage = async (file: File) => {
    const fd = new FormData();
    fd.append("file", file);
    const res = await fetch("/api/upload", { method: "POST", body: fd });
    if (!res.ok) throw new Error("Falha no upload");
    const data = await res.json();
    setForm((f) => ({ ...f, photo: data.url }));
  };

  const save = async (e: FormEvent) => {
    e.preventDefault();
    setSaving(true);
    const payload = {
      name: form.name,
      photo: form.photo,
      rating: Number(form.rating),
      text: form.text,
      role: form.role || undefined,
    };
    const url = editing
      ? `/api/testimonials/${editing.id}`
      : "/api/testimonials";
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
    if (!confirm("Remover este depoimento?")) return;
    await fetch(`/api/testimonials/${id}`, { method: "DELETE" });
    load();
  };

  return (
    <div className="grid gap-10 lg:grid-cols-5">
      <form
        onSubmit={save}
        className="space-y-4 border border-elomiah-green/10 bg-white/50 p-6 lg:col-span-2"
      >
        <h2 className="font-display text-2xl text-elomiah-green">
          {editing ? "Editar depoimento" : "Novo depoimento"}
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
          <span className="section-label">Papel / contexto</span>
          <input
            value={form.role}
            onChange={(e) => setForm({ ...form, role: e.target.value })}
            className="input-admin"
          />
        </label>
        <label className="block">
          <span className="section-label">Nota</span>
          <select
            value={form.rating}
            onChange={(e) => setForm({ ...form, rating: e.target.value })}
            className="input-admin"
          >
            {[5, 4, 3, 2, 1].map((n) => (
              <option key={n} value={n}>
                {n}
              </option>
            ))}
          </select>
        </label>
        <label className="block">
          <span className="section-label">Depoimento</span>
          <textarea
            required
            rows={4}
            value={form.text}
            onChange={(e) => setForm({ ...form, text: e.target.value })}
            className="input-admin"
          />
        </label>
        <label className="block">
          <span className="section-label">Foto (URL)</span>
          <input
            value={form.photo}
            onChange={(e) => setForm({ ...form, photo: e.target.value })}
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
          <button type="submit" disabled={saving} className="btn-primary flex-1">
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
              <p className="font-display text-xl text-elomiah-green">{item.name}</p>
              <p className="line-clamp-2 text-xs text-elomiah-muted">{item.text}</p>
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
  );
}
