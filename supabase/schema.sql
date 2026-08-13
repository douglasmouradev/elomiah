-- Persistência do admin na Vercel (roda no SQL Editor do Supabase)
-- 1. Crie o projeto, copie URL e service_role para .env
-- 2. Execute este script
-- 3. Storage → New bucket: uploads (Public)
--
-- Chaves usadas em site_store.payload:
--   products | achadinhos | testimonials | sales | waitlist

create table if not exists public.site_store (
  key text primary key,
  payload jsonb not null default '[]'::jsonb,
  updated_at timestamptz not null default now()
);

alter table public.site_store enable row level security;

-- Evita erro se a policy já existir
do $$
begin
  if not exists (
    select 1 from pg_policies
    where schemaname = 'public'
      and tablename = 'site_store'
      and policyname = 'site_store_read_public'
  ) then
    create policy "site_store_read_public"
      on public.site_store
      for select
      to anon, authenticated
      using (true);
  end if;
end $$;
