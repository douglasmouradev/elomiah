import Link from "next/link";

export default function NotFound() {
  return (
    <div className="flex min-h-[70svh] flex-col items-center justify-center px-6 pt-24 text-center">
      <p className="section-label">404</p>
      <h1 className="heading-display mt-6 text-4xl md:text-5xl">
        Essência não encontrada
      </h1>
      <div className="gold-rule mx-auto my-8" />
      <p className="max-w-md text-elomiah-muted">
        A página que você busca se dissipou como uma névoa. Volte ao refúgio.
      </p>
      <Link href="/" className="btn-primary mt-10">
        Ir para o início
      </Link>
    </div>
  );
}
