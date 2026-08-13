"use client";

import { cn } from "@/lib/utils";

/** Avatar com iniciais — evita fotos stock genéricas */
export function InitialsAvatar({
  name,
  className,
}: {
  name: string;
  className?: string;
}) {
  const initials = name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((p) => p[0]?.toUpperCase() ?? "")
    .join("");

  return (
    <div
      className={cn(
        "flex items-center justify-center bg-elomiah-surface font-display text-elomiah-green",
        className
      )}
      aria-hidden
    >
      <span className="text-[0.85em] tracking-wide">{initials || "E"}</span>
    </div>
  );
}
