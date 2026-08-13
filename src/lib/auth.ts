import { cookies } from "next/headers";

const COOKIE = "elomiah_admin";

export function getAdminSecret() {
  return process.env.ADMIN_SECRET || "elomiah-admin-dev-only";
}

export function getAdminPassword() {
  if (process.env.NODE_ENV === "production") {
    return process.env.ADMIN_PASSWORD || "";
  }
  return process.env.ADMIN_PASSWORD || "elomiah2024";
}

export function isAdminConfigured() {
  return Boolean(process.env.ADMIN_PASSWORD && process.env.ADMIN_SECRET);
}

export function isAuthenticated() {
  const token = cookies().get(COOKIE)?.value;
  return token === getAdminSecret() && Boolean(getAdminSecret());
}

export { COOKIE as ADMIN_COOKIE };
