import { revalidatePath } from "next/cache";
import type { Product } from "@/lib/types";

export async function revalidateCatalog(products?: Product[]) {
  revalidatePath("/");
  revalidatePath("/loja");
  revalidatePath("/achadinhos");
  revalidatePath("/depoimentos");

  if (products) {
    for (const p of products) {
      revalidatePath(`/produtos/${p.slug}`);
    }
  } else {
    revalidatePath("/produtos", "layout");
  }
}
