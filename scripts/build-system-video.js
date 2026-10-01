const sharp = require("sharp");
const fs = require("fs");
const path = require("path");
const { spawnSync } = require("child_process");

const ROOT = path.join(__dirname, "..");
const TMP = path.join(ROOT, ".tmp-promo");
const SRC = path.join(TMP, "sistema");
const FRAMES = path.join(TMP, "sistema-frames");
const OUT_DIR = path.join(ROOT, "public", "videos");
const W = 1920;
const H = 1080;
const FPS = 30;

const FFMPEG = path.join(
  process.env.LOCALAPPDATA || "",
  "Microsoft/WinGet/Packages/Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0-full_build/bin/ffmpeg.exe"
);

function ffmpeg(args) {
  const bin = fs.existsSync(FFMPEG) ? FFMPEG : "ffmpeg";
  const r = spawnSync(bin, ["-y", ...args], { stdio: "inherit" });
  if (r.status !== 0) throw new Error("ffmpeg falhou");
}

function esc(s) {
  return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

async function titleCard(file, opts) {
  const svg = Buffer.from(`<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
    <rect width="100%" height="100%" fill="#f3efe8"/>
    <rect x="56" y="40" width="${W - 112}" height="${H - 80}" fill="none" stroke="rgba(184,149,107,0.35)" stroke-width="1"/>
    <text x="960" y="430" text-anchor="middle" font-family="Calibri, 'Segoe UI', sans-serif" font-size="22" fill="#b8956b" letter-spacing="8">${esc(opts.kicker.toUpperCase())}</text>
    <text x="960" y="540" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-size="86" fill="#152921" letter-spacing="8">${esc(opts.title)}</text>
    <text x="960" y="610" text-anchor="middle" font-family="Georgia, serif" font-size="22" fill="#b8956b">—</text>
    <text x="960" y="680" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-style="italic" font-size="32" fill="rgba(21,41,33,0.55)">${esc(opts.caption)}</text>
    ${opts.footer ? `<text x="960" y="980" text-anchor="middle" font-family="Calibri, 'Segoe UI', sans-serif" font-size="20" fill="#5c5c56" letter-spacing="2">${esc(opts.footer)}</text>` : ""}
  </svg>`);
  await sharp(svg).jpeg({ quality: 94 }).toFile(file);
}

async function screenFrame(src, dest, label) {
  const fitted = await sharp(src)
    .resize(W, H, { fit: "cover", position: "north" })
    .toBuffer();

  const bar = Buffer.from(`<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
    <rect x="0" y="0" width="${W}" height="78" fill="rgba(12,26,20,0.78)"/>
    <text x="64" y="48" font-family="Calibri, 'Segoe UI', sans-serif" font-size="22" fill="#f7f3ec" letter-spacing="3">${esc(label)}</text>
    <text x="1856" y="48" text-anchor="end" font-family="Georgia, serif" font-size="18" fill="#b8956b">ELOMIAH</text>
  </svg>`);

  await sharp(fitted)
    .composite([{ input: await sharp(bar).png().toBuffer() }])
    .jpeg({ quality: 92 })
    .toFile(dest);
}

function scene(input, seconds, out) {
  const frames = Math.round(seconds * FPS);
  ffmpeg([
    "-loop",
    "1",
    "-i",
    input,
    "-vf",
    `scale=2112:1188,zoompan=z='min(1.0+on*0.00055,1.08)':x='iw/2-(iw/zoom/2)':y='0':d=${frames}:s=${W}x${H}:fps=${FPS},format=yuv420p`,
    "-t",
    String(seconds),
    "-r",
    String(FPS),
    "-an",
    out,
  ]);
}

async function main() {
  fs.mkdirSync(FRAMES, { recursive: true });
  fs.mkdirSync(OUT_DIR, { recursive: true });

  await titleCard(path.join(FRAMES, "t0.jpg"), {
    kicker: "Plataforma da marca",
    title: "ELOMIAH",
    caption: "Loja, checkout WhatsApp e painel admin.",
  });

  const shots = [
    ["01-home.png", "01.jpg", "01  ·  Home editorial"],
    ["02-loja.png", "02.jpg", "02  ·  Catálogo da loja"],
    ["03-produto.png", "03.jpg", "03  ·  Página do produto"],
    ["04-checkout.png", "04.jpg", "04  ·  Checkout pelo WhatsApp"],
    ["05-curso.png", "05.jpg", "05  ·  Página do curso"],
    ["06-admin-vendas.png", "06.jpg", "06  ·  Vendas, gráficos e CSV"],
    ["07-admin-aviseme.png", "07.jpg", "07  ·  Lista Avise-me"],
    ["08-admin-produtos.png", "08.jpg", "08  ·  Cadastro de produtos"],
  ];

  for (const [src, dest, label] of shots) {
    await screenFrame(path.join(SRC, src), path.join(FRAMES, dest), label);
  }

  await titleCard(path.join(FRAMES, "t1.jpg"), {
    kicker: "Pronto para operar",
    title: "O sistema",
    caption: "Do aroma ao pedido — tudo em um só lugar.",
    footer: "elomiah.com.br  ·  /admin",
  });

  const clips = [
    ["t0.jpg", 3.4, "c0.mp4"],
    ["01.jpg", 3.6, "c1.mp4"],
    ["02.jpg", 3.2, "c2.mp4"],
    ["03.jpg", 3.3, "c3.mp4"],
    ["04.jpg", 3.6, "c4.mp4"],
    ["05.jpg", 2.8, "c5.mp4"],
    ["06.jpg", 4.0, "c6.mp4"],
    ["07.jpg", 2.8, "c7.mp4"],
    ["08.jpg", 3.2, "c8.mp4"],
    ["t1.jpg", 4.0, "c9.mp4"],
  ];

  clips.forEach(([img, sec, name]) =>
    scene(path.join(FRAMES, img), sec, path.join(FRAMES, name))
  );

  const list = path.join(FRAMES, "list.txt");
  fs.writeFileSync(
    list,
    clips.map(([, , name]) => `file '${name.replace(/\\/g, "/")}'`).join("\n")
  );

  const raw = path.join(FRAMES, "raw.mp4");
  ffmpeg(["-f", "concat", "-safe", "0", "-i", list, "-c", "copy", raw]);

  const out = path.join(OUT_DIR, "elomiah-sistema.mp4");
  ffmpeg([
    "-i",
    raw,
    "-vf",
    "fade=t=in:st=0:d=0.5,fade=t=out:st=33.2:d=0.7",
    "-c:v",
    "libx264",
    "-pix_fmt",
    "yuv420p",
    "-crf",
    "18",
    "-preset",
    "medium",
    "-movflags",
    "+faststart",
    out,
  ]);

  const desktop = path.join(
    process.env.USERPROFILE || ROOT,
    "Desktop",
    "ELOMIAH-sistema.mp4"
  );
  fs.copyFileSync(out, desktop);
  console.log("ok", out, (fs.statSync(out).size / 1024 / 1024).toFixed(1) + " MB");
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
