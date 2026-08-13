"use client";

import { useEffect } from "react";
import { useCartStore } from "@/store/cart";

/** Sincroniza preços/estoque do carrinho com o catálogo atual */
export function CartSync() {
  const syncFromCatalog = useCartStore((s) => s.syncFromCatalog);

  useEffect(() => {
    fetch("/api/products")
      .then((r) => r.json())
      .then((products) => syncFromCatalog(products))
      .catch(() => {});
  }, [syncFromCatalog]);

  return null;
}
