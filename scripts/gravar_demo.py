"""Grava um vídeo de demonstração do site Elomiah (todas as telas principais)."""

from __future__ import annotations

import shutil
import subprocess
import sys
import time
from pathlib import Path

from playwright.sync_api import TimeoutError as PlaywrightTimeout
from playwright.sync_api import sync_playwright

BASE = "http://127.0.0.1:8000"
SENHA = "ElomiahDemo2026"
CLIENTE = "cliente.demo@elomiah.local"
ATELIE = "atelie.demo@elomiah.local"
ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "storage" / "demo"
FFMPEG = Path(
    r"C:\Users\Douglas\AppData\Roaming\Python\Python314\site-packages\imageio_ffmpeg\binaries\ffmpeg-win-x86_64-v7.1.exe"
)

CAPTION_JS = r"""
(() => {
  if (document.getElementById('elomiah-demo-cap')) return;
  const el = document.createElement('div');
  el.id = 'elomiah-demo-cap';
  el.setAttribute('aria-hidden', 'true');
  el.style.cssText = [
    'position:fixed',
    'left:28px',
    'right:28px',
    'bottom:24px',
    'z-index:400',
    'padding:14px 22px',
    'background:#1B4332',
    'color:#FDFBF6',
    'font:500 15px/1.45 Inter,system-ui,sans-serif',
    'letter-spacing:.04em',
    'pointer-events:none',
    'border-left:3px solid #C9A24B',
  ].join(';');
  document.documentElement.appendChild(el);
})();
"""


def caption(page, text: str) -> None:
    page.evaluate(CAPTION_JS)
    page.evaluate(
        """(t) => {
          const el = document.getElementById('elomiah-demo-cap');
          if (el) el.textContent = t;
        }""",
        text,
    )


def hold(page, seconds: float, text: str | None = None) -> None:
    if text:
        try:
            caption(page, text)
        except Exception:
            pass
    page.wait_for_timeout(int(seconds * 1000))


def goto(page, path: str, text: str, seconds: float = 2.6) -> None:
    page.goto(BASE + path, wait_until="domcontentloaded", timeout=45000)
    page.wait_for_timeout(400)
    hold(page, seconds, text)


def scroll_down(page, px: int = 700) -> None:
    page.evaluate("(y) => window.scrollBy(0, y)", px)
    page.wait_for_timeout(900)


def aceitar_cookies(page) -> None:
    btn = page.locator('[data-cookie="todos"]')
    try:
        if btn.count() and btn.first.is_visible():
            btn.first.click(timeout=2000)
            page.wait_for_timeout(400)
    except Exception:
        pass


def preencher_login(page, email: str, action: str) -> None:
    page.locator('input[name="email"]').fill(email)
    page.locator('input[name="senha"]').fill(SENHA)
    page.wait_for_timeout(600)
    page.locator(f'form[action*="{action}"] button[type="submit"]').click()
    page.wait_for_load_state("domcontentloaded")
    page.wait_for_timeout(800)


def titulo(page) -> None:
    page.set_content(
        """
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <style>
    html,body{margin:0;height:100%;background:#FDFBF6;color:#1B4332;
      font-family:Georgia,'Times New Roman',serif;display:grid;place-items:center}
    .wrap{text-align:center;padding:40px}
    p.k{letter-spacing:.42em;font-size:12px;color:#C9A24B;margin:0 0 18px}
    h1{font-weight:400;font-size:56px;letter-spacing:.12em;margin:0 0 16px}
    p.s{font-size:18px;color:#5C6B61;margin:0}
  </style>
</head>
<body>
  <div class="wrap">
    <p class="k">REFÚGIO</p>
    <h1>ELOMIAH</h1>
    <p class="s">Onde o sagrado encontra a essência.</p>
    <p class="s" style="margin-top:28px;font-size:15px">Demonstração do site</p>
  </div>
</body>
</html>
        """
    )
    hold(page, 3.2)


