# 02 · Arquitectura — CUMPLIR

> **Conclusión:** CUMPLIR es un monolito PHP servido por Apache con **cinco subsistemas**: (A) login fuera del framework, (B) capa web autenticada con un micro-framework custom (`lib/` + `app/`), (C) capa API REST con su propio micro-framework (`api/`), (D) cargas masivas con UI propia (`DATA/UPDATE/` + `api/file/update/`), y (E) un **subsistema de reportes independiente construido sobre Flight PHP** (`REPORTES/REPORTES/`). La sesión PHP vive server-side y se activa por un endpoint dedicado; el front además guarda metadatos en `localStorage`. Todo el acceso a datos es `mysqli` con interpolación de strings, sin ORM ni prepared statements.

---

## 1. Vista de alto nivel

```
                        Browser (jQuery + Bootstrap, AJAX/fetch)
                                       │
        ┌──────────────────┬──────────┴───────────┬───────────────────────┐
        ▼                  ▼                       ▼                       ▼
  PÁGINA LOGIN       ÁREA AUTENTICADA       CARGAS MASIVAS          REPORTES (Flight)
  cumplir/index.php  cumplir/public/        DATA/UPDATE/...         REPORTES/REPORTES/
  (form + JS)        (vistas dinámicas)     api/file/update/...     (MVC sobre Flight)
        │                  │                       │                       │
        └────────── HTTPS / AJAX / form-POST ──────┴───────────────────────┘
                                       │
              ┌────────────────────────┼─────────────────────────┐
              ▼                        ▼                          ▼
   cumplir/api/api/index.php   procesar.php por dominio   REPORTES/REPORTES/index.php
   (entry API REST)            (importadores)             (Flight::start())
              │                        │                          │
        Router (custom)          mysqli directo             Flight Router
        ApiController            (DATA/UPDATE/DB.php)        Controllers → Tools
              │                        │                          │
              └────────────────────────┴──────────┬───────────────┘
                                                   ▼
                            MariaDB 11.8.6 en Hostinger (u815310395_data, 23 tablas)
                            conexión siempre "localhost" desde el servidor PHP
```

> Las tres clases de conexión (`api/lib/DB.php`, `REPORTES/REPORTES/app/db/db.php`, `DATA/UPDATE/DB.php`) apuntan todas a `("localhost","u815310395_data","«REDACTADO»","u815310395_data")` — credenciales hardcoded, idénticas entre subsistemas.

---

## 2. Layout en disco (árbol comentado)

