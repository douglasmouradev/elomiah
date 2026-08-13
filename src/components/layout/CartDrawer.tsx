"use client";

import Image from "next/image";
import { X, Minus, Plus } from "lucide-react";
import { useCartStore } from "@/store/cart";
import { formatPrice } from "@/lib/format";
import { whatsappLink, isConfiguredWhatsApp } from "@/lib/site";
import { estimateFreight, formatFreightRange, GIFT_WRAP_PRICE } from "@/lib/freight";
import { trackBeginCheckout } from "@/lib/analytics";
import { AnimatePresence, motion } from "framer-motion";
import { FormEvent, useEffect, useId, useRef, useState } from "react";
import type { CartItem } from "@/lib/types";

const CHECKOUT_KEY = "elomiah-checkout";

type CheckoutForm = {
  name: string;
  phone: string;
  cep: string;
  street: string;
  number: string;
  neighborhood: string;
  city: string;
  notes: string;
  giftWrap: boolean;
  giftMessage: string;
};

const emptyCheckout = (): CheckoutForm => ({
  name: "",
  phone: "",
  cep: "",
  street: "",
  number: "",
  neighborhood: "",
  city: "",
  notes: "",
  giftWrap: false,
  giftMessage: "",
});

function loadCheckout(): CheckoutForm {
  if (typeof window === "undefined") return emptyCheckout();
  try {
    const raw = window.localStorage.getItem(CHECKOUT_KEY);
    if (!raw) return emptyCheckout();
    const parsed = JSON.parse(raw) as Partial<CheckoutForm>;
    return {
      ...emptyCheckout(),
      name: parsed.name || "",
      phone: parsed.phone || "",
      cep: parsed.cep || "",
      street: parsed.street || "",
      number: parsed.number || "",
      neighborhood: parsed.neighborhood || "",
      city: parsed.city || "",
      notes: parsed.notes || "",
      giftWrap: Boolean(parsed.giftWrap),
      giftMessage: parsed.giftMessage || "",
    };
  } catch {
    return emptyCheckout();
  }
}

function maskCep(value: string) {
  const digits = value.replace(/\D/g, "").slice(0, 8);
  if (digits.length <= 5) return digits;
  return `${digits.slice(0, 5)}-${digits.slice(5)}`;
}

function maskPhone(value: string) {
  const digits = value.replace(/\D/g, "").slice(0, 11);
  if (digits.length <= 2) return digits;
  if (digits.length <= 7) {
    return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
  }
  return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
}

function buildMessage(items: CartItem[], total: string, form: CheckoutForm) {
  const addressParts = [
    form.street,
    form.number ? `nº ${form.number}` : null,
    form.neighborhood,
  ].filter(Boolean);
  const freight = estimateFreight(form.cep);
  const giftLine = form.giftWrap
    ? `• Embalagem presente — ${formatPrice(GIFT_WRAP_PRICE)}`
    : null;
  const lines = [
    `Olá! Gostaria de finalizar meu pedido Elomiah.`,
    ``,
    `Nome: ${form.name}`,
    form.phone ? `WhatsApp: ${form.phone}` : null,
    `CEP: ${form.cep}`,
    addressParts.length ? `Endereço: ${addressParts.join(", ")}` : null,
    form.city ? `Cidade: ${form.city}` : null,
    freight ? `Frete estimado: ${formatFreightRange(freight)}` : null,
    ``,
    `Pedido:`,
    ...items.map(
      (i) =>
        `• ${i.product.name} x${i.quantity} — ${formatPrice(i.product.price * i.quantity)}`
    ),
    giftLine,
    `Total produtos${form.giftWrap ? " + presente" : ""}: ${total}`,
    form.giftWrap && form.giftMessage
      ? `Mensagem do presente: ${form.giftMessage}`
      : null,
    form.notes ? `\nObservação: ${form.notes}` : null,
  ];
  return lines.filter((line) => line !== null).join("\n");
}

