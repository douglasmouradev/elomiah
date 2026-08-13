import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./src/pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/components/**/*.{js,ts,jsx,tsx,mdx}",
    "./src/app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  theme: {
    extend: {
      colors: {
        elomiah: {
          green: "#152921",
          gold: "#B8956B",
          cream: "#F7F3EC",
          dark: "#0C1A14",
          muted: "#5C5C56",
          surface: "#EDE8DF",
          ink: "#1A1A18",
        },
      },
      fontFamily: {
        display: ["var(--font-cormorant)", "Georgia", "serif"],
        body: ["var(--font-dm-sans)", "system-ui", "sans-serif"],
      },
      letterSpacing: {
        brand: "0.18em",
        soft: "0.06em",
        wide: "0.14em",
      },
      boxShadow: {
        soft: "0 8px 32px rgba(12, 26, 20, 0.05)",
        lift: "0 24px 64px rgba(12, 26, 20, 0.09)",
        card: "0 2px 0 rgba(21, 41, 33, 0.04)",
      },
      backgroundImage: {
        linen:
          "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.028'/%3E%3C/svg%3E\")",
        grain:
          "url(\"data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='g'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='1.2' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23g)' opacity='0.04'/%3E%3C/svg%3E\")",
      },
      animation: {
        "fade-up": "fadeUp 0.9s ease-out forwards",
        mist: "mist 2.8s ease-out forwards",
      },
      keyframes: {
        fadeUp: {
          "0%": { opacity: "0", transform: "translateY(20px)" },
          "100%": { opacity: "1", transform: "translateY(0)" },
        },
        mist: {
          "0%": { opacity: "0", transform: "translateY(8px) scale(0.96)" },
          "30%": { opacity: "0.55" },
          "100%": { opacity: "0", transform: "translateY(-40px) scale(1.08)" },
        },
      },
      transitionTimingFunction: {
        premium: "cubic-bezier(0.22, 1, 0.36, 1)",
      },
    },
  },
  plugins: [],
};
export default config;
