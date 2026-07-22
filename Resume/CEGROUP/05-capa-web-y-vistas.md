# CEGROUP — Capa Web y Vistas (documentación verificada campo por campo)

> Alcance: capa web del proyecto legacy PHP `cegroup/`. Todo lo aquí descrito está leído directamente del código. Las citas son `archivo:línea` relativas a `cegroup/`. Cuando algo no se puede afirmar con el código a la vista, se marca como **No verificado**.
>
> Fecha de los fuentes: vistas/acciones datadas entre 2022-01 y 2022-11 (un par tocadas en 2023). El front consume una API externa documentada por separado (ver doc de capa API). Aquí solo se documenta lo que el navegador ejecuta.

---

## 0. Cómo arranca y enruta la aplicación (mecánica real)

El front NO es una SPA. Es PHP server-side con un mini-router casero que decide qué `view` incluir, más un archivo JS por vista que aporta toda la lógica de cliente (jQuery + `fetch`).

### 0.1 Punto de entrada y sesión

- **`index.php` (raíz del proyecto)** = pantalla de **login**. `index.php:2-3` hace `session_start(); session_destroy();` — entrar al login siempre destruye la sesión PHP previa.
  - Formulario `#frm` con dos campos: `#username` (text, `required`, `index.php:61`) y `#userpass` (password, `index.php:66`; el `required` está mal puesto en el botón toggle, `index.php:67`, no en el input).
  - Botón submit "INGRESO." (`index.php:73`).
  - Lógica inline (no en `action/`): `login()` (`index.php:108`) arma la URL `http_data + 'login?u='+user+'&p='+pass` (`index.php:113`) y llama `select_asesor()` → `fetch` GET (`index.php:118-126`).
  - `process_data()` (`index.php:128`): si `data.num == 1`, guarda en **localStorage** `session_name`, `session_user`, `session_type`, `session_avatar`, `session='ACTIVE'` (`index.php:133-137`), luego pega a `app/php/session.php?a=on&t=<usertype>` (`index.php:132,138-142`) y redirige a `public/` (`index.php:141`).
  - Si falla: toast de error "Los datos de acceso no son correctos" (`index.php:150-171`). Loguea `data` y `data.sql` en consola (fuga de SQL al cliente, `index.php:169-170`).
  - `http_data` apunta por defecto a producción `http://gestioncobranza.com/api/api/` (`index.php:105-106`).

- **`public/index.php`** = guardián + bootstrap de la app interna (`public/index.php`):
  - `session_start()`; si NO existe `$_SESSION["session"]` → `header("Location: ../")` (vuelve al login) (`public/index.php:3,11-12`).
  - Si hay sesión: `chdir(dirname(__DIR__))`, define `SYS_PATH="lib/"` y `APP_PATH="app/"`, requiere `lib/init.php`, luego `header.php`, instancia `new App`, luego `footer.php` (`public/index.php:4-10`).

- **`app/php/session.php`**: maneja la sesión PHP del navegador (`session.php`). `?a=on&t=<type>` → `$_SESSION["session"]='ACTIVE'` y `$_SESSION["session_type"]=$type` (`session.php:4-7`). Cualquier otro valor de `a` → `session_destroy()` (`session.php:8-9`). **El `session_type` que decide el menú sale de aquí.**

### 0.2 Mini-framework (`lib/`)

- **`lib/init.php`**: carga en orden `App.php`, `Action.php`, `Css.php`, `Router.php`, `http/routes.php`, `Response.php` (`init.php:2-7`).
- **`lib/Router.php`**: registro estático `add($route,$controller,$method)` en array (`Router.php:8-10`); `getAction($route)` devuelve el match o lanza `Exception("La ruta '$route' NO fue encontrada")` (`Router.php:11-17`).
- **`lib/App.php`**: lee `$_GET["url"]`; si existe, resuelve ruta → `require controllers/<Controller>.php` → `new $controller()->$method()` (`App.php:5-13`). Si la ruta no existe (Exception) → `require views/error.php` (`App.php:14-15`). Si NO hay `url` → `require views/index.php` (`App.php:17-18`). **Ojo:** `views/index.php` está vacío (ver §3.13).
- **`lib/Response.php`**: `Response::render($view)` simplemente hace `require APP_PATH."views/".$view.".php"` (`Response.php:8`). No hay capa de plantilla; la vista es HTML plano.
- **`lib/Action.php`**: instanciada en el footer. Lee `$_GET["url"]`, resuelve la ruta y **inyecta `<script src="../app/action/<method>.js">`** (`Action.php:11`). Si no hay `url` inyecta `action/index.js` (`Action.php:16`). **Este es el acoplamiento clave: el nombre de la vista = nombre del método = nombre del archivo JS.**
- **`lib/Css.php`**: clase análoga a `Action` que inyectaría `<link ... css/<method>.css>` por vista (`Css.php:11`). **No verificado que se use: `Css` nunca se instancia** (`grep "new Css"` = 0 resultados). Los `.css` por vista tampoco existen en `assets/css/` (solo hay `app.min.css`, `main.css`, bootstrap, icons). En la práctica el estilo por vista NO se carga; toda la presentación cuelga de `app.min.css` + `main.css`.

### 0.3 Controlador y rutas

**`app/controllers/MainController.php`** — un único controlador. Cada método solo hace `Response::render("<vista>")`:

| Método | Línea | Render |
|---|---|---|
| `index()` | `MainController.php:3` | `index` |
| `buscar()` | `MainController.php:4` | `buscar` |
| `gestion()` | `MainController.php:5` | `gestion` |
| `acuerdos()` | `MainController.php:6` | `acuerdos` |
| `comunicacion()` | `MainController.php:7` | `comunicacion` |
| `asesores()` | `MainController.php:8` | `asesores` |
| `reportes()` | `MainController.php:9` | `reportes` |
| `resumen()` | `MainController.php:10` | `resumen` |
| `base()` | `MainController.php:11` | `base` |
| `perfil()` | `MainController.php:12` | `perfil` |
| `alertas()` | `MainController.php:13` | `alertas` |
| `filtro()` | `MainController.php:14` | `filtro` |
| `filtrotabla()` | `MainController.php:15` | `filtrotabla` |

