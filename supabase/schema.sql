-- Persistência do admin na Vercel (roda no SQL Editor do Supabase)
-- 1. Crie o projeto, copie URL e service_role para .env
-- 2. Execute este script
-- 3. Storage → New bucket: uploads (Public)

create table if not exists public.site_store (
  key text primary key,
  payload jsonb not null default '[]'::jsonb,
  updated_at timestamptz not null default now()
);

alter table public.site_store enable row level security;

-- A API usa a service_role (bypassa RLS). Sem policies públicas de escrita.
create policy "site_store_read_public"
  on public.site_store
  for select
  to anon, authenticated
  using (true);
