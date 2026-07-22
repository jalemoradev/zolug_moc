# 01 · Visión general — CEGROUP

> **Conclusión:** CEGROUP es un sistema web PHP legacy de **gestión de cobranza / recuperación de cartera**, en producción activa (última gestión registrada el **2026-06-20**, un día antes de la introspección), alojado en GoDaddy cPanel con base de datos **MariaDB 10.11.16**. ~7.5k líneas de PHP propio, 20 tablas, ~2.4M filas de datos operativos. Arrastra defectos graves de seguridad y calidad de datos verificados en código y en BD.

---

## 1. Qué es

Plataforma interna para que una agencia de cobranza gestione **créditos vencidos entregados por bancos/clientes**, los asigne a asesores y registre la gestión telefónica de recuperación.

Evidencia de dominio en el propio código y datos:
- Tablas del modelo: `t_base`, `t_cartera`, `t_campana`, `t_asignacion`, `t_gestiones`, `t_acuerdos`, `t_saldos`, `t_pagos`, `t_telefonos`, `t_procesos`, `t_resumen`, `t_decil`, `t_email`, `t_alertas`, `t_estados`, `t_usuarios` (RESUMEN.md — 20 tablas).
- Menú de la aplicación: BUSQUEDA, GESTION, ACUERDOS, RESUMEN, ASESORES, REPORTES, BASE DE DATOS (`app/parts/menu.php:3-16`).
- Endpoints REST orientados a gestión: `g_operacion`, `g_gestiones`, `g_acuerdos`, `g_aportes`, `g_telefonos`, `g_resumen`, etc. (`api/app/http/routes.php:3-25`).

> **No verificado (modelo de negocio):** el significado exacto de cada flujo (qué es un "decil", "campana", "subs", reglas de negociación de acuerdos, comisiones) no está documentado en el código y NO se deduce aquí. Falta entrevista con el equipo.

---

## 2. Para quién — usuarios y roles

Dos tipos de usuario, verificados tanto en código como en datos:

| Tipo (`usertype`) | Rol | Menú visible | Nº usuarios (BD) |
|---|---|---|---|
| `0` | Admin | BUSQUEDA, ASESORES, REPORTES, BASE DE DATOS | **3** |
| `1` | Asesor | BUSQUEDA, GESTION, ACUERDOS, RESUMEN | **62** |

- Asignación de menú: `app/parts/menu.php:15-24` (`if($menu_session_type ==0)` → admin, else asesor).
- Distribución real: `column_profiles.json` → `t_usuarios.usertype` tiene **solo 2 valores distintos**: `0` (3 filas) y `1` (62 filas). Total 65 usuarios.

> **Discrepancia con doc previo:** el doc anterior (`resumen/01`) describía el asesor como `usertype = 1+` insinuando múltiples niveles. **Falso según datos:** solo existen `0` y `1`. No hay jerarquía de niveles de asesor en la BD.

Estado de usuarios: columna `t_usuarios.estado` (varchar(10), default `'FALSE'`) — **64 = `TRUE`** (activos), **1 = `FALSE`** (deshabilitado) (`column_profiles.json`). El login exige `estado='TRUE'` (`api/app/rest/login.php:17`).

---

## 3. Stack técnico real

| Capa | Tecnología | Versión / evidencia |
|---|---|---|
| Lenguaje backend | PHP (mysqli directo, sin Composer/framework) | Versión exacta **No verificada** — no hay `composer.json` ni `phpinfo`; los `error_log` no indican versión. Sintaxis usada es compatible PHP 7+. |
| Base de datos | **MariaDB 10.11.16-cll-lve** (CloudLinux LVE) | `connection.json` → `server_version: "10.11.16-MariaDB-cll-lve"` |
| Charset de conexión BD | **latin1** / `latin1_swedish_ci` a nivel servidor | `connection.json` → `db_charset: "latin1"`. Las tablas mezclan colaciones: la mayoría `utf8mb3_general_ci`, `t_base` usa `utf8mb3_spanish_ci`, `t_email`/`t_saldos` usan `latin1_swedish_ci` (RESUMEN.md). |
| Acceso a datos | `mysqli` con interpolación de strings (sin prepared statements) | `api/lib/DB.php:2-9`, `api/app/rest/login.php:17` |
| Frontend | jQuery + Bootstrap 5 + AJAX/fetch (sin SPA) | `index.php:90-96`, `app/parts/footer.php:17-26` |
| Libs JS | DataTables, Toastr, Moment, MetisMenu, SimpleBar, node-waves, owl.carousel (todas locales) | `index.php:14-16,90-95`, `footer.php:17-26` |
| Hosting | GoDaddy / SecureServer cPanel | `connection.json` host `p3plzcpnl489480.prod.phx3.secureserver.net`; ruta en disco `/home/g2ikz73cb0c2/public_html/` (`public/error_log`, `api/error_log`) |
| Storage de archivos | Filesystem local del cPanel (CSV de import) | `api/UPDATE/**/file/*.csv` |
| Auth | Sesión PHP + `localStorage`, sin tokens/JWT/OAuth | Ver `04-autenticacion-sesion.md` |

**No usa:** Composer, NPM, framework PHP, ORM, migraciones, tests, CI/CD, Docker, variables de entorno. Credenciales hardcoded en código (`api/lib/DB.php:5`).

---

## 4. Tamaño del proyecto

