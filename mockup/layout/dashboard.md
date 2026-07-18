# dashboard.html — Layout (app-shell)

Estructura base del dashboard, sin contenido. Basado en la app Plasma (`img/hero.webp`, `img/plasma.webp`).

## Tema
**Oscuro y claro** — switch sol/luna en el header.

## Estructura
- **Header (50px de alto), sin borde.**
  - Izquierda: **logo** suelto (glifo violeta en oscuro, oscuro en claro) + "ZOLUG".
  - Centro: **breadcrumb** `avatar / rol / nombre` con "/" como separadores.
  - Derecha, 2 secciones: **switch sol/luna** (interruptor con perilla) · divisor · **notificaciones + chat**.
- **Rail / aside (60px de ancho), solo iconos, sin borde.** Arriba, las **opciones** (placeholders — los módulos aún no están definidos). **Al fondo del rail: el icono de Cerrar sesión.**
- **Main (contenido).** Vacío, con lienzo punteado. El **borde va aquí** (top + left) con **esquina superior-izquierda redondeada**, sin margen — así el header + rail se leen como un solo bloque.
- **Chat drawer** — panel lateral que abre el icono de chat (placeholder).

## Interacción
Switch de tema y opción activa del rail. El icono de chat es solo un botón, **sin acción** (el panel de chat no está definido). Todo cliente, sin backend.

## Bordes
- **`--hair`** — divisores finos internos (el divisor entre el switch y las acciones). Oscuro `rgba(255,255,255,.06)` · Claro `rgba(0,0,0,.08)`.
- **`--border`** — borde del **panel de contenido** (`.content`); es el que **separa las secciones**. Oscuro `rgba(255,255,255,.22)` · Claro `rgba(0,0,0,.32)`. Claramente visible.
- **Header y rail: SIN borde** a propósito — se leen como un solo bloque.
- **Main:** el borde vive aquí — `border-top` + `border-left` + esquina superior-izquierda redondeada (18px).

## Base
Tokens, tipografía Inter e iconos Lucide de `base.html`. Estética shadcn.

## Correcciones de accesibilidad aplicadas

| Elemento | Antes | Ahora | Criterio |
|---|---|---|---|
| **Opción activa del rail** | solo color — **1.61:1** entre icono activo e inactivo | **barra de acento** de 3px al borde izquierdo (5.08:1 oscuro · 5.02:1 claro) | [1.4.1](https://www.w3.org/TR/WCAG21/#use-of-color) **nivel A** |
| **Perilla del switch** | `#2a2740` oscuro (1.29:1) · `#ffffff` claro sobre riel blanco (**1.00:1**) | `text-muted` en ambos temas — 7.78:1 y 6.04:1 | [1.4.11](https://www.w3.org/TR/WCAG21/#non-text-contrast) AA |
| **Botones del rail** | 6 de 7 sin nombre accesible | `aria-label` + `aria-current="page"` en el activo | [4.1.2](https://www.w3.org/TR/WCAG21/#name-role-value) **nivel A** |
| **Foco de teclado** | ninguna regla en todo el archivo | `:focus-visible` con anillo de acento | [2.4.7](https://www.w3.org/TR/WCAG21/#focus-visible) AA |
| **Separadores `/` del breadcrumb** | texto real, se leían en voz alta | `aria-hidden="true"` | — |
| **Token `--error`** | `#dc424a` (fallaba como texto en claro) | `#df5158` oscuro · `#c62f38` claro | 1.4.3 AA |

### Por qué una barra y no más color
El icono activo y el inactivo tienen **casi la misma luminancia** (1.61:1). WCAG 1.4.1 permite usar color como distinción única solo si los dos colores difieren en luminancia **≥3:1** — no llegan ni a la mitad. La barra es una diferencia de **forma** (está o no está), que sí cumple. Es el mismo mecanismo que usa la pestaña activa de la referencia (`img/hero.webp`).

## Lo que NO es incumplimiento
El borde del `.content` (1.93:1) y los `--hair` **no violan 1.4.11** — la norma trata como decorativo *"a dark border line between contrasting backgrounds"*. Su sutileza es decisión estética heredada de Plasma.

## Pendiente / notas
- **Paleta clara: ya NO es provisional.** Sincronizada con la oficial de `base.html` (`#f0edf5`, `#f8f6fb`, `#ffffff`, `#605c6b`, `#c62f38`, acento `#5a58c2`).
- **Opciones del rail** — iconos placeholder y nombres `aria-label="Opción N"`. Son honestos, no inventados: **los módulos del negocio no están definidos**. Reemplazar por los nombres reales cuando se definan.
- Nombre "Ana Gómez" y la foto son de muestra; el rol "Gestor de cobranza" sí es real.