**`app/http/routes.php`** — 13 rutas, una por método anterior, todas al mismo controlador:

`index, buscar, gestion, acuerdos, comunicacion, asesores, reportes, resumen, base, perfil, alertas, filtro, filtrotabla` (`routes.php:2-14`).

**Hallazgos de enrutamiento:**
- `clientes` y `cliente` **NO tienen ruta ni método** en el controlador. Sin embargo existe `views/clientes.php` (con contenido) y `action/clientes.js`, y `clientes.js` enlaza a `cliente?c=<cedula>` (`clientes.js:82`). → **`clientes`/`cliente` son vistas huérfanas** (sin ruta que las sirva). Ver §3.16.
- `comunicacion` tiene ruta y método (`routes.php:6`, `MainController.php:7`) pero su vista (`views/comunicacion.php`) y su JS (`action/comunicacion.js`) están **vacíos** (0 bytes). Renderiza una página en blanco. → **stub/muerta**. Ver §3.6.
- `index` tiene ruta pero `views/index.php` y `action/index.js` están **vacíos** (0 bytes). Como `App` solo cae en `views/index.php` cuando no hay `url`, y `public/index.php` siempre entra con sesión activa, la pantalla "home" interna es una página en blanco. Ver §3.13.

---

## 1. Menú lateral y control por rol (`app/parts/menu.php`)

El menú se construye en PHP a partir de `$_SESSION['session_type']` (`menu.php:18`). Cada ítem es un array `[ruta, etiqueta, icono_boxicons]`:

| Variable | Ruta | Etiqueta | Icono | Línea |
|---|---|---|---|---|
| `$menu_buscar` | `buscar` | BUSQUEDA | `bx-search-alt` | `menu.php:3` |
| `$menu_gestion` | `gestion` | GESTION | `bx-pencil` | `menu.php:4` |
| `$menu_acuerdos` | `acuerdos` | ACUERDOS | `bx-select-multiple` | `menu.php:5` |
| `$menu_comunicacion` | `comunicacion` | COMUNICACION | `bx-conversation` | `menu.php:6` |
| `$menu_asesores` | `asesores` | ASESORES | `bxs-user-detail` | `menu.php:7` |
| `$menu_reportes` | `reportes` | REPORTES | `bx-poll` | `menu.php:8` |
| `$menu_resumen` | `resumen` | RESUMEN | `bx-bar-chart-square` | `menu.php:9` |
| `$menu_base` | `base` | BASE DE DATOS | `bx-data` | `menu.php:10` |
| `$menu_process` | `procesos` | PROCESOS | `bxs-edit` | `menu.php:11` |

**Reglas de visibilidad por rol** (`menu.php:15-24`):

- **Usuario / asesor** (`session_type != 0`) → `$menu_session_user` = **BUSQUEDA, GESTION, ACUERDOS, RESUMEN** (`menu.php:15`).
- **Admin / coordinador** (`session_type == 0`) → `$menu_session_admin` = **BUSQUEDA, ASESORES, REPORTES, BASE DE DATOS** (`menu.php:16`).
- Lógica: `if($menu_session_type==0){ admin } else { user }` (`menu.php:20-24`).

El `foreach` renderiza cada ítem como `<li><a href="ruta"><i class="bx icono"></i><span>etiqueta</span></a></li>` (`menu.php:35-46`).

**Hallazgos del menú:**
- `$menu_comunicacion` y `$menu_process` **se declaran pero nunca se usan** (no entran en ninguno de los dos arrays de `menu.php:15-16`). COMUNICACION y PROCESOS nunca aparecen en el menú para ningún rol.
- No existe ruta `procesos` ni vista `procesos.php`; el ítem sería un enlace muerto aunque se mostrara.
- Vistas accesibles desde header (no menú): `perfil`, `alertas` (ver §2). `filtro`, `filtrotabla` solo se alcanzan desde botones internos de `gestion` (ver §3).
- El control de rol es puramente **visual** (oculta ítems de menú). No hay guard server-side por ruta: cualquier sesión activa puede pedir `?url=base`, `?url=asesores`, etc. y `App` la renderiza igual. **El rol no protege endpoints de vista.**

---

## 2. Layout (`header.php` + `footer.php`)

### 2.1 `app/parts/header.php` (incluido por `public/index.php` antes de la vista)

- `<title>COBRANZA</title>` (`header.php:6`). Idioma `es`.
- CSS cargados (`header.php:11-16`): `bootstrap.min.css`, DataTables `datatables.min.css`, `icons.min.css`, `toastr.min.css`, `app.min.css`, `main.css`.
- `<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">` (`header.php:19`).
- **Topbar** (`header.php:21-66`):
  - Logo → `href="index"` (`header.php:25`).
  - Botón hamburguesa `#vertical-menu-btn` (toggle del sidebar, manejado en `app.js`) (`header.php:34`).
  - Botón fullscreen `data-toggle="fullscreen"` (`header.php:40`).
  - **Botón de alertas** `#header_btn_alertas` → `onclick="pageDir('alertas')"` (`header.php:45`). Su contenido (campana + badge con número) lo inyecta `main.js` vía `process_data_alerta` (ver §2.3).
  - Dropdown de usuario: avatar fijo `images/page/user.png`, `<span id="session_user_name">` (lo llena `main.js`), opciones **PERFIL** (`href="perfil"`, `header.php:56`) y **CERRAR SESIÓN** (`onclick="sessionOff()"`, `header.php:61`).
- Al final: `require "menu.php"` (`header.php:67`).

### 2.2 `app/parts/footer.php`

- Footer estático "2022 © Derechos reservados / Diseño y Desarrollo AGENCIA CRAFT" (`footer.php:5-9`).
- **Scripts globales** (`footer.php:17-26`): jQuery, bootstrap bundle, metisMenu, simplebar, node-waves, DataTables, toastr, `moment.min.js`, `app.js`, `main.js`.
- `<?php $action = new Action; ?>` (`footer.php:28`) → inyecta el `<script src="../app/action/<vista>.js">` correspondiente (mecánica §0.2).

