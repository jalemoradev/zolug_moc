# clients.html — Listado de clientes

Pantalla que lista **todos los clientes**. Cada fila abre el detalle (`client_detali.html`). Basado en el shell `layout/dashboard.html`; tokens, tipografía Inter e iconos Lucide de `base.html`.

## Tema
**Oscuro y claro** — switch sol/luna del header. Paleta de la regla de `base.md`.

## Estructura

### 1. Cabecera de pantalla (80px, ancho completo) — patrón de `base.md`
- **Extremo izquierdo:** botón **Volver** (flecha `arrow-left`, cuadrado 40×40) + hairline vertical.
- **Izquierda:** título **"Clientes"** + descripción con el **conteo total** ("N clientes").
- **Derecha — buscador:** control compuesto = **dropdown propio del sistema** (no `<select>` nativo; botón de **ancho fijo 132px** con chevron que rota) para elegir el campo (`Nombre` / `Cédula` / `Teléfono`) + **input** de búsqueda con icono de lupa. El dropdown define por cuál campo se busca; la opción activa va en acento con un **check**.

### 2. Contenedor de la lista (ocupa todo el alto)
Panel con borde `--border` + `--bg-surface` que **llena el alto** del área de contenido. Adentro:
- **Área con scroll interno** + **`thead` fijo** (sticky) al desplazar.
- **Tabla** a 100% del ancho, con exactamente **4 columnas** (definidas por el usuario):
  1. **Tipo de documento** (píldora: CC / CE / PA).
  2. **Número de documento** (tabular).
  3. **Nombre**.
  4. **Teléfono principal** (tabular).
  - Quinta celda: **chevron** como afordancia de "abre el detalle".
- **Paginador** fijo al fondo del contenedor.

### 3. Paginador
- **Izquierda:** rango **"Mostrando a–b de N"** + **filas por página**: **dropdown del sistema** (8 / 25 / 50). Como el paginador vive al fondo del contenedor (`.cl-panel` con `overflow:hidden`), este menú **abre hacia arriba** (`bottom: calc(100% + 8px)`) para no recortarse.
- **Derecha:** **Anterior** (‹) + **números de página** con la **página actual resaltada** en acento + **Siguiente** (›). Con muchas páginas colapsa con **elipsis** (`1 … 4 5 6 … 20`), mostrando siempre primera y última. Anterior/Siguiente se **deshabilitan** en los extremos.

## Interacción
Todo cliente, sin backend.
- **Fila → detalle:** clic o `Enter` sobre una fila navega a `client_detali.html` (filas `tabindex`).
- **Búsqueda en vivo:** filtra por el campo elegido en el dropdown; para **Cédula/Teléfono** ignora puntos y espacios (compara solo dígitos); al escribir vuelve a la **página 1**; si nada coincide, muestra el estado **"Sin resultados"**.
- **Dropdowns (filtro y filas):** un mismo componente `.cl-dd` con dos variantes; abren/cierran, cierran con clic afuera o `Esc`, marcan la opción con check y actualizan el label.
- **Paginación:** los números y filas-por-página recalculan la vista; siempre operan **sobre los resultados filtrados**.

## Estados
**loading** (skeleton de filas al cargar) · default · hover de fila · **focus** (anillo, fila enfocable) · **empty** ("Sin resultados" cuando la búsqueda no arroja nada). `prefers-reduced-motion` respetado.

## Responsive
Hasta tablet: el buscador pasa a **ancho completo** bajo el título; la tabla se **apila** en pares etiqueta/valor (sin scroll horizontal). El contenedor sigue ocupando el alto y el paginador queda abajo.

## Accesibilidad
`:focus-visible` global (incluidos botón y opciones del dropdown); filas operables con teclado (`Enter`); `aria-label` en filas y controles de paginación; dropdowns con `aria-haspopup="listbox"`/`aria-expanded` y opciones `role="option"`; input con `aria-label`. Escala tipográfica de `base.html` (piso 14 / cuerpo 16), tabular-nums en documento/teléfono. Color al mínimo.

## Datos y límites honestos
- Los **20 clientes** son **muestra (fixture)** del prototipo.
- **Columnas:** solo las cuatro pedidas. `Tipo de documento`, `Número de documento` y `Nombre` son campos directos de la tabla `clients`; **`Teléfono principal` es un join** de `client_phones` (el que tiene `is_primary`).
- No se incluyó **"Agregar cliente"** ni filtros adicionales por no estar confirmados; el buscador y la paginación quedan listos para el volumen real (la tabla `clients` ronda las decenas de miles de filas).
