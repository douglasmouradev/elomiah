const sharp = require("sharp");
const path = require("path");
const fs = require("fs");

const ROOT = path.join(__dirname, "..");
const SRC = path.join(ROOT, "public", "images", "produtos");
const OUT = path.join(SRC, "thumbs");
const CREAM = { r: 253, g: 251, b: 246 };
const W = 900;
const H = 1200;
const PRODUCT_H = 1020;
/** Largura fixa do produto após normalizar → visual uniforme */
const PRODUCT_W = 420;

fs.mkdirSync(OUT, { recursive: true });

async function extract(srcFile, region) {
  const src = path.join(SRC, srcFile);
  const meta = await sharp(src).metadata();
  const left = Math.max(0, Math.min(region.left | 0, meta.width - 2));
  const top = Math.max(0, Math.min(region.top | 0, meta.height - 2));
  const width = Math.min(region.width, meta.width - left);
  const height = Math.min(region.height, meta.height - top);
  return sharp(src).extract({ left, top, width, height }).toBuffer();
}

async function uniformThumb(buffer, dest) {
  // Cover em caixa fixa: todos ficam EXATAMENTE PRODUCT_W x PRODUCT_H
  const normalized = await sharp(buffer)
    .resize(PRODUCT_W, PRODUCT_H, {
      fit: "cover",
      position: "centre",
    })
    .toBuffer();

  const left = Math.round((W - PRODUCT_W) / 2);
  const top = Math.round((H - PRODUCT_H) / 2);

  await sharp({
    create: { width: W, height: H, channels: 3, background: CREAM },
  })
    .composite([{ input: normalized, left, top }])
    .jpeg({ quality: 90, mozjpeg: true, chromaSubsampling: "4:4:4" })
    .toFile(path.join(OUT, dest));

  console.log(`ok ${dest}  ${PRODUCT_W}x${PRODUCT_H} centered`);
}

async function main() {
  const jobs = [
    {
      file: "despertar.png",
      dest: "despertar.jpg",
      // Coluna do frasco, sem painel
      region: async () => {
        const m = await sharp(path.join(SRC, "despertar.png")).metadata();
        return {
          left: Math.floor(m.width * 0.14),
          top: 0,
          width: Math.floor(m.width * 0.24),
          height: m.height,
        };
      },
    },
    {
      file: "recomeco.png",
      dest: "recomeco.jpg",
      region: async () => {
        const m = await sharp(path.join(SRC, "recomeco.png")).metadata();
        return {
          left: Math.floor(m.width * 0.04),
          top: Math.floor(m.height * 0.01),
          width: Math.floor(m.width * 0.42), // evita faixa lilás do painel
          height: Math.floor(m.height * 0.98),
        };
      },
    },
    {
      file: "encontro.png",
      dest: "encontro.jpg",
      region: async () => {
        const m = await sharp(path.join(SRC, "encontro.png")).metadata();
        return {
          left: Math.floor(m.width * 0.04),
          top: Math.floor(m.height * 0.01),
          width: Math.floor(m.width * 0.42),
          height: Math.floor(m.height * 0.98),
        };
      },
    },
    {
      file: "equilibrio.png",
      dest: "equilibrio.jpg",
      region: async () => {
        const m = await sharp(path.join(SRC, "equilibrio.png")).metadata();
        return {
          left: Math.floor(m.width * 0.04),
          top: Math.floor(m.height * 0.01),
          width: Math.floor(m.width * 0.42),
          height: Math.floor(m.height * 0.98),
        };
      },
    },
    {
      file: "colecao-elo.png",
      dest: "colecao-elo.jpg",
      region: async () => {
        const m = await sharp(path.join(SRC, "colecao-elo.png")).metadata();
        return {
          left: Math.floor(m.width * 0.04),
          top: Math.floor(m.height * 0.01),
          width: Math.floor(m.width * 0.42),
          height: Math.floor(m.height * 0.98),
        };
      },
    },
    {
      file: "colecao-refugio.png",
      dest: "silencio.jpg",
      region: async () => {
        const m = await sharp(path.join(SRC, "colecao-refugio.png")).metadata();
        const bw = Math.floor(m.width / 5.2);
        return {
          left: Math.floor(m.width * 0.795),
          top: Math.floor(m.height * 0.12),
          width: bw,
          height: Math.floor(m.height * 0.84),
        };
      },
    },
  ];

  for (const job of jobs) {
    const region = await job.region();
    const buf = await extract(job.file, region);
    await uniformThumb(buf, job.dest);
  }

  await sharp(path.join(OUT, "despertar.jpg"))
    .resize(1200, 1600, { fit: "fill" })
    .jpeg({ quality: 92, mozjpeg: true })
    .toFile(path.join(OUT, "despertar-hero.jpg"));
  console.log("ok despertar-hero.jpg");
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