```
PROYECTO_CUMPLIR/
└── cumplir/
    ├── index.php             ← LOGIN. HTML+JS inline, fuera del framework. session_destroy() al cargar.
    ├── public/
    │   ├── index.php         ← GATE: chequea $_SESSION["session"], bootstrap de la capa web.
    │   └── .htaccess         ← rewrite ^(.+)$ → index.php?url=$1  (idéntico a CEGROUP)
    ├── lib/                  ← MICRO-FRAMEWORK WEB (6 archivos, BYTE-IDÉNTICO a CEGROUP)
    │   ├── init.php          ← require de App/Action/Css/Router → http/routes.php → Response
    │   ├── Router.php        ← tabla estática route → {controller, method}
    │   ├── App.php           ← dispatcher: $_GET["url"] → controller->method()
    │   ├── Response.php      ← render(view, params) → require views/<view>.php
    │   ├── Action.php        ← emite <script src="../app/action/<method>.js">
    │   └── Css.php           ← emite <link href="../app/assets/css/<method>.css">
    ├── app/                  ← CAPA WEB
    │   ├── controllers/MainController.php   ← 13 métodos
    │   ├── http/routes.php                  ← 13 rutas (index, buscar, gestion, ...)
    │   ├── parts/                           ← header.php, menu.php, footer.php
    │   ├── php/session.php                  ← activa/destruye sesión (IDÉNTICO a CEGROUP)
    │   ├── views/                           ← 17 vistas .php (JSX-like, HTML)
    │   ├── component/                       ← 9 parciales (base/, reporte/)
    │   ├── action/                          ← 15 archivos JS (uno por vista)
    │   └── assets/                          ← libs vendored (jQuery, Bootstrap, DataTables, ...)
    ├── api/                  ← CAPA API REST
    │   ├── api/
    │   │   ├── index.php     ← entry: session_start(), new Api()
    │   │   └── .htaccess     ← rewrite → index.php?url=$1
    │   ├── lib/              ← MICRO-FRAMEWORK API (initial, Api, Router, Restapi, DB, Put)
    │   ├── app/
    │   │   ├── controllers/ApiController.php   ← 23 métodos (incluye phone, sin g_email)
    │   │   ├── http/routes.php                 ← 23 rutas
    │   │   └── rest/                           ← 26 endpoints (incl. phone.php; sin g_email)
    │   └── file/
    │       ├── sql/         ← scripts de export (admin/, user/) con error_log
    │       └── update/      ← importadores legacy + SUBIR_ACUERDOS/ALERTAS/GESTION/RESUMEN/TELEFONOS
    ├── DATA/                 ← CARGAS MASIVAS (UI propia) — reubicado vs CEGROUP (api/UPDATE/)
    │   ├── UPDATE/
    │   │   ├── index.php     ← menú con botones de import + botón "LLAMAR" experimental (tel:)
    │   │   ├── DB.php        ← conn mysqli (mismas credenciales)
    │   │   ├── styles.css
    │   │   ├── ASIGNACION/  BASE/  CAMPANA/  CARTERA/  DECIL/  PROCESOS/  SALDOS/
    │   │   │     └── (index.php, index.js, upload.php, procesar.php, file/*.csv)
    │   │   └── REASIGNACION/ ← dominio EXTRA único de CUMPLIR
    │   └── OCULTAR/          ← utilidad única de CUMPLIR
    │         ├── OCULTAR.csv
    │         └── procesar.php
    ├── REPORTES/             ← carpeta padre — solo contiene la subcarpeta anidada
    │   └── REPORTES/         ← SUBSISTEMA FLIGHT (único de CUMPLIR)
    │       ├── index.php     ← require db + Flight + autoload + routes → Flight::start()
    │       ├── .htaccess     ← rewrite TODO → index.php (front controller Flight)
    │       ├── app/
    │       │   ├── db/db.php ← conn mysqli (mismas credenciales)
    │       │   └── flight/   ← framework Flight vendored (Engine, Flight, net/, core/, ...)
    │       └── src/
    │           ├── routes/      ← ApiStart + Index/Gestiones/Acuerdos/Proyeccion/Reportes/Error
    │           ├── controllers/ ← Index, Gestiones, Acuerdos, Proyeccion, Reportes (5)
    │           ├── tools/       ← GetAsesores, Gestiones, Acuerdos, Proyeccion, headExcel (5)
    │           └── view/        ← gestiones, acuerdos, proyeccion (+ *File) + Index + style.css
    └── images/               ← imágenes de template a nivel raíz (no en app/assets/)
```

---

## 3. Convención de enrutado (las tres `.htaccess`)

Tres reescrituras Apache, dos patrones distintos:

1. **`public/.htaccess`** y **`api/api/.htaccess`** (idénticos, **byte-idénticos a CEGROUP**):
   ```apache
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-l
   RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]
   ```
   Convierten `/public/gestion` → `index.php?url=gestion`. El micro-framework lee `$_GET["url"]`.

2. **`REPORTES/REPORTES/.htaccess`** (patrón front-controller de Flight):
   ```apache
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```
   Reescribe **toda** la URL a `index.php` (sin `?url=`). Flight parsea el path y el método HTTP internamente.

---

## 4. Capas y responsabilidades

### Capa A — Login (`cumplir/index.php`)

