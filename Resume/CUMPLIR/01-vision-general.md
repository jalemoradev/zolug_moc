# 01 · Visión general — CUMPLIR

> **Conclusión:** CUMPLIR (`cumplir.net`) es un sistema web PHP legacy de **gestión de cobranza / recuperación de cartera**, en producción activa (varias tablas operativas modificadas el **2026-06-20**, un día antes de la introspección), alojado en **Hostinger** con base de datos **MariaDB 11.8.6-MariaDB-log**. Comparte ~80% del esqueleto con CEGROUP pero diverge en puntos concretos verificados: un **subsistema REPORTES construido sobre el framework Flight**, un endpoint REST `phone`, ausencia del endpoint de Email, login por **HTTPS** con **dos base-URLs** (`dir_data_server` + `dir_file_server`), y cargas masivas reubicadas en `DATA/UPDATE/` con un dominio extra (REASIGNACION) y una utilidad `OCULTAR`. **23 tablas**, ~2.6M filas operativas. Arrastra los mismos defectos graves de seguridad y calidad de datos que CEGROUP.

---

## 1. Qué es

Plataforma interna para que una agencia de cobranza gestione **créditos vencidos**, los asigne a asesores y registre la gestión de recuperación.

Evidencia de dominio en código y datos:

- Modelo de datos: `t_base`, `t_cartera`, `t_campana`, `t_asignacion`, `t_gestiones`, `t_acuerdos`, `t_saldos`, `t_pagos`, `t_telefonos`, `t_procesos`, `t_resumen`, `t_decil`, `t_alertas`, `t_estados`, `t_usuarios`, `t_mensaje`, `t_subs`, `r_acuerdo`, + las tablas de cache `reporte_acuerdos`, `reporte_gestion`, `reporte_proyeccion`, + look-up `l_campana`, `l_cartera` (RESUMEN.md — 23 tablas).
- Menú de la aplicación: BUSQUEDA, GESTION, ACUERDOS, COMUNICACION, ASESORES, REPORTES, RESUMEN, BASE DE DATOS, PROCESOS (`app/parts/menu.php:3-11`).
- Endpoints REST orientados a gestión: `g_operacion`, `g_gestiones`, `g_acuerdos`, `g_aportes`, `g_telefonos`, `g_resumen`, `phone`, etc. (`api/app/http/routes.php:3-26`).
- Branding interno: `meta content="Sistema Gestión"`, `meta content="AGENCIA CRAFT"` (`index.php:11-12`); título `COBRANZA / Versión 3.0` (`index.php:31-32`).

> **No verificado (modelo de negocio):** el significado exacto de cada flujo (qué es un "decil", "campana", "subs", reglas de negociación de acuerdos, comisiones, qué representa "OCULTAR" funcionalmente) NO está documentado en el código y NO se deduce aquí. Falta entrevista con el equipo.

---

## 2. Para quién — usuarios y roles

Dos tipos de usuario, verificados en código y datos:

| Tipo (`usertype`) | Rol | Menú visible | Funcionalidad |
|---|---|---|---|
| `0` | Admin | BUSQUEDA, ASESORES, REPORTES, BASE DE DATOS | Gestión asesores, reportes (Flight), cargas masivas |
| `≠0` | Asesor | BUSQUEDA, GESTION, ACUERDOS, RESUMEN | Operación diaria: gestionar créditos |

- Asignación de menú: `app/parts/menu.php:15-24` → `$menu_session_admin = [buscar, asesores, reportes, base]`, `$menu_session_user = [buscar, gestion, acuerdos, resumen]`; `if($menu_session_type ==0)` → admin, `else` → asesor.
- Distribución real: `t_usuarios` tiene **13 filas** (RESUMEN.md). Equipo pequeño vs los 65 usuarios de CEGROUP.
- El sample de `t_usuarios` (`_evidence/samples/t_usuarios.json`) muestra usertype `0` (admin: JURIDICOS.CUMPLIR, UBINEY.CERON, CUENTA.CUMPLIR) y `1` (asesor: YESMIN.HOYOS, DANIEL.TROYANO).

