# 02 · Arquitectura — CEGROUP

> **Conclusión:** Aplicación PHP monolítica con **dos microframeworks gemelos y duplicados**: uno sirve las vistas web (`lib/` + `app/`), otro sirve la API REST (`api/lib/` + `api/app/`). El backend web solo emite **HTML estático + un `<script>` por ruta**; toda la lógica de datos vive en JavaScript que llama por `fetch` a la API REST, la cual ejecuta SQL directo con `mysqli` y devuelve JSON. La sesión vive en PHP (`$_SESSION`) y, en paralelo, en `localStorage`. Las cargas masivas son scripts standalone fuera de ambos frameworks.

---

## 1. Vista de alto nivel

```
        Browser (jQuery + Bootstrap + fetch)
                       │
   ┌───────────────────┼────────────────────┐
   │                   │                     │
   ▼                   ▼                     ▼
 LOGIN            ÁREA AUTENTICADA       CARGA MASIVA
 cegroup/         cegroup/public/        cegroup/api/UPDATE/<dominio>/
 index.php        (vistas dinámicas)     (scripts standalone)
   │                   │                     │
   │ fetch login       │ fetch g_*/b_*/d_*   │ upload + procesar CSV
   └─────────┬─────────┴──────────┬──────────┘
             ▼                     ▼
   cegroup/api/api/index.php   (mismo backend)
   (entry API REST)                │
             │                     │
        Router → ApiController → rest/<endpoint>.php
                                     │
                                  conn (mysqli) → MariaDB 10.11.16
                                     │
                                  echo json_encode(...)
```

Dos capas separadas físicamente (`cegroup/` web y `cegroup/api/` REST) que **comparten la misma BD** pero **no comparten estado entre sí**: la capa web solo produce HTML + JS; el JS habla directo con la capa API.

---

## 2. Layout en disco (árbol comentado)

```
cegroup/
├── index.php                 # Login. FUERA del framework. HTML + JS inline (fetch a API login)
├── .htaccess                 # VACÍO (0 bytes) — sin reglas en la raíz
│
├── public/                   # Front controller del área autenticada
│   ├── index.php             # Gate de sesión + bootstrap del framework web (13 líneas)
│   └── .htaccess             # RewriteRule ^(.+)$ index.php?url=$1  → enruta cualquier path a ?url=
│
├── lib/                      # MICROFRAMEWORK WEB (6 archivos) — ver 03-framework-web.md
│   ├── init.php              # Carga App, Action, Css, Router, routes, Response
│   ├── App.php               # Dispatcher: $_GET["url"] → Router → Controller → método
│   ├── Router.php            # Tabla estática ruta → {controller, method}
│   ├── Response.php          # render(view): require app/views/<view>.php
│   ├── Action.php            # Emite <script src="../app/action/<method>.js">
│   └── Css.php               # Emite <link ...<method>.css> — *NUNCA SE INSTANCIA (código muerto)*
│
├── app/                      # CAPA WEB (UI)
│   ├── http/routes.php       # Router::add() × 13 (rutas web)
│   ├── controllers/MainController.php  # 13 métodos, todos hacen Response::render(<vista>)
│   ├── views/                # 17 archivos HTML; index.php y (parcialmente) otras vacías
│   │   ├── error.php         # Vista 404 ("ruta no encontrada")
│   │   └── ...               # buscar, gestion, acuerdos, resumen, asesores, reportes, base, etc.
│   ├── parts/                # Fragmentos compartidos
│   │   ├── header.php        # <head> + topbar + require menu.php  (CSS estático aquí)
│   │   ├── menu.php          # Sidebar según $_SESSION['session_type'] (admin vs asesor)
│   │   └── footer.php        # Scripts + <?php new Action ?> (inyecta el JS de la ruta)
│   ├── php/session.php       # Activa/destruye $_SESSION (12 líneas) — ver 04
│   ├── action/               # 15 archivos JS: la lógica real de cada vista (fetch a la API)
│   ├── component/            # Parciales reporte/base — stubs / poco usados
│   └── assets/               # CSS, JS (main.js, app.js, fetch.js, moment), fonts, libs vendored
│
└── api/                      # CAPA API REST
    ├── error_log             # Logs PHP (errores de memoria en b_data.php)
    ├── api/
    │   ├── index.php         # Entry REST: session_start + define paths + new Api
    │   └── .htaccess         # RewriteRule ^(.+)$ index.php?url=$1
    ├── lib/                  # MICROFRAMEWORK API (6 archivos) — duplica lib/ web
    │   ├── initial.php       # Carga Put, Api, Router, routes, Restapi, DB
    │   ├── Api.php           # Dispatcher (idéntico a App.php salvo el fallback)
    │   ├── Router.php        # Idéntico a lib/Router.php
    │   ├── Restapi.php       # render(view): require app/rest/<view>.php
    │   ├── DB.php            # class conn extends mysqli — credenciales HARDCODED
    │   └── Put.php           # Parser manual de multipart en PUT
    ├── app/
    │   ├── http/routes.php   # Router::add() × 23 (rutas API)
    │   ├── controllers/ApiController.php  # 23 métodos, todos Restapi::render(<endpoint>)
    │   └── rest/             # 25 archivos: 23 endpoints + index.php + error.php
    │                         #   (+ huérfanos: asesor.php y clientes.php sin ruta — ver §6)
    ├── UPDATE/<DOMINIO>/      # Cargas masivas con UI (index.php + index.js + upload.php + procesar.php)
    │   ├── BASE/ SALDOS/ TELEFONOS/ CARTERA/ CAMPANA/
    │   ├── ASIGNACION/ DECIL/ GESTIONES/ PROCESOS/ MAIL/
    │   ├── DB.php            # COPIA de credenciales (segunda copia)
    │   └── elimina.php
    └── file/update/SALDOS/   # Script de import residual (index.php)
```