Fuera del framework. HTML + JS inline. `session_start()` + `session_destroy()` al inicio (`index.php:2-3`). Define **dos** base-URLs con `const` (`index.php:103-104`): `dir_data_server` (HTTPS, API) y `dir_file_server` (HTTPS, archivos). Detalle completo en `04-autenticacion-sesion.md`.

### Capa B — Web autenticada (`public/` + `lib/` + `app/`)

```
public/index.php (gate sesión)
  └── chdir(raíz); define SYS_PATH=lib/, APP_PATH=app/
        └── require lib/init.php
              └── App.php → Router::getAction($_GET["url"]) → require controllers/<X>.php → $controller->$method()
                    └── Response::render(view) → require app/views/<view>.php
        └── require app/parts/header.php  (HTML head + topbar + menu)
        └── new App                       (ejecuta el controller/vista)
        └── require app/parts/footer.php  → new Action → emite <script src="../app/action/<method>.js">
```

- Vistas = HTML estático; la lógica vive en `app/action/<X>.js` (AJAX a la API).
- `app/parts/menu.php:15-24` decide el menú por `$_SESSION['session_type']` (`==0` admin, else asesor).
- **Igual que CEGROUP en estructura.** Diferencias de contenido en vistas (sin sección Email) no se cubren en este doc de arquitectura.

### Capa C — API REST (`api/api/` + `api/lib/` + `api/app/`)

```
api/api/index.php
  └── session_start(); chdir(api/); define SYS_PATH=lib/, APP_PATH=app/
        └── require lib/initial.php  (Put, Api, Router, routes, Restapi, DB)
        └── new Api → Router::getAction($_GET["url"]) → require controllers/ApiController.php → método
              └── Restapi::render(view) → require app/rest/<view>.php
                    └── new conn (mysqli) → query interpolada → echo json_encode(...)
```

- `api/api/index.php:8-22` define `format_post()` (uppercase del input; rama `encode`/`decode`/`null`, fija a `null`). **No verificado** uso real desde todos los endpoints — definida globalmente.
- **Esqueleto byte-idéntico a CEGROUP** (`Api.php`, `Router.php`, `Restapi.php`, `Put.php`, `initial.php`). Difiere `DB.php` (credenciales) y el conjunto de endpoints (`phone` presente, `g_email` ausente).

### Capa D — Cargas masivas (`DATA/UPDATE/` + `api/file/update/`)

- **`DATA/UPDATE/`**: cada dominio (`ASIGNACION`, `BASE`, `CAMPANA`, `CARTERA`, `DECIL`, `PROCESOS`, `SALDOS`, `REASIGNACION`) tiene `index.php` (UI), `index.js`, `upload.php`, `procesar.php` y carpeta `file/` con CSVs. `DATA/UPDATE/DB.php` aporta la conexión; `DATA/UPDATE/index.php` es el menú (con un botón "LLAMAR" experimental `tel:` — `index.php:36-40`).
- **`DATA/OCULTAR/`**: utilidad única de CUMPLIR (`OCULTAR.csv` + `procesar.php`). **No verificado** su efecto exacto sobre la BD en este doc (no es alcance de arquitectura).
- **`api/file/update/`**: importadores legacy (`ASIGNAR`, `BASE`, `CAMPANA`, `CARTERA`, `DECIL`, `PROCESOS`, `SALDOS`) + 6 carpetas `SUBIR_*` (`SUBIR_ACUERDOS/ALERTAS/GESTION/RESUMEN/TELEFONOS`).

> **Diferencia clave vs CEGROUP:** CEGROUP centraliza importadores en `api/UPDATE/`. CUMPLIR los reparte en **`DATA/UPDATE/`** (con UI) y `api/file/update/` (scripts), y añade REASIGNACION + OCULTAR.

### Capa E — Reportes Flight (`REPORTES/REPORTES/`) — único de CUMPLIR