**Hallazgo:** `fetch.js` (que contiene `fetch_get_data`, `fetch_post_data`, `moneda_format`, etc.) **NO se incluye en el footer**. Esas funciones, sin embargo, sí están definidas en `main.js` (`main.js:111-179`). `assets/js/fetch.js` parece una copia previa/duplicada; la fuente real en runtime es `main.js`. **No verificado** que `fetch.js` se cargue en ningún punto.

### 2.3 Helpers globales en `assets/js/main.js` (cargado en todas las vistas internas)

- Al cargar: lee `localStorage.session`; si `=='ACTIVE'` → `sessionData()`, si no → `sessionOff()` (`main.js:1-4`).
- `sessionOff()` (`main.js:8-15`): pega a `session.php?a=off`, limpia localStorage, redirige a `../` (login).
- `sessionData()` (`main.js:17-23`): pinta `#session_user_name`, y consulta alertas de hoy `b_alertas?a=<user>&t=hoy&h=<hora>` para el badge.
- `sessionDataUser(x)` = `localStorage.getItem(x)` (`main.js:24-26`) — usado por TODAS las vistas para sacar `session_user`, `session_name`, `session_avatar`.
- **URLs base** (`main.js:30-36`): local vs server; activo = **server** (`http://gestioncobranza.com/api/api/` y `/api/file/`).
- `getUrl(par)` (`main.js:42-63`): parser de query string (usado para leer `?o=` en gestion, `?c=` en clientes).
- `pageDir(page)` (`main.js:65-83`): navegación a `alertas`/`filtro`/`filtrotabla`/`gestion`; default = `history.back()`.
- `moneda_format()` / `number_format()` (`main.js:85-108`): formato de moneda con separador de miles.
- `fetch_get_data(url,get_process)` (`main.js:111-120`): GET a `http_data+url`, parsea JSON, llama `process_data_get(data,get_process)` **que cada vista define en su propio JS**.
- `fetch_get_alerta` + `process_data_alerta` (`main.js:122-139`): pinta la campana del header con el conteo.
- `fetch_post_data(url,formData,post_process,form)` (`main.js:145-156`): POST con `FormData`, llama `process_data_pots(data,post_process,form)` definido por la vista.
- `msj_notification(type,msj)` (`main.js:160-179`): toast (toastr).

### 2.4 `assets/js/app.js`

Theme/plantilla minificada (metisMenu init, toggle sidebar `#vertical-menu-btn`, fullscreen, dark/light/rtl switch, preloader). Es boilerplate del template "Skote/Craft", no lógica de negocio (`app.js:1`).

---

## 3. Catálogo de las vistas

> Convención de cada ficha: **propósito → líneas → estructura/paneles → formularios y campos → botones y su acción JS/endpoint → tablas → estado**. Los endpoints son rutas relativas que `fetch_*` antepone con `http_data` (la API). Documentados aquí solo como contrato vista↔endpoint; el detalle del backend va en la doc de la capa API.

---

### 3.1 `buscar.php` — Búsqueda de operaciones/personas

- **Propósito:** buscar créditos por OPERACIÓN, CÉDULA o NOMBRE y saltar a su `gestion`. Vista del menú de ambos roles.
- **Líneas:** 127 (`buscar.php`). JS: `buscar.js` (199 líneas).
- **Formulario `#frm_search`** (`buscar.php:9-22`):
  | Campo | id | Tipo | Req | Opciones/Notas |
  |---|---|---|---|---|
  | Tipo de búsqueda | `#frm_search_select` | `<select>` | — | OPERACIÓN / CÉDULA / NOMBRE (`buscar.php:13-15`) |
  | Valor | `#frm_search_input` | text | `required` (`buscar.php:17`) | término libre |
  | (submit) | botón lupa | submit | — | dispara la búsqueda |
- **Acción JS** (`buscar.js:14-33`): según el select, llama:
  - OPERACION → `b_data?t=o&v=<valor>` (proceso `OPERACION`)
  - CEDULA → `b_data?t=c&v=<valor>` (proceso `DATA`)
  - NOMBRE → `b_data?t=n&v=<valor>` (proceso `DATA`)
- **Paneles de resultado** (control con `section_box()`, `buscar.js:189-192`): `#section_box_true` (resultados), `#section_box_loader` (spinner), `#section_box_false` ("No se encontraron resultados", `buscar.php:104-108`).
- **Tablas** (`buscar.php:27-93`), todas con columna final = botón `→ gestion?o=<operacion>`:
  - `#tbl_operacion`: # / T CÉDULA / T NOMBRE / C1 CÉDULA / C1 NOMBRE / C2 CÉDULA / C2 NOMBRE (campos JS: `tcedula, tnombre, ccedula, cnombre, gcedula, gnombre`, `buscar.js:62-77`).
  - `#tbl_titular`: # / OPERACIÓN / CÉDULA / NOMBRE (`buscar.js:112-124`).
  - `#tbl_codeudor`: idem con `ccedula/cnombre` (`buscar.js:137-149`).
  - `#tbl_garante`: idem con `gcedula/gnombre` (`buscar.js:162-174`).
- **Estado:** ACTIVA.

---

### 3.2 `gestion.php` — Ficha de gestión de un crédito (vista más compleja)

- **Propósito:** núcleo operativo del asesor. Muestra todo el crédito por operación `?o=` y permite registrar gestión, teléfono, email, acuerdo, cambio de estado, resumen y alerta. Vista del menú de usuario.
- **Líneas:** 782 (`gestion.php`). JS: `gestion.js` (1001 líneas) — el más grande.
- **Carga inicial** (`gestion.js:5-15`): lee `?o=`; si falta → `error_no_operacion()` ("INGRESE OPERACIÓN"). Si existe: `create_process()` (resetea flags localStorage), consulta `g_mensaje?a=<user>` (mensaje del coordinador → modal) y `g_operacion?o=<op>` (datos del crédito).
- **`g_operacion`** (`gestion.js:370-453`) llena el panel INFO y dispara 8 fetch encadenados: `g_telefonos`, `g_gestiones?p=init`, `g_aportes`, `g_acuerdos`, `g_estados`, `g_resumen?p=init`, `g_alertas`, `g_email` (`gestion.js:410-417`).

