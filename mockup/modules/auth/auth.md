# auth.html — Autenticación

Flujo de login y recuperación de contraseña.

## Tema
**Solo oscuro.**

## Estructura
Layout partido: formulario a la izquierda (fondo más oscuro), panel de marca a la derecha (más claro, con logo, tagline y prueba social).

- **Logo** — lockup en chip claro (isotipo oscuro + "ZOLUG").
- **Prueba social** — 5 fotos reales superpuestas + `+300` y la leyenda "Más de 300 personas usan nuestros sistemas".

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