Subsistema MVC completamente separado, sobre **Flight PHP** (no el micro-framework del resto).

```
REPORTES/REPORTES/index.php
  └── require app/db/db.php          (clase conn)
  └── require app/flight/Flight.php  + app/flight/autoload.php
  └── require src/routes/ApiStart.php  → require Index, Gestiones, Acuerdos, Proyeccion, Reportes, Error
  └── Flight::start()  → resuelve método+path → controller → tool → mysqli
```

**18 declaraciones `Flight::route`** (`grep -c Flight::route src/routes/*` = 18) = **17 rutas reales + 1 catch-all `*`**, con métodos HTTP reales:

| Método + Ruta | Handler | Acción |
|---|---|---|
| `GET /` y `GET /reportes` | `Init()` (Index ctrl) | render `src/view/Index.php` |
| `GET /reportes/gestiones` | `gestiones()` | render vista (lee cache) |
| `GET /reportes/acuerdos` | `acuerdos()` | render vista |
| `GET /reportes/proyeccion` | `proyeccion()` | render vista |
| `GET /reportes/{gestiones,acuerdos,proyeccion}/file` | `*File()` | vistas de export Excel |
| `POST /gestiones/date` | `postGestionesDate()` | recalcula columna del día |
| `POST /gestiones/all` | `postGestionesAll()` | recalcula totales del mes |
| `DELETE /gestiones/all` | `deleteGestionesAll()` | TRUNCATE + re-init filas |
| `POST/DELETE /acuerdos/*` | controller Acuerdos | mismo patrón |
| `POST/DELETE /proyeccion/*` | controller Proyeccion | mismo patrón |
| `*` (catch-all) | Error | `Flight::json({status:false, message:'Ruta no encontrada'})` |

**Patrón de cache pre-computado** (verificado en `src/controllers/Gestiones.php` + `src/tools/Gestiones.php`):

```
POST /gestiones/date  (date=YYYY-MM-DD)
  1. $dia = date('d', date)                                   (Gestiones.php:11)
  2. getAsesores() → SELECT username FROM t_usuarios WHERE usertype=1 ORDER BY username
                                                              (GetAsesores.php:4)
  3. por cada asesor:
       getGestionAsesorDate(asesor,date) → COUNT t_gestiones por fecha    (tools/Gestiones.php:2-7)
       getGestionAsesorDateOpe(asesor,date) → COUNT DISTINCT operacion    (tools/Gestiones.php:9-14)
       insertReportGestion(asesor,ges,ope,dia) → UPDATE reporte_gestion SET ges_<dia>, ope_<dia>
  4. (sin response JSON explícito)
```

- `reporte_gestion` es una **tabla wide**: columnas `ges_01..ges_31`, `ope_01..ope_31`, `ges_t`, `ope_t` (una por día del mes + total) — visible en el `INSERT` de `initReportGestiones()` (`tools/Gestiones.php:42-44`).
- `DELETE /gestiones/all` hace `TRUNCATE reporte_gestion` y re-inserta una fila por asesor con `'-'` en todas las columnas (`tools/Gestiones.php:34-45`).
- Las 10 filas observadas en `reporte_gestion` (RESUMEN.md) = los 10 asesores (`usertype=1`).

> **Defectos verificados en el módulo Flight:**
> - **`echo $sql` de debug** en producción: `tools/Gestiones.php:11` (`echo $sql = "SELECT DISTINCT ...";`) filtra el SQL en la respuesta.
> - **Año hardcoded `2026`** en los rangos mensuales: `tools/Gestiones.php:17-18` y `26-27` (`$f1='2026-'.$date.'-01'; $f2='2026-'.$date.'-31'`). Romperá al cambiar de año y usa día `31` para todos los meses.
> - **SQL injection** por interpolación de `$asesor`/`$date` sin escape (todas las funciones de `tools/`).

---

## 5. Flujo completo de un request

### 5.1 WEB — usuario navega a `/public/gestion`

