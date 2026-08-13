export type ProductCategory =
  | "spray-ambiente"
  | "perfume"
  | "difusor"
  | "kits";

export type Marketplace = "shopee" | "amazon" | "mercadolivre" | "interno";

export type AchadinhoChannel = "shopee" | "amazon" | "mercadolivre" | "collshop";

export interface OlfactoryNotes {
  top: string;
  heart: string;
  base: string;
}

export interface Product {
  id: string;
  slug: string;
  name: string;
  aroma: string;
  collection: string;
  accentColor: string;
  quote: string;
  category: ProductCategory;
  price: number;
  stock: number;
  description: string;
  shortDescription: string;
  notes: OlfactoryNotes;
  usage: string;
  precautions: string;
  technical: string;
  images: string[];
  benefits?: string[];
  featured: boolean;
  marketplace: Marketplace;
  marketplaceUrl?: string;
  createdAt: string;
  updatedAt: string;
}

export interface Achadinho {
  id: string;
  name: string;
  price: number;
  image: string;
  marketplace: AchadinhoChannel;
  url: string;
  description: string;
}

export interface Testimonial {
  id: string;
  name: string;
  photo: string;
  rating: number;
  text: string;
  role?: string;
}

export interface CartItem {
  product: Product;
  quantity: number;
}

export const CATEGORY_LABELS: Record<ProductCategory, string> = {
  "spray-ambiente": "Spray Ambiente",
  perfume: "Perfume",
  difusor: "Difusor",
  kits: "Kits",
};

export const USAGE_PADRAO =
  "Borrife no ambiente sempre que desejar perfumar e criar uma atmosfera acolhedora. Evite aplicar diretamente sobre pessoas, animais, alimentos e superfícies delicadas.";

export const PRECAUTIONS_PADRAO =
  "Manter fora do alcance de crianças e animais domésticos. Produto inflamável. Evite contato com os olhos. Em caso de contato, lave com água em abundância. Conservar em local fresco, seco e ao abrigo da luz solar.";

export const BENEFITS_BY_SLUG: Record<string, string[]> = {
  despertar: [
    "Frescor de manga verde",
    "Abre o dia com presença",
    "120 ml · longa duração",
    "Embalagem de coleção",
  ],
  recomeco: [
    "Notas de figo e madeira",
    "Ritual de novos ciclos",
    "120 ml · longa duração",
    "Embalagem de coleção",
  ],
  encontro: [
    "Ládano resinoso e floral",
    "Atmosfera de conexão",
    "120 ml · longa duração",
    "Embalagem de coleção",
  ],
  equilibrio: [
    "Vanilla aveludada",
    "Harmonia para o ambiente",
    "120 ml · longa duração",
    "Embalagem de coleção",
  ],
  silencio: [
    "Âmbar acolhedor e profundo",
    "Convite à pausa",
    "120 ml · longa duração",
    "Embalagem de coleção",
  ],
  "colecao-elo": [
    "Lavanda e algodão suaves",
    "Pensado para ambientes infantis",
    "120 ml · cruelty-free",
    "Fórmula vegana",
  ],
  "kit-refugio": [
    "5 essências da Coleção Refúgio",
    "Economia vs. compra avulsa",
    "Presente pronto",
    "Experiência completa da marca",
  ],
};