**Header / barra superior** (`gestion.php:6-73`):
- Form `.buscar_operacion` con input `name="o"` (`gestion.php:8-13`) — re-buscar operación.
- `item_1` → `next_operation()` (avanza a la siguiente operación del filtro, `gestion.php:15`); `item_2` → `pageDir('filtro')` (`gestion.php:18`); `item_3` → `pageDir('filtrotabla')` (`gestion.php:21`).
- Tarjetas **Saldo Total** `#data_total` y **Saldo Capital** `#data_capital` (`gestion.php:65-72`).
- Botonera INFO (cambia el panel visible): INFO/TITU/COD1/COD2/TELE/GEST/APOR → `view_info_*()` (`gestion.php:35-62`, JS `gestion.js:24-76`).

**Línea de pestañas de formulario** (`gestion.php:76-102`), con check de "completado": GESTIONES, TELÉFONOS, ACUERDOS, ESTADO, RESUMEN, ALERTA → `form_view_*()` (`gestion.js:85-130`).

**Paneles de información (solo lectura)** (`gestion.php:106-500`):
- INFO (`info_view_info`): Operación, Banco, F Vencimiento, F Ingreso, Estado, Sub Estado, Dependencia, Campaña, Cartera, Decil, Asignado a (campos JS `operacion, banco, fvencimiento, fingreso, estado, sub, dependencia, campana, cartera, decil, asesor`, `gestion.js:373-406`).
- TITULAR / COD1 / COD2: Cédula, Nombre, T Fijo, T Movil (con inputs hidden `info_data_input_*` que alimentan los formularios de acuerdo/resumen, `gestion.php:177-251`).
- TELÉFONOS `#tbl_telefonos`: id / Télefono / Detalle (`gestion.js:455-469`); enlace a EMAILS arriba a la derecha (`gestion.php:264-268`).
- EMAILS `#tbl_emails`: id / EMAIL (`gestion.js:471-484`).
- GEST `#section_view_gestion`: tarjetas Fecha-Hora / Asesor / id / texto; botón "Ver Todo" → `btn_gestion_view_all()` → `g_gestiones?o=<op>&p=all` (`gestion.php:356`, `gestion.js:143-146`).
- APOR `#tbl_view_aportes`: Fecha / Valor / Asesor + suma total `#suma_aportes_totales` (`gestion.js:513-532`).
- ACUE `#tbl_view_acuerdos`: id / Cliente / Nombre / F_Acuerdo / Valor / Estado / Asesor / F_Registro (`gestion.js:534-553`).
- RESU `#tbl_view_resumen`: id, F_Ingreso, Cedula, Nombre, Operación, Tipo, Canal, Télefono, Contacto, Acuerdos, N_Cuotas, V_Credito, V_Negociado, Condonado, Asesor, F_Registro (`gestion.js:570-597`); "Ver Todo" → `g_resumen?p=all`.
- ALER `#tbl_view_alertas`: Fecha / Hora / Asesor / Alerta (`gestion.js:603-618`).

**Formularios de registro** (panel `.contenido_form`, `gestion.php:503-755`):

| Form | id | Campos (id · tipo · req) | Endpoint POST | Línea JS |
|---|---|---|---|---|
| Gestión | `#form_insert_gest` | `gest_textarea_gestion` textarea req | `g_gestiones` | `gestion.js:643-657` |
| Teléfono | `#form_insert_tele` | `tele_input_telefono` number req · `tele_input_detalle` text req · botón "Saltar" (`status_process('tele')`) | `g_telefonos` | `gestion.js:660-674` |
| Email | `#form_insert_email` | `tele_input_email` email req | `g_email` | `gestion.js:688-698` |
| Acuerdo | `#form_insert_acue` | `acue_select_cliente` (TITULAR/CODEUDOR_1/CODEUDOR_2/TERCERO) req · `acue_input_nombre` text req (autocompletado por `acue_select_change`) · `acue_input_valor` number req · `acue_input_fecha_pago` date req · "Saltar" | `g_acuerdos` | `gestion.js:733-752` |
| Estado | `#form_insert_esta` | `esta_select_estados` req · `esta_select_sub_estados` req (sub filtrado por estado, `esta_select_change`) | `g_estados` | `gestion.js:754-767` |
| Resumen | `#form_insert_resu` | `resu_input_fingreso` hidden · `resu_select_cliente` req · `resu_input_cedula`/`resu_input_nombre` (autollenado por `resu_select_change`) · `resu_select_canal` (TELEFONO CELULAR/FIJO/MSN/WHATSAPP) · `resu_input_telefono` number · `resu_select_contacto` SI/NO · `resu_select_acuerdo` SI/NO · `resu_input_ncuotas`/`vcredito`/`vnegociado`/`condonado` number (default 0) | `g_resumen` | `gestion.js:769-804` |
| Alerta | `#form_insert_aler` | `aler_input_fecha` date req · `aler_select_num` (08–18 h) req · `aler_textarea_detalle` req · "Saltar" | `g_alertas` | `gestion.js:806-823` |

- **Modal** `#modal_center` "MENSAJE DE COORDINADOR" (`gestion.php:768-780`): se muestra si `g_mensaje` devuelve `num` (`gestion.js:361-368`).
- **Flujo "completar 6 operaciones":** cada submit/saltar pone `process_<x>=1` en localStorage (`gestion.js:262-265`); `next_operation()` exige los 6 en 1 antes de avanzar al siguiente del filtro (`gestion.js:278-285`).
- **Hallazgo crítico (seguridad/negocio):** el control de "solo el asesor asignado puede editar" está **comentado** (`gestion.js:440-448`). Actualmente `.contenido_form` se muestra siempre (`gestion.js:437-438`): **cualquier asesor puede editar cualquier operación**, no solo la asignada. **No verificado** si la API replica la restricción.
- **Estado:** ACTIVA (vista central).

---

### 3.3 `acuerdos.php` — Listado de acuerdos por operación/mes/fecha

