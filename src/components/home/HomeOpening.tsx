"use client";

import dynamic from "next/dynamic";
import { useEffect, useState, useCallback } from "react";

const SprayOpening = dynamic(
  () => import("./SprayOpening").then((m) => m.SprayOpening),
  { ssr: false, loading: () => null }
);

const STORAGE_KEY = "elomiah-skip-opening-v5";

export function HomeOpening() {
  const [show, setShow] = useState(false);

  useEffect(() => {
    const skip = window.localStorage.getItem(STORAGE_KEY) === "1";
    const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    setShow(!skip && !reduce);
  }, []);

  const dismiss = useCallback(() => {
    window.localStorage.setItem(STORAGE_KEY, "1");
    setShow(false);
  }, []);

  if (!show) return null;

  return <SprayOpening onDismiss={dismiss} />;
}