> **Discrepancia con doc previo:** `resumen/02` describía un árbol `api/file/update/` con 10 dominios y `api/file/sql/admin|user/...` con generadores Excel. En el snapshot actual **solo existe** `api/file/update/SALDOS/index.php`; **no hay** `api/file/sql/`. Las cargas masivas viven en `api/UPDATE/<dominio>/`. Anotado como diferencia de versión.

> **Discrepancia con doc previo:** `resumen/02` y `resumen/03` afirmaban que `header.php` instancia `new Css` y `new Action`. **Falso, verificado:** `grep "new Css"` no devuelve resultados (la clase `Css` nunca se instancia — es código muerto); `new Action` aparece **solo** en `footer.php:28`. El CSS se carga estáticamente en `header.php:11-16`, no por ruta.

---

## 3. Capas y responsabilidades

### Capa A — Login (`cegroup/index.php`)
Fuera de todo framework. `session_start()` + `session_destroy()` al cargar (`index.php:2-3`). HTML + JS inline que hace `fetch` al endpoint `login` de la API y luego activa la sesión local. Detalle en `04-autenticacion-sesion.md`.

### Capa B — Web autenticada (`public/` + `lib/` + `app/`)
- `public/index.php` valida sesión y arranca el framework.
- Los controllers son **cero-lógica**: cada método llama `Response::render("<vista>")` (`MainController.php:3-15`).
- Las vistas son **HTML estático** con contenedores vacíos.
- `footer.php` instancia `new Action`, que emite `<script src="../app/action/<ruta>.js">`.
- Ese JS (`app/action/<ruta>.js`) hace los `fetch` a la API y rellena el DOM.

### Capa C — API REST (`api/api/` + `api/lib/` + `api/app/`)
- `api/api/index.php` arranca el framework API y hace `new Api`.
- `ApiController` es **cero-lógica**: cada método llama `Restapi::render("<endpoint>")` (`ApiController.php:3-25`).
- Cada `rest/<endpoint>.php` fija cabeceras CORS, instancia `new conn` (mysqli), ejecuta SQL interpolado y hace `echo json_encode(...)`.

### Capa D — Cargas masivas (`api/UPDATE/<dominio>/`)
Scripts PHP **standalone**, accesibles por URL directa (`gestioncobranza.com/api/UPDATE/BASE/`). Patrón típico por dominio: `index.php` (UI) + `index.js` (cliente) + `upload.php` (recibe archivo) + `procesar.php` (parsea CSV → SQL). Tienen su **propia copia** de credenciales (`api/UPDATE/DB.php`).

> **No verificado:** flujo interno exacto de cada `procesar.php` (mapeo columna→tabla) — no inspeccionado en este lote; pertenece a otro documento.

---

## 4. Convención de enrutado (las dos `.htaccess`)

