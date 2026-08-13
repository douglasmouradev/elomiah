const sharp = require("sharp");

async function photoPanel(file, outH) {
  const src = `public/images/produtos/${file}`;
  const meta = await sharp(src).metadata();
  // Painel fotográfico = ~48% esquerdo da ficha
  const cropW = Math.round(meta.width * 0.48);
  const panel = await sharp(src)
    .extract({ left: 0, top: 0, width: cropW, height: meta.height })
    .resize({ height: outH })
    .jpeg({ quality: 94 })
    .toBuffer();
  const m = await sharp(panel).metadata();
  return { buf: panel, width: m.width, height: m.height };
}

async function main() {
  const cream = { r: 243, g: 239, b: 232 };
  const W = 2400;
  const H = 1500;
  const bottleH = 1240;

  const panels = await Promise.all([
    photoPanel("recomeco-ficha.jpg", bottleH),
    photoPanel("encontro-ficha.jpg", bottleH),
    photoPanel("equilibrio-ficha.jpg", bottleH),
  ]);

  const gap = 28;
  const totalW =
    panels.reduce((s, p) => s + p.width, 0) + gap * (panels.length - 1);
  const startX = Math.round(W - totalW - 90);
  const startY = Math.round((H - bottleH) / 2);

  const layers = [];
  let x = startX;
  for (const p of panels) {
    // sombra suave sob cada painel
    const shW = Math.round(p.width * 0.9);
    const shSvg = Buffer.from(
      `<svg xmlns="http://www.w3.org/2000/svg" width="${shW}" height="36">
        <defs>
          <radialGradient id="s" cx="50%" cy="50%" rx="50%" ry="50%">
            <stop offset="0%" stop-color="rgb(21,41,33)" stop-opacity="0.12"/>
            <stop offset="100%" stop-color="rgb(21,41,33)" stop-opacity="0"/>
          </radialGradient>
        </defs>
        <ellipse cx="${shW / 2}" cy="18" rx="${shW / 2}" ry="18" fill="url(#s)"/>
      </svg>`
    );
    const shadow = await sharp(shSvg).png().toBuffer();
    layers.push({
      input: shadow,
      left: Math.round(x + (p.width - shW) / 2),
      top: startY + bottleH - 22,
    });
    layers.push({ input: p.buf, left: x, top: startY });
    x += p.width + gap;
  }

  await sharp({
    create: { width: W, height: H, channels: 3, background: cream },
  })
    .composite(layers)
    .jpeg({ quality: 94, mozjpeg: true })
    .toFile("public/images/produtos/colecao-refugio-hero.jpg");

  await sharp("public/images/produtos/colecao-refugio-hero.jpg")
    .extract({
      left: Math.max(0, startX - 60),
      top: 40,
      width: Math.min(W - Math.max(0, startX - 60), 1200),
      height: H - 80,
    })
    .resize(1080, 1620, { fit: "cover" })
    .jpeg({ quality: 93, mozjpeg: true })
    .toFile("public/images/produtos/colecao-refugio-hero-mobile.jpg");

  console.log("ok", { startX, totalW, panels: panels.map((p) => p.width) });
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
