# CUMPLIR — Capa Web y Vistas

> Documentación verificada de la capa web de **CUMPLIR**. Todas las rutas relativas a `cumplir/`.
> Fuente: lectura completa de `app/controllers/`, `app/http/`, `app/parts/`, `app/views/*.php`, `app/action/*.js` y verificación cruzada con la capa API (`api/app/rest/`).
> Comparación byte-a-byte contra CEGROUP (`cegroup/...`) vía `diff` donde se indica.

---

## 0. Resumen ejecutivo de diferencias vs CEGROUP

La capa web de CUMPLIR es casi idéntica a CEGROUP. Las diferencias **reales y verificadas** son:

| # | Diferencia | Evidencia | Tipo |
|---|---|---|---|
| 1 | **`gestion.php` ELIMINA la sección Email** (subvista `info_view_email`, tabla `tbl_emails`, botón `form_view_email()`, form `form_insert_email`) | `diff` views; `app/views/gestion.php` no contiene esos IDs | Funcional (resta) |
| 2 | **`gestion.php` AÑADE columna `estado` en la tabla de teléfonos** (`<th>estado</th>`, `app/views/gestion.php:267`) | `diff` views | Funcional (suma) |
| 3 | **`gestion.js` AÑADE gestión de estado de teléfono**: `phoneUpdateStatus()`, `update_post_status_phone()`, botones activar/desactivar por teléfono, endpoint `phone` | `app/action/gestion.js:22-26,446-459,869-881` | Funcional (suma) |
| 4 | **`gestion.js` ELIMINA toda la lógica de Email** (`view_info_email`, `form_view_email`, `form_insert_email`, `select_get_emails`, `insert_post_email`, llamada a `g_email`) | `diff` action JS | Funcional (resta) |
| 5 | **`gestion.js` activa la asignación de asesor** (CEGROUP la tenía comentada `/* */`); usa `.trim()` en la comparación de asesor | `app/action/gestion.js:404-432` | Funcional (cambio) |
| 6 | **`gestion.js` muestra `referencia` junto al banco** (`banco + ' ' + referencia`) | `app/action/gestion.js:364` | Cosmético/datos |
| 7 | A nivel **API**: CUMPLIR tiene `phone.php` (CEGROUP no), CUMPLIR **no tiene** `g_email.php` (CEGROUP sí) | `api/app/rest/`, `api/app/http/routes.php` | Funcional |
| 8 | `acuerdos.php`: label del botón año **2026** (CEGROUP: 2023) | `app/views/acuerdos.php:38` | Cosmético |
| 9 | `resumen.php`: label del botón año **2026** (CEGROUP: 2023) | `app/views/resumen.php:31` | Cosmético |
| 10 | `asesores.php`: estilo del contenedor de tabla (altura fija `398px` en vez de `overflow-y: scroll`) | `app/views/asesores.php:38-41` | Cosmético |

> **Corrección al documento previo** (`resumen/05-capa-web.md`, `06-vistas.md`): afirmaba que `acuerdos.php`, `asesores.php` y `resumen.php` eran "idénticas" a CEGROUP. Esto es **incorrecto** — los tres difieren (puntos 8-10). Además omitía la **adición** del feature de estado de teléfono (puntos 2, 3, 7), describiendo el cambio de `gestion` solo como "email removido".

---

## 1. Controlador y rutas

### MainController (`app/controllers/MainController.php:1-17`)

**Byte-idéntico a CEGROUP** (`diff` → IDENTICAL). 13 métodos, todos sin lógica, cada uno delega a `Response::render()`:

```php
public function buscar() { Response::render("buscar"); }
```

Sin parámetros, sin acceso a BD, sin transformación. Métodos: `index, buscar, gestion, acuerdos, comunicacion, asesores, reportes, resumen, base, perfil, alertas, filtro, filtrotabla`.

### Rutas (`app/http/routes.php:1-15`)

**Byte-idéntico a CEGROUP** (`diff` → IDENTICAL). 13 rutas registradas con `Router::add(ruta, "MainController", metodo)`:

| Ruta `?url=` | Método | Vista cargada |
|---|---|---|
| `index` | `index()` | `views/index.php` (vacía) |
| `buscar` | `buscar()` | `views/buscar.php` |
| `gestion` | `gestion()` | `views/gestion.php` |
| `acuerdos` | `acuerdos()` | `views/acuerdos.php` |
| `comunicacion` | `comunicacion()` | `views/comunicacion.php` (vacía) |
| `asesores` | `asesores()` | `views/asesores.php` |
| `reportes` | `reportes()` | `views/reportes.php` |
| `resumen` | `resumen()` | `views/resumen.php` |
| `base` | `base()` | `views/base.php` |
| `perfil` | `perfil()` | `views/perfil.php` |
| `alertas` | `alertas()` | `views/alertas.php` |
| `filtro` | `filtro()` | `views/filtro.php` |
| `filtrotabla` | `filtrotabla()` | `views/filtrotabla.php` |

**Vistas en `views/` SIN ruta** (huérfanas): `clientes.php`, `pagos.php`, `error.php`. `error.php` lo invoca el `Router` ante ruta inexistente (no listada como método). `clientes.php` y `pagos.php` no tienen método ni ruta → código muerto desde la capa web.

---

## 2. Layout y parts

Los tres parts son **byte-idénticos a CEGROUP** (`diff` → IDENTICAL en los tres).

