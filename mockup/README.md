# zolug — Mockups (documentación maestra)

Índice y documentación completa de la carpeta `mockup/`. Punto de entrada único: desde aquí se llega a la fuente de verdad del sistema de diseño y a cada prototipo.

## Qué es

**zolug es un CRM para un call center de cobranza.** Esta carpeta contiene los **mockups HTML** del producto: pantallas estáticas, **autocontenidas** (un solo `.html` por pantalla, sin build, sin dependencias externas), **todo cliente, sin backend**. Los datos que se ven son **fixtures de muestra**; ninguna pantalla persiste ni llama a un servidor.

Cada prototipo tiene su documentación `.md` al lado, con el mismo nombre. Este README las integra.

## Fuente de verdad y jerarquía

1. **`base.html` + `base.md`** — la **fuente de verdad** del sistema de diseño: tokens de color (ambos temas), escala tipográfica, iconos, logo, bordes, reglas de pantalla. Todo lo demás obedece a esto.
2. **`layout/dashboard.html`** — el **shell estructural** (app-shell): topbar, rail, panel de contenido. Toda pantalla de módulo parte de aquí.
3. **`modules/**`** — las pantallas concretas, construidas sobre 1 y 2.
4. **`design-system/zolug/MASTER.md`** — artefacto **generado** por la skill de diseño; existe para el *gate* de proceso. **No refleja los tokens reales** (ver [Advertencia sobre MASTER.md](#advertencia-mastermd)).

> Regla de oro del proyecto (CLAUDE.md): **NUNCA se asume nada del negocio.** Lo que no está definido, se deja explícito como pendiente — no se inventa. Por eso cada `.md` cierra con una sección de *límites honestos*.

## Árbol completo — archivo por archivo

```
mockup/
├── README.md                         ← este archivo (documentación maestra)
├── .design-gate-receipt.json         ← recibo del design-gate (prueba de proceso, ver Enforcement)
├── base.html                         ← REFERENCIA: tokens, tipografía, iconos, logo, componentes
├── base.md                           ← doc de base.html (paleta, escala, bordes, reglas de pantalla)
├── design-system/
│   └── zolug/
│       ├── MASTER.md                 ← scaffold generado (NO es la paleta real — ver advertencia)
│       └── pages/                    ← vacío (overrides por página; ninguno definido aún)
├── layout/
│   ├── dashboard.html                ← REFERENCIA: app-shell (topbar 50px, rail 60px, panel .content)
│   └── dashboard.md                  ← doc del shell
└── modules/
    ├── auth/
    │   ├── auth.html                 ← login + recuperación de contraseña (solo tema oscuro)
    │   └── auth.md
    ├── clients/
    │   ├── clients.html              ← listado de clientes (tabla + búsqueda + paginación)
    │   ├── clients.md
    │   ├── client_detali.html        ← detalle de cliente (ficha de la persona + modales por dominio)
    │   └── client_detali.md
    └── search/
        ├── search.html               ← búsqueda (solo cabecera: dropdown de campo + input)
        └── search.md
```

Todo `.html` es autocontenido: CSS en `<style>`, iconos SVG incrustados en un mapa `ICONS`, JS en `<script>` al final. No hay `.css`, `.js`, ni imágenes sueltas.

## Sistema de diseño (resumen)

El detalle vive en **`base.md`**. Resumen operativo:

### Temas
**Oscuro y claro.** Oscuro medido de la app Plasma; claro derivado (misma familia violeta) con WCAG AA verificado. Tokens en `:root` y `:root[data-theme="light"]`. Switch sol/luna en el header.

### Paleta (modo oscuro — regla dura)
Fondo `#0d0c13` · superficie `#14121c` · elevado `#1a1922` · texto `#ffffff`/`#a2a9ad` · acento `#5a58c2` (fondo botón) / `#7474ef` (texto/icono/estado) · logo `#955dcd` · error `#df5158`. **Prohibido inventar, hardcodear o sustituir** un color fuera de la paleta.

### Tipografía
Una sola familia **Inter**, numerales tabulares. **Escala fija de 6 tamaños** `{32, 24, 20, 18, 16, 14}px` sobre 8 roles (`--fs-*`). **Piso 14px, cuerpo 16px.** Prohibido inventar tamaños fuera de la escala.

### Iconos
**Lucide** (outline, stroke 2). Cada `.html` incrusta solo los iconos que usa en su mapa `ICONS` y los pinta con `paintIcons()` sobre los `[data-i]`.

### Bordes — dos tokens, no intercambiables
- **`--border`** → encierra o separa bloques: contenedores (panel, tarjeta, tabla, buscador, dropdown, modal, input, botón-icono) **y el separador bajo la cabecera de 80px** (`.screen-head`). Oscuro `rgba(255,255,255,.22)` · claro `rgba(0,0,0,.32)`.
- **`--hair`** → separaciones **internas**: filas y cabecera de tabla, divisores entre secciones de una card, filas del modal, separador entre estadísticas. Oscuro `rgba(255,255,255,.06)` · claro `rgba(0,0,0,.08)`.

**Sin colores ni sombras hardcodeadas.** Los menús flotantes (dropdowns, modales) se separan solo con `--border` + `--bg-elevated`.

### Estructura de pantalla (CRM)
Toda pantalla de módulo, dentro de `.content` del shell, cumple:
- **Ancho completo** (nada de columnas centradas angostas).
- **Cabecera de 80px** al 100% del ancho: botón **Volver** (`arrow-left`, 40×40) + hairline vertical + **título** + descripción a la izquierda; **acciones** a la derecha; separada del contenido por un borde inferior (`--border`).
- **Responsive hasta tablet (~768px)** sin scroll horizontal.
- **Densidad CRM**: denso pero legible, respetando la escala.
- **Color al mínimo**: base neutra; acento con intención, no decorativo. Jerarquía por tamaño/peso/espacio, no por color.

## Prototipos

| Pantalla | Archivo | Tema | Estado | Doc |
|---|---|---|---|---|
| **App-shell** | `layout/dashboard.html` | Oscuro + claro | Referencia estructural; rail con módulos **placeholder** | [dashboard.md](layout/dashboard.md) |
| **Autenticación** | `modules/auth/auth.html` | Solo oscuro | Login + recuperación (3 pasos) + éxito | [auth.md](modules/auth/auth.md) |
| **Listado de clientes** | `modules/clients/clients.html` | Oscuro + claro | Tabla (4 columnas) + búsqueda por campo + paginación | [clients.md](modules/clients/clients.md) |
| **Detalle de cliente** | `modules/clients/client_detali.html` | Oscuro + claro | Ficha de la persona (`clients` + `client_*`) + modales por dominio | [client_detali.md](modules/clients/client_detali.md) |
| **Búsqueda** | `modules/search/search.html` | Oscuro + claro | Solo cabecera: dropdown de campo + input (sin resultados definidos) | [search.md](modules/search/search.md) |

Cada `.md` documenta a fondo su pantalla: estructura, interacción, estados, responsive, accesibilidad y **límites honestos** (qué es dato real del modelo y qué es muestra/placeholder).

### Alcance de cada pantalla (una línea)
- **dashboard** — cascarón: topbar (logo, breadcrumb, switch de tema, notificaciones/chat), rail de iconos (opciones **no definidas** aún + cerrar sesión), panel de contenido vacío con borde y esquina redondeada.
- **auth** — 3 columnas (rail de marca / formulario / panel social). Login con mostrar-contraseña; recuperación con código de 6 dígitos y checklist de requisitos en vivo. Envíos simulados.
- **clients** — lista todos los clientes; cada fila abre el detalle. Búsqueda en vivo por Nombre/Cédula/Teléfono (dropdown del sistema), paginación con elipsis, filas-por-página (dropdown que abre hacia arriba). Skeleton al cargar, estado "Sin resultados".
- **client_detali** — **solo la persona** (no créditos/gestiones/pagos como datos propios). Card de identidad + 7 iconos (uno por tabla `client_*`) que abren un modal inferior por dominio (teléfonos, correos, residencias, trabajos, EPS, vehículos, propiedades). Header con 2 dropdowns que **listan** créditos y procesos jurídicos.
- **search** — únicamente la cabecera: dropdown propio (Teléfono/Cédula/Operación/Proceso, ancho fijo) + input cuyo placeholder cambia según la opción. Sin resultados (por definir).

## Convenciones técnicas

- **Autocontenido**: cada pantalla es un `.html` que corre abriéndolo en el navegador, sin servidor.
- **Iconos**: mapa `ICONS` (defs de Lucide) + `svg(nombre, tamaño)` + pintado sobre `[data-i]`. Para agregar un icono se añade su path al mapa.
- **Dropdown del sistema** (patrón compartido, no `<select>` nativo): botón `aria-haspopup="listbox"` + `aria-expanded`, menú `role="listbox"` con opciones `role="option"`, opción activa en acento con **check**; cierra con clic afuera o `Esc`. Implementado en `search.html` (`.sb-dd`) y `clients.html` (`.cl-dd`, con variantes filtro y "Filas").
  - **Cuidado con `overflow:hidden`**: un menú dentro de un contenedor recortado (p. ej. el paginador de `clients.html`) debe **abrir hacia arriba** para no cortarse.
- **Pintar iconos inyecta `display:inline-flex` inline** en los `[data-i]`; para ocultar/mostrar el check por estado se usa `!important` en CSS.
- **Idioma**: interfaz en español; código, clases y comentarios en inglés.

## Enforcement (hooks que gobiernan `mockup/`)

Definidos en `.claude/rules/mockup-design-system.md` e implementados en `.claude/hooks/` (fuera de esta carpeta). Hacen **mecánicamente imposible** saltarse el proceso o romper el sistema:

| Hook | Momento | Qué hace |
|---|---|---|
| **`design-gate.mjs`** | PreToolUse (Write/Edit) | Bloquea escribir un mockup de módulo si no existe prueba de proceso: `design-system/**/MASTER.md` + un **`.design-gate-receipt.json` fresco (<180 min)** con las skills `ui-ux-pro-max` e `impeccable` y las referencias leídas. **Fail-closed.** |
| **`check-mockup.mjs`** | PostToolUse (Write/Edit) | Rechaza (exit 2) cualquier `mockup/**.html` que viole el **piso de 14px**, la escala de tamaños, el **foco visible**, o que reintroduzca el switch viejo (`--knob`). |
| **`no-shell-mockup-write.mjs`** | PreToolUse (Bash) | Bloquea editar un mockup por shell (`cat >`, `sed -i`, `tee`, `cp/mv`…) para forzar el uso de Write/Edit y no evadir los gates. |

Exentos (definen el sistema): `base.html` y `layout/dashboard.html`. Los tres hooks tienen test (`*.test.mjs`).

**Límite honesto de los hooks:** garantizan el *proceso* (correr las skills, dejar el recibo, no editar por atajos), **no la calidad del juicio** de diseño. Eso lo cubren la revisión visual y el detector de la skill.

## Cómo abrir

Cada pantalla es un archivo estático:

```
open mockup/layout/dashboard.html          # macOS
open mockup/modules/clients/clients.html
```

No hay `npm install`, ni servidor, ni pasos de build. Los prototipos también se registran en `../index.html` (galería en la raíz de `figma/`).

## Accesibilidad (transversal)

Verificada con la fórmula normativa de WCAG 2.1, medida **contra el peor fondo** de cada tema. Cubierto en todas las pantallas: `:focus-visible` global, contraste AA en texto, no depender solo del color (la opción activa del rail usa una **barra de forma**, no solo color), `aria` en controles, foco atrapado + retorno en modales, `prefers-reduced-motion`. El detalle por pantalla vive en cada `.md`.

## Límites honestos — lo que NO está definido

Consistente con la regla de no asumir. Pendiente de definir por el usuario:
- **Los módulos del negocio** — el rail del shell usa iconos y nombres placeholder (`aria-label="Opción N"`). Los módulos reales no están definidos.
- **Resultados de la búsqueda** (`search.html`) — qué muestra al buscar y con qué columnas queda por definir.
- **Procesos jurídicos** — se listan en el header del detalle, pero **la entidad no existe en `modelo-datos.md`** (solo hay `processes`, que es el estado del crédito). Tabla por definir.
- **Trabajo (detalle)** — Departamento y Barrio/Vereda existen en el modelo pero no se muestran por no tener valores validados.
- **Colores de gráficas y píldoras de estado** (verde/rojo/amarillo/naranja) — no medidos, sin definir; cuando se definan no podrán distinguirse solo por color.
- **Montos/bancos de créditos** en el detalle — placeholder; su significado de negocio no se infiere.

## Advertencia: MASTER.md {#advertencia-mastermd}

`design-system/zolug/MASTER.md` es un **scaffold generado** por la skill `ui-ux-pro-max`. Existe porque el `design-gate` exige su presencia como prueba de proceso. **Sus valores son genéricos y NO son los del proyecto**: propone azul `#2563EB` y fuentes Fira Code/Fira Sans con import de Google Fonts, cuando el sistema real usa la **paleta violeta de Plasma** e **Inter** (una sola familia, sin CDN). **Para cualquier decisión de diseño, la fuente de verdad es `base.html` / `base.md`, no MASTER.md.**