- **Propósito:** consultar acuerdos del asesor. Vista del menú de usuario.
- **Líneas:** 139 (`acuerdos.php`). JS: `acuerdos.js` (124 líneas).
- **3 formularios de filtro** (`acuerdos.php:18-59`):
  | Form | Campo | Endpoint |
  |---|---|---|
  | `#frm_get_operacion` | `#frm_get_operacion_valor` text req | `b_acuerdos?t=o&v=<op>&a=<user>` (`acuerdos.js:16-25`) |
  | `#frm_get_mes` | `#frm_get_mes_valor` select ENERO–DICIEMBRE (botón etiquetado "2023") | `b_acuerdos?t=m&v=<mm>&a=<user>` (`acuerdos.js:27-36`) |
  | `#frm_get_fecha` | `#frm_get_fecha_valor` date req | `b_acuerdos?t=f&v=<fecha>&a=<user>` (`acuerdos.js:38-47`) |
- **Tabla `#tbl_result`** (`acuerdos.php:71-99`): # / OPERACIÓN / CLIENTE / NOMBRE / F_ACUERDO / VALOR (formateado) + botón `→ gestion?o=` (campos JS `operacion, cliente, nombre, facuerdo, valor`, `acuerdos.js:78-94`). La fila demo "Mark/Otto/@mdo" es placeholder estático que se reemplaza al cargar.
- **Botón DESCARGAR** `#btn_download` (`acuerdos.php:103-105`): href apunta a `http_file+'sql/user/acue/index.php?sql='+sql` (`acuerdos.js:97`). **Está deshabilitado en runtime:** la línea que lo muestra (`$("#btn_download").slideDown(0)`) está **comentada** (`acuerdos.js:98`). → botón muerto.
- **Hallazgo:** el JS recibe `data.acuerdos.sql` y lo usaría en la URL de descarga (exposición de SQL al cliente).
- **Estado:** ACTIVA (con botón de descarga inactivo).

---

### 3.4 `comunicacion.php` — VACÍA

- **Propósito:** **No verificado** (sin contenido). Tiene ruta (`routes.php:6`) y método (`MainController.php:7`), pero `views/comunicacion.php` = **0 bytes** y `action/comunicacion.js` = **0 bytes**. Renderiza página en blanco. El ítem de menú COMUNICACION existe como variable pero no se monta para ningún rol (§1).
- **Estado:** STUB/MUERTA.

---

### 3.5 `asesores.php` — CRUD de asesores (panel admin)

- **Propósito:** crear/editar/activar/desactivar/eliminar asesores y enviarles mensajes. Vista del menú admin.
- **Líneas:** 194 (`asesores.php`). JS: `asesores.js` (339 líneas).
- **Header** (`asesores.php:8-31`): buscador `#frm_select` (`#select_nombre`) → `admin_asesor?t=texto&v=<nombre>` (`asesores.js:187-193`); botón "Nuevo Asesor" → `frm_box('insert')`; botón "Asesores" (excel) → `export_asesores()` abre `http_file+'sql/asesores/index.php'` (`asesores.js:153-155`).
- **Tabla `#tbl_asesores`** (`asesores.php:40-56`): avatar / Cédula / Nombre / Usuario / Télefono / Ranking(posicion) / Estado(badge) / acciones (mensaje, editar) (`asesores.js:95-131`). Carga inicial `admin_asesor?t=all` (`asesores.js:7`).
- **Formularios** (panel `.contenido_form`):
  | Form | id | Campos | Endpoint |
  |---|---|---|---|
  | Crear | `#frm_insert` | `insert_cedula` number req · `insert_nombre` text req · `insert_telefono` number req · `insert_avatar` select (woman1/man1) req · `insert_username` text req | `admin_asesor?t=insert` (`asesores.js:169-185`) |
  | Editar | `#frm_update` | `update_cedula` number **readonly** · `update_nombre` req · `update_telefono` req · `update_username` req | `admin_asesor?t=update` (`asesores.js:195-209`) |
  | (botones en editar) | — | Activar `update_estado('TRUE')` / Desactivar `update_estado('FALSE')` → `admin_asesor?t=estado`; ELIMINAR `delete_asesor()` → `admin_asesor?t=delete` | `asesores.js:211-224` |
  | Mensaje | `#frm_msj` | `mjs_username` text readonly · `mjs_detalle` textarea req | `admin_asesor?t=mensaje_insert`; borrar → `t=mensaje_delete` (`asesores.js:228-246`) |
- Edición se abre con `select_cedula(c)` → `admin_asesor?t=cedula&v=<c>` (`asesores.js:54-57`); mensaje con `select_mensaje(a)` → `admin_asesor?t=mensaje&v=<a>` (`asesores.js:59-64`).
- **Estado:** ACTIVA.

---

### 3.6 `reportes.php` — Generación de reportes (panel admin)

- **Propósito:** abrir reportes (nueva pestaña) por rango/fecha/asesor. Vista del menú admin.
- **Líneas:** 219 (`reportes.php`). JS: `reportes.js` (160 líneas).
- Carga el combo de asesores con `admin_asesor?t=all` → llena todos los `.select_asesor` (`reportes.js:2,59-67`).
- **4 formularios** (cada uno con Fecha DE / Fecha HASTA / Asesor), seleccionados por los botones `btn_select_view('gest'|'acue'|'reca'|'base')` (`reportes.php:191-209`, `reportes.js:9-49`):
  | Form | id | Campos | Reporte |
  |---|---|---|---|
  | Gestiones | `#frm_gest` | `gest_f1` date · `gest_f2` date · `gest_asesor` select req | `gest` |
  | Acuerdos | `#frm_acue` | `acue_f1` · `acue_f2` · `acue_asesor` | `acue` |
  | Recaudo | `#frm_reca` | `reca_f1` · `reca_f2` · `reca_asesor` | `reca` |
  | Bases | `#frm_base` | solo `base_asesor` select req (sin fechas) | `base` |
- **Lógica de apertura** (`reportes.js:114-157`): `type_process(f1,f2)` decide `asesor`/`fecha`/`rango`/`error`; `type_consult()` abre en pestaña nueva `http_file+'sql/admin/<r>/index.php?...'` con los parámetros. `error` (f1>f2) → toast.
- **Hallazgo:** los reportes se generan en endpoints PHP separados bajo `file/sql/admin/...`, no en esta capa.
- **Estado:** ACTIVA.