### Header (`app/parts/header.php:1-67`)
- Topbar: logo (`logo_sm.svg`/`logo_lg.svg`), botón hamburguesa (`#vertical-menu-btn`), botón fullscreen.
- Botón alertas `#header_btn_alertas` → `onclick="pageDir('alertas')"` (línea 45).
- Dropdown usuario: avatar `user.png`, `#session_user_name` (poblado por JS), opciones: **PERFIL** (`href="perfil"`) y **CERRAR SESIÓN** (`onclick="sessionOff()"`).
- `<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">`.
- Incluye `menu.php` al final (línea 67).

### Menu por rol (`app/parts/menu.php:1-64`)

Lee `$_SESSION['session_type']`. Define 9 items, expone solo 4 según rol:

```php
if($menu_session_type ==0){ $menu_session_status = $menu_session_admin; }
else{ $menu_session_status = $menu_session_user; }
```

| Item | Ruta | Icono | `session_type == 0` (ADMIN) | `session_type != 0` (USER) |
|---|---|---|:--:|:--:|
| BUSQUEDA | `buscar` | `bx-search-alt` | ✅ | ✅ |
| GESTION | `gestion` | `bx-pencil` | — | ✅ |
| ACUERDOS | `acuerdos` | `bx-select-multiple` | — | ✅ |
| RESUMEN | `resumen` | `bx-bar-chart-square` | — | ✅ |
| ASESORES | `asesores` | `bxs-user-detail` | ✅ | — |
| REPORTES | `reportes` | `bx-poll` | ✅ | — |
| BASE DE DATOS | `base` | `bx-data` | ✅ | — |
| COMUNICACION | `comunicacion` | `bx-conversation` | **definido, NO listado** | **definido, NO listado** |
| PROCESOS | `procesos` | `bxs-edit` | **definido, NO listado, ruta inexistente** | idem |

- `$menu_session_admin = [buscar, asesores, reportes, base]` (línea 16).
- `$menu_session_user = [buscar, gestion, acuerdos, resumen]` (línea 15).
- **Defectos idénticos a CEGROUP**: `$menu_comunicacion` y `$menu_process` se definen pero nunca se incluyen en ningún array de rol; `$menu_process` apunta a `procesos`, ruta que **no existe** en `routes.php`.

### Footer (`app/parts/footer.php:1-33`)
- Texto: "2022 © Derechos reservados — Diseño y Desarrollo AGENCIA CRAFT".
- Carga JS globales: jQuery, Bootstrap bundle, metisMenu, simplebar, node-waves, DataTables, toastr, moment, `app.js`, `main.js`.
- Línea 28: `<?php $action = new Action; ?>` — inyecta el JS de acción correspondiente a la ruta actual (mecanismo `lib/Action.php`).

---

## 3. Catálogo de las 17 vistas

Conteo verificado (`wc -l views/*.php`): total **2.345 líneas**.

> Cada vista carga su JS homónimo en `app/action/` (mismo nombre). Todos los JS son **byte-idénticos a CEGROUP excepto `gestion.js`** (`diff` action JS → solo `gestion.js: DIFFERENT`).

---

### 3.1 `index.php` — 0 líneas — VACÍA (stub)
Ruta `index`. Archivo vacío. JS `index.js` también vacío. Es la landing tras login; el contenido real lo provee el layout + redirección JS. Idéntica a CEGROUP.

---

### 3.2 `buscar.php` (126 líneas) — Búsqueda de operaciones/clientes
**Idéntica a CEGROUP** (`diff` → IDENTICAL).

**Propósito:** buscar por OPERACIÓN, CÉDULA o NOMBRE y mostrar resultados en 4 tablas.

**Formulario** `#frm_search` (líneas 9-22):
| Campo | id | Tipo | Valores |
|---|---|---|---|
| Tipo búsqueda | `frm_search_select` | select | `OPERACION`, `CEDULA`, `NOMBRE` |
| Término | `frm_search_input` | text (required) | libre |
| Submit | — | button | lupa |

**Tablas resultado** (ocultas por defecto, dentro de `#section_box_true`):
- `#table_box_operacion` → `tbody#tbl_operacion`: `# / T CÉDULA / T NOMBRE / C1 CÉDULA / C1 NOMBRE / C2 CÉDULA / C2 NOMBRE / (acción)`.
- `#table_box_titular` → `tbody#tbl_titular`: `# / OPERACIÓN / CÉDULA / NOMBRE / (acción)`.
- `#table_box_codeudor` → `tbody#tbl_codeudor`: mismas columnas.
- `#table_box_garante` → `tbody#tbl_garante`: mismas columnas.
- Estados auxiliares: `#section_box_loader` (spinner), `#section_box_false` ("No se encontraron resultados").

**Acciones JS** (`action/buscar.js`):
| Acción | Endpoint | Línea JS |
|---|---|---|
| Buscar por OPERACION | `GET b_data?t=o&v=<valor>` → `OPERACION` | `buscar.js:23` |
| Buscar por CEDULA | `GET b_data?t=c&v=<valor>` → `DATA` | `buscar.js:26` |
| Buscar por NOMBRE | `GET b_data?t=n&v=<valor>` → `DATA` | `buscar.js:29` |

---

### 3.3 `gestion.php` (669 líneas) — **VISTA PRINCIPAL — DIFERENTE vs CEGROUP**
**DIFERENTE** (`diff` → DIFFERENT). CEGROUP: 781 líneas. CUMPLIR: 669 (−112). JS `gestion.js` también DIFFERENT (CUMPLIR 881 vs CEGROUP 1000 líneas).

**Propósito:** pantalla de trabajo del asesor sobre una operación: consultar 9 paneles de información y registrar gestiones por 6 formularios.

