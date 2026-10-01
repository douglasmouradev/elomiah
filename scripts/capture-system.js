const { chromium } = require("playwright");
const fs = require("fs");
const path = require("path");

const OUT = path.join(__dirname, "..", ".tmp-promo", "sistema");
const BASE = process.env.ELOMIAH_URL || "http://localhost:3000";

async function shot(page, name) {
  const file = path.join(OUT, `${name}.png`);
  await page.screenshot({ path: file, type: "png" });
  console.log("ok", name);
}

async function main() {
  fs.mkdirSync(OUT, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1600, height: 900 },
    deviceScaleFactor: 1,
    locale: "pt-BR",
  });
  await context.addInitScript(() => {
    window.localStorage.setItem("elomiah-skip-opening-v5", "1");
  });

  const page = await context.newPage();
  page.setDefaultTimeout(25000);

  await page.goto(`${BASE}/`, { waitUntil: "networkidle" });
  await page.waitForTimeout(1200);
  await shot(page, "01-home");

  await page.goto(`${BASE}/loja`, { waitUntil: "networkidle" });
  await page.waitForTimeout(800);
  await shot(page, "02-loja");

  await page.goto(`${BASE}/produtos/despertar`, { waitUntil: "networkidle" });
  await page.waitForTimeout(800);
  await shot(page, "03-produto");

  await page.getByRole("button", { name: /Adicionar ao ritual/i }).click();
  await page.waitForTimeout(900);
  await shot(page, "04-checkout");
  await page.getByRole("button", { name: "Fechar carrinho" }).click().catch(() => {});
  await page.waitForTimeout(400);

  await page.goto(`${BASE}/curso`, { waitUntil: "networkidle" });
  await page.waitForTimeout(600);
  await shot(page, "05-curso");

  await page.goto(`${BASE}/admin`, { waitUntil: "networkidle" });
  const pwd = page.locator('input[type="password"]');
  if (await pwd.count()) {
    await pwd.fill("elomiah2024");
    await page.click('button[type="submit"]');
    await page.waitForTimeout(1500);
  }

  const demoIds = await page.evaluate(async () => {
    const mk = (name, daysAgo, status) =>
      fetch("/api/sales", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          channel: "whatsapp",
          status,
          customerName: name,
          customerPhone: "(71) 98491-0000",
          city: "Salvador / BA",
          createdAt: new Date(Date.now() - daysAgo * 86400000).toISOString(),
          items: [
            {
              productId: "1",
              productName: "Despertar",
              quantity: 1,
              unitPrice: 89.9,
            },
          ],
        }),
      }).then((r) => r.json());

    const a = await mk("Ana Souza", 2, "confirmado");
    const b = await mk("Carla Lima", 6, "confirmado");
    const c = await mk("Rafael Nunes", 1, "iniciado");
    return [a.id, b.id, c.id].filter(Boolean);
  });

  await page.reload({ waitUntil: "networkidle" });
  await page.waitForTimeout(1800);
  await page.getByText("Receita confirmada").waitFor({ timeout: 10000 }).catch(() => {});
  await shot(page, "06-admin-vendas");

  await page.locator("button.chip").filter({ hasText: /^Avise-me$/ }).click();
  await page.waitForTimeout(600);
  await shot(page, "07-admin-aviseme");

  await page.locator("button.chip").filter({ hasText: /^Produtos$/ }).click();
  await page.waitForTimeout(800);
  await shot(page, "08-admin-produtos");

  for (const id of demoIds || []) {
    await page.evaluate(async (saleId) => {
      await fetch(`/api/sales/${saleId}`, { method: "DELETE" });
    }, id);
  }

  await browser.close();
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