---

### 3.7 `resumen.php` — KPIs mensuales del asesor

- **Propósito:** mostrar contadores del mes para el asesor logueado. Vista del menú de usuario.
- **Líneas:** 172 (`resumen.php`). JS: `resumen.js` (110 líneas).
- **Formulario `#form_search`** (`resumen.php:16-32`): `#form_search_select` select ENERO–DICIEMBRE (botón "2023"). Submit → `select_mes(v)` (pinta "MES 2022", `resumen.js:38-79`) y `b_resumen?a=<user>&v=<mm>` (`resumen.js:14-22`).
- **6 tarjetas KPI** (`resumen.php:50-164`): GESTIONES `#item_gest`, ACUERDOS `#item_acue`, PAGOS `#item_pago` (moneda), RESUMEN `#item_resu`, BASE `#item_base`, POSICIÓN `#item_posi`. Llenado en `select_data` con `data.gestiones.num, acuerdos.num, pagos.pagos, resumen.num, base.num, posicion.posicion` (`resumen.js:90-111`).
- **Hallazgo:** el botón dice "2023" (`resumen.php:31`) pero `select_mes` etiqueta el periodo como "... 2022" (`resumen.js:78`). Inconsistencia de año hardcodeado.
- **Estado:** ACTIVA.

---

### 3.8 `base.php` — Carga de bases de datos (panel admin)

- **Propósito:** subir archivos (pagos, saldos, cuentas, campañas, deciles). Vista del menú admin.
- **Líneas:** 140 (`base.php`). JS: `base.js` (48 líneas).
- **Botones de selección** (panel `.contenido_form`, `base.php:113-132`): CARGAR PAGOS (`pago`), ACTUALIZAR SALDOS (`sald`), ASIGNAR CUENTAS (`cuen`), CARGAR CAMPAÑAS (`camp`), CARGAR DECILES (`deci`) → `btn_select_view(v)` (`base.js:5-40`), que setea `#file_type` y muestra el form de archivo.
- **Formulario `#frm_file`** (`base.php:34-46`): `#file_csv` file, `#file_type` hidden, botón "Subir Archivo" (type=button), y botón "Descargar Formato".
- **Hallazgo crítico — funcionalidad NO implementada:**
  - El handler `form_file()` escucha `#frm_filet` (con **t** extra), pero el form se llama `#frm_file` → **el listener nunca se engancha** (`base.js:43-48`). Además el cuerpo del submit está vacío (`base.js:45-47`).
  - El botón "Subir Archivo" es `type="button"` sin `onclick` (`base.php:38`) → no hace nada.
  - El botón "Descargar Formato" no tiene acción (`base.php:41-44`).
  - La tarjeta "REGISTROS DE PAGOS / 12 Registros" es **maqueta estática** dentro de un `div.no_view` (oculto) (`base.php:58-81`).
  - **Conclusión:** `base` es UI presente pero **la carga de archivos no funciona en esta capa web** (sería un placeholder; la carga real, si existe, está fuera). **No verificado** que exista subida funcional.
- **Estado:** ACTIVA (UI) / NO FUNCIONAL (subida rota).

---

### 3.9 `perfil.php` — Perfil del usuario

- **Propósito:** ver KPIs de hoy, cambiar avatar y contraseña. Accesible desde el dropdown del header (`header.php:56`).
- **Líneas:** 222 (`perfil.php`). JS: `perfil.js` (139 líneas).
- Carga `d_usuario?a=<user>` → KPIs "GESTIONES HOY" `#gestiones_hoy` y "ACUERDOS HOY" `#acuerdos_hoy` (`perfil.js:6-7,50-56`). Datos de perfil (user/name/avatar) salen de localStorage (`perfil.js:58-68`).
- **Avatares** (`#section_box_avatar`, `perfil.php:91-142`): 16 botones (man1–8, woman1–8) → `cambiar_avatar(v)` → `d_usuario_cambio?a=<user>&t=avatar&v=<avatar>` (`perfil.js:70-73`); al ok actualiza localStorage e imagen (`perfil.js:83-92`).
- **Cambio de contraseña** (`#form_pass`, `perfil.php:146-165`):
  | Campo | id | Tipo | Notas |
  |---|---|---|---|
  | Nueva contraseña | `#input_pass_1` | text (¡no password!) | req |
  | Repetir | `#input_pass_2` | text | req |
  - Validación cliente (`perfil.js:96-127`): ≤12 chars (mensaje dice "máximo 20" en `perfil.php:148` — inconsistente), prohíbe `' " _ & $`, y exige que coincidan. Endpoint: `d_usuario_cambio?a=<user>&t=password&v=<pass>` (`perfil.js:114`).
- **Hallazgos:** contraseña viaja **en texto plano por GET** en la URL (`perfil.js:114`); inputs de contraseña son `type="text"` (visible en pantalla); funciones `form_view_esta`/`form_view_cart` (`perfil.js:19-26`) están definidas pero sin uso en esta vista (código muerto copiado).
- **Estado:** ACTIVA.

---

### 3.10 `alertas.php` — Bandeja de alertas del asesor

- **Propósito:** listar alertas del asesor (todas / por operación / por fecha). Accesible desde la campana del header (`pageDir('alertas')`).
- **Líneas:** 91 (`alertas.php`). JS: `alertas.js` (108 líneas).
- Carga inicial: `b_alertas?a=<user>&t=all` (`alertas.js:7`).
- **Filtros** (`alertas.php:18-27`): `#form_operacion` (`#form_operacion_input` text req) → `b_alertas?a=<user>&t=operacion&o=<op>` (`alertas.js:75-85`); `#form_fecha` (`#form_fecha_input` date req) → `b_alertas?a=<user>&t=fecha&f=<fecha>` (`alertas.js:87-97`).
- **Tabla `#tbody`** (`alertas.php:42-56`): # / OPERACIÓN / ASESOR / FECHA / HORA / ALERTA + botón `→ gestion?o=` (campos JS `operacion, asesor, fecha, hora, alerta`, `alertas.js:40-52`).
- **Estado:** ACTIVA.