#### Header (líneas 5-73)
- Form buscar operación `.buscar_operacion` → input `name="o"` (required), submit lupa (línea 8-13).
- Menú de items (líneas 14-24):
  | Botón | onclick |
  |---|---|
  | `item_1` siguiente | `next_operation()` |
  | `item_2` filtro | `pageDir('filtro')` |
  | `item_3` filtrotabla | `pageDir('filtrotabla')` |
- `#msj_loader` ("Procesando…"), `#msj_result`.
- Botones de paneles INFO (líneas 34-63):
  | Botón | onclick | Panel |
  |---|---|---|
  | INFO | `view_info_info()` | General |
  | TITU | `view_info_titu()` | Titular |
  | COD1 | `view_info_cod1()` | Codeudor 1 |
  | COD2 | `view_info_cod2()` | Codeudor 2 |
  | TELE | `view_info_tele()` | Teléfonos |
  | GEST | `view_info_gest()` | Gestiones |
  | APOR | `view_info_apor()` | Aportes |
- Indicadores: **Saldo Total** `#data_total`, **Saldo Capital** `#data_capital` (líneas 64-71).

#### Línea de formularios (líneas 75-100)
Botones que abren cada form de escritura, cada uno con su check de estado `#icon_check_*`:
| Botón | onclick | check |
|---|---|---|
| GESTIONES | `form_view_gest()` | `icon_check_gest` |
| TELÉFONOS | `form_view_tele()` | `icon_check_tele` |
| ACUERDOS | `form_view_acue()` | `icon_check_acue` |
| ESTADO | `form_view_esta()` | `icon_check_esta` |
| RESUMEN | `form_view_resu()` | `icon_check_resu` |
| ALERTA | `form_view_aler()` | `icon_check_aler` |

#### Paneles INFO (líneas 102-429)

**INFO General** (`#info_view_info`, líneas 106-162) — tabla campo→valor:
`data_operacion`, `data_cuenta` (Banco), `data_fvencimiento`, `data_fingreso`, `data_estado`, `data_sub` (Sub Estado), `data_dependencia`, `data_campana`, `data_cartera`, `data_decil`, `data_asesor` (Asignado a).

**Titular** (`#info_view_titu`, 164-193): `data_tcedula` (+ hidden `info_data_input_tcedula`), `data_tnombre` (+ hidden `info_data_input_tnombre`), `data_ttel1` (T Fijo), `data_ttel2` (T Movil).

**Codeudor 1** (`#info_view_cod1`, 195-224): `data_ccedula` (+ hidden), `data_cnombre` (+ hidden), `data_ctel1`, `data_ctel2`.

**Codeudor 2** (`#info_view_cod2`, 226-255): `data_gcedula` (+ hidden), `data_gnombre` (+ hidden), `data_gtel1`, `data_gtel2`.

**Teléfonos** (`#info_view_tele`, 257-281) → `tbody#tbl_telefonos`:
columnas `estado / id / Télefono / Detalle`.
> **DIFERENCIA vs CEGROUP**: la columna **`estado`** (línea 267) es nueva en CUMPLIR. CEGROUP solo tiene `id / Télefono / Detalle`.

**Gestion** (`#info_view_gest`, 283-310): cabecera con enlace **"Ver Todo"** `#gestion_btn_ver_todo` → `btn_gestion_view_all()`. Contenedor `#section_view_gestion` con cards (fecha/hora, asesor, contador).

**Aportes** (`#info_view_apor`, 312-334): total `#suma_aportes_totales`; tabla `tbody#tbl_view_aportes` → `Fecha / Valor / Asesor`.

**Acuerdos** (`#info_view_acue`, 336-362): tabla `tbody#tbl_view_acuerdos` → `id / Cliente / Nombre / F_Acuerdo / Valor / Estado / Asesor / F_Registro`.

**Resumen** (`#info_view_resu`, 364-407): enlace **"Ver Todo"** `#resumen_btn_ver_todo` → `btn_resumen_view_all()`; tabla `tbody#tbl_view_resumen` → `id / F_Ingreso / Cedula / Nombre / Operación / Tipo / Canal / Télefono / Contacto / Acuerdos / N_Cuotas / V_Credito / V_Negociado / Condonado / Asesor / F_Registro`.

**Alertas** (`#info_view_aler`, 409-429): tabla `tbody#tbl_view_alertas` → `Fecha / Hora / Asesor / Alerta`.

> **DIFERENCIA vs CEGROUP**: NO existe panel `#info_view_email` ni tabla `#tbl_emails` (eliminado).

#### Formularios de escritura (`.contenido_form`, líneas 434-643)

**Registrar Gestión** (`#form_insert_gest`, 437-445): `gest_textarea_gestion` (textarea required).

**Registrar Télefono** (`#form_insert_tele`, 447-465): `tele_input_telefono` (number required), `tele_input_detalle` (text required); botón Guardar + "Saltar" → `status_process('tele')`.

**Registrar Acuerdo** (`#form_insert_acue`, 467-500):
| Campo | id | Tipo | Valores |
|---|---|---|---|
| Cliente | `acue_select_cliente` | select | TITULAR, CODEUDOR_1, CODEUDOR_2, TERCERO |
| Nombre | `acue_input_nombre` | text required | — |
| Valor | `acue_input_valor` | number required | — |
| Fecha Pago | `acue_input_fecha_pago` | date required | — |

Botón Guardar + "Saltar" → `status_process('acue')`.

**Cambiar Estado** (`#form_insert_esta`, 502-523): `esta_select_estados` (select, poblado por JS), `esta_select_sub_estados` (select, poblado por JS).

