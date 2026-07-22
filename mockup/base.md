# base.html — Guía visual

Referencia visual del proyecto zolug. Fuente de verdad de tokens, tipografía, iconos, logo y sistema de componentes.

## Tema
**Oscuro y claro.** El tema oscuro está medido de Plasma; el claro es **derivado** (misma familia violeta) con **WCAG AA verificado**. Ambos definidos como tokens `:root` y `:root[data-theme="light"]`.

## Paleta — modo oscuro (regla dura)

**Estos, y solo estos, son los colores del modo oscuro de zolug.** Medidos de Plasma, WCAG AA verificado. **Toda pantalla de módulo** toma sus colores oscuros de estos tokens exactos, vía las variables del shell `layout/dashboard.html`. **Prohibido inventar, hardcodear o sustituir** un color oscuro fuera de esta paleta.

| Token (`base.html`) | Variable en pantalla | Valor (oscuro) | Uso |
|---|---|---|---|
| `bg/page` | `--bg-page` | `#0d0c13` | Fondo de página |
| `bg/surface` | `--bg-surface` | `#14121c` | Panel / superficie |
| `bg/elevated` | `--bg-elevated` | `#1a1922` | Tarjeta / fila / nodo |
| `text/primary` | `--text-primary` | `#ffffff` | Texto principal (17.41:1) |
| `text/muted` | `--text-muted` | `#a2a9ad` | Texto secundario (7.31:1) |
| `accent/solid` | `--accent-solid` | `#5a58c2` | Acento: fondo de botón (blanco 5.82:1) |
| `accent/on-dark` | `--accent-on-dark` | `#7474ef` | Acento: texto / icono / estado (4.55:1) |
| `accent/logo-ref` | `--logo` | `#955dcd` | Color real del logo — referencia, **NO AA como texto** |
| `feedback/error` | `--error` | `#df5158` | Error (4.52:1 sobre el peor fondo) |
| `border/default` (contenedor) | `--border` | `rgba(255,255,255,.22)` | Borde de contenedores — ver **Bordes** |
| hairline (divisor) | `--hair` | `rgba(255,255,255,.06)` | Divisor fino interno — ver **Bordes** |

**Notas:** el acento se usa **con intención, no decorativamente** (ver *Color al mínimo*). El logo (`--logo` / `accent/logo-ref`) no cumple AA como texto: para texto/icono de acento se usa **`--accent-on-dark`**. Los valores del tema claro viven en `:root[data-theme="light"]` (sección de tokens claros de `base.html`).

## Contenido
- **Paleta (11 tokens)** — medida de la aplicación Plasma, WCAG AA verificado. Fondo `#0d0c13`, superficie `#14121c`, elevado `#1a1922`, bordes, texto, acento `#5a58c2`/`#7474ef`, `accent/logo-ref #955dcd`, error `#df5158`.
- **Profundidad por capas** — demo de page → surface → elevated con bordes.
- **Tipografía** — Inter (una sola familia), numerales tabulares. **Escala de 8 roles sobre 6 tamaños** (ver sección propia), piso 14px, cuerpo 16px.
- **Iconos** — Lucide (outline, stroke 2, 24px), 29 iconos base incrustados (offline).
- **Marca / logo** — 3 SVG (`logo.svg`, `logo_text.svg`, `text.svg`) con `fill:currentColor` para recolorear.
- **Componentes** — el proyecto usa **shadcn/ui** (Radix + Tailwind).
- **Bloque CSS** — tokens `--color-*` listos para Tailwind / React + Vite.

## Bordes

**Tokens canónicos del layout (los que TODA pantalla debe usar), en ambos temas:**

| Token | Uso | Oscuro | Claro |
|---|---|---|---|
| `--border` | Borde de contenedores: panel de contenido, tarjetas, bloques, tablas, secciones — **el que separa** | `rgba(255,255,255,.22)` | `rgba(0,0,0,.32)` |
| `--hair` | Divisores finos internos (líneas sutiles dentro de un bloque) | `rgba(255,255,255,.06)` | `rgba(0,0,0,.08)` |

**Regla dura:** toda pantalla toma sus bordes de **`--border`** (contenedores) y **`--hair`** (divisores internos), en ambos temas. **Prohibido inventar otro color de borde** o hardcodearlo. Son los mismos tokens que define el shell `dashboard.html`.

**Tokens hex heredados (inputs/tarjetas estilo Plasma), ambos temas:**
- **`border/subtle`** — separador tenue. Oscuro `#201E2A` · Claro `#E5E1EC`.
- **`border/default`** — borde estándar de inputs. Oscuro `#2A2740` · Claro `#D8D3E2`.

## Estructura de pantalla (CRM de call center)

**zolug es un CRM para un call center de cobranza.** El diseño se optimiza para operar rápido: densidad útil, cada espacio aprovechado, información clara y ordenada. **Toda pantalla de módulo** (dentro de `.content` de `dashboard.html`) DEBE cumplir:

