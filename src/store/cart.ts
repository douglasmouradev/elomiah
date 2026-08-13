import { create } from "zustand";
import { persist } from "zustand/middleware";
import { CartItem, Product } from "@/lib/types";

interface CartState {
  items: CartItem[];
  isOpen: boolean;
  hydrated: boolean;
  open: () => void;
  close: () => void;
  toggle: () => void;
  addItem: (product: Product, quantity?: number) => boolean;
  removeItem: (productId: string) => void;
  updateQuantity: (productId: string, quantity: number) => void;
  clear: () => void;
  syncFromCatalog: (products: Product[]) => void;
  totalItems: () => number;
  totalPrice: () => number;
}

export const useCartStore = create<CartState>()(
  persist(
    (set, get) => ({
      items: [],
      isOpen: false,
      hydrated: false,
      open: () => set({ isOpen: true }),
      close: () => set({ isOpen: false }),
      toggle: () => set((s) => ({ isOpen: !s.isOpen })),
      addItem: (product, quantity = 1) => {
        if (product.stock <= 0) return false;

        const items = [...get().items];
        const existing = items.find((i) => i.product.id === product.id);
        const currentQty = existing?.quantity ?? 0;
        const nextQty = Math.min(currentQty + quantity, product.stock);

        if (nextQty <= currentQty) return false;

        if (existing) {
          existing.quantity = nextQty;
          existing.product = product;
        } else {
          items.push({ product, quantity: nextQty });
        }

        set({ items, isOpen: true });
        return true;
      },
      removeItem: (productId) =>
        set({ items: get().items.filter((i) => i.product.id !== productId) }),
      updateQuantity: (productId, quantity) => {
        const item = get().items.find((i) => i.product.id === productId);
        if (!item) return;

        const capped = Math.min(Math.max(0, quantity), item.product.stock);
        if (capped <= 0) {
          get().removeItem(productId);
          return;
        }
        set({
          items: get().items.map((i) =>
            i.product.id === productId ? { ...i, quantity: capped } : i
          ),
        });
      },
      clear: () => set({ items: [] }),
      syncFromCatalog: (products) => {
        set({
          items: get()
            .items.map((item) => {
              const fresh = products.find((p) => p.id === item.product.id);
              if (!fresh || fresh.stock <= 0) return null;
              return {
                product: fresh,
                quantity: Math.min(item.quantity, fresh.stock),
              };
            })
            .filter(Boolean) as CartItem[],
        });
      },
      totalItems: () => get().items.reduce((acc, i) => acc + i.quantity, 0),
      totalPrice: () =>
        get().items.reduce((acc, i) => acc + i.product.price * i.quantity, 0),
    }),
    {
      name: "elomiah-cart",
      partialize: (state) => ({ items: state.items }),
      onRehydrateStorage: () => () => {
        useCartStore.setState({ hydrated: true });
      },
    }
  )
);
