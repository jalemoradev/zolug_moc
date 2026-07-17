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

## Pendiente / notas
- **Paleta clara PROVISIONAL** — definida en este archivo, **no sale de `base.html`** (que solo tiene la oscura). Falta definirla formalmente.
- **Opciones del rail** — iconos placeholder; faltan los nombres/iconos reales de los módulos.
- Nombre "Ana Gómez" y la foto son de muestra; el rol "Gestor de cobranza" sí es real.
