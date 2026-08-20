# Elomiah — site institucional e e-commerce

Refúgio de aromatizantes e perfumes de alto padrão. Slogan: **Onde o sagrado encontra a essência.**

Stack: PHP 8.3+ (testado em 8.5), MySQL 8+, MVC próprio, PDO com prepared statements, HTML/CSS/JS + GSAP.

## 1. Requisitos

- PHP 8.3 ou superior (extensões `pdo_mysql`, `mbstring`, `fileinfo`, `gd` recomendada)
- MySQL 8.0+ (InnoDB, `utf8mb4`)
- Navegador moderno

Ambientes sugeridos no Windows: **Laragon** ou **XAMPP**. O PHP CLI já basta para o servidor embutido.

## 2. Configuração

1. Copie `.env.example` para `.env` (já existe um `.env` local).
2. Ajuste:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=elomiah
DB_USER=root
DB_PASS=
APP_URL=http://localhost:8000
SESSION_SECURE=0
WHATSAPP=5571984916767
```

3. Crie o banco e os dados iniciais:

```bash
php database/install.php
```

Ou importe manualmente no MySQL, nesta ordem:

- `database/schema.sql`
- `database/seed.sql`

## 3. Subir o site

Na raiz do projeto:

```bash
php -S localhost:8000 -t public public/router.php
```

No Windows, você também pode dar um duplo clique em `serve.bat`.

Abra [http://localhost:8000](http://localhost:8000).

### Apache (XAMPP / Laragon)

Aponte o DocumentRoot para a pasta `public/`. O arquivo `public/.htaccess` já redireciona as rotas para o front controller.

## 4. Acesso administrativo

- URL: [http://localhost:8000/admin](http://localhost:8000/admin)
- E-mail inicial: o definido em `database/seed.sql`

**Troque a senha no primeiro acesso** em [http://localhost:8000/admin/conta](http://localhost:8000/admin/conta). Não publique senha em README nem no GitHub. O login bloqueia a conta após 5 tentativas (15 minutos) e aplica rate limiting por IP.

No painel: dashboard com Chart.js, CRUD de produtos (**+ Adicionar novo produto**), pedidos, moderação de depoimentos, edição do curso, pagamento e log de auditoria.

## 5. Pagamento

**Pix** abre o QR nesta página, na chave da Elomiah. Sem Mercado Pago, o ateliê confere na Nubank e marca o pedido como pago. Com Mercado Pago, a página confirma sozinha.

**Cartão** confirma no próprio pedido (número não fica no site). No ateliê: [Pagamento](http://localhost:8000/admin/pagamento) — cole Access Token e a chave pública do [painel de desenvolvedores](https://www.mercadopago.com.br/developers/panel/app). Também pode ir no `.env`:

```
MERCADOPAGO_ACCESS_TOKEN=APP_USR-ou-TEST-seu-token
MERCADOPAGO_PUBLIC_KEY=APP_USR-chave-publica
```

Cadastre a Nubank como conta de recebimento no Mercado Pago (agência 0001, conta 16993107-6, banco 0260). Em produção use HTTPS em `APP_URL`.

E-mails de pedido (recebido, pago, enviado) saem se o SMTP estiver no `.env` (`MAIL_HOST`, `MAIL_USER`, `MAIL_PASS`). Sem host, o site tenta `mail()` e registra em `storage/logs/mail.log`.

## 6. Estrutura

```
app/Core          Núcleo MVC, Auth, CSRF, PDO, Carrinho, Upload
app/Controllers   Páginas públicas e app/Controllers/Admin
app/Models        Tabelas do domínio
app/Views         Templates PHP
app/Middleware    Admin + rastreio de visitas (IP apenas em hash)
config/           app, database, routes
database/         schema.sql, seed.sql, install.php
public/           Front controller, CSS, JS, imagens, uploads
storage/logs      Log de erros
```

## 7. Segurança e LGPD

- Senhas com `password_hash()` (`PASSWORD_DEFAULT` — bcrypt/Argon2id conforme o PHP)
- Sessão `httponly`, `samesite=strict`; em produção defina `SESSION_SECURE=1`
- CSRF em todos os POST
- PDO prepared statements em 100% das queries
- Saída escapada com `htmlspecialchars` (`e()`)
- Rate limiting em login, contato, checkout, depoimentos e solicitações LGPD
- Banner de cookies (essenciais / todos / recusar)
- Páginas `/privacidade`, `/termos` e `/meus-dados` (exportar ou excluir)
- Consentimento explícito no cadastro e no checkout
- IPs de visita armazenados só como hash SHA-256

### HTTPS em produção

1. Instale um certificado (Let’s Encrypt no Apache/Nginx).
2. Descomente o redirect HTTPS em `public/.htaccess`.
3. No `.env`: `APP_URL=https://seudominio.com` e `SESSION_SECURE=1`.
4. Forçar HSTS no servidor, se possível.

## 8. Páginas

| Rota | Conteúdo |
|---|---|
| `/` | Home com hero, spray 3D, vitrine, depoimentos, curso |
| `/loja` | Grid com filtro de categoria e ordenação |
| `/produto/{slug}` | Galeria com zoom, notas olfativas, compra interna ou marketplace |
| `/curso` | Landing do curso |
| `/sobre` | Bio da marca e da Geo |
| `/depoimentos` | Grid + envio para moderação |
| `/checkout` | Sacola + CEP + Pix ou cartão no Mercado Pago + LGPD |
| `/conta` | Pedidos, rastreio e endereço de envio |
| `/contato` | Formulário e WhatsApp |

O CEP consulta `https://viacep.com.br/ws/{cep}/json/`. Se a API falhar, o endereço pode ser preenchido à mão.

## 9. Manutenção

- Produtos: `/admin/produtos` — imagens JPEG/PNG/WebP, até 4 MB cada.
- Uploads ficam em `public/assets/uploads/`.
- Erros de aplicação: `storage/logs/app.log` (com `APP_DEBUG=false` em produção).

Código PSR-12, PHP 8.3+, comentado nos pontos de regra de negócio. Composer é opcional: o `bootstrap.php` já autocarrega `App\` se `vendor/` não existir.