---

### 3.11 `filtro.php` — Constructor de filtro (localStorage)

- **Propósito:** definir un filtro (por estado+sub, cartera o campaña) que se guarda en localStorage y luego dirige la navegación de operaciones en `gestion`/`filtrotabla`. Sin ruta en menú; se llega desde `gestion`/`filtrotabla` (botón filtro).
- **Líneas:** 156 (`filtro.php`). JS: `filtro.js` (240 líneas).
- Carga catálogos: `g_estados`, `d_campana`, `d_cartera` (`filtro.js:7-9`).
- **3 formularios** (pestañas ESTADOS/CARTERA/CAMPAÑA, `filtro.php:31-40`, `filtro.js:20-31`):
  | Form | Campos | Acción |
  |---|---|---|
  | `#form_esta` | `esta_select_estados` req · `esta_select_sub_estados` req (sub filtrado por estado) | guarda `status_filter='estado'`, `filter_estado`, `filter_estado_value` en localStorage (`filtro.js:158-170`) |
  | `#form_cart` | `cart_select_cartera` req | guarda `status_filter='cartera'`, `filter_table_value` (`filtro.js:172-183`) |
  | `#form_camp` | `camp_select_campana` req | guarda `status_filter='campana'`, `filter_table_value` (`filtro.js:185-196`) |
- **Panel info** (`filtro.php:44-77`): muestra ESTADO/SUB ESTADO/CARTERA/CAMPAÑA activos y "CONSULTA ACTIVA"/"NO HAY FILTRO" (`status_filter()`, `filtro.js:107-131`).
- **Botones header** (`filtro.php:13-18`): `item_1` → `next_operation()` → `b_datafilter?...` y redirige a `gestion?o=<primera operación>` (`filtro.js:203-236`); `item_2` → `delete_filter()` limpia localStorage (`filtro.js:136-146`).
- **Estado:** ACTIVA. El "filtro" es 100% client-side vía localStorage; el backend solo recibe los parámetros en `b_datafilter`.

---

### 3.12 `filtrotabla.php` — Resultados del filtro en tabla

- **Propósito:** mostrar en tabla todas las operaciones que cumplen el filtro guardado, con salto a cada `gestion`. Se llega desde `gestion`/`filtro`.
- **Líneas:** 65 (`filtrotabla.php`). JS: `filtrotabla.js` (130 líneas).
- Carga: `status_filter()` lee el filtro de localStorage y pide `b_datafilter?t=<tipo>&a=<user>[&e=&s= | &c=]` proceso `SELECT_GET_DATAFILTER` (`filtrotabla.js:12-30`).
- **Tabla `#tbody`** (`filtrotabla.php:36-52`): # / ESTADO / SUB_ESTADO / F_GESTIÓN / OPERACIÓN / ASESOR + botón `→ gestion?o=` (campos JS `estado, sub, fgestion, operacion, asesor`, `filtrotabla.js:57-71`).
- **Botones header** (`filtrotabla.php:13-18`): `item_1` → `next_operation()` (proceso `SELECT_GET_GESTION`) salta directo a `gestion?o=` de la primera operación (`filtrotabla.js:83-117`); `item_2` → `pageDir('filtro')`.
- **Estado:** ACTIVA.

---

### 3.13 `index.php` (vista) — VACÍA

- **Propósito:** sería el "home" interno (lo que `App` carga sin `?url`, `App.php:17-18`). `views/index.php` = **0 bytes**, `action/index.js` = **0 bytes**. En la práctica nunca se ve un home con contenido: el login redirige siempre a `public/` y de ahí se navega por menú.
- **Estado:** VACÍA (placeholder).

---

### 3.14 `pagos.php` — VACÍA / HUÉRFANA

- **Propósito:** **No verificado**. `views/pagos.php` = **0 bytes**, `action/pagos.js` = **0 bytes**. **No tiene ruta ni método** en el controlador. No referenciada por ningún enlace de vista.
- **Estado:** VACÍA + HUÉRFANA.

---

### 3.15 `error.php` — Página 404

- **Propósito:** mostrada por `App` cuando la ruta no existe (`App.php:14-15`). HTML estático "4🛟4 — Lo siento, ruta no encontrada." + imagen (`error.php:7-18`). Sin JS, sin formularios.
- **Estado:** ACTIVA (sin ruta propia; la usa el router como fallback).

---

### 3.16 `clientes.php` / `cliente` — Registro de clientes (HUÉRFANA)

- **Propósito:** registrar clientes (con PDF) y listarlos en DataTable. **No tiene ruta ni método** en el controlador (`grep "cliente"` en routes/controllers = 0). Existe la vista y su JS, pero **no es alcanzable** por el router.
- **Líneas:** 137 (`clientes.php`). JS: `clientes.js` (117 líneas).
- **Formulario `#frm_insert_cliente`** (`clientes.php:28-80`):
  | Campo | id | Tipo | Req |
  |---|---|---|---|
  | Cédula | `#cedula` | text | req |
  | Nombre | `#nombre` | text | req |
  | Teléfono | `#telefono` | tel | req |
  | Correo | `#correo` | email | req |
  | Dirección | `#direccion` | text | req |
  | Documento PDF | `#file` | file | req |
  - Submit → `insert_clientes()` POST `FormData` a **`http + "api/clientes"`** (`clientes.js:29,40-51`). **Hallazgo:** usa la variable `http` que **no está definida** en `main.js` (allí es `http_data`/`http_file`) → en runtime esta vista lanzaría `ReferenceError`. Otra evidencia de vista abandonada.
- **Lista `#tbl`** (DataTable, `clientes.php:104-117`, `clientes.js:68-93`): CÉDULA / NOMBRE / TELÉFONO / CORREO / acción (enlace a `cliente?c=<cedula>`, ruta inexistente, `clientes.js:82`).
- Toggle registro/lista por botones `mostrar_registro()` / `mostrar_lista()` (`clientes.php:14-15`, `clientes.js:5-14`).
- **Estado:** HUÉRFANA (sin ruta) + probablemente rota (`http` indefinido, endpoint `api/clientes` distinto al resto que usa `http_data`).

