# auth.html — Autenticación

Flujo de login y recuperación de contraseña.

## Tema
**Solo oscuro.**

## Estructura — 3 secciones (columnas)
1. **Rail izquierdo (64px):** isotipo `logo.svg` en **blanco** arriba, "ZOLUG" vertical al medio, y la versión **V.5.3** abajo. Su propia columna, con borde a la derecha.
2. **Formulario (centro):** logo `logo_text.svg` (isotipo + "zolug") **en blanco** + el login/recuperación.
3. **Panel de marca (derecha):** jerarquía — logo de **codelab** (blanco) → "zolug es un desarrollo de codelab" → 5 fotos `+300` → **dos cifras grandes** (`+300` personas · `+10` empresas, separadas por un divisor) → línea corta "ya trabajan con los sistemas a la medida de codelab".

Divisores visibles entre las 3 secciones (`rgba(255,255,255,.16)`). En pantallas < 880px se ocultan el rail y el panel de marca; queda solo el formulario.

## Pantallas (interactivas, sin backend)
1. **Login** — usuario + contraseña (mostrar/ocultar con ojo), enlace "¿Olvidó su contraseña?".
2. **Recuperación (3 pasos)** con barras de progreso (verde=hecho, acento=actual):
   - Solicitar código (correo).
   - Verificar código (6 dígitos, auto-avance).
   - Nueva contraseña — con **checklist de requisitos en vivo**: mínimo 8 caracteres, una mayúscula, una minúscula, un número, un carácter especial. No deja guardar hasta cumplir los cinco.
3. **Éxito** — confirmación → vuelve a login.

## Base
Tokens, tipografía Inter e iconos Lucide de `base.html`. Estética shadcn.

## Bordes
- **Divisor entre secciones** (formulario | panel de marca): `.pane-brand` con `border-left` 1px `rgba(255,255,255,.16)` — subido para que se distinga claramente.
- **Inputs, tarjetas y barras de progreso:** `border/default` (`#2A2740`).
- Tema oscuro únicamente.

## Correcciones de accesibilidad aplicadas
Todas verificadas con la fórmula normativa de WCAG 2.1 contra el fondo real de cada elemento.

| Elemento | Antes | Ahora | Criterio |
|---|---|---|---|
| Texto "ZOLUG" vertical y "V.5.3" | `#4b4a54` — **2.26:1** | `#797787` — 4.51:1 | [1.4.3](https://www.w3.org/TR/WCAG21/#contrast-minimum) AA |
| Placeholder de los inputs | `#5f5e68` — **2.73:1** | `#82818d` — 4.54:1 | 1.4.3 AA |
| Token `--error` | `#dc424a` | `#df5158` — 5.12:1 | 1.4.3 AA |
| Barra de paso apagada | `border/default` — **1.37:1** | `#5d578e` — 3.02:1 | [1.4.11](https://www.w3.org/TR/WCAG21/#non-text-contrast) AA |
| Foco de teclado | sin regla para enlaces y botones | `:focus-visible` con anillo de acento | [2.4.7](https://www.w3.org/TR/WCAG21/#focus-visible) AA |

Las **3 barras son el único indicador de paso** (no hay números). Si la barra apagada no se ve, el usuario no sabe cuántos pasos faltan — por eso entra en 1.4.11 como información de estado.

## Lo que NO se cambió, y por qué
El borde de los inputs (`#2a2740`) da **1.29:1** contra el fondo adyacente. No es incumplimiento: la norma exime el borde cuando el control ya tiene contenido visible que delata su presencia, y estos inputs tienen **etiqueta visible arriba** más un **icono interior a 7.31:1**.

> *"If a control has visible content (such as text or a sufficiently contrasting icon)... a border... is not required, as is therefore not subject to non-text contrast requirements."*
> — [W3C, Understanding 1.4.11](https://www.w3.org/WAI/WCAG21/Understanding/non-text-contrast)

## Nota
Los envíos son simulados (mockup). Las fotos de prueba social son de relleno, no usuarios reales.