**Registrar Resumen** (`#form_insert_resu`, 525-604):
| Campo | id | Tipo | Valores |
|---|---|---|---|
| (oculto) | `resu_input_fingreso` | hidden | — |
| Cliente | `resu_select_cliente` | select | TITULAR, CODEUDOR_1, CODEUDOR_2, TERCERO |
| Cédula | `resu_input_cedula` | text required | — |
| Nombre | `resu_input_nombre` | text required | — |
| Canal | `resu_select_canal` | select | TELEFONO CELULAR, TELEFONO FIJO, MSN, WHATSAPP |
| # Teléfono | `resu_input_telefono` | number required | — |
| Contacto | `resu_select_contacto` | select | SI, NO |
| Acuerdo | `resu_select_acuerdo` | select | SI, NO |
| Número Cuotas | `resu_input_ncuotas` | number (def 0) | — |
| Valor Credito | `resu_input_vcredito` | number (def 0) | — |
| Valor Negociado | `resu_input_vnegociado` | number (def 0) | — |
| Valor Condonado | `resu_input_condonado` | number (def 0) | — |

**Registrar Alerta** (`#form_insert_aler`, 606-640): `aler_input_fecha` (date required), `aler_select_num` (select horas 08-18), `aler_textarea_detalle` (textarea required); botón Guardar + "Saltar" → `status_process('aler')`.

> **DIFERENCIA vs CEGROUP**: NO existe `#form_view_email` / `#form_insert_email` (eliminado).

#### Modal coordinador (líneas 656-668)
`#modal_center` — "MENSAJE DE COORDINADOR", cuerpo `#modal_center_msj`.

#### Acciones JS — endpoints (`action/gestion.js`)
| Acción | Endpoint | Línea JS | ¿Diferencia? |
|---|---|---|---|
| Mensaje coordinador (init) | `GET g_mensaje?a=<asesor>` → `SELECT_GET_MENSAJE` | 12 | — |
| Cargar operación | `GET g_operacion?o=<op>` → `SELECT_GET_OPERACION` | 13 | — |
| **Activar/desactivar teléfono** | `POST phone` (`status`,`id`) → `UPDATE_POST_STATUS_PHONE` | 26 | **SOLO CUMPLIR** |
| Teléfonos | `GET g_telefonos?o=<op>` → `SELECT_GET_TELEFONOS` | 398, 812, 874 | — |
| Gestiones (init) | `GET g_gestiones?o=<op>&p=init` → `SELECT_GET_GESTIONES` | 399, 799 | — |
| Gestiones (ver todo) | `GET g_gestiones?o=<op>&p=all` → `SELECT_GET_GESTIONES` | 136 | — |
| Aportes | `GET g_aportes?o=<op>` → `SELECT_GET_APORTES` | 400 | — |
| Acuerdos | `GET g_acuerdos?o=<op>` → `SELECT_GET_ACUERDOS` | 401, 825 | — |
| Estados | `GET g_estados` → `SELECT_GET_ESTADOS` | 402 | — |
| Resumen (init) | `GET g_resumen?o=<op>&p=init` → `SELECT_GET_RESUMEN` | 403, 849 | — |
| Resumen (ver todo) | `GET g_resumen?o=<op>&p=all` → `SELECT_GET_RESUMEN` | 141 | — |
| Alertas | `GET g_alertas?o=<op>` → `SELECT_GET_ALERTAS` | 404, 861 | — |
| Datos filtro (next) | `GET b_datafilter?...` → `SELECT_GET_DATAFILTER` | 284-294 | — |
| Insertar gestión | `POST g_gestiones` → `INSERT_POST_GESTION` | 636 | — |
| Insertar teléfono | `POST g_telefonos` → `INSERT_POST_TELEFONO` | 653 | — |
| Insertar acuerdo | `POST g_acuerdos` → `INSERT_POST_ACUERDO` | 674 | — |
| Insertar estado | `POST g_estados` → `INSERT_POST_ESTADO` | 689 | — |
| Insertar resumen | `POST g_resumen` → `INSERT_POST_RESUMEN` | 726 | — |
| Insertar alerta | `POST g_alertas` → `INSERT_POST_ALERTA` | 745 | — |

**Eliminados vs CEGROUP** (presentes en `cegroup/app/action/gestion.js`, ausentes aquí): `view_info_email`, `form_view_email`, `form_insert_email`, `select_get_emails`, `insert_post_email`, y las llamadas `GET g_email?o=<op>` y `POST g_email`.

**Lógica de asignación de asesor** (`gestion.js:404-432`): en CUMPLIR está **activa** (CEGROUP la tenía comentada en bloque `/* … */`). Compara con `.trim()`: `operacion_data["asesor"].trim() == sessionDataUser('session_user').trim()`. También muestra banco + referencia: `$('#data_cuenta').html(operacion_data['banco']+' '+operacion_data['referencia'])` (línea 364). En la tabla de teléfonos renderiza la celda `estado` + dos botones que llaman `phoneUpdateStatus('activo',id)` y `phoneUpdateStatus('',id)` (líneas 446-459).

**Verificación API**: `api/app/rest/phone.php` **existe** y `Router::add("phone", "ApiController", "phone")` está registrado; `api/app/rest/g_email.php` **NO existe** (en CEGROUP sí). Corrobora puntos 3, 4 y 7.

---

### 3.4 `acuerdos.php` (138 líneas) — Consulta histórica de acuerdos — **DIFERENTE (cosmético)**
**DIFERENTE** (`diff` → DIFFERENT). Única diferencia: línea 38, label del botón **"2026"** (CEGROUP: "2023"). `action/acuerdos.js` IDÉNTICO.