---

## 4. Tabla resumen (vista → ruta → endpoints → estado)

| Vista | Ruta (routes.php) | Menú (rol) | Endpoints que consume | Estado |
|---|---|---|---|---|
| `index` (login) | — (es la raíz `index.php`) | n/a | `login`, `session.php?a=on` | ACTIVA (entry) |
| `index` (vista interna) | `index` (`routes.php:2`) | logo header | — | **VACÍA** |
| `buscar` | `buscar` (`routes.php:3`) | user + admin | `b_data?t=o/c/n` | ACTIVA |
| `gestion` | `gestion` (`routes.php:4`) | user | `g_mensaje, g_operacion, g_telefonos, g_email, g_gestiones, g_aportes, g_acuerdos, g_estados, g_resumen, g_alertas, b_datafilter` (GET) · POST `g_gestiones, g_telefonos, g_email, g_acuerdos, g_estados, g_resumen, g_alertas` | ACTIVA (núcleo) |
| `acuerdos` | `acuerdos` (`routes.php:5`) | user | `b_acuerdos?t=o/m/f` · descarga `file/sql/user/acue` (deshabilitada) | ACTIVA (descarga rota) |
| `comunicacion` | `comunicacion` (`routes.php:6`) | (no se monta) | — | **STUB/VACÍA** |
| `asesores` | `asesores` (`routes.php:7`) | admin | `admin_asesor?t=all/texto/cedula/mensaje/insert/update/estado/delete/mensaje_insert/mensaje_delete` · excel `file/sql/asesores` | ACTIVA |
| `reportes` | `reportes` (`routes.php:8`) | admin | `admin_asesor?t=all` · abre `file/sql/admin/{gest,acue,reca,base}` | ACTIVA |
| `resumen` | `resumen` (`routes.php:9`) | user | `b_resumen?a=&v=` | ACTIVA |
| `base` | `base` (`routes.php:10`) | admin | (ninguno funcional — subida rota) | ACTIVA UI / **NO FUNCIONAL** |
| `perfil` | `perfil` (`routes.php:11`) | dropdown header | `d_usuario`, `d_usuario_cambio?t=avatar/password` | ACTIVA |
| `alertas` | `alertas` (`routes.php:12`) | campana header | `b_alertas?t=all/operacion/fecha/hoy` | ACTIVA |
| `filtro` | `filtro` (`routes.php:13`) | desde gestion/filtrotabla | `g_estados, d_campana, d_cartera, b_datafilter` | ACTIVA |
| `filtrotabla` | `filtrotabla` (`routes.php:14`) | desde gestion/filtro | `b_datafilter` | ACTIVA |
| `pagos` | — | — | — | **VACÍA + HUÉRFANA** |
| `clientes` / `cliente` | — | — | `api/clientes` (variable `http` indefinida) | **HUÉRFANA + rota** |
| `error` | — (fallback del router) | n/a | — | ACTIVA (404) |

---

## 5. Resumen de hallazgos

**Vistas muertas / no alcanzables:**
- `index` (interna), `pagos`, `comunicacion` → **archivos de 0 bytes** (vista y JS). `comunicacion` tiene ruta pero renderiza en blanco; `pagos` ni siquiera tiene ruta.
- `clientes`/`cliente` → **huérfanas** (sin ruta ni método) y con código roto (variable `http` indefinida, endpoint `api/clientes` que no encaja con el patrón `http_data` del resto).
- Variables de menú `$menu_comunicacion` y `$menu_process` declaradas pero **nunca montadas** (`menu.php:6,11`); `procesos` no tiene ruta ni vista.
- Clase `lib/Css.php` definida pero **nunca instanciada** → el CSS por vista no se carga (no existen los `.css` por vista).
- `assets/js/fetch.js` duplica funciones de `main.js` y **no se incluye** en el footer.

**Funcionalidad presente pero rota/inactiva:**
- `base`: subida de archivos no funciona (listener apunta a `#frm_filet` inexistente, botones sin acción, tarjeta de conteo estática). UI fachada.
- `acuerdos`: botón DESCARGAR oculto (la línea `slideDown` está comentada, `acuerdos.js:98`).
- `gestion`: el bloqueo "solo el asesor asignado edita" está **comentado** (`gestion.js:440-448`) → **cualquier asesor edita cualquier operación**.

**Formularios clave (donde está el negocio real):**
- `gestion` (7 formularios POST: gestión, teléfono, email, acuerdo, estado, resumen, alerta) — es el motor de la cobranza.
- `asesores` (CRUD + mensajes) — administración de usuarios.
- `filtro`/`filtrotabla` — cola de trabajo del asesor, gobernada por localStorage.

**Acoplamientos vista ↔ endpoint / mecánica frágil:**
- Acoplamiento por nombre: `ruta == método == views/<x>.php == action/<x>.js` (inyectado por `lib/Action.php`). Renombrar uno rompe la cadena.
- Cada vista DEBE definir `process_data_get` y/o `process_data_pots` con la firma exacta esperada por `main.js` (`fetch_get_data`/`fetch_post_data`). No hay contrato tipado.
- El "estado" del trabajo (filtros, flujo de 6 pasos en gestion) vive en **localStorage**, no en servidor → no portable entre equipos/navegadores y borrable por el usuario.
- Toda autenticación/identidad de asesor se pasa como query param `a=<session_user>` leído de localStorage → manipulable desde el cliente.

**Seguridad (observaciones de la capa web):**
- Contraseñas (login y cambio en `perfil`) viajan por **GET en la URL** (`index.php:113`, `perfil.js:114`); inputs de contraseña en `perfil` son `type="text"`.
- SQL devuelto al cliente y logueado en consola (`index.php:170`, `acuerdos.js:95-97`).
- Control de rol solo oculta ítems de menú; no hay guard por ruta de vista (cualquier sesión activa puede renderizar `base`, `asesores`, etc. vía `?url=`).
- `index.php` raíz hace `session_destroy()` al cargar el login; la sesión "real" del navegador se reconstruye en `session.php?a=on`.