```
1. Browser → GET https://cumplir.net/public/gestion
2. Apache (public/.htaccess) → index.php?url=gestion
3. public/index.php → session_start(); isset($_SESSION["session"]) ? continuar : Location ../
4. chdir raíz; require lib/init.php; require app/parts/header.php (HTML+menu)
5. new App → Router::getAction("gestion") → MainController::gestion() → Response::render("gestion")
6. require app/views/gestion.php
7. require app/parts/footer.php → new Action → <script src="../app/action/gestion.js">
8. Browser ejecuta gestion.js → fetch https://cumplir.net/api/api/g_operacion?o=...
```

### 5.2 API — JS hace `fetch` a `/api/api/phone`

```
1. POST/GET https://cumplir.net/api/api/phone  (id, status)
2. Apache (api/api/.htaccess) → index.php?url=phone
3. api/api/index.php → session_start(); new Api()
4. Router::getAction("phone") → ApiController::phone() → Restapi::render("phone")
5. api/app/rest/phone.php → new conn → UPDATE t_telefonos SET status=$status WHERE id=$id → JSON
```

### 5.3 FLIGHT — `POST /gestiones/date`

```
1. POST https://cumplir.net/REPORTES/REPORTES/gestiones/date  (date=2026-06-20)
2. Apache (REPORTES/.htaccess) → index.php (toda la URL)
3. index.php → Flight::start() resuelve "POST /gestiones/date"
4. → postGestionesDate() → getAsesores() → bucle por asesor → UPDATE reporte_gestion
5. (sin response JSON; efecto: cache actualizado)
```

---

## 6. Dónde vive la sesión y los assets

**Sesión:**
- Server-side: `$_SESSION["session"]='ACTIVE'` + `$_SESSION["session_type"]` — activada por `app/php/session.php?a=on&t=<usertype>` (12 líneas, **idéntico a CEGROUP**).
- Cliente: `localStorage` guarda `session_name`, `session_user`, `session_type`, `session_avatar`, `session='ACTIVE'` (`index.php:135-139`).
- El gate de la capa web (`public/index.php:3`) solo chequea `isset($_SESSION["session"])`. La capa API (`api/api/index.php:2`) hace `session_start()` pero **no valida** la sesión por ruta — cada endpoint es accesible sin gate. El módulo Flight **no hace `session_start()`** en su `index.php` — sin control de sesión propio (ver `04`).

**Assets:** servidos como archivos estáticos desde `app/assets/` (libs vendored). `Action`/`Css` inyectan `<script>`/`<link>` por nombre de método de ruta. `images/` está a nivel raíz, fuera de `app/assets/`.

---

## 7. Defectos arquitectónicos verificados

- **Credenciales hardcoded** triplicadas (`api/lib/DB.php:5`, `REPORTES/REPORTES/app/db/db.php:4`, `DATA/UPDATE/DB.php`). Sin `.env`.
- **Cero abstracción de datos** en la capa REST: cada endpoint reabre conexión, interpola, formatea y hace `echo`.
- **Sin autenticación a nivel de API ni de Flight**: el gate de sesión solo existe en `public/index.php`.
- **SQL injection** sistemático (interpolación de strings en todas las capas).
- **Debug filtrado en producción** (`echo $sql`) en el módulo Flight.
- **Lógica con fechas frágiles**: año `2026` y día `31` hardcoded en reportes.
- **Sin tests, sin logging estructurado, sin CI/CD** (despliegue por FTP/cPanel inferido).

---

## 8. Despliegue (inferido)

- Código sube por FTP / panel Hostinger. **No verificado** pipeline.
- DB MariaDB del mismo hosting (conexión `localhost` desde PHP; `srv450.hstgr.io` desde fuera).
- Versión visible: "COBRANZA / Versión 3.0" (`index.php:31-32`), footer "2022 © Derechos reservados" (`app/parts/footer.php:5`).