> **Nota de código:** el gate de menú usa `if($menu_session_type ==0)` y trata *cualquier otro valor* como asesor (`menu.php:20-24`). No hay jerarquía de niveles de asesor en el código; el doc previo describía `usertype = 1+` como si hubiera múltiples niveles — en el código solo se distingue `0` vs `≠0`.

Estado de usuarios: columna `t_usuarios.estado` (varchar(10), default `'FALSE'`) — el login exige `estado='TRUE'` (`api/app/rest/login.php:17`). Los 5 usuarios del sample tienen `estado='TRUE'`.

---

## 3. Stack técnico real

| Capa | Tecnología | Versión / evidencia |
|---|---|---|
| Lenguaje backend | PHP (mysqli directo, sin Composer en la app principal) | Versión exacta **No verificada** — no hay `composer.json` ni `phpinfo`. Sintaxis compatible PHP 7+. |
| **Módulo Reportes** | **Flight PHP (micro-framework MVC)** | `REPORTES/REPORTES/app/flight/Flight.php`, `index.php:3` (`require 'app/flight/Flight.php'`), `index.php:6` (`Flight::start()`) |
| Base de datos | **MariaDB 11.8.6-MariaDB-log** | `connection.json` → `server_version: "11.8.6-MariaDB-log"`, `version_comment: "MariaDB Server"` |
| Charset de conexión BD | **utf8mb4** / `utf8mb4_unicode_ci` a nivel servidor | `connection.json` → `db_charset: "utf8mb4"`, `db_collation: "utf8mb4_unicode_ci"` |
| Acceso a datos | `mysqli` con interpolación de strings (sin prepared statements) | `api/lib/DB.php:2-9`, `api/app/rest/login.php:17`, `REPORTES/REPORTES/app/db/db.php:2-9` |
| Frontend | jQuery + Bootstrap 5 + AJAX/fetch (sin SPA) | `index.php:88-94`, `app/parts/footer.php:17-26` |
| Libs JS | DataTables, Toastr, Moment, MetisMenu, SimpleBar, node-waves, owl.carousel, ApexCharts, ECharts, Leaflet (todas locales en `app/assets/libs/`) | `index.php:14-16,88-94`, listado en `app/assets/libs/` |
| Hosting | **Hostinger** | `connection.json` host `srv450.hstgr.io`, db `u815310395_data` |
| Storage de archivos | Filesystem local del hosting (CSV de import) | `DATA/UPDATE/**/file/*.csv`, `api/file/update/**/*.csv` |
| Auth | Sesión PHP + `localStorage`, sin tokens/JWT/OAuth | Ver `04-autenticacion-sesion.md` |

> **Corrección al doc previo:** el `resumen/01-vision-general.md` afirmaba "MariaDB 11.8.6 / 10.6.24-cll-lve" y "posible inconsistencia de versión reportada". La evidencia actual (`connection.json`, introspección 2026-06-21) reporta **únicamente** `11.8.6-MariaDB-log`. **No verificado:** ninguna mención a `10.6.24-cll-lve` en la evidencia disponible. Se reporta una sola versión.

> **Diferencia de charset vs CEGROUP:** CEGROUP usa `latin1` a nivel de conexión; CUMPLIR usa **utf8mb4** (`connection.json`). Las tablas, sin embargo, mezclan colaciones (ver §4).

---

## 4. Tamaño del proyecto

**Código (verificado por inspección de disco):**