**Propósito:** consultar acuerdos por operación, por mes, o por fecha exacta.

**Formularios** (líneas 18-59):
| Form | Campo | id | Tipo |
|---|---|---|---|
| `#frm_get_operacion` | Operación | `frm_get_operacion_valor` | text required; submit "OPERACIÓN" |
| `#frm_get_mes` | Mes | `frm_get_mes_valor` | select ENERO-DICIEMBRE (01-12); submit "**2026**" |
| `#frm_get_fecha` | Fecha | `frm_get_fecha_valor` | date required (en dropdown calendario) |

**Tabla resultado** `#section_box_true` → `tbody#tbl_result`: `# / OPERACIÓN / CLIENTE / NOMBRE / F_ACUERDO / VALOR / (acción +)`. Botón descarga `#btn_download` (oculto). Estados `#section_box_loader`, `#section_box_false`.

**Acciones JS** (`action/acuerdos.js`); `asesor = sessionDataUser('session_user')` en cada submit:
| Acción | Endpoint | Línea JS |
|---|---|---|
| Por operación | `GET b_acuerdos?t=o&v=<op>&a=<asesor>` → `RESULT` | 23 |
| Por mes | `GET b_acuerdos?t=m&v=<mes>&a=<asesor>` → `RESULT` | 34 |
| Por fecha | `GET b_acuerdos?t=f&v=<fecha>&a=<asesor>` → `RESULT` | 45 |

> **Inconsistencia visual**: el label "2026" es estático; el año real lo fija el backend `b_acuerdos.php` (no es parámetro enviado desde el front). El label es decorativo.

---

### 3.5 `comunicacion.php` — 0 líneas — VACÍA (stub)
Ruta `comunicacion` (registrada). Archivo vacío; `comunicacion.js` también vacío (0 bytes). El item de menú COMUNICACION nunca se lista, así que la ruta no es alcanzable por navegación. Stub. Idéntica a CEGROUP.

---

### 3.6 `asesores.php` (194 líneas) — Panel admin de asesores — **DIFERENTE (cosmético)**
**DIFERENTE** (`diff` → DIFFERENT). Única diferencia: líneas 38-41, el contenedor de tabla usa `style="height: 398px"` en vez de `overflow-y: scroll`. `action/asesores.js` IDÉNTICO.

**Propósito:** CRUD de asesores + mensajería + export.

**Header** (líneas 8-29):
- Form `#frm_select` → `#select_nombre` (text required, placeholder "Buscar Asesor…"), submit lupa.
- Botón "Nuevo Asesor" → `frm_box('insert')`.
- Botón "Asesores" (Excel) → `export_asesores()`.

**Tabla** `tbody#tbl_asesores` (líneas 40-56): `(avatar) / Cédula / Nombre / Usuario / Télefono / Ranking / Estado / (acción)`.

**Formularios** (`.contenido_form`):
- `#frm_box_loader` — spinner.
- `#frm_insert` (CREAR, 67-100): `insert_cedula` (number), `insert_nombre` (text), `insert_telefono` (number), `insert_avatar` (select woman1/man1 = Femenino/Masculino), `insert_username` (text).
- `#frm_update` (EDITAR, 103-151): `update_cedula` (number, readonly), `update_nombre`, `update_telefono`, `update_username`; botones Activar `update_estado('TRUE')`, Desactivar `update_estado('FALSE')`, ELIMINAR `delete_asesor()`.
- `#frm_msj` (MENSAJE, 154-186): `mjs_username` (readonly), `mjs_detalle` (textarea); botones Guardar + Borrar `delete_mensaje()`.

**Acciones JS** (`action/asesores.js`):
| Acción | Endpoint | Línea JS |
|---|---|---|
| Listar todos (init) | `GET admin_asesor?t=all` → `SELECT_DATA_ASESOR` | 7, 291, 301, 311, 322 |
| Por cédula | `GET admin_asesor?t=cedula&v=<c>` → `SELECT_DATA_CEDULA` | 56 |
| Mensaje por asesor | `GET admin_asesor?t=mensaje&v=<a>` → `SELECT_DATA_MENSAJE` | 63 |
| Buscar por texto | `GET admin_asesor?t=texto&v=<nombre>` → `SELECT_DATA_ASESOR` | 191 |
| Insertar | `POST admin_asesor?t=insert` → `POTS_INSERT_ASESOR` | 183 |
| Actualizar | `POST admin_asesor?t=update` → `POTS_UPDATE_ASESOR` | 207 |
| Estado | `POST admin_asesor?t=estado` → `POTS_ESTADO_ASESOR` | 216 |
| Eliminar | `POST admin_asesor?t=delete` → `POTS_DELETE_ASESOR` | 223 |
| Insertar mensaje | `POST admin_asesor?t=mensaje_insert` → `POTS_INSERT_MENSAJE` | 236 |
| Eliminar mensaje | `POST admin_asesor?t=mensaje_delete` → `POTS_DELETE_MENSAJE` | 245 |
| Exportar Excel | `window.open(http_file+'sql/asesores/index.php')` | 154 |

---

