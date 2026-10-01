# Elomiah

Site institucional e e-commerce da marca **Elomiah** — refúgio de aromatizantes e perfumes de alto padrão.

**Slogan:** *Onde o sagrado encontra a essência.*

## Stack

- Next.js 14 (App Router) + TypeScript + Tailwind CSS
- Framer Motion + GSAP ScrollTrigger
- Zustand (carrinho persistido)
- Zod (validação de APIs)
- Painel admin (`/admin`) com JSON local ou **Supabase**

## Como rodar

```bash
cp .env.example .env.local
# Edite .env.local com WhatsApp, senhas admin, etc.
npm install
npm run dev
```

Abra [http://localhost:3000](http://localhost:3000).

## Variáveis de ambiente

Veja `.env.example`. **Obrigatório em produção:**

- `ADMIN_PASSWORD` e `ADMIN_SECRET`
- `NEXT_PUBLIC_WHATSAPP`
- `NEXT_PUBLIC_SITE_URL`

Opcionais: Instagram, e-mails, URLs de marketplaces, `NEXT_PUBLIC_GA_ID`.

## Checklist de produção (ordem)

Conteúdo visual já está no repositório (Achadinhos, Sobre e depoimentos). No ar, complete:

1. **Supabase** (obrigatório na Vercel):
   - Crie projeto em [supabase.com](https://supabase.com)
   - Rode `supabase/schema.sql` no SQL Editor
   - Storage → bucket público `uploads`
   - Preencha `SUPABASE_URL` + `SUPABASE_SERVICE_ROLE_KEY`
2. **URLs públicas** — `NEXT_PUBLIC_SITE_URL=https://elomiah.com.br`
3. **Senhas fortes** — `ADMIN_PASSWORD` e `ADMIN_SECRET` (não use os valores de desenvolvimento)
4. **GA4** — `NEXT_PUBLIC_GA_ID` (eventos: add_to_cart, begin_checkout, buy_now_whatsapp, purchase)
5. **Marketplaces** — `NEXT_PUBLIC_SHOPEE_URL`, `AMAZON`, `MERCADOLIVRE` (só se a marca vender lá)
6. **Foto oficial da Geo** — se quiser, troque `public/images/geo.jpg`
7. **Depoimentos reais** — troque textos/fotos no admin quando tiverem autorização
8. **Estoque** — ajuste no admin
9. Frete estimado por CEP — já no carrinho

O admin mostra o status desse checklist após o login.

## Admin

- URL: `/admin` (não linkada no footer)
- CRUD de produtos, achadinhos e depoimentos + upload
- Após salvar, o site revalida (ISR 60s)

## Deploy (Vercel)

1. Conecte o repositório
2. Configure as variáveis de `.env.example`
3. Sem Supabase, o admin grava JSON local (efêmero em serverless)

## Scripts

- `npm run optimize-images` — gera thumbs dos produtos

## Páginas

`/`, `/loja`, `/produtos/[slug]`, `/curso`, `/achadinhos`, `/sobre`, `/depoimentos`, `/contato`, `/faq`, `/privacidade`, `/termos`, `/trocas-devolucoes`, `/admin`
