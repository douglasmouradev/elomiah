export type SprayOrigin = { x: number; y: number };

export const SPRAY_DURATION_MS = 980;
export const SPRAY_NAV_AT = 0.48;

export const SPRAY_EASE_EXPAND = "cubic-bezier(0.22, 1, 0.36, 1)";
export const SPRAY_EASE_PAGE = "cubic-bezier(0.45, 0, 0.55, 1)";

export function supportsSprayAnimation(): boolean {
  if (typeof window === "undefined") return false;
  if (typeof HTMLCanvasElement === "undefined") return false;
  if (typeof requestAnimationFrame === "undefined") return false;
  return true;
}

export function easeExpand(t: number): number {
  return 1 - Math.pow(1 - t, 3.4);
}

export function easePage(t: number): number {
  return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
}

export function easeOutSoft(t: number): number {
  return 1 - Math.pow(1 - t, 2.2);
}

export function isInternalHref(href: string | null): href is string {
  if (!href) return false;
  if (href.startsWith("#")) return false;
  if (href.startsWith("mailto:") || href.startsWith("tel:")) return false;
  if (href.startsWith("http://") || href.startsWith("https://")) return false;
  return href.startsWith("/");
}

export function shouldSkipSpray(href: string, pathname: string): boolean {
  if (href === pathname) return true;
  if (href.startsWith("/admin")) return true;
  return false;
}

/** Direção principal do leque — do clique em direção ao centro da tela */
export function sprayAngle(origin: SprayOrigin, w: number, h: number): number {
  const cx = w * 0.52;
  const cy = h * 0.48;
  return Math.atan2(cy - origin.y, cx - origin.x);
}

/** Raio até o canto mais distante — névoa precisa cobrir a tela toda */
export function maxSprayReach(origin: SprayOrigin, w: number, h: number): number {
  const corners = [
    [0, 0],
    [w, 0],
    [0, h],
    [w, h],
  ];
  return Math.max(...corners.map(([x, y]) => Math.hypot(x - origin.x, y - origin.y))) * 1.08;
}
