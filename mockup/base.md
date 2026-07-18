# base.html — Guía visual

Referencia visual del proyecto zolug. Fuente de verdad de tokens, tipografía, iconos, logo y sistema de componentes.

## Tema
**Oscuro y claro.** El tema oscuro está medido de Plasma; el claro es **derivado** (misma familia violeta) con **WCAG AA verificado**. Ambos definidos como tokens `:root` y `:root[data-theme="light"]`.

## Contenido
- **Paleta (11 tokens)** — medida de la aplicación Plasma, WCAG AA verificado. Fondo `#0d0c13`, superficie `#14121c`, elevado `#1a1922`, bordes, texto, acento `#5a58c2`/`#7474ef`, `accent/logo-ref #955dcd`, error `#df5158`.
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

## Cómo se miden los contrastes
Siempre contra el **peor fondo** de cada tema: `bg/elevated` en oscuro, `bg/page` en claro. Medir solo contra el fondo de página oculta fallas sobre paneles y tarjetas, que es donde vive la mayoría del texto.

Fórmula normativa de [WCAG 2.1](https://www.w3.org/TR/WCAG21/#dfn-contrast-ratio), umbral de linealización `0.04045` (vigente desde mayo de 2021; el `0.03928` que circula está obsoleto).

## Correcciones de accesibilidad aplicadas
- **`feedback/error`: `#dc424a` → `#df5158`.** El anterior daba 4.08:1 sobre `bg/elevated` y 4.34:1 sobre `bg/surface` — fallaba [SC 1.4.3](https://www.w3.org/TR/WCAG21/#contrast-minimum) justo donde se usa (dentro de tarjetas y formularios). El nuevo da 4.52:1 en el peor caso.
- **Indicador de foco global** — regla `:focus-visible` para todo elemento operable con teclado, exigido por [SC 2.4.7](https://www.w3.org/TR/WCAG21/#focus-visible).
- Los ratios de la tabla de tokens se recalcularon contra el peor fondo. `accent/logo-ref` pasa de "4.38:1" a **3.92:1** — sigue sin cumplir AA como texto, por eso la interfaz usa `accent/on-dark`.

## Lo que NO es incumplimiento (verificado contra el W3C)
Los bordes por debajo de 3:1 **no violan** [SC 1.4.11](https://www.w3.org/WAI/WCAG21/Understanding/non-text-contrast). La norma exime explícitamente el borde de un control que ya tiene contenido visible (etiqueta o icono con contraste suficiente), y trata como decorativo *"a dark border line between contrasting backgrounds"*. La sutileza de los bordes es una **decisión estética** heredada de Plasma, no una falla.

## Pendiente
- Colores de gráficas y píldoras de estado (verde/rojo/amarillo/naranja): no medidos, sin definir. Cuando se definan, **no pueden distinguirse solo por color**: `error` y `accent` tienen contraste mutuo de 1.03:1 bajo deuteranopía (simulación Viénot 1999, válida solo para protanopía y deuteranopía).