Ambos front controllers usan la misma regla mod_rewrite:

```apache
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-l
RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]
```
(`public/.htaccess:1-7`, `api/api/.htaccess:1-7`)

Efecto: si la ruta solicitada **no** es un archivo/directorio/symlink real, se reescribe a `index.php?url=<path>` preservando el resto del query string (`[QSA]`). Así `public/gestion` → `index.php?url=gestion`, y `api/api/g_operacion?o=123` → `index.php?url=g_operacion&o=123`. La raíz `cegroup/.htaccess` está **vacía** (0 bytes).

---

## 5. Flujo completo de un request

### 5.1 Request WEB — usuario navega a `/public/gestion`

```
1. Browser → GET https://gestioncobranza.com/public/gestion
2. Apache (public/.htaccess) reescribe → public/index.php?url=gestion
3. public/index.php:2  session_start()
4. public/index.php:3  if (isset($_SESSION["session"]))   ← gate de sesión
       (si NO hay sesión → header("Location: ../") → login)   (public/index.php:11-12)
5. public/index.php:4-6  chdir(dirname(__DIR__)); define SYS_PATH="lib/", APP_PATH="app/"
6. public/index.php:7  require lib/init.php
       → carga App, Action, Css, Router (init.php:2-5)
       → require app/http/routes.php  → Router::add() × 13 (init.php:6)
       → carga Response (init.php:7)
7. public/index.php:8  require app/parts/header.php
       → <head> con CSS ESTÁTICO (header.php:11-16)
       → topbar + require menu.php (header.php:67)
            → menu.php lee $_SESSION['session_type'] y pinta sidebar admin o asesor (menu.php:18-24)
8. public/index.php:9  $app = new App
       → App::__construct (App.php:4) lee $_GET["url"] = "gestion"
       → Router::getAction("gestion") = {MainController, gestion} (App.php:8)
       → require app/controllers/MainController.php; (new MainController)->gestion() (App.php:11-13)
       → MainController::gestion() → Response::render("gestion") (MainController.php:5)
       → Response::render → require app/views/gestion.php  (Response.php:8)   ← HTML al browser
9. public/index.php:10 require app/parts/footer.php
       → scripts base (footer.php:17-26)
       → <?php new Action ?> (footer.php:28)
            → Action lee $_GET["url"]="gestion", emite <script src="../app/action/gestion.js"> (Action.php:8-11)
10. Browser ejecuta gestion.js → fetch a http://gestioncobranza.com/api/api/<endpoint> (base en main.js:33)
```

Si la ruta no existe en el Router, `App` captura la `Exception` y hace `require app/views/error.php` (`App.php:14-15`). Si no hay `$_GET["url"]`, carga `app/views/index.php` (vacío) (`App.php:17-18`).

### 5.2 Request API — JS hace `fetch` a `/api/api/g_operacion?o=12345`

```
1. Browser → GET http://gestioncobranza.com/api/api/g_operacion?o=12345
2. Apache (api/api/.htaccess) reescribe → api/api/index.php?url=g_operacion&o=12345
3. api/api/index.php:2  session_start()
4. api/api/index.php:3-6  chdir(dirname(__DIR__)); define SYS_PATH, APP_PATH, CODING=null
5. api/api/index.php:7  require lib/initial.php
       → carga Put, Api, Router (initial.php:2-4)
       → require app/http/routes.php → Router::add() × 23 (initial.php:5)
       → carga Restapi, DB (initial.php:6-7)
6. api/api/index.php:23  $app = new Api
       → Api::__construct (Api.php:4) lee $_GET["url"]="g_operacion"
       → Router::getAction("g_operacion") = {ApiController, g_operacion} (Api.php:7)
       → require app/controllers/ApiController.php; (new ApiController)->g_operacion() (Api.php:10-12)
       → ApiController::g_operacion() → Restapi::render("g_operacion") (ApiController.php:6)
       → Restapi::render → require app/rest/g_operacion.php (Restapi.php:8)
7. rest/g_operacion.php:
       - header CORS: Access-Control-Allow-Origin: * (patrón de login.php:2-4)
       - new conn → mysqli a localhost con creds hardcoded (DB.php:5)
       - ejecuta SQL interpolado con $_GET (sin prepared statements)
       - echo json_encode($data)
8. Browser recibe JSON y rellena DOM
```