export function CartDrawer() {
  const {
    items,
    isOpen,
    close,
    removeItem,
    updateQuantity,
    totalPrice,
    clear,
  } = useCartStore();
  const panelRef = useRef<HTMLElement>(null);
  const formId = useId();
  const [form, setForm] = useState<CheckoutForm>(emptyCheckout);
  const [checkoutSent, setCheckoutSent] = useState(false);
  const formRef = useRef(form);
  formRef.current = form;

  useEffect(() => {
    setForm(loadCheckout());
  }, []);

  useEffect(() => {
    if (!isOpen) return;
    try {
      setCheckoutSent(
        Boolean(window.sessionStorage.getItem("elomiah-checkout-sent"))
      );
    } catch {
      setCheckoutSent(false);
    }
  }, [isOpen]);

  useEffect(() => {
    if (!isOpen) return;
    const onKey = (e: KeyboardEvent) => {
      if (e.key === "Escape") close();
      if (e.key !== "Tab" || !panelRef.current) return;
      const focusable = panelRef.current.querySelectorAll<HTMLElement>(
        'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
      );
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    };
    window.addEventListener("keydown", onKey);
    window.setTimeout(() => {
      panelRef.current
        ?.querySelector<HTMLElement>("button[aria-label='Fechar carrinho']")
        ?.focus();
    }, 50);
    return () => window.removeEventListener("keydown", onKey);
  }, [isOpen, close]);

  const persist = (next: CheckoutForm) => {
    setForm(next);
    window.localStorage.setItem(CHECKOUT_KEY, JSON.stringify(next));
  };

  useEffect(() => {
    const digits = form.cep.replace(/\D/g, "");
    if (digits.length !== 8) return;
    let cancelled = false;
    fetch(`https://viacep.com.br/ws/${digits}/json/`)
      .then((res) => res.json())
      .then(
        (data: {
          erro?: boolean;
          localidade?: string;
          uf?: string;
          logradouro?: string;
          bairro?: string;
        }) => {
          if (cancelled || data.erro || !data.localidade) return;
          const current = formRef.current;
          const city = data.uf
            ? `${data.localidade} / ${data.uf}`
            : data.localidade;
          persist({
            ...current,
            city,
            street: data.logradouro || current.street,
            neighborhood: data.bairro || current.neighborhood,
          });
        }
      )
      .catch(() => undefined);
    return () => {
      cancelled = true;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form.cep]);

  const canCheckout =
    form.name.trim().length >= 2 &&
    form.phone.replace(/\D/g, "").length >= 10 &&
    form.cep.replace(/\D/g, "").length === 8 &&
    form.street.trim().length >= 2 &&
    form.number.trim().length >= 1 &&
    form.neighborhood.trim().length >= 2;

  const freight = estimateFreight(form.cep);
  const productsTotal = totalPrice();
  const checkoutTotal = productsTotal + (form.giftWrap ? GIFT_WRAP_PRICE : 0);

  const onCheckout = async (e: FormEvent) => {
    e.preventDefault();
    if (!canCheckout || !isConfiguredWhatsApp()) return;
    trackBeginCheckout(checkoutTotal, items.length);

    const payload = {
      channel: "whatsapp" as const,
      customerName: form.name.trim(),
      customerPhone: form.phone.trim() || undefined,
      city: form.city.trim() || undefined,
      cep: form.cep.trim() || undefined,
      giftWrap: form.giftWrap ? GIFT_WRAP_PRICE : 0,
      notes: [
        form.giftWrap && form.giftMessage
          ? `Presente: ${form.giftMessage}`
          : null,
        form.notes.trim() || null,
      ]
        .filter(Boolean)
        .join(" · ") || undefined,
      items: items.map((item) => ({
        productId: item.product.id,
        productName: item.product.name,
        quantity: item.quantity,
        unitPrice: item.product.price,
      })),
    };

    try {
      await fetch("/api/sales", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
    } catch {
      // Não bloqueia o WhatsApp se o registro falhar
    }

    const href = whatsappLink(
      buildMessage(items, formatPrice(checkoutTotal), form)
    );
    window.open(href, "_blank", "noopener,noreferrer");
    try {
      window.sessionStorage.setItem(
        "elomiah-checkout-sent",
        new Date().toISOString()
      );
    } catch {
      /* ignore */
    }
    setCheckoutSent(true);
    close();
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <>
          <motion.div
            className="fixed inset-0 z-[60] bg-elomiah-dark/40"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={close}
            aria-hidden
          />
          <motion.aside
            ref={panelRef}
            className="fixed right-0 top-0 z-[70] flex h-full w-full max-w-md flex-col bg-elomiah-cream shadow-lift"
            initial={{ x: "100%" }}
            animate={{ x: 0 }}
            exit={{ x: "100%" }}
            transition={{ type: "tween", duration: 0.35, ease: [0.22, 1, 0.36, 1] }}
            role="dialog"
            aria-modal="true"
            aria-labelledby={`${formId}-title`}
          >
            <div className="flex items-center justify-between border-b border-elomiah-green/[0.08] px-6 py-6">
              <h2 id={`${formId}-title`} className="heading-display text-2xl">
                Seu ritual
              </h2>
              <button
                type="button"
                onClick={close}
                aria-label="Fechar carrinho"
                className="text-elomiah-muted hover:text-elomiah-green"
              >
                <X size={20} />
              </button>
            </div>

            {checkoutSent && items.length > 0 && (
              <div className="border-b border-elomiah-green/[0.08] bg-elomiah-green/[0.03] px-6 py-3 text-sm text-elomiah-muted">
                Pedido enviado ao WhatsApp.{" "}
                <button
                  type="button"
                  className="text-elomiah-green underline-offset-2 hover:underline"
                  onClick={() => {
                    clear();
                    try {
                      window.sessionStorage.removeItem("elomiah-checkout-sent");
                    } catch {
                      /* ignore */
                    }
                    setCheckoutSent(false);
                  }}
                >
                  Esvaziar carrinho
                </button>
              </div>
            )}

            <div className="flex min-h-0 flex-1 flex-col overflow-y-auto">
              <div className="flex-1 px-6 py-6">
                {items.length === 0 ? (
                  <p className="text-sm text-elomiah-muted">
                    Seu carrinho está em silêncio. Explore as essências da loja.
                  </p>
                ) : (
                  <ul className="space-y-6">
                    {items.map(({ product, quantity }) => (
                      <li key={product.id} className="flex gap-4">
                        <div className="relative h-20 w-16 overflow-hidden bg-elomiah-green/5">
                          <Image
                            src={product.images[0]}
                            alt={product.name}
                            fill
                            className="object-cover"
                            sizes="64px"
                          />
                        </div>
                        <div className="flex-1">
                          <p className="font-display text-lg text-elomiah-green">
                            {product.name}
                          </p>
                          <p className="text-sm text-elomiah-gold">
                            {formatPrice(product.price)}
                          </p>
                          <div className="mt-2 flex items-center gap-3">
                            <button
                              type="button"
                              aria-label={`Diminuir quantidade de ${product.name}`}
                              onClick={() =>
                                updateQuantity(product.id, quantity - 1)
                              }
                              className="text-elomiah-muted"
                            >
                              <Minus size={14} />
                            </button>
                            <span className="text-sm">{quantity}</span>
                            <button
                              type="button"
                              aria-label={`Aumentar quantidade de ${product.name}`}
                              disabled={quantity >= product.stock}
                              onClick={() =>
                                updateQuantity(product.id, quantity + 1)
                              }
                              className="text-elomiah-muted disabled:opacity-40"
                            >
                              <Plus size={14} />
                            </button>
                            <button
                              type="button"
                              onClick={() => removeItem(product.id)}
                              className="ml-auto text-xs text-elomiah-muted underline"
                            >
                              Remover
                            </button>
                          </div>
                        </div>
                      </li>
                    ))}
                  </ul>
                )}
              </div>

              {items.length > 0 && (
                <form
                  onSubmit={onCheckout}
                  className="space-y-4 border-t border-elomiah-green/10 px-6 py-6"
                >
                  <div className="mb-1 flex justify-between font-display text-xl">
                    <span>Total</span>
                    <span className="text-elomiah-gold">
                      {formatPrice(checkoutTotal)}
                    </span>
                  </div>
                  {form.giftWrap && (
                    <p className="text-xs text-elomiah-muted">
                      Inclui embalagem presente ({formatPrice(GIFT_WRAP_PRICE)})
                    </p>
                  )}

                  <label className="block">
                    <span className="section-label">Nome</span>
                    <input
                      required
                      value={form.name}
                      onChange={(e) => persist({ ...form, name: e.target.value })}
                      className="input-brand mt-1"
                      autoComplete="name"
                    />
                  </label>
                  <label className="block">
                    <span className="section-label">WhatsApp</span>
                    <input
                      required
                      inputMode="tel"
                      value={form.phone}
                      onChange={(e) =>
                        persist({ ...form, phone: maskPhone(e.target.value) })
                      }
                      className="input-brand mt-1"
                      autoComplete="tel"
                      placeholder="(71) 90000-0000"
                    />
                  </label>
                  <div className="grid grid-cols-2 gap-4">
                    <label className="block">
                      <span className="section-label">CEP</span>
                      <input
                        required
                        inputMode="numeric"
                        value={form.cep}
                        onChange={(e) =>
                          persist({ ...form, cep: maskCep(e.target.value) })
                        }
                        className="input-brand mt-1"
                        autoComplete="postal-code"
                        placeholder="00000-000"
                      />
                    </label>
                    <label className="block">
                      <span className="section-label">Cidade</span>
                      <input
                        value={form.city}
                        onChange={(e) =>
                          persist({ ...form, city: e.target.value })
                        }
                        className="input-brand mt-1"
                        autoComplete="address-level2"
                      />
                    </label>
                  </div>
                  <label className="block">
                    <span className="section-label">Endereço</span>
                    <input
                      required
                      value={form.street}
                      onChange={(e) =>
                        persist({ ...form, street: e.target.value })
                      }
                      className="input-brand mt-1"
                      autoComplete="street-address"
                      placeholder="Rua, avenida…"
                    />
                  </label>
                  <div className="grid grid-cols-2 gap-4">
                    <label className="block">
                      <span className="section-label">Número</span>
                      <input
                        required
                        value={form.number}
                        onChange={(e) =>
                          persist({ ...form, number: e.target.value })
                        }
                        className="input-brand mt-1"
                        autoComplete="address-line2"
                        placeholder="Nº"
                      />
                    </label>
                    <label className="block">
                      <span className="section-label">Bairro</span>
                      <input
                        required
                        value={form.neighborhood}
                        onChange={(e) =>
                          persist({ ...form, neighborhood: e.target.value })
                        }
                        className="input-brand mt-1"
                        autoComplete="address-level3"
                      />
                    </label>
                  </div>
                  <label className="flex cursor-pointer items-start gap-3 border border-elomiah-green/10 px-3 py-3">
                    <input
                      type="checkbox"
                      checked={form.giftWrap}
                      onChange={(e) =>
                        persist({ ...form, giftWrap: e.target.checked })
                      }
                      className="mt-1"
                    />
                    <span>
                      <span className="block text-sm font-medium text-elomiah-green">
                        Embalagem para presente
                      </span>
                      <span className="mt-0.5 block text-xs text-elomiah-muted">
                        Laço e cartão · {formatPrice(GIFT_WRAP_PRICE)}
                      </span>
                    </span>
                  </label>
                  {form.giftWrap && (
                    <label className="block">
                      <span className="section-label">Mensagem do cartão</span>
                      <textarea
                        rows={2}
                        value={form.giftMessage}
                        onChange={(e) =>
                          persist({ ...form, giftMessage: e.target.value })
                        }
                        className="input-brand mt-1 resize-none"
                        placeholder="Opcional — dedicatória"
                      />
                    </label>
                  )}
                  <label className="block">
                    <span className="section-label">Observação</span>
                    <textarea
                      rows={2}
                      value={form.notes}
                      onChange={(e) =>
                        persist({ ...form, notes: e.target.value })
                      }
                      className="input-brand mt-1 resize-none"
                      placeholder="Opcional"
                    />
                  </label>

                  {isConfiguredWhatsApp() ? (
                    <button
                      type="submit"
                      disabled={!canCheckout}
                      className="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-50"
                    >
                      Finalizar pelo WhatsApp
                    </button>
                  ) : (
                    <p className="text-center text-sm text-elomiah-muted">
                      Configure NEXT_PUBLIC_WHATSAPP para finalizar pedidos.
                    </p>
                  )}
                  <p className="text-center text-xs leading-relaxed text-elomiah-muted">
                    {freight
                      ? `Frete estimado: ${formatFreightRange(freight)}. Valor final no WhatsApp · Pix ou cartão.`
                      : "Informe o CEP para estimar frete · Pagamento via Pix ou cartão · Confirmação no WhatsApp."}
                  </p>
                </form>
              )}
            </div>
          </motion.aside>
        </>
      )}
    </AnimatePresence>
  );
}
