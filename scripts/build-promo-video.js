const sharp = require("sharp");
const fs = require("fs");
const path = require("path");
const { spawnSync } = require("child_process");

const ROOT = path.join(__dirname, "..");
const OUT_DIR = path.join(ROOT, "public", "videos");
const TMP = path.join(ROOT, ".tmp-promo");
const W = 1080;
const H = 1920;
const FPS = 30;
const CREAM = { r: 243, g: 239, b: 232 };

const FFMPEG =
  process.env.FFMPEG_PATH ||
  path.join(
    process.env.LOCALAPPDATA || "",
    "Microsoft/WinGet/Packages/Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe/ffmpeg-9.0-full_build/bin/ffmpeg.exe"
  );

const ASSETS = path.join(
  process.env.USERPROFILE || "",
  ".cursor/projects/c-Users-Douglas-Desktop-Projetos-ELOMIAH/assets"
);

function ffmpeg(args) {
  const bin = fs.existsSync(FFMPEG) ? FFMPEG : "ffmpeg";
  const r = spawnSync(bin, ["-y", ...args], { stdio: "inherit" });
  if (r.status !== 0) throw new Error("ffmpeg falhou");
}

function svgText({ title, subtitle, caption, footer, light }) {
  const fill = light ? "#f7f3ec" : "#152921";
  const muted = light ? "rgba(247,243,236,0.72)" : "#b8956b";
  const sub = subtitle
    ? `<text x="540" y="1024" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-size="28" fill="${muted}" letter-spacing="8">${escapeXml(subtitle)}</text>`
    : "";
  const cap = caption
    ? `<text x="540" y="${subtitle ? 1110 : 1048}" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-style="italic" font-size="36" fill="${light ? "rgba(247,243,236,0.8)" : "rgba(21,41,33,0.55)"}">${escapeXml(caption)}</text>`
    : "";
  const foot = footer
    ? `<text x="540" y="1760" text-anchor="middle" font-family="Calibri, 'Segoe UI', sans-serif" font-size="22" fill="${muted}" letter-spacing="4">${escapeXml(footer)}</text>`
    : "";
  return Buffer.from(`<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
    <text x="540" y="940" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-size="86" fill="${fill}" letter-spacing="10">${escapeXml(title)}</text>
    ${sub}${cap}${foot}
  </svg>`);
}

