# client_detali.html — Detalle de cliente (solo la persona)

Ficha de una persona del modelo. Alcance **exclusivo**: la persona (`clients` + tablas `client_*`). **No** incluye créditos, gestiones, pagos, acuerdos ni jurídico como datos propios de la ficha (los créditos y procesos jurídicos solo se **listan** en dos dropdowns del header — ver abajo). Basado en el shell `layout/dashboard.html`; tokens, tipografía Inter e iconos Lucide de `base.html`.

## Tema
**Oscuro y claro** — switch sol/luna del header. Paleta de la regla de `base.md` (Paleta — modo oscuro).

## Estructura

### 1. Cabecera de pantalla (80px, ancho completo) — patrón de `base.md`
- **Extremo izquierdo:** botón **Volver** (flecha `arrow-left`, cuadrado 40×40, borde `--border` + fondo `--bg-surface`) + hairline vertical.
- **Izquierda:** título **"Detalle de cliente"** + descripción.
- **Derecha:** **2 dropdowns** seguidos:
  - **Créditos** — al desplegar lista los créditos del cliente; cada uno: **rol** (Titular / Codeudor), **banco**, **valor total**.
  - **Procesos jurídicos** — al desplegar lista los procesos; cada uno: **rol** (Demandante / Demandado) y **número de proceso**.
  - Ambos pueden estar vacíos ("puede no tener"). Abren/cierran, cierran con clic afuera o `Esc`.

### 2. Card de identidad
- **Izquierda:** avatar (iniciales) + **nombre** + **documento** (`tipo número`).
- **Derecha:** **7 iconos**, uno por cada tabla relacionada del cliente (`modelo-datos.md`): teléfonos, correos, residencias, trabajos, salud (EPS), vehículos, propiedades. Cada icono **abre el modal inferior** de su dominio (ver §4). Fondo `--bg-surface`, borde `--border`.

### 3. Secciones de la card (resumen "lo principal / actual")
Cada sección va separada por una **línea divisora con label** (small-caps). Muestra solo el registro vigente:
- **Contacto** — teléfono **principal** + correo.
- **Ubicación** — residencia **actual** con todos sus campos: Departamento · Ciudad · Barrio/Vereda · Dirección · Detalle.
- **Salud** — EPS **vigente**: EPS · Régimen · Estado.
- **Trabajo** — trabajo **activo**: Empleador · Teléfono · Salario · Ciudad · Dirección.
- **Bienes** — Vehículos y Propiedades juntos, cada uno con icono + nombre + **badge de conteo** (no tienen "principal"; el detalle completo se ve en su modal).
- Todas las secciones comparten **una grilla de columnas** para que los campos queden alineados verticalmente entre secciones.

### 4. Modal inferior por dominio (bottom sheet)
Se abre desde los 7 iconos (y desde Vehículos/Propiedades de Bienes). Ocupa el **100% del ancho**, esquinas rectas, con **grabber**. Contiene:
- **Cabecera:** icono del dominio + título + subtítulo que **enseña la regla de marcado** (p. ej. "Un solo teléfono principal; varios pueden estar en uso") + botón **Agregar** + cerrar.
- **Tabla** de todos los registros del dominio, con la **fila principal/actual resaltada** (ancla visual) y los controles agrupados a la derecha con divisor:
  - **Teléfonos / Correos:** columna **En uso** (switch, booleano por registro) + columna **Principal** (radio, selección única).
  - **Residencias / Salud / Trabajos:** columna **Actual / Vigente / Activo** (radio, selección única).
  - **Vehículos / Propiedades:** sin marca; solo listar y registrar.
- **Registrar:** el botón "Agregar" (se oculta mientras el formulario está abierto) despliega un formulario en **grilla responsive** (campos con su tipo: `tel`/`email`/numérico), **foco al primer campo** y **validación** del obligatorio; **Guardar/Cancelar** en su propia fila. Al guardar, la nueva fila hace **flash** (feedback de éxito).

## Interacción
Todo cliente, sin backend. Los datos se mutan en memoria: marcar principal/actual mueve el estado; en uso alterna; registrar agrega la fila. Dropdowns y modal cierran con clic afuera / `Esc`.

## Estados (cobertura)
default · hover · **focus** (anillo; foco al abrir el modal) · active · **disabled** (el radio del registro ya marcado) · **loading** (skeleton al abrir el modal) · **empty** (icono + guía cuando no hay registros) · **error** (validación del formulario) · **success** (flash de la fila nueva).

## Accesibilidad
`aria` en switch (`role="switch"`/`aria-checked`) y radios; **foco atrapado** dentro del modal con **retorno de foco** al disparador al cerrar; `Esc`; `:focus-visible` global; `prefers-reduced-motion` (sin animaciones). Escala tipográfica de `base.html` (piso 14 / cuerpo 16). Color al mínimo: acento solo para estado/acción.

## Responsive
Hasta tablet: las secciones de la card reflujan la grilla (5→3→2 columnas); el modal apila la tabla fila por fila (sin scroll horizontal) y el formulario reduce columnas. Sin scroll horizontal en ningún punto.

## Datos y límites honestos
- Los datos son **muestra (fixture)** del prototipo; la persona es fiel a `modelo-datos.md` (person-only). **No se inventan agregados** ni campos fuera del modelo.
- **Créditos** existe en el modelo (`credits`): rol se deriva de `holder_client_id` (Titular) / `codebtor_client_ids` (Codeudor); valor total = `balances.total`; banco = `types_banks`. Los montos/bancos mostrados son placeholder.
- **Procesos jurídicos NO está en `modelo-datos.md`** todavía (solo existe `processes`, que es el estado del crédito). El dropdown usa los campos indicados por el usuario (rol demandante/demandado + número); esa tabla queda pendiente de definir en el modelo.
- **Trabajo**: el modelo tiene además Departamento y Barrio/Vereda; no se muestran porque no había valores validados (pendiente de confirmar).
