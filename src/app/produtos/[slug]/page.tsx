import { Metadata } from "next";
import { notFound } from "next/navigation";
import { getProductBySlug, getProducts } from "@/lib/data";
import { ProductDetail } from "@/components/produtos/ProductDetail";
import { ProductJsonLd } from "@/components/seo/ProductJsonLd";
import { siteConfig } from "@/lib/site";

interface Props {
  params: { slug: string };
}

export const revalidate = 60;

export async function generateStaticParams() {
  const products = await getProducts();
  return products.map((p) => ({ slug: p.slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const product = await getProductBySlug(params.slug);
  if (!product) return { title: "Produto" };

  const image = product.images[0]?.startsWith("http")
    ? product.images[0]
    : `${siteConfig.url}${product.images[0]}`;

  return {
    title: product.name,
    description: product.shortDescription,
    openGraph: {
      title: `${product.name} | Elomiah`,
      description: product.shortDescription,
      images: [{ url: image, alt: product.name }],
    },
  };
}

export default async function ProductPage({ params }: Props) {
  const product = await getProductBySlug(params.slug);
  if (!product) notFound();

  const all = await getProducts();
  const related = all
    .filter((p) => p.id !== product.id && p.collection === product.collection)
    .slice(0, 4);

  return (
    <>
      <ProductJsonLd product={product} />
      <ProductDetail product={product} related={related} />
    </>
  );
}