- 1 entry point de login: `cumplir/index.php` (176 líneas).
- 1 gate web: `cumplir/public/index.php` (13 líneas).
- Framework web `lib/`: 6 archivos (`init.php`, `Router.php`, `App.php`, `Response.php`, `Action.php`, `Css.php`) — **byte-idénticos a CEGROUP** (ver `03-framework-web.md`).
- Capa web `app/`: 1 controlador (`MainController.php`), **13 rutas** (`app/http/routes.php:2-14`), 17 vistas en `app/views/`, 15 archivos JS en `app/action/`, 9 componentes parciales en `app/component/`.
- Framework API `api/lib/`: 6 archivos (`initial.php`, `Api.php`, `Router.php`, `Restapi.php`, `DB.php`, `Put.php`).
- Capa API `api/app/`: 1 controlador (`ApiController.php`), **23 rutas** (`api/app/http/routes.php:3-26`, incluye `phone`), 26 archivos en `api/app/rest/` (incluye `phone.php`).
- Cargas masivas: `DATA/UPDATE/` con 8 dominios (ASIGNACION, BASE, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS, **REASIGNACION**) + `DATA/OCULTAR/`; y `api/file/update/` con scripts legacy + 6 carpetas `SUBIR_*`.
- **Módulo REPORTES Flight:** `REPORTES/REPORTES/` con **18 declaraciones `Flight::route`** (17 rutas reales + 1 catch-all `*`; `grep Flight::route` en `src/routes/`), 5 controllers, 5 tools, 8 vistas, el framework Flight vendored en `app/flight/`.

> **Líneas de PHP propio:** no contadas exactamente en esta pasada; el doc previo estimaba ~5,500. **No verificado** el número exacto.

---

## 5. Estado de actividad

**Producción viva.** `UPDATE_TIME` de `INFORMATION_SCHEMA` (`tables.json`):

| Tabla | Última modificación | Filas (COUNT) |
|---|---|---:|
| `t_gestiones` | **2026-06-20 18:12:54** | 801,051 |
| `t_resumen` | **2026-06-20 18:12:03** | 488,584 |
| `t_procesos` | **2026-06-20 18:12:00** | 109,865 |
| `t_telefonos` | **2026-06-20 18:08:27** | 133,934 |
| `t_acuerdos` | **2026-06-20 15:08:55** | 11,322 |
| `t_alertas` | **2026-06-20 14:57:47** | 304 |
| `reporte_gestion` | **2026-06-18 19:43:23** | 10 |
| `t_campana` / `t_decil` | 2026-06-11 | 55,154 / 55,154 |
| `t_saldos` | 2026-06-11 05:53:13 | 55,154 |
| `t_asignacion` / `t_base` / `t_cartera` | 2026-06-09 | 58,648 / 50,381 / 56,444 |
| `reporte_acuerdos` | 2026-06-02 09:37:10 | 10 |
| `reporte_proyeccion` | 2026-06-02 09:37:15 | 10 |
| `t_pagos`, `t_mensaje`, `r_acuerdo` | sin `UPDATE_TIME` | 0 / 0 / 0 |

Lecturas:

- **Gestión diaria activa:** `t_gestiones` y `t_resumen` cambian a diario; el 2026-06-20 hubo actividad hasta las 18:12.
- **Cache de reportes parcialmente computado:** las tres tablas `reporte_*` ahora **sí tienen datos** (10 filas cada una = 10 asesores), pero con fechas de actualización distintas — `reporte_gestion` se recalculó el 2026-06-18, mientras `reporte_acuerdos` y `reporte_proyeccion` no se tocan desde el 2026-06-02. Confirma el patrón de cache pre-computado del módulo Flight (ver `02-arquitectura.md`).

  > **Corrección al doc previo:** el `resumen/01` decía que `reporte_acuerdos` y `reporte_proyeccion` tenían "placeholders" y "dos de tres reportes nunca computados". La evidencia actual muestra **10 filas en las tres** con `UPDATE_TIME` poblado — sí fueron computadas al menos una vez (2026-06-02). Lo que difiere es la frecuencia de recálculo, no su existencia.

- **Tablas vacías:** `t_pagos` (0), `t_mensaje` (0), `r_acuerdo` (0) — sin uso. El defecto de 0 pagos registrados se mantiene (igual que CEGROUP).
- **Calidad de datos (`quality.json`):**
  - **Passwords triviales:** `t_usuarios.userpass` = `md5('0')` en **10 de 13** usuarios → password `0`. Vector trivial (ver `04`).
  - **Duplicados masivos:** `t_gestiones` 8.293× filas por operación distinta; `t_resumen` 6.062×; `t_procesos` 1.131× (109,865 filas vs 50,381 operaciones distintas en `t_base`); `t_telefonos` 2.114×.
  - **Fechas `0000-00-00`:** `t_procesos.fgestion` 5,838; `t_resumen.fingreso` 1,088; `t_base.fvencimiento`/`fingreso` 44 cada una.
  - **Filas basura `operacion=0`:** `t_procesos` 47, `t_gestiones` 21, `t_resumen` 17, `t_asignacion` 12, `t_telefonos` 10, etc.

