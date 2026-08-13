/** Estimativa de frete — origem Salvador/BA (orientativa; confirmação no WhatsApp) */

export type FreightEstimate = {
  label: string;
  minDays: number;
  maxDays: number;
  priceFrom: number;
  priceTo: number;
};

export function estimateFreight(cep: string): FreightEstimate | null {
  const digits = cep.replace(/\D/g, "");
  if (digits.length < 5) return null;

  const prefix = Number(digits.slice(0, 2));
  const prefix3 = Number(digits.slice(0, 3));

  // Grande Salvador / RMS (CEPs 40xxx–42xxx típicos)
  if (prefix >= 40 && prefix <= 42) {
    return {
      label: "Grande Salvador",
      minDays: 1,
      maxDays: 3,
      priceFrom: 12,
      priceTo: 22,
    };
  }

  // Interior da Bahia (43–48)
  if (prefix >= 43 && prefix <= 48) {
    return {
      label: "Interior da Bahia",
      minDays: 2,
      maxDays: 5,
      priceFrom: 18,
      priceTo: 35,
    };
  }

  // Nordeste (exceto BA já coberta)
  if (
    (prefix >= 49 && prefix <= 65) ||
    (prefix3 >= 600 && prefix3 <= 639)
  ) {
    return {
      label: "Nordeste",
      minDays: 4,
      maxDays: 8,
      priceFrom: 28,
      priceTo: 48,
    };
  }

  // Sudeste
  if (
    (prefix >= 1 && prefix <= 19) ||
    (prefix >= 20 && prefix <= 28) ||
    (prefix >= 30 && prefix <= 39)
  ) {
    return {
      label: "Sudeste",
      minDays: 5,
      maxDays: 10,
      priceFrom: 32,
      priceTo: 58,
    };
  }

  // Sul
  if (prefix >= 80 && prefix <= 99) {
    return {
      label: "Sul",
      minDays: 6,
      maxDays: 12,
      priceFrom: 35,
      priceTo: 65,
    };
  }

  // Centro-Oeste / Norte / demais
  return {
    label: "Demais regiões",
    minDays: 7,
    maxDays: 15,
    priceFrom: 38,
    priceTo: 79,
  };
}

export function formatFreightRange(est: FreightEstimate) {
  const price =
    est.priceFrom === est.priceTo
      ? `R$ ${est.priceFrom.toFixed(2).replace(".", ",")}`
      : `R$ ${est.priceFrom.toFixed(2).replace(".", ",")} – R$ ${est.priceTo
          .toFixed(2)
          .replace(".", ",")}`;
  return `${price} · ${est.minDays}–${est.maxDays} dias úteis (${est.label})`;
}

/** Embalagem presente (upsell) */
export const GIFT_WRAP_PRICE = 18.9;
