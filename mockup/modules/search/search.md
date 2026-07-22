# search.html — Búsqueda

Módulo de búsqueda. Es **solo la cabecera de pantalla**: sin contenido debajo. Su única función es elegir **por qué campo** se busca y **escribir el valor**. Basado en el shell `layout/dashboard.html`; tokens, tipografía Inter e iconos Lucide de `base.html`.

## Tema
**Oscuro y claro** — switch sol/luna del header. Paleta de la regla de `base.md`.

## Estructura

### Cabecera de pantalla (80px, ancho completo) — patrón de `base.md`
- **Extremo izquierdo:** botón **Volver** (flecha `arrow-left`, cuadrado 40×40, borde `--border` + fondo `--bg-surface`) + hairline vertical.
- **Izquierda:** título **"Búsqueda"** + descripción.
- **Derecha (acciones) — el buscador:** un control compuesto de dos partes dentro de un mismo contenedor (`--border` + `--bg-surface`):
  1. **Dropdown propio del sistema** (no `<select>` nativo): botón de **ancho fijo (140px)** con el label a la izquierda y el chevron a la derecha — **no cambia de tamaño** según la opción. Al abrir, muestra un panel (`--bg-elevated` + `--border`) con las **4 opciones**: `Teléfono` · `Cédula` · `Operación` · `Proceso`. La opción activa va en acento con un **check** a la derecha.
  2. **Campo de valor** (input) con icono de lupa; el **placeholder cambia** según la opción elegida ("Ingrese el teléfono / la cédula / el número de operación / el número de proceso").

### Contenido
**Vacío** a propósito (el módulo es solo la cabecera). No hay resultados ni tabla.

## Interacción
Todo cliente, sin backend.
- El dropdown **abre/cierra**, cierra al hacer clic afuera o con `Esc`, y al elegir una opción actualiza el label, marca el check y ajusta el placeholder del input.
- El input **no dispara** una búsqueda real (no hay resultados definidos): es la entrada del valor.

## Accesibilidad
Dropdown con `aria-haspopup="listbox"` / `aria-expanded` y opciones con `role="option"`; input con `aria-label`; botón Volver con `aria-label`/`title`; `:focus-visible` global. Escala tipográfica de `base.html` (piso 14 / cuerpo 16). Color al mínimo (acento solo en la opción activa y el foco).

## Responsive
Hasta tablet: la cabecera **apila** sus partes y el buscador pasa a **ancho completo** (el input crece, el dropdown mantiene su ancho fijo). Sin scroll horizontal.

## Bordes / sistema
Todo borde sale de **`--border`** (contenedores: buscador, dropdown, panel, botón Volver) y **`--hair`** (hairline bajo la cabecera y divisor vertical). **Sin colores ni sombras hardcodeadas:** el panel del dropdown se separa solo con `--border` + `--bg-elevated`, como el resto del sistema.

## Límites honestos
- El título "Búsqueda" y la descripción son **etiquetas de pantalla**, no datos de negocio.
- **No hay resultados ni columnas definidas.** Qué muestra al buscar y con qué campos **queda por definir** (pendiente de confirmar con el usuario).
- Las cuatro opciones (Teléfono/Cédula/Operación/Proceso) son las indicadas por el usuario. **"Proceso"** alude al número de proceso jurídico, entidad que **aún no está en `modelo-datos.md`**.
