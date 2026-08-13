"use client";

import {
  createContext,
  useCallback,
  useContext,
  type ReactNode,
} from "react";
import { useRouter } from "next/navigation";
import type { SprayOrigin } from "@/lib/spray-transition";

type SprayTransitionContextValue = {
  navigate: (href: string, origin?: SprayOrigin) => void;
  isTransitioning: boolean;
};

const SprayTransitionContext = createContext<SprayTransitionContextValue | null>(
  null
);

export function useSprayTransition() {
  const ctx = useContext(SprayTransitionContext);
  if (!ctx) {
    throw new Error("useSprayTransition must be used within SprayTransitionProvider");
  }
  return ctx;
}

export function SprayTransitionProvider({ children }: { children: ReactNode }) {
  const router = useRouter();
  const navigate = useCallback(
    (href: string) => {
      router.push(href);
    },
    [router]
  );

  return (
    <SprayTransitionContext.Provider
      value={{ navigate, isTransitioning: false }}
    >
      {children}
    </SprayTransitionContext.Provider>
  );
}