**Código** (medido sobre `cegroup/`, excluyendo `app/assets/`):
- **~7,548 líneas de PHP** propias (`find . -name "*.php" -not -path "*/assets/*" | xargs wc -l`).
- **~2,705 líneas de JS de aplicación** en `app/action/*.js` (15 archivos).
- 1 entry point de login (`index.php`).
- Microframework web: 6 archivos (`lib/`). Microframework API: 6 archivos (`api/lib/`) — duplicado.
- 13 rutas web → 13 vistas + 1 vista por defecto vacía + 1 vista de error (`app/http/routes.php`, `app/views/`).
- 23 rutas API → controlador con 23 métodos (`api/app/http/routes.php`, `api/app/controllers/ApiController.php`).
- Scripts de carga masiva en `api/UPDATE/<dominio>/` (BASE, SALDOS, TELEFONOS, CARTERA, CAMPANA, ASIGNACION, DECIL, GESTIONES, PROCESOS, MAIL).

> **Discrepancia con doc previo:** el doc anterior estimó "~3,500 líneas de PHP". El conteo real es **~7,548** (más del doble). El previo también describía un árbol `api/file/update/` con 10 dominios y `api/file/sql/`; en el snapshot actual **no existen** esos directorios — solo está `api/file/update/SALDOS/`. Anotado como cambio de estructura entre versiones.

**Datos** (RESUMEN.md, COUNT real):

| Tabla | Filas |
|---|---:|
| `t_gestiones` | 1,838,261 |
| `t_resumen` | 493,254 |
| `t_telefonos` | 119,431 |
| `t_procesos` | 57,590 |
| `t_decil` / `t_campana` / `t_asignacion` | ~54,931 c/u |
| `t_saldos` / `t_cartera` | ~54,930 c/u |
| `t_base` | 54,931 |
| `t_acuerdos` | 10,902 |
| `t_email` | 11,728 |
| `t_usuarios` | 65 |
| `t_pagos` | **0** (vacía) |
| `t_mensaje` | **0** (vacía) |

Total aproximado ≈ **2.4 millones de filas** concentradas en gestiones y resumen.

---

## 5. Estado de actividad

**Sistema vivo y en uso diario** — basado en datos, no en metadatos de tabla:

- `t_gestiones.fecha`: min **2020-01-02**, max **2026-06-20** (`column_profiles.json`). La última gestión es de un día antes de la introspección (server_now = 2026-06-21).
- `t_base.fingreso` max **2026-06-17**, `t_acuerdos.fregistro` max **2026-06-19**, `t_alertas.fecha` max **2026-06-28** (alertas a futuro).

> **Aclaración sobre `update_time`:** el doc anterior sugería deducir actividad de "fechas update_time de tablas". **No es posible:** `tables.json` reporta `update_time = NULL` para las 20 tablas, y `create_time = 2026-06-05 10:41:56` idéntico en todas (fecha de un restore/migración, no del dato original). La evidencia de actividad proviene de los valores `min/max` de columnas fecha, no de los metadatos InnoDB.

**Calidad de datos — defectos verificados** (`quality.json`):
- Fechas inválidas `0000-00-00`: `t_resumen.fingreso` 7,343 filas, `t_procesos.fgestion` 1,328, `t_acuerdos.fregistro` 741, `t_base.fingreso` 79, `t_base.fvencimiento` 48, otras.
- Fechas absurdas a futuro: `t_resumen.fingreso` max **2045-09-00** (mes/día inválidos), `t_base.fvencimiento` max **2030-05-06**, `t_procesos.fgestion` max **2026-10-27**.
- Filas basura `operacion=0`: `t_gestiones` 34, `t_resumen` 6, `t_acuerdos` 1, `t_email` 1.
- Duplicación de operaciones: `t_gestiones` 14.75 filas/operación (esperado — es histórico de gestión), `t_resumen` 5.44×, `t_telefonos` 1.88×, `t_acuerdos` 1.25×.
- Contraseñas triviales: **6 de 65** usuarios con `userpass = md5('0')` (= contraseña `"0"`) (`quality.json` password_md5_zero; ver `04-autenticacion-sesion.md`).

**Defectos operativos verificados** (`error_log`):
- `api/app/rest/b_data.php:128` agota repetidamente el límite de memoria de PHP (128 MB) — decenas de `Fatal error: Allowed memory size of 134217728 bytes exhausted` entre dic-2023 y ene-2024 (`api/error_log`).
- Evento de caída de BD: `public/index.php:2` lanzó `Uncaught Exception: Could not connect to the server` repetidamente el 11-ago-2025 (`public/error_log`).

---

## 6. Dominios y URLs

- **Dominio público:** `gestioncobranza.com` — hardcoded en el JS de login (`index.php:105`) y en el JS de la app (`app/assets/js/main.js:33-34`).
- **Base API en producción:** `http://gestioncobranza.com/api/api/` (`main.js:33`, `index.php:105`). **HTTP, no HTTPS.**
- **Base de archivos:** `http://gestioncobranza.com/api/file/` (`main.js:34`).
- **Base local de desarrollo (no activa):** `http://localhost/server/cobranza_api/api/` y `.../file/` (`main.js:30-31`, `index.php:104`).
- **Ruta en disco del servidor:** `/home/g2ikz73cb0c2/public_html/` (cuenta cPanel `g2ikz73cb0c2`) (`public/error_log`, `api/error_log`).

**Branding interno visible en código:** "COBRANZA Version 3.0" (`index.php:32-33`), autor "AGENCIA CRAFT" (`index.php:12`, `footer.php:9`), "Sistema Gestion" (`index.php:11`), "© 2022" (`footer.php:5`), y un meta description residual "Desarrollo para asociación alcaravan" (`app/parts/header.php:8`).

---

## 7. Para qué sirve esta documentación

Inventario verificado para reconstruir CEGROUP en una versión mejorada:
- `02-arquitectura.md` — capas, layout, flujo de request web y API.
- `03-framework-web.md` — el microframework `lib/`.
- `04-autenticacion-sesion.md` — login, sesión, roles y debilidades verificadas.