def encerrar(page) -> None:
    page.set_content(
        """
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <style>
    html,body{margin:0;height:100%;background:#1B4332;color:#FDFBF6;
      font-family:Georgia,'Times New Roman',serif;display:grid;place-items:center}
    .wrap{text-align:center}
    p.k{letter-spacing:.42em;font-size:12px;color:#C9A24B;margin:0 0 18px}
    h1{font-weight:400;font-size:42px;margin:0 0 12px}
    p{color:#C9B99A;margin:0}
  </style>
</head>
<body>
  <div class="wrap">
    <p class="k">ELOMIAH</p>
    <h1>O ritual está no ar.</h1>
    <p>localhost:8000</p>
  </div>
</body>
</html>
        """
    )
    hold(page, 3.0)


def gravar() -> Path:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    for old in OUT_DIR.glob("*.webm"):
        old.unlink(missing_ok=True)

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={"width": 1440, "height": 900},
            record_video_dir=str(OUT_DIR),
            record_video_size={"width": 1440, "height": 900},
            locale="pt-BR",
        )
        video_path = None
        page = None
        try:
            page = context.new_page()
            page.set_default_timeout(20000)

            titulo(page)

            goto(page, "/", "Home — hero, coleção e o spray do refúgio.", 3.4)
            aceitar_cookies(page)
            page.wait_for_timeout(1800)
            hold(page, 1.2, "Home — hero, coleção e o spray do refúgio.")
            scroll_down(page, 800)
            hold(page, 2.0, "A vitrine das névoas, 120 ml em vidro.")
            scroll_down(page, 900)
            hold(page, 1.8, "Depoimentos, curso e o rodapé.")

            goto(page, "/loja", "A loja — filtro por coleção e ordenação.", 2.4)
            aceitar_cookies(page)
            chip = page.locator("a.chip", has_text="Coleção Refúgio")
            if chip.count():
                chip.first.click()
                page.wait_for_load_state("domcontentloaded")
                hold(page, 2.2, "Coleção Refúgio — as cinco névoas.")

            goto(
                page,
                "/produto/despertar",
                "Produto — galeria, notas olfativas e à sacola.",
                2.8,
            )
            scroll_down(page, 420)
            hold(page, 1.6, "Notas de saída, corpo e fundo.")
            add = page.locator('button:has-text("Adicionar à sacola")')
            if add.count():
                add.first.click()
                page.wait_for_load_state("domcontentloaded")
                hold(page, 1.8, "Despertar entrou na sacola.")

            goto(
                page,
                "/curso",
                "O Ritual das Essências — formação com a Geo.",
                2.8,
            )
            scroll_down(page, 700)
            hold(page, 1.8, "Módulos, matrícula na sacola, sem kit físico.")

            goto(page, "/sobre", "Sobre — a marca e a Geo.", 2.4)
            scroll_down(page, 500)
            goto(page, "/depoimentos", "Depoimentos — envio para moderação no ateliê.", 2.4)
            goto(page, "/contato", "Contato — formulário e WhatsApp.", 2.2)
            goto(page, "/privacidade", "LGPD — política de privacidade e cookies.", 2.2)
            scroll_down(page, 400)
            goto(page, "/meus-dados", "Seus dados — exportar ou excluir.", 2.2)
            goto(page, "/termos", "Termos de uso.", 1.8)
            goto(page, "/recuperar-senha", "Recuperar senha — carta vai por e-mail.", 2.2)
            goto(page, "/cadastro", "Cadastro — conta para comprar e acompanhar.", 2.0)

            goto(page, "/entrar", "A cliente entra para concluir a compra.", 1.8)
            preencher_login(page, CLIENTE, "/entrar")
            hold(page, 2.0, "Conta — pedidos, perfil, senha e endereço.")
            scroll_down(page, 500)

            goto(page, "/carrinho", "Sacola — quantidade, despacho e o checkout.", 2.4)
            seguir = page.locator("a.btn", has_text="checkout")
            if not seguir.count():
                seguir = page.locator("a.btn-gold")
            if seguir.count():
                seguir.first.click()
                page.wait_for_load_state("domcontentloaded")
                hold(page, 2.0, "Checkout — destino, CEP e pagamento na página.")

                if page.locator("#cep").count():
                    page.locator("#cep").fill("01310-100")
                    page.wait_for_timeout(1600)
                    if page.locator("#numero").input_value() == "":
                        page.locator("#numero").fill("100")
                    if page.locator("#logradouro").input_value() == "":
                        page.locator("#logradouro").fill("Avenida Paulista")
                        page.locator("#bairro").fill("Bela Vista")
                        page.locator("#cidade").fill("São Paulo")
                        page.locator("#estado").fill("SP")
                    hold(page, 1.6, "CEP preenche rua, bairro e cidade.")

                to_pay = page.locator("[data-to-pay]")
                if to_pay.count():
                    to_pay.first.click()
                    page.wait_for_timeout(800)
                    hold(page, 2.0, "Pix nesta página. Cartão acende com o Mercado Pago.")
                    lgpd = page.locator('input[name="lgpd"]')
                    if lgpd.count():
                        lgpd.first.check()
                    submit = page.locator('form[data-checkout] button[type="submit"]')
                    if submit.count():
                        submit.first.click()
                        page.wait_for_load_state("domcontentloaded")
                        hold(page, 3.2, "Pedido recebido — QR Pix e o recibo depois do pago.")

            goto(page, "/conta", "A conta guarda pedidos, recibo e o cancelar do Pix aberto.", 2.6)
            page.goto(BASE + "/sair", wait_until="domcontentloaded")
            page.wait_for_timeout(600)

            goto(page, "/admin/login", "Ateliê — o painel da Geo.", 2.0)
            preencher_login(page, ATELIE, "/admin/login")
            hold(page, 2.8, "Painel — visitas, conversão e pedidos.")

            for path, text in [
                ("/admin/produtos", "Produtos — estoque, fotos e o CRUD da coleção."),
                ("/admin/pedidos", "Pedidos — marcar pago, despacho e recibo."),
                ("/admin/depoimentos", "Moderar depoimentos antes de ir à vitrine."),
                ("/admin/curso", "Curso — preço, módulos e o link das aulas."),
                ("/admin/contato", "Mensagens de /contato, com sino."),
                ("/admin/lgpd", "Fila LGPD — exportar ou excluir em 15 dias."),
                ("/admin/pagamento", "Pix, emitente do recibo, Mercado Pago e despacho."),
                ("/admin/logs", "Auditoria — o que o ateliê alterou."),
                ("/admin/conta", "Trocar a senha do ateliê no primeiro acesso."),
            ]:
                goto(page, path, text, 2.5)
                scroll_down(page, 360)

            encerrar(page)
        finally:
            try:
                page.close()
            except Exception:
                pass
            try:
                if page is not None and page.video:
                    video_path = page.video.path()
            except Exception:
                pass
            context.close()
            browser.close()

    if not video_path:
        raise RuntimeError("Playwright não gravou o arquivo de vídeo.")
    return Path(video_path)


def converter(webm: Path) -> Path:
    mp4 = OUT_DIR / "elomiah-demonstracao.mp4"
    if mp4.exists():
        mp4.unlink()
    ffmpeg = FFMPEG if FFMPEG.is_file() else Path(shutil.which("ffmpeg") or "")
    if not ffmpeg.is_file():
        dest = OUT_DIR / "elomiah-demonstracao.webm"
        shutil.copy2(webm, dest)
        return dest
    cmd = [
        str(ffmpeg),
        "-y",
        "-i",
        str(webm),
        "-c:v",
        "libx264",
        "-pix_fmt",
        "yuv420p",
        "-movflags",
        "+faststart",
        "-crf",
        "20",
        str(mp4),
    ]
    subprocess.run(cmd, check=True, capture_output=True)
    return mp4


def main() -> int:
    print("Gravando tour…")
    try:
        webm = gravar()
    except PlaywrightTimeout as exc:
        print("Tempo esgotado numa página:", exc, file=sys.stderr)
        return 1
    print("WebM:", webm)
    time.sleep(0.4)
    final = converter(webm)
    print("Vídeo:", final)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
