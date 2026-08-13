"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect, useState } from "react";
import { Product } from "@/lib/types";
import { formatPrice } from "@/lib/format";
import { useCartStore } from "@/store/cart";
import { ProductCard } from "@/components/ui/ProductCard";
import { ExternalLink, Minus, Plus, ShoppingBag } from "lucide-react";
import { isConfiguredWhatsApp, whatsappLink } from "@/lib/site";
import { trackAddToCart, trackBuyNow } from "@/lib/analytics";
import { NotifyWhenAvailable } from "@/components/produtos/NotifyWhenAvailable";

export function ProductDetail({
  product,
  related,
}: {
  product: Product;
  related: Product[];
}) {
  const [active, setActive] = useState(0);
  const [zoom, setZoom] = useState({ x: 50, y: 50, on: false });
  const [qty, setQty] = useState(1);
  const [added, setAdded] = useState(false);
  const addItem = useCartStore((s) => s.addItem);
  const inCart = useCartStore(
    (s) => s.items.find((i) => i.product.id === product.id)?.quantity ?? 0
  );

  const isExternal =
    product.marketplace !== "interno" && Boolean(product.marketplaceUrl);
  const remaining = Math.max(0, product.stock - inCart);
  const outOfStock = remaining <= 0;

  useEffect(() => {
    setQty((n) => Math.min(Math.max(1, n), Math.max(1, remaining)));
  }, [remaining]);

  const handleBuy = () => {
    if (isExternal && product.marketplaceUrl) {
      window.open(product.marketplaceUrl, "_blank", "noopener,noreferrer");
      return;
    }
    if (outOfStock) return;
    const ok = addItem(product, qty);
    if (ok) {
      trackAddToCart({
        id: product.id,
        name: product.name,
        price: product.price,
        quantity: qty,
      });
      setAdded(true);
      setQty(1);
      window.setTimeout(() => setAdded(false), 2500);
    }
  };

  const handleBuyNow = async () => {
    if (isExternal || outOfStock || !isConfiguredWhatsApp()) return;
    trackBuyNow({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: qty,
    });
    try {
      await fetch("/api/sales", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          channel: "whatsapp",
          customerName: "Compra rápida (WhatsApp)",
          items: [
            {
              productId: product.id,
              productName: product.name,
              quantity: qty,
              unitPrice: product.price,
            },
          ],
        }),
      });
    } catch {
      // segue para o WhatsApp mesmo se o registro falhar
    }
    const total = formatPrice(product.price * qty);
    const message = [
      `Olá! Quero comprar agora:`,
      ``,
      `• ${product.name} (Aroma ${product.aroma}) x${qty} — ${total}`,
      ``,
      `Aguardo orientação de frete e pagamento.`,
    ].join("\n");
    window.open(whatsappLink(message), "_blank", "noopener,noreferrer");
  };

  const currentImage = product.images[active] || product.images[0];
  const isThumb = currentImage.includes("/thumbs/");

  return (
    <div className="section-padding overflow-x-hidden pt-32">
      <div className="container-wide">
          <nav
            aria-label="Navegação estrutural"
            className="mb-10 flex flex-wrap items-center gap-2 text-sm text-elomiah-muted"
          >
            <Link href="/" className="transition-colors hover:text-elomiah-green">
              Início
            </Link>
            <span aria-hidden>/</span>
            <Link href="/loja" className="transition-colors hover:text-elomiah-green">
              Loja
            </Link>
            <span aria-hidden>/</span>
            <span className="text-elomiah-green">{product.name}</span>
          </nav>

        <div className="grid gap-14 lg:grid-cols-2 lg:gap-20">
          <div className="min-w-0">
            <div
              className="relative aspect-[3/4] w-full overflow-hidden bg-elomiah-surface shadow-soft"
              onMouseMove={(e) => {
                const rect = e.currentTarget.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                setZoom({ x, y, on: true });
              }}
              onMouseLeave={() => setZoom((z) => ({ ...z, on: false }))}
              onClick={(e) => {
                if (window.matchMedia("(hover: hover)").matches) return;
                const rect = e.currentTarget.getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;
                setZoom((z) => ({ x, y, on: !z.on }));
              }}
            >
              <Image
                src={currentImage}
                alt={product.name}
                fill
                priority
                sizes="(max-width: 1024px) 100vw, 50vw"
                className={
                  isThumb
                    ? "object-contain object-center transition-transform duration-300"
                    : "object-contain p-3 transition-transform duration-300"
                }
                style={
                  zoom.on
                    ? {
                        transform: "scale(1.18)",
                        transformOrigin: `${zoom.x}% ${zoom.y}%`,
                      }
                    : undefined
                }
              />
            </div>
            {product.images.length > 1 && (
              <div className="mt-4 flex gap-3 overflow-x-auto">
                {product.images.map((img, i) => (
                  <button
                    key={img}
                    type="button"
                    onClick={() => setActive(i)}
                    className={`relative h-20 w-16 shrink-0 overflow-hidden border bg-elomiah-cream ${
                      active === i
                        ? "border-elomiah-gold"
                        : "border-elomiah-green/15"
                    }`}
                  >
                    <Image
                      src={img}
                      alt={`${product.name} — imagem ${i + 1}`}
                      fill
                      className="object-cover object-left"
                      sizes="64px"
                    />
                  </button>
                ))}
              </div>
            )}
          </div>

          <div className="min-w-0">
            <p className="section-label">{product.collection}</p>
            <h1 className="heading-display mt-4 text-4xl md:text-5xl lg:text-[3.25rem]">
              {product.name}
            </h1>
            <p
              className="mt-4 text-sm tracking-wide"
              style={{ color: product.accentColor }}
            >
              Aroma {product.aroma}
            </p>
            <div className="gold-rule mt-6" />
            <blockquote className="mt-8 font-display text-xl italic leading-relaxed text-elomiah-green/90 md:text-2xl">
              &ldquo;{product.quote}&rdquo;
            </blockquote>
            <p className="mt-8 font-display text-3xl text-elomiah-green">
              {formatPrice(product.price)}
            </p>
            <p
              className={`mt-2 text-sm ${outOfStock ? "text-red-700" : "text-elomiah-muted"}`}
            >
              {outOfStock
                ? inCart > 0
                  ? "Quantidade máxima já está no carrinho"
                  : "Indisponível no momento"
                : remaining <= 5
                  ? `Últimas ${remaining} unidades`
                  : "Disponível para envio"}
            </p>
            <p className="mt-6 leading-relaxed text-elomiah-muted">
              {product.description}
            </p>

            <div className="mt-12 space-y-6 border-y border-elomiah-green/[0.08] py-10">
              <p className="section-label">Pirâmide olfativa</p>
              <NotesBlock title="Notas de saída" value={product.notes.top} />
              <NotesBlock title="Notas de corpo" value={product.notes.heart} />
              <NotesBlock title="Notas de fundo" value={product.notes.base} />
            </div>

            {product.benefits && product.benefits.length > 0 && (
              <div className="mt-8 flex flex-wrap gap-x-6 gap-y-3 border-y border-elomiah-green/[0.08] py-5 text-sm text-elomiah-muted">
                {product.benefits.map((b) => (
                  <span key={b} className="leading-snug">
                    {b}
                  </span>
                ))}
              </div>
            )}

            <div className="mt-10 space-y-6 text-sm">
              <div>
                <p className="section-label">Modo de usar</p>
                <p className="mt-2 text-elomiah-muted">{product.usage}</p>
              </div>
              <div>
                <p className="section-label">Precauções</p>
                <p className="mt-2 text-elomiah-muted">{product.precautions}</p>
              </div>
              <div>
                <p className="section-label">Ficha técnica</p>
                <p className="mt-2 text-elomiah-muted">{product.technical}</p>
              </div>
            </div>

            <div className="mt-10 flex flex-wrap items-center gap-4">
              {!isExternal && !outOfStock && (
                <div className="flex items-center border border-elomiah-green/15">
                  <button
                    type="button"
                    aria-label="Diminuir quantidade"
                    onClick={() => setQty((n) => Math.max(1, n - 1))}
                    className="flex h-12 w-12 items-center justify-center text-elomiah-muted transition-colors hover:text-elomiah-green"
                  >
                    <Minus size={16} strokeWidth={1.25} />
                  </button>
                  <span className="min-w-8 text-center text-sm tabular-nums">
                    {qty}
                  </span>
                  <button
                    type="button"
                    aria-label="Aumentar quantidade"
                    disabled={qty >= remaining}
                    onClick={() => setQty((n) => Math.min(remaining, n + 1))}
                    className="flex h-12 w-12 items-center justify-center text-elomiah-muted transition-colors hover:text-elomiah-green disabled:opacity-30"
                  >
                    <Plus size={16} strokeWidth={1.25} />
                  </button>
                </div>
              )}
              <button
                type="button"
                onClick={handleBuy}
                disabled={!isExternal && outOfStock}
                className="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
              >
                {isExternal ? (
                  <>
                    <ExternalLink size={16} />
                    Adquirir no marketplace
                  </>
                ) : outOfStock ? (
                  "Esgotado"
                ) : (
                  <>
                    <ShoppingBag size={16} />
                    Adicionar ao ritual
                  </>
                )}
              </button>
              {!isExternal && !outOfStock && isConfiguredWhatsApp() && (
                <button
                  type="button"
                  onClick={handleBuyNow}
                  className="btn-outline"
                >
                  Comprar agora
                </button>
              )}
            </div>
            {added && (
              <p className="mt-3 text-sm text-elomiah-green" role="status">
                Adicionado ao carrinho ✓
              </p>
            )}
            {!isExternal && outOfStock && (
              <NotifyWhenAvailable
                productSlug={product.slug}
                productName={product.name}
              />
            )}
          </div>
        </div>

        {related.length > 0 && (
          <div className="mt-28">
            <h2 className="heading-display text-3xl">Outras essências da coleção</h2>
            <div className="gold-rule mt-6" />
            <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
              {related.map((p, i) => (
                <ProductCard key={p.id} product={p} index={i} />
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}

function NotesBlock({ title, value }: { title: string; value: string }) {
  return (
    <div>
      <p className="section-label">{title}</p>
      <p className="mt-2 font-display text-xl text-elomiah-green">{value}</p>
    </div>
  );
}
