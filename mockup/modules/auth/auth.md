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

## Nota
Los envíos son simulados (mockup). Las fotos de prueba social son de relleno, no usuarios reales.
