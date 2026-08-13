/** Eventos GA4 (só disparam se gtag existir após consentimento) */

type GtagFn = (...args: unknown[]) => void;

function gtag(): GtagFn | undefined {
  if (typeof window === "undefined") return undefined;
  return (window as Window & { gtag?: GtagFn }).gtag;
}

export function trackEvent(
  name: string,
  params?: Record<string, string | number | boolean>
) {
  const fn = gtag();
  if (!fn) return;
  fn("event", name, params);
}

export function trackAddToCart(product: {
  id: string;
  name: string;
  price: number;
  quantity: number;
}) {
  trackEvent("add_to_cart", {
    currency: "BRL",
    value: product.price * product.quantity,
    item_id: product.id,
    item_name: product.name,
    quantity: product.quantity,
  });
}

export function trackBeginCheckout(value: number, items: number) {
  trackEvent("begin_checkout", {
    currency: "BRL",
    value,
    items,
  });
}

export function trackBuyNow(product: {
  id: string;
  name: string;
  price: number;
  quantity: number;
}) {
  trackEvent("buy_now_whatsapp", {
    currency: "BRL",
    value: product.price * product.quantity,
    item_id: product.id,
    item_name: product.name,
    quantity: product.quantity,
  });
}
