# base.html — Guía visual

Referencia visual del proyecto zolug. Fuente de verdad de tokens, tipografía, iconos, logo y sistema de componentes.

## Tema
**Oscuro y claro.** El tema oscuro está medido de Plasma; el claro es **derivado** (misma familia violeta) con **WCAG AA verificado**. Ambos definidos como tokens `:root` y `:root[data-theme="light"]`.

## Contenido
- **Paleta (11 tokens)** — medida de la aplicación Plasma, WCAG AA verificado. Fondo `#0d0c13`, superficie `#14121c`, elevado `#1a1922`, bordes, texto, acento `#5a58c2`/`#7474ef`, `accent/logo-ref #955dcd`, error `#dc424a`.
- **Profundidad por capas** — demo de page → surface → elevated con bordes.
- **Tipografía** — Inter (una sola familia), numerales tabulares para cifras.
- **Iconos** — Lucide (outline, stroke 2, 24px), 29 iconos base incrustados (offline).
- **Marca / logo** — 3 SVG (`logo.svg`, `logo_text.svg`, `text.svg`) con `fill:currentColor` para recolorear.
- **Componentes** — el proyecto usa **shadcn/ui** (Radix + Tailwind).
- **Bloque CSS** — tokens `--color-*` listos para Tailwind / React + Vite.

## Bordes
Dos tokens de borde, ambos temas:
- **`border/subtle`** — separador tenue. Oscuro `#201E2A` · Claro `#E5E1EC`.
- **`border/default`** — borde estándar (inputs, tarjetas, paneles). Oscuro `#2A2740` · Claro `#D8D3E2`.

## Pendiente
- Colores de gráficas y píldoras de estado (verde/rojo/amarillo/naranja): no medidos, sin definir.