### 3.7 `reportes.php` (218 líneas) — Panel admin de reportes
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/reportes.js` IDÉNTICO.

**Propósito:** generar 4 reportes Excel (gestiones, acuerdos, recaudo, bases) por rango de fecha y asesor.

**Selector de vista** (`.contenido_form`, 189-213) — botones que muestran cada form:
`btn_select_view('gest')`, `('acue')`, `('reca')`, `('base')`.

**Formularios** (todos con select de asesor poblado por `admin_asesor?t=all`):
| Form | Campos | id |
|---|---|---|
| `#frm_gest` GESTIONES | Fecha DE, Fecha HASTA, Asesor | `gest_f1`, `gest_f2`, `gest_asesor` |
| `#frm_acue` ACUERDOS | Fecha DE, Fecha HASTA, Asesor | `acue_f1`, `acue_f2`, `acue_asesor` |
| `#frm_reca` RECAUDO | Fecha DE, Fecha HASTA, Asesor | `reca_f1`, `reca_f2`, `reca_asesor` |
| `#frm_base` BASES | Asesor (sin TODOS) | `base_asesor` |

**Acciones JS** (`action/reportes.js`):
| Acción | Endpoint | Línea JS |
|---|---|---|
| Poblar selects asesor | `GET admin_asesor?t=all` → `SELECT_DATA_ASESORES` | 2 |
| Reporte base (sin fechas) | `window.open(http_file+'sql/admin/'+r+'/index.php?a='+a)` | 150 |
| Reporte 1 fecha | `…?t='+t+'&a='+a` | 138 |
| Reporte (fecha simple) | `…?t='+t+'&a='+a+'&f='+f1` | 142 |
| Reporte rango | `…?t='+t+'&a='+a+'&f1='+f1+'&f2='+f2` | 146 |

> Los reportes abren scripts PHP servidos desde `http_file` (`sql/admin/<reporte>/index.php`), no endpoints REST.

---

### 3.8 `resumen.php` (171 líneas) — Dashboard KPIs — **DIFERENTE (cosmético)**
**DIFERENTE** (`diff` → DIFFERENT). Única diferencia: línea 31, label "**2026**" (CEGROUP "2023"). `action/resumen.js` IDÉNTICO.

**Propósito:** dashboard de 6 indicadores del mes para el asesor logueado.

**Selector** `#form_search` (16-32): `#form_search_select` (select ENERO-DICIEMBRE 01-12), submit "**2026**".

**KPIs** (`#section_box_true`, 47-166):
| KPI | id | Icono |
|---|---|---|
| GESTIONES | `item_gest` | bx-edit-alt |
| ACUERDOS | `item_acue` | fa-handshake |
| PAGOS | `item_pago` | fa-money-bill |
| RESUMEN | `item_resu` | bx-file-blank |
| BASE | `item_base` | fa-database |
| POSICIÓN | `item_posi` | fa-chart-bar |

Cabecera `#fecha_selected`. Loader `#section_box_loader` ("Calculando").

**Acción JS** (`action/resumen.js:21`): `asesor = sessionDataUser('session_user')`; `GET b_resumen?a=<asesor>&v=<mes>` → `RESULT`.

> El año "2026" es estático en el botón; el backend `b_resumen.php` define el año real.

---

### 3.9 `base.php` (139 líneas) — Panel admin de cargas de datos
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/base.js` IDÉNTICO.

**Propósito (intención):** subir archivos para 5 tipos de carga.

**Header** (8-22): input deshabilitado placeholder "BASES DE DATOS", `#msj_loader`, `#msj_result`.

**Selector de carga** (`.contenido_form`, 111-133) — cada botón llama `btn_select_view(<tipo>)`:
| Botón | tipo | Texto |
|---|---|---|
| CARGAR PAGOS | `pago` | mdi-cash-plus |
| ACTUALIZAR SALDOS | `sald` | mdi-cash-multiple |
| ASIGNAR CUENTAS | `cuen` | mdi-account-settings |
| CARGAR CAMPAÑAS | `camp` | mdi-percent |
| CARGAR DECILES | `deci` | mdi-order-numeric-descending |

**Form de archivo** `#frm_file` (34-46): `#file_csv` (file), `#file_type` (hidden, lo setea `btn_select_view`), botón "Subir Archivo", botón "Descargar Formato". Tarjeta informativa "REGISTROS DE PAGOS / 12 Registros" (datos hardcoded, decorativa).

**Estado real (verificado en `action/base.js`):**
- `btn_select_view(v)` solo cambia el título `#btn_tittle` y setea `#file_type`, luego muestra `#frm_file` (líneas 5-40).
- `form_file()` hace `$("#frm_filet").on("submit", …)` — **el id `#frm_filet` no existe** (la vista usa `#frm_file`). El handler nunca se engancha, y aunque lo hiciera, su cuerpo solo hace `e.preventDefault()` sin enviar nada (líneas 43-48).
- **No hay ninguna llamada `fetch_*` ni upload** en `base.js`.
- **Conclusión: la carga de archivos desde esta vista es un STUB no funcional.** La carga real ocurre por los scripts independientes en `DATA/UPDATE/<TIPO>/` (fuera de la capa web SPA). Idéntico defecto a CEGROUP.

---

### 3.10 `perfil.php` (221 líneas) — Perfil del usuario
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/perfil.js` IDÉNTICO.

**Propósito:** ver perfil, cambiar avatar, cambiar contraseña; mostrar KPIs del día.

**Tarjeta perfil** (14-43): avatar `#perfil_avatar` (loader `#section_avatar_loader` / `#section_avatar`), `#perfil_user` (usuario), `#perfil_name` (nombre). Botones: contraseña `view_section('password')`, avatar `view_section('avatar')`.

**KPIs** (46-87): GESTIONES HOY `#gestiones_hoy`, ACUERDOS HOY `#acuerdos_hoy`.

**Sección avatares** (`#section_box_avatar`, 91-142): 16 botones, cada uno `cambiar_avatar('<id>')` — `man1..man8`, `woman1..woman8`.