Si la ruta API no existe, `Api` captura la `Exception` y hace `require app/rest/error.php` → JSON `{success:"error", message:"Ruta no encontrada"}` con HTTP 404 (`Api.php:13-14`, `error.php:7-9`). Sin `url`, devuelve `rest/index.php` (`Api.php:16-17`).

---

## 6. Separación front/back y estado

- **Front (HTML):** solo `app/views/*.php` + `app/parts/*`. No contienen lógica de datos; son contenedores.
- **Front (lógica):** `app/action/*.js` + `app/assets/js/main.js`. Aquí están los `fetch`, el render del DOM y la sesión de cliente.
- **Back web:** solo decide qué vista y qué JS cargar. No toca la BD.
- **Back API:** único punto que toca la BD. Cada endpoint reabre conexión `mysqli`.
- **No hay estado compartido** entre la capa web y la API más allá del `$_SESSION` de PHP (ambas hacen `session_start()` sobre el mismo dominio/cookie).

### Dónde vive la sesión
- **Servidor:** `$_SESSION["session"]` y `$_SESSION["session_type"]`, activados por `app/php/session.php` (`session.php:6-7`) y leídos por `public/index.php:3` (gate) y `menu.php:18` (rol).
- **Cliente:** `localStorage` con `session`, `session_type`, `session_name`, `session_user`, `session_avatar` (`index.php:133-137`). La **identidad del usuario** (nombre, username) vive **solo** en `localStorage`; el servidor solo conoce que hay sesión y el tipo. Detalle en `04-autenticacion-sesion.md`.

### Cómo se sirven los assets
- CSS/JS/fonts/libs salen del filesystem bajo `app/assets/` mediante rutas relativas `../app/assets/...` desde `public/` (`header.php:10-16`, `footer.php:17-26`). La regla mod_rewrite **no** los intercepta porque las `RewriteCond` excluyen archivos reales (`!-f`).
- CSS del login se sirve con rutas relativas `app/assets/...` (sin `../`) porque `index.php` está en la raíz (`index.php:14-19`).

---

## 7. Defectos arquitectónicos verificados

1. **Duplicación de framework:** `lib/` (web) y `api/lib/` (API) son copias casi idénticas (`Router.php` es literalmente igual; `App.php` vs `Api.php` solo difieren en el fallback). Cualquier cambio debe hacerse dos veces.
2. **`App`/`Action`/`Css` duplican el parser de URL** (`parseUrl()` repetido en cada clase: `App.php:21`, `Action.php:19`, `Css.php:20`).
3. **`Css` es código muerto:** definida en `lib/Css.php` pero nunca instanciada. El CSS se carga estático en `header.php`.
4. **Endpoints huérfanos en la API:**
   - Ruta `g_asesor` → método `g_asesor` que **no existe** en `ApiController` (verificado: `grep -c g_asesor ApiController.php` = 0). Invocar `api/api/g_asesor` produciría un `Fatal error: call to undefined method` (`routes.php:5` vs `ApiController.php`).
   - Existe método `asesor()` (`ApiController.php:5`) y archivo `rest/asesor.php`, pero **ninguna ruta** apunta a él.
   - Existe archivo `rest/clientes.php` sin ruta ni método → código muerto.
5. **Triple copia de credenciales de BD:** `api/lib/DB.php`, `api/UPDATE/DB.php`, y `api/UPDATE/<dominio>/*` con conexiones propias.
6. **Sin abstracción de acceso a datos:** cada endpoint reescribe connect → query → format → echo, con SQL interpolado (ver `04` para el caso de `login.php`).
7. **Sin logging estructurado:** errores van a `print()`/`echo` o al `error_log` del cPanel. Confirmado por `error_log` con fatales recurrentes (`b_data.php:128`, memoria agotada).

---

## 8. Despliegue (inferido)

> **No verificado** (no hay scripts ni config de deploy en el repo). Indicios:
- Ruta en disco `/home/g2ikz73cb0c2/public_html/` (cPanel GoDaddy) — subida por FTP/File Manager probable.
- BD MariaDB en el mismo servidor (`connection.json` host = mismo `secureserver.net`); `new conn` conecta a `"localhost"` (`DB.php:5`), confirmando co-localización.
- Sin CI/CD, sin staging, sin migraciones (no hay archivos de schema versionado).
- Versión visible: "COBRANZA Version 3.0" (`index.php:32-33`).
