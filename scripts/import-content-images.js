const sharp = require("sharp");
const fs = require("fs");
const path = require("path");

const ASSETS = path.join(
  process.env.USERPROFILE,
  ".cursor/projects/c-Users-Douglas-Desktop-Projetos-ELOMIAH/assets"
);
const ROOT = path.join(__dirname, "..");

async function squareJpeg(src, dest, size = 1200) {
  await sharp(src)
    .rotate()
    .resize(size, size, { fit: "cover", position: "centre" })
    .jpeg({ quality: 90, mozjpeg: true })
    .toFile(dest);
}

async function main() {
  const ach = path.join(ROOT, "public/images/achadinhos");
  const depo = path.join(ROOT, "public/images/depoimentos");
  fs.mkdirSync(ach, { recursive: true });
  fs.mkdirSync(depo, { recursive: true });

  const achadinhos = [
    ["achadinho-vela.png", "vela.jpg"],
    ["achadinho-lavanda.png", "lavanda.jpg"],
    ["achadinho-bandeja.png", "bandeja.jpg"],
    ["achadinho-sabonete.png", "sabonete.jpg"],
    ["achadinho-incenso.png", "incenso.jpg"],
    ["achadinho-copo.png", "copo.jpg"],
  ];

  for (const [from, to] of achadinhos) {
    await squareJpeg(path.join(ASSETS, from), path.join(ach, to));
  }

  await sharp(path.join(ASSETS, "geo-atelier.png"))
    .rotate()
    .resize(1400, 1750, { fit: "cover", position: "centre" })
    .jpeg({ quality: 92, mozjpeg: true })
    .toFile(path.join(ROOT, "public/images/geo.jpg"));

  const depos = [
    ["depo-manga.png", "mariana.jpg"],
    ["depo-ambar.png", "camila.jpg"],
    ["depo-flores.png", "helena.jpg"],
    ["depo-frasco.png", "beatriz.jpg"],
    ["depo-linho.png", "ana.jpg"],
  ];
  for (const [from, to] of depos) {
    await squareJpeg(path.join(ASSETS, from), path.join(depo, to), 600);
  }

  console.log("imagens ok");
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