**Cambio contraseña** (`#section_box_password`, 146-165): nota "máximo 20 caracteres, no se aceptan caracteres especiales ('&$_\")", `#msj_danger`; form `#form_pass` → `#input_pass_1`, `#input_pass_2` (ambos text required).

**Acciones JS** (`action/perfil.js`):
| Acción | Endpoint | Línea JS |
|---|---|---|
| Cargar usuario (init) | `GET d_usuario?a=<perfil_user>` → `SELECT_DATA_USUARIO` | 7 |
| Cambiar avatar | `GET d_usuario_cambio?a=<user>&t=avatar&v=<v>` → `SELECT_USUARIO_CAMBIO_AVATAR` | 72 |
| Cambiar contraseña | `GET d_usuario_cambio?a=<user>&t=password&v=<pass>` → `SELECT_USUARIO_CAMBIO_PASS` | 114 |

> Nota de seguridad (heredada de CEGROUP): la contraseña viaja como **parámetro GET** en query string y los inputs son `type="text"` (visibles).

---

### 3.11 `alertas.php` (90 líneas) — Lista de alertas
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/alertas.js` IDÉNTICO.

**Propósito:** ver alertas del asesor; filtrar por operación o por fecha.

**Filtros** (18-27): `#form_operacion` → `#form_operacion_input` (text required, submit "OPERACION"); `#form_fecha` → `#form_fecha_input` (date required, submit "FECHA").

**Tabla** `tbody#tbody` (39-57): `# / OPERACIÓN / ASESOR / FECHA / HORA / ALERTA / (acción)`. Estados `#section_box_loader`, `#section_box_false`.

**Acciones JS** (`action/alertas.js`); `asesor = sessionDataUser('session_user')`:
| Acción | Endpoint | Línea JS |
|---|---|---|
| Todas (init) | `GET b_alertas?a=<asesor>&t=all` → `SELECT_ALERTAS_DATA` | 7 |
| Por operación | `GET b_alertas?a=<asesor>&t=operacion&o=<op>` → `SELECT_ALERTAS_DATA` | 83 |
| Por fecha | `GET b_alertas?a=<asesor>&t=fecha&f=<fecha>` → `SELECT_ALERTAS_DATA` | 95 |

---

### 3.12 `filtro.php` (156 líneas) — Constructor de filtro
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/filtro.js` IDÉNTICO.

**Propósito:** construir un filtro (estados/cartera/campaña) acumulable y previsualizar el SQL.

**Header** (6-28): título "FILTRAR DATOS"; menú: `next_operation()` (volver), `delete_filter()` (limpiar). `#msj_loader`.

**Selector** (30-40): `form_view_esta()`, `form_view_cart()`, `form_view_camp()`.

**Panel resumen** (`#info_view_info`, 45-76): muestra `data_estado`, `data_sub`, `data_cartera`, `data_campana` (todos "null" por defecto); `#consult_sql` previsualiza la consulta.

**Formularios**:
- `#form_esta` (83-104): `esta_select_estados`, `esta_select_sub_estados` → "Agregar al Filtro".
- `#form_cart` (108-123): `cart_select_cartera` → "Agregar al Filtro".
- `#form_camp` (128-143): `camp_select_campana` → "Agregar al Filtro".

**Acciones JS** (`action/filtro.js`):
| Acción | Endpoint | Línea JS |
|---|---|---|
| Poblar estados | `GET g_estados` → `SELECT_GET_ESTADOS` | 7 |
| Poblar campañas | `GET d_campana` → `SELECT_GET_CAMPANA` | 8 |
| Poblar carteras | `GET d_cartera` → `SELECT_GET_CARTERA` | 9 |
| Datos filtro (null) | `GET b_datafilter?t=null&a=<asesor>` → `SELECT_GET_DATAFILTER` | 207 |
| Datos por estado/sub | `GET b_datafilter?t=<f>&a=<asesor>&e=<estado>&s=<sub>` → `SELECT_GET_DATAFILTER` | 214 |
| Datos por valor (cartera/campaña) | `GET b_datafilter?t=<f>&c=<value>&a=<asesor>` → `SELECT_GET_DATAFILTER` | 217 |

---

### 3.13 `filtrotabla.php` (66 líneas) — Tabla resultado del filtro
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/filtrotabla.js` IDÉNTICO.

**Propósito:** mostrar en tabla los registros que cumplen el filtro construido.

**Header** (6-28): título "DATOS FILTRADOS"; menú: `next_operation()`, `pageDir('filtro')`. `#msj_loader`.

**Tabla** `tbody#tbody` (36-52): `# / ESTADO / SUB_ESTADO / F_GESTIÓN / OPERACIÓN / ASESOR / (acción)`.

**Acciones JS** (`action/filtrotabla.js`):
| Acción | Endpoint | Línea JS |
|---|---|---|
| Datos (null) | `GET b_datafilter?t=null&a=<asesor>` → `SELECT_GET_DATAFILTER` / `SELECT_GET_GESTION` | 16, 88 |
| Datos por estado/sub | `GET b_datafilter?t=<f>&a=<asesor>&e=<estado>&s=<sub>` → idem | 23, 95 |
| Datos por valor | `GET b_datafilter?t=<f>&c=<value>&a=<asesor>` → idem | 26, 98 |

---

### 3.14 `clientes.php` (137 líneas) — CRUD de clientes — HUÉRFANA (sin ruta)
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). `action/clientes.js` IDÉNTICO.
**Sin ruta ni método en `MainController`/`routes.php`** → no alcanzable por navegación normal. Código muerto desde la capa web.

**Propósito (intención):** registrar clientes con documento PDF y listarlos.