function escapeXml(s) {
  return s
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

async function cardCream(file, opts) {
  const lines = [
    opts.kicker
      ? `<text x="540" y="820" text-anchor="middle" font-family="Calibri, 'Segoe UI', sans-serif" font-size="18" fill="#b8956b" letter-spacing="10">${escapeXml(opts.kicker.toUpperCase())}</text>`
      : "",
    `<text x="540" y="940" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-size="${opts.size || 92}" fill="#152921" letter-spacing="12">${escapeXml(opts.title)}</text>`,
    opts.line
      ? `<text x="540" y="1010" text-anchor="middle" font-family="Georgia, serif" font-size="22" fill="#b8956b">—  —</text>`
      : "",
    opts.caption
      ? `<text x="540" y="1088" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-style="italic" font-size="32" fill="rgba(21,41,33,0.55)">${escapeXml(opts.caption)}</text>`
      : "",
    opts.footer
      ? `<text x="540" y="1740" text-anchor="middle" font-family="Calibri, 'Segoe UI', sans-serif" font-size="22" fill="#5c5c56" letter-spacing="3">${escapeXml(opts.footer)}</text>`
      : "",
  ].join("");

  const svg = Buffer.from(
    `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
      <rect width="100%" height="100%" fill="#f3efe8"/>
      <rect x="70" y="70" width="${W - 140}" height="${H - 140}" fill="none" stroke="rgba(184,149,107,0.35)" stroke-width="1"/>
      ${lines}
    </svg>`
  );
  await sharp(svg).jpeg({ quality: 94 }).toFile(file);
}

async function photoFrame(src, dest, crop, overlay) {
  let img = sharp(src);
  const meta = await img.metadata();
  if (crop) {
    img = sharp(src).extract(crop(meta));
  }
  const fitted = await img
    .resize(W, H, { fit: "cover", position: overlay.position || "centre" })
    .toBuffer();

  const veil = Buffer.from(`<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}">
    <defs>
      <linearGradient id="g" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#f3efe8" stop-opacity="0.18"/>
        <stop offset="48%" stop-color="#152921" stop-opacity="0"/>
        <stop offset="100%" stop-color="#0c1a14" stop-opacity="0.55"/>
      </linearGradient>
    </defs>
    <rect width="100%" height="100%" fill="url(#g)"/>
  </svg>`);

  const layers = [
    { input: await sharp(veil).png().toBuffer() },
    {
      input: await sharp(
        svgText({
          title: overlay.title,
          subtitle: overlay.subtitle,
          caption: overlay.caption,
          footer: overlay.footer,
          light: true,
        })
      )
        .png()
        .toBuffer(),
    },
  ];

  await sharp(fitted)
    .composite(layers)
    .jpeg({ quality: 93 })
    .toFile(dest);
}

function leftPanel(meta) {
  return {
    left: 0,
    top: 0,
    width: Math.round(meta.width * 0.48),
    height: meta.height,
  };
}

function scene(input, seconds, out) {
  const frames = Math.round(seconds * FPS);
  ffmpeg([
    "-loop",
    "1",
    "-i",
    input,
    "-vf",
    `scale=1296:2304,zoompan=z='min(1.0+on*0.0009,1.12)':x='iw/2-(iw/zoom/2)':y='ih/2-(ih/zoom/2)':d=${frames}:s=${W}x${H}:fps=${FPS},format=yuv420p`,
    "-t",
    String(seconds),
    "-r",
    String(FPS),
    "-an",
    out,
  ]);
}

async function main() {
  fs.mkdirSync(TMP, { recursive: true });
  fs.mkdirSync(OUT_DIR, { recursive: true });

  const cinematic = path.join(ASSETS, "promo-cinematic.png");
  const mist = path.join(ASSETS, "promo-mist.png");

  const f0 = path.join(TMP, "00.jpg");
  const f1 = path.join(TMP, "01.jpg");
  const f2 = path.join(TMP, "02.jpg");
  const f3 = path.join(TMP, "03.jpg");
  const f4 = path.join(TMP, "04.jpg");
  const f5 = path.join(TMP, "05.jpg");
  const f6 = path.join(TMP, "06.jpg");
  const f7 = path.join(TMP, "07.jpg");

  await cardCream(f0, {
    kicker: "Coleção Refúgio",
    title: "ELOMIAH",
    line: true,
    caption: "Onde o sagrado encontra a essência.",
  });

  await photoFrame(cinematic, f1, null, {
    title: "ELOMIAH",
    subtitle: "COLEÇÃO REFÚGIO",
    caption: "Cinco essências para o cotidiano",
    position: "centre",
  });

  await photoFrame(
    path.join(ROOT, "public/images/produtos/despertar-ficha.jpg"),
    f2,
    leftPanel,
    {
      title: "Despertar",
      subtitle: "AROMA MANGA VERDE",
      position: "centre",
    }
  );

  await photoFrame(
    path.join(ROOT, "public/images/produtos/recomeco-ficha.jpg"),
    f3,
    leftPanel,
    {
      title: "Recomeço",
      subtitle: "AROMA FIGO",
      position: "centre",
    }
  );

  await photoFrame(
    path.join(ROOT, "public/images/produtos/encontro-ficha.jpg"),
    f4,
    leftPanel,
    {
      title: "Encontro",
      subtitle: "AROMA LÁDANO",
      position: "centre",
    }
  );

  await photoFrame(mist, f5, null, {
    title: "Ritual",
    caption: "Presença, quietude, aroma.",
    position: "centre",
  });

  await photoFrame(
    path.join(ROOT, "public/images/produtos/colecao-refugio-hero-mobile.jpg"),
    f6,
    null,
    {
      title: "Refúgio",
      subtitle: "SPRAY DE AMBIENTE · 120 ML",
      position: "centre",
    }
  );

  await cardCream(f7, {
    kicker: "Agora online",
    title: "ELOMIAH",
    line: true,
    caption: "elomiah.com.br",
    footer: "Loja  ·  Curso  ·  Essências",
    size: 84,
  });

  const clips = [
    [f0, 3.0, "s0.mp4"],
    [f1, 4.0, "s1.mp4"],
    [f2, 3.2, "s2.mp4"],
    [f3, 3.0, "s3.mp4"],
    [f4, 3.0, "s4.mp4"],
    [f5, 3.4, "s5.mp4"],
    [f6, 3.2, "s6.mp4"],
    [f7, 4.2, "s7.mp4"],
  ];

  clips.forEach(([img, sec, name]) => scene(img, sec, path.join(TMP, name)));

  const list = path.join(TMP, "list.txt");
  fs.writeFileSync(
    list,
    clips.map(([, , name]) => `file '${name.replace(/\\/g, "/")}'`).join("\n")
  );

  const raw = path.join(TMP, "raw.mp4");
  ffmpeg([
    "-f",
    "concat",
    "-safe",
    "0",
    "-i",
    list,
    "-c",
    "copy",
    raw,
  ]);

  const out = path.join(OUT_DIR, "elomiah-divulgacao.mp4");
  ffmpeg([
    "-i",
    raw,
    "-vf",
    "fade=t=in:st=0:d=0.6,fade=t=out:st=25.6:d=0.8",
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
    "ELOMIAH-divulgacao.mp4"
  );
  fs.copyFileSync(out, desktop);
  const mb = (fs.statSync(out).size / 1024 / 1024).toFixed(1);
  console.log("ok", out, mb + " MB", "duração ~27s 9:16");
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
