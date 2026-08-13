"use client";

import { useEffect } from "react";
import Link from "next/link";

export default function Error({
  error,
  reset,
}: {
  error: Error & { digest?: string };
  reset: () => void;
}) {
  useEffect(() => {
    console.error(error);
  }, [error]);

  return (
    <div className="flex min-h-[60svh] flex-col items-center justify-center px-6 pt-24 text-center">
      <h1 className="font-display text-3xl text-elomiah-green">
        Algo saiu do ritual
      </h1>
      <p className="mt-4 max-w-md text-elomiah-muted">
        Ocorreu um erro inesperado. Tente novamente ou volte ao início.
      </p>
      <div className="mt-8 flex flex-wrap justify-center gap-4">
        <button type="button" onClick={reset} className="btn-primary">
          Tentar novamente
        </button>
        <Link href="/" className="btn-outline">
          Início
        </Link>
      </div>
    </div>
  );
}
