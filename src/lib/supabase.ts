const SUPABASE_URL = process.env.SUPABASE_URL?.replace(/\/$/, "");
const SUPABASE_KEY = process.env.SUPABASE_SERVICE_ROLE_KEY;

export function isSupabaseEnabled() {
  return Boolean(SUPABASE_URL && SUPABASE_KEY);
}

function headers(prefer?: string): HeadersInit {
  return {
    apikey: SUPABASE_KEY as string,
    Authorization: `Bearer ${SUPABASE_KEY}`,
    "Content-Type": "application/json",
    ...(prefer ? { Prefer: prefer } : {}),
  };
}

export async function storeRead<T>(key: string, fallback: T): Promise<T> {
  if (!isSupabaseEnabled()) {
    throw new Error("Supabase não configurado");
  }

  const res = await fetch(
    `${SUPABASE_URL}/rest/v1/site_store?key=eq.${encodeURIComponent(key)}&select=payload`,
    { headers: headers(), cache: "no-store" }
  );

  if (!res.ok) {
    throw new Error(`Falha ao ler ${key} no Supabase`);
  }

  const rows = (await res.json()) as { payload: T }[];
  if (!rows.length) {
    await storeWrite(key, fallback);
    return fallback;
  }
  return rows[0].payload;
}

export async function storeWrite<T>(key: string, payload: T): Promise<void> {
  if (!isSupabaseEnabled()) {
    throw new Error("Supabase não configurado");
  }

  const res = await fetch(
    `${SUPABASE_URL}/rest/v1/site_store?on_conflict=key`,
    {
      method: "POST",
      headers: headers("resolution=merge-duplicates,return=minimal"),
      body: JSON.stringify({
        key,
        payload,
        updated_at: new Date().toISOString(),
      }),
    }
  );

  if (!res.ok) {
    throw new Error(`Falha ao gravar ${key} no Supabase`);
  }
}

export async function uploadToSupabase(
  buffer: Buffer,
  filename: string,
  contentType: string
): Promise<string> {
  if (!isSupabaseEnabled()) {
    throw new Error("Supabase não configurado");
  }

  const res = await fetch(
    `${SUPABASE_URL}/storage/v1/object/uploads/${filename}`,
    {
      method: "POST",
      headers: {
        apikey: SUPABASE_KEY as string,
        Authorization: `Bearer ${SUPABASE_KEY}`,
        "Content-Type": contentType,
        "x-upsert": "true",
      },
      body: new Uint8Array(buffer),
    }
  );

  if (!res.ok) {
    const detail = await res.text();
    throw new Error(`Falha no upload: ${detail}`);
  }

  return `${SUPABASE_URL}/storage/v1/object/public/uploads/${filename}`;
}