**Toggle** (13-17): `mostrar_registro()`, `mostrar_lista()`.

**Form registro** `#frm_insert_cliente` (28-80):
| Campo | id | Tipo |
|---|---|---|
| Cédula | `cedula` | text required |
| Nombre | `nombre` | text required |
| Teléfono | `telefono` | tel required |
| Correo | `correo` | email required |
| Dirección | `direccion` | text required |
| Documento PDF | `file` | file required |

Botones: Guardar `#btn_register_active`, Guardando `#btn_register_disable`. Error `#msn_register_error`.

**Lista** `#section_list_cliente` → tabla `#tbl` / `tbody#tbl_body`: `CÉDULA / NOMBRE / TELÉFONO / CORREO / (acción)`. Loader `#list_cliente_loader`.

**Acción JS** (`action/clientes.js`): arma `FormData` con cedula, nombre, telefono, correo, direccion, file (líneas 30-35). El destino concreto del POST no usa `fetch_*` estándar visible en el grep — coherente con vista huérfana; no se documenta endpoint REST verificado para esta vista.

---

### 3.15 `error.php` (20 líneas) — Página 404
**IDÉNTICA a CEGROUP** (`diff` → IDENTICAL). Sin ruta propia; la sirve el `Router` ante ruta no encontrada. Markup estático "404 — Lo siento, ruta no encontrada" + imagen `error-img.png`. Sin JS.

---

### 3.16 `pagos.php` — 0 líneas — VACÍA (stub) — HUÉRFANA
Sin ruta ni método. Archivo vacío; `pagos.js` vacío (0 bytes). Código muerto. Idéntica a CEGROUP.

---

### 3.17 Componentes parciales (`app/component/`) — STUBS
9 archivos PHP, **todos `<h1>hola soy X</h1>`** (carpeta planificada nunca implementada), idéntico a CEGROUP. No referenciados por ninguna vista. Código muerto.

```
component/base/{base,cuentas,estados,pagos,saldos}.php
component/reporte/{acuerdos,base,gestiones,recaudo}.php
```

---

## 4. Tabla resumen: vista → ruta → endpoints → estado → diferencia vs CEGROUP

| Vista | Ruta | Endpoints disparados | Estado | Diferencia vs CEGROUP |
|---|---|---|---|---|
| `index.php` | `index` | — | Vacía (stub) | Idéntica |
| `buscar.php` | `buscar` | `b_data` | Funcional | Idéntica |
| `gestion.php` | `gestion` | `g_mensaje, g_operacion, g_telefonos, g_gestiones, g_aportes, g_acuerdos, g_estados, g_resumen, g_alertas, b_datafilter, **phone**` | Funcional | **DIFERENTE** — quita Email, añade estado-teléfono (`phone`), activa asignación asesor |
| `acuerdos.php` | `acuerdos` | `b_acuerdos` | Funcional | DIFERENTE (cosmético: label 2026) |
| `comunicacion.php` | `comunicacion` | — | Vacía (stub) | Idéntica |
| `asesores.php` | `asesores` | `admin_asesor`, export `sql/asesores` | Funcional | DIFERENTE (cosmético: altura tabla) |
| `reportes.php` | `reportes` | `admin_asesor`, `sql/admin/<rep>` | Funcional | Idéntica |
| `resumen.php` | `resumen` | `b_resumen` | Funcional | DIFERENTE (cosmético: label 2026) |
| `base.php` | `base` | — (upload roto: `#frm_filet` inexistente) | **Stub no funcional** | Idéntica |
| `perfil.php` | `perfil` | `d_usuario, d_usuario_cambio` | Funcional | Idéntica |
| `alertas.php` | `alertas` | `b_alertas` | Funcional | Idéntica |
| `filtro.php` | `filtro` | `g_estados, d_campana, d_cartera, b_datafilter` | Funcional | Idéntica |
| `filtrotabla.php` | `filtrotabla` | `b_datafilter` | Funcional | Idéntica |
| `clientes.php` | (sin ruta) | FormData clientes (no REST verificado) | **Huérfana** | Idéntica |
| `error.php` | (Router 404) | — | Estática | Idéntica |
| `pagos.php` | (sin ruta) | — | Vacía + huérfana | Idéntica |
| `component/*` | — | — | Stubs `<h1>` | Idéntica |

**Total: 17 archivos en `views/`** (13 con ruta + `clientes`, `pagos`, `error` sin ruta + `index` con ruta). Vacías: `index`, `pagos`, `comunicacion`. Huérfanas: `clientes`, `pagos` (`error` la usa el Router).

---

## 5. Evidencia

- `app/controllers/MainController.php:1-17` — `diff` vs CEGROUP: IDENTICAL.
- `app/http/routes.php:1-15` — `diff` vs CEGROUP: IDENTICAL.
- `app/parts/{header,menu,footer}.php` — `diff` vs CEGROUP: IDENTICAL (los tres).
- `diff` de las 16 vistas no vacías: solo `acuerdos.php`, `asesores.php`, `gestion.php`, `resumen.php` difieren.
- `diff` de los 15 `action/*.js`: solo `gestion.js` difiere.
- `gestion.php` 669 líneas (CEGROUP 781); `gestion.js` 881 líneas (CEGROUP 1000).
- API: `api/app/rest/phone.php` existe + ruta `phone` registrada (`api/app/http/routes.php`); `api/app/rest/g_email.php` NO existe (CEGROUP sí).
- `base.js:44` engancha a `#frm_filet` (la vista define `#frm_file`) → handler huérfano; sin `fetch_*` de upload.
- `app/component/` → 9 stubs `<h1>hola soy X</h1>`.