- **Ancho completo.** El contenido ocupa el **100% del ancho** de `.content`. **Prohibidas** las columnas centradas angostas (nada de `max-width` tipo blog/editorial): en un CRM se aprovecha todo el ancho.
- **Cabecera de pantalla — 80px, ancho completo.** Toda pantalla arranca con una cabecera de **80px de alto** al 100% del ancho:
  - **Extremo izquierdo — botón "Volver".** Antes del título va un botón para volver atrás: icono de flecha (`arrow-left`), **cuadrado 40×40**, con el mismo estilo que los demás iconos del header — **borde `--border`, fondo `--bg-surface`, hover `--bg-elevated`**. Separado del bloque de título por un **hairline vertical** (`--border`, ~34px de alto). Solo icono, con `aria-label`/`title` "Volver".
  - **Izquierda:** el **título** de la pantalla + una **descripción** breve.
  - **Derecha:** los **botones de acción** que necesite la pantalla (primario + secundarios), alineados a la derecha.
  - Separada del contenido por un hairline inferior. Puede quedar sticky.
- **Responsive hasta tablet (~768px).** El layout se adapta con gracia: las rejillas reflujan a menos columnas y la cabecera apila/condensa sus acciones sin romperse. **Sin scroll horizontal** en ningún punto.
- **Densidad CRM, no ruido.** Presentar la información aprovechando el espacio, clara y organizada: denso pero **legible y sobrio** — respetando la escala tipográfica (piso 14 / cuerpo 16) y el **color al mínimo**.
- **Color al mínimo (regla permanente).** Base neutra (fondos, texto, hairlines). El acento se usa **con moderación y con intención**, no decorativamente. **Prohibido** llenar la pantalla de chips/píldoras/badges/iconos coloreados. La jerarquía se logra con **tamaño, peso y espacio**, no con color. La vigencia/estado se comunica con texto y peso, no con color.

## Tipografía — escala de tamaños

Una sola familia (**Inter**) con numerales tabulares. Los tamaños son **independientes del tema** (idénticos en claro y oscuro). Ocho roles sobre seis tamaños; cada rol define tamaño + peso + interlineado + tracking, no solo el px.

| Rol | Token | Tamaño | Peso | Interlineado | Tracking | Uso |
|---|---|---|---|---|---|---|
| **Display XL** | `--fs-display-xl` | 32px | 700 | 1.2 | −0.02em | cifras hero / marketing (login `+300`·`+10`) |
| **Display L** | `--fs-display-l` | 24px | 700 | 1.2 | −0.02em | título de página (h1 de módulo) |
| **Heading** | `--fs-heading` | 20px | 600 | 1.35 | −0.01em | título de sección / card / drawer |
| **Subheading** | `--fs-subheading` | 18px | 600 | 1.35 | −0.01em | nombre destacado / subtítulo |
| **Body** | `--fs-body` | 16px | 400 | 1.5 | 0 | párrafos y datos — **piso de lectura** |
| **Body strong** | `--fs-body` + `--fw-semibold` | 16px | 600 | 1.5 | 0 | valor destacado dentro del cuerpo |
| **Label** | `--fs-label` | 14px | 600 | 1.35 | 0.05em · MAYÚS | etiquetas small-caps, headers de tabla, badges |
| **Caption** | `--fs-caption` | 14px | 400 | 1.35 | 0 | descripción auxiliar / texto de ayuda |

**Tokens de apoyo:** pesos `--fw-regular 400` · `--fw-medium 500` · `--fw-semibold 600` · `--fw-bold 700`; interlineados `--lh-tight 1.2` · `--lh-snug 1.35` · `--lh-normal 1.5`; tracking `--ls-tight −0.02em` · `--ls-snug −0.01em` · `--ls-label 0.05em`.

### Reglas duras
- **Piso 14px. Cuerpo 16px.** Ningún texto por debajo de 14px.
- **Prohibido inventar tamaños** fuera de estos 6 tokens. Si un caso parece necesitar otro tamaño, es un rol nuevo que se discute y se agrega aquí — no se hardcodea suelto.
- **WCAG no fija un mínimo de píxeles.** Lo que exige es que el texto se pueda **ampliar al 200% sin romper el layout** ([SC 1.4.4](https://www.w3.org/TR/WCAG21/#resize-text)). El piso de 14px y el cuerpo de 16px son **política del proyecto** para baja visión, presbicia y astigmatismo.
- **Esto no resuelve daltonismo.** El daltonismo es discriminación de color, no de tamaño; agrandar el texto no ayuda a un daltónico. Ese eje se cubre con contraste y no-depender-solo-del-color, no con esta escala.
- **Texto grande = umbral 3:1.** Todo lo ≥18px, o ≥14px en negrita (Display XL/L, Heading, Subheading, Body strong), cuenta como "texto grande" en WCAG y su contraste mínimo baja de 4.5:1 a 3:1.

### Estado de la migración
Las pantallas de módulo (`auth`, `dashboard`, `detalle-panel-c`) ya están **migradas a esta escala** (2026-07-21): todos sus `font-size` usan los tokens `var(--fs-*)`, sin tamaños ad-hoc. La escala se subió un escalón respecto a la primera versión (piso 12→**14**, cuerpo 14→**16**) para favorecer la baja visión. Único pendiente ajeno a la tipografía: `detalle-panel-c` todavía trae el switch viejo (`--knob`), que el hook bloquea hasta reemplazarlo por el de `dashboard.html`.

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