**Colaciones de tabla** (`tables.json`) — mezcla heredada pese a la conexión utf8mb4:

- `utf8mb4_unicode_ci`: `r_acuerdo`, `reporte_acuerdos`, `reporte_gestion`, `reporte_proyeccion` (tablas nuevas).
- `utf8mb3_spanish_ci`: `t_base`.
- `utf8mb3_general_ci`: el resto (`t_gestiones`, `t_acuerdos`, `t_usuarios`, etc.).

---

## 6. Dominios y URLs

- **URL pública / login:** `https://cumplir.net/` (`index.php`).
- **API REST:** `https://cumplir.net/api/api/` (`index.php:103` → `dir_data_server`).
- **Base de archivos:** `https://cumplir.net/api/file/` (`index.php:104` → `dir_file_server`). Segunda base-URL ausente en CEGROUP.
- **Módulo Reportes (Flight):** `https://cumplir.net/REPORTES/REPORTES/...` (rutas Flight; ver `02`). **No verificado** que se sirva exactamente bajo esa ruta pública — inferido de la estructura en disco `REPORTES/REPORTES/`.
- **Cargas masivas:** `DATA/UPDATE/<dominio>/index.php` y `api/file/update/<dominio>/`.
- **Hosting:** Hostinger, `srv450.hstgr.io` (`connection.json`).

---

## 7. Diferencias de alto nivel vs CEGROUP

| Aspecto | CEGROUP | CUMPLIR | Evidencia |
|---|---|---|---|
| Hosting / DB host | GoDaddy cPanel | Hostinger (`srv450.hstgr.io`) | `connection.json` |
| Versión DB | MariaDB 10.11.16 | MariaDB **11.8.6** | `connection.json` |
| Charset conexión | latin1 | **utf8mb4** | `connection.json` |
| Nº tablas | 20 | **23** (`reporte_*`, `r_acuerdo`, `t_subs`, sin `t_email`) | RESUMEN.md |
| Nº usuarios | 65 | **13** | RESUMEN.md |
| Login URL | HTTP (`gestioncobranza.com`) | **HTTPS** (`cumplir.net`) | `index.php:103` |
| Base-URLs en login | 1 (`dir_server`, `let`) | **2** (`dir_data_server` + `dir_file_server`, `const`) | `index.php:103-107` |
| Módulo Reportes | (custom framework) | **Flight MVC dedicado** | `REPORTES/REPORTES/` |
| Endpoint Email | `g_email` presente | **ausente** | `api/app/http/routes.php` (sin `g_email`) |
| Endpoint Phone | ausente | **`phone`** (POST → `UPDATE t_telefonos`) | `api/app/http/routes.php:26`, `api/app/rest/phone.php` |
| Cargas masivas | `api/UPDATE/` | **`DATA/UPDATE/`** + `DATA/OCULTAR/` + dominio REASIGNACION | árbol `DATA/` |

**Lo que NO cambia (verificado byte-idéntico):** el micro-framework web `lib/` (6 archivos), `public/index.php`, `public/.htaccess`, `api/api/.htaccess`, `app/php/session.php`, y el esqueleto del framework API `api/lib/`. Ver `03-framework-web.md`.

---

## 8. Para qué sirve esta documentación

- **Migrar:** atención especial al **módulo Flight REPORTES** (lógica de cache no trivial) y a las diferencias de cargas masivas (`DATA/`).
- **Corregir vulnerabilidades:** ver `04-autenticacion-sesion.md` (passwords `0`, credenciales en URL GET, bypass de sesión).
- **Onboarding:** `01` → `02` → `04`.
- **Decidir consolidación con CEGROUP:** ~80% de código compartido y verificado idéntico, pero divergencias funcionales reales (Flight, phone, sin email, dos base-URLs). Evaluar costo de mantener dos versus unificar.
