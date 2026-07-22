# Módulos de negocio y procesos — CEGROUP

> Documenta lo que el código HACE, no lo que debería hacer. Cada afirmación cita `archivo:línea` o dato de evidencia (`_evidence/*.json`, introspección 2026-06-21). El modelo de negocio NO se infiere: se describe el comportamiento observable.

## Contexto técnico mínimo

- App PHP "mini-framework" casero. Web en `cegroup/` (sesión PHP) + API REST en `cegroup/api/` (sin sesión efectiva).
- Frontend: jQuery + `fetch`/`$.ajax` desde `app/action/*.js`. Markup en `app/views/*.php`.
- Backend API: dispatcher `api/lib/Api.php` → `Router` → `ApiController` → `Restapi::render()` que hace `require app/rest/<endpoint>.php`.
- BD: MariaDB `data_cegroup` (GoDaddy shared hosting, ruta de producción `/home/g2ikz73cb0c2/public_html/` — evidencia: `api/file/sql/admin/base/error_log`).
- URL base de producción **HTTP (no HTTPS)**: `http://gestioncobranza.com/api/api/` y `.../api/file/` (`app/assets/js/main.js:32-33`).

## Catálogo de módulos

| Módulo | Vista | JS de acción | Endpoints REST principales | Estado |
|---|---|---|---|---|
| Autenticación | `index.php` (raíz) | inline en `index.php:108` | `login` | Funcional (inseguro) |
| Sesión | `app/php/session.php` | `main.js` | — | Funcional (bypass trivial) |
| Búsqueda | `views/buscar.php` | `action/buscar.js` | `b_data` | Funcional |
| Gestión (núcleo) | `views/gestion.php` (781 líneas) | `action/gestion.js` (27 KB) | `g_operacion`, `g_telefonos`, `g_gestiones`, `g_acuerdos`, `g_estados`, `g_alertas`, `g_resumen`, `g_email`, `g_aportes`, `g_mensaje`, `b_datafilter` | Funcional |
| Acuerdos (consulta) | `views/acuerdos.php` | `action/acuerdos.js` | `b_acuerdos`, `file/sql/user/acue/` | Funcional (año 2023 hardcoded) |
| Alertas | `views/alertas.php` | `action/alertas.js` | `b_alertas` | Funcional |
| Filtros de cartera | `views/filtro.php`, `views/filtrotabla.php` | `action/filtro.js`, `action/filtrotabla.js` | `b_datafilter`, `d_campana`, `d_cartera`, `g_estados` | Funcional |
| Resumen / productividad | `views/resumen.php` | `action/resumen.js` | `b_resumen` | Roto parcial (año 2023 hardcoded) |
| Perfil | `views/perfil.php` | `action/perfil.js` | `d_usuario`, `d_usuario_cambio` | Funcional (password por GET) |
| Asesores (admin) | `views/asesores.php` | `action/asesores.js` | `admin_asesor`, `file/sql/admin/ases/` | Funcional (export roto) |
| Reportes (admin) | `views/reportes.php` | `action/reportes.js` | `file/sql/admin/{gest,acue,reca,base}/` | Funcional (recaudo vacío) |
| Base de datos / cargas | `views/base.php` | `action/base.js` | `api/UPDATE/*`, `api/file/update/*` | Funcional (sin auth, sin tx) |
| Comunicación | `views/comunicacion.php` (**0 bytes**) | `action/comunicacion.js` (**0 bytes**) | — | No implementado |
| Clientes | `views/clientes.php` | `action/clientes.js` | `clientes` (tabla inexistente) | Huérfano / roto |
| Pagos | `views/pagos.php` (**0 bytes**) | `action/pagos.js` (**0 bytes**) | `g_aportes` (siempre vacío) | No implementado |
| Mensaje de coordinador | (modal en `gestion.php`) | `gestion.js`, `asesores.js` | `g_mensaje`, `admin_asesor?t=mensaje_*` | Implementado, sin uso (`t_mensaje`=0 filas) |

Tamaños de vista (verificado con `wc`): `comunicacion.php`, `index.php`, `pagos.php` = 0 bytes. Stubs de `app/component/base/*` y `app/component/reporte/*` contienen solo `<h1>hola soy X</h1>`.

---

## Proceso 1 — Login

**Actor:** cualquier usuario (admin o asesor).

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Usuario abre la raíz `cegroup/index.php`. Al cargar: `session_start(); session_destroy();` + JS `localStorage.clear()`. | `index.php:2-3`, `index.php:101` |
| 2 | Submit del form → `fetch('http://gestioncobranza.com/api/api/login?u=<user>&p=<pass>')` (GET, password en URL). | `index.php:113` |
| 3 | `login.php` ejecuta `SELECT * FROM t_usuarios WHERE username='$user' AND userpass='md5($pass)' AND estado='TRUE'`. | `api/app/rest/login.php:15-17` |
| 4 | Si `num==1`: JS guarda `session_name/user/type/avatar` en `localStorage` y `session='ACTIVE'`. | `index.php:130-137` |
| 5 | JS llama `app/php/session.php?a=on&t=<usertype>` que setea `$_SESSION["session"]='ACTIVE'` y `$_SESSION["session_type"]=$t`. | `index.php:132`, `session.php:4-7` |
| 6 | Redirección a `public/`. | `index.php:141` |

**Efecto observable:** sesión PHP activa + datos de usuario en localStorage. El menú lateral depende de `$_SESSION['session_type']`: `0`→menú admin, distinto de 0→menú asesor (`app/parts/menu.php:20-24`).

**Defectos verificados:**
- Password viaja en query string GET (`index.php:113`) → queda en logs de servidor/proxy e historial.
- `md5()` sin salt (`login.php:15`). 6 de 65 usuarios tienen `userpass = md5('0')` (`_evidence/quality.json` → `password_md5_zero`).
- SQL injection: `$user`/`$pass` interpolados sin escape (`login.php:17`).
- Sin HTTPS (`main.js:32`).
- El único gate de acceso a la web (`public/index.php:3`, `isset($_SESSION["session"])`) lo activa `session.php?a=on` SIN credenciales (ver Proceso 2).

---

## Proceso 2 — Activación/bypass de sesión

**Actor:** cualquiera con la URL.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | `GET app/php/session.php?a=on&t=0` setea `$_SESSION["session"]='ACTIVE'` y `session_type=0` (admin) sin validar nada. | `session.php:3-7` |
| 2 | `public/index.php` solo verifica `isset($_SESSION["session"])`. Si existe → renderiza la app; si no → redirige a la raíz. | `public/index.php:3,11` |

**Efecto observable:** entrar como admin sin contraseña visitando `session.php?a=on&t=0` y luego `public/`. La API REST nunca verifica sesión (`api/lib/Api.php` no contiene chequeo de auth), así que los endpoints son accesibles aun sin este paso.

---

## Proceso 3 — Búsqueda transversal

**Actor:** admin o asesor. **Vista:** `views/buscar.php`. **JS:** `buscar.js`.

| Paso | Acción UI | Endpoint | SQL / tablas | Evidencia |
|---|---|---|---|---|
| 1 | Elegir modo y escribir valor | — | — | `buscar.js:14-33` |
| 2a | Modo OPERACION | `GET b_data?t=o&v=<op>` | `SELECT * FROM t_base WHERE operacion='$v'` | `buscar.js:23`, `b_data.php:46-48` |
| 2b | Modo CÉDULA | `GET b_data?t=c&v=<ced>` | 3 queries: `t_base WHERE tcedula='$v'` / `ccedula` / `gcedula` | `buscar.js:26`, `b_data.php:64-137` |
| 2c | Modo NOMBRE | `GET b_data?t=n&v=<txt>` | 3 queries `LIKE '%$v%'` sobre `tnombre`/`cnombre`/`gnombre` | `buscar.js:29`, `b_data.php:71,96,121` |
| 3 | Resultado en 3 tablas (TITULAR / CODEUDOR / GARANTE) | — | — | `b_data.php:30-34` |
| 4 | Click en resultado → `gestion?o=<op>` | — | — | `buscar.js:72,119,144,169` |

**Defectos:** SQL injection en `$v` (todas las queries). `LIKE '%$v%'` sobre 54.931 filas sin índice → full scan (0 índices secundarios en toda la BD, `_evidence/indexes.json`). Sin paginación.

---

## Proceso 4 — Gestión diaria del asesor (proceso central)

**Actor:** asesor. **Vista:** `views/gestion.php`. **JS:** `gestion.js`.

### 4.1 Carga de la operación

Al abrir `gestion?o=<op>`, `gestion.js` dispara secuencialmente (todas GET):

| Orden | Endpoint | Efecto | Evidencia |
|---|---|---|---|
| 1 | `g_mensaje?a=<asesor>` | Si hay mensaje → abre modal "mensaje de coordinador" | `gestion.js:12,361-368` |
| 2 | `g_operacion?o=<op>` | JOIN de 7 tablas → datos del crédito | `gestion.js:13`, `g_operacion.php:48-85` |
| 3 | `g_telefonos?o=<op>` | tabla de teléfonos | `gestion.js:410` |
| 4 | `g_gestiones?o=<op>&p=init` | últimas 3 gestiones | `gestion.js:411`, `g_gestiones.php:46-51` |
| 5 | `g_aportes?o=<op>` | pagos (siempre vacío, `t_pagos`=0) | `gestion.js:412`, `g_aportes.php` |
| 6 | `g_acuerdos?o=<op>` | acuerdos | `gestion.js:413` |
| 7 | `g_estados` | catálogos estado/sub (dropdowns) | `gestion.js:414`, `g_estados.php:20-24` |
| 8 | `g_resumen?o=<op>&p=init` | últimos 3 resúmenes | `gestion.js:415` |
| 9 | `g_alertas?o=<op>` | alertas | `gestion.js:416` |
| 10 | `g_email?o=<op>` | emails | `gestion.js:417` |

`g_operacion` ejecuta un `INNER JOIN` de `t_base` con `t_asignacion`, `t_campana`, `t_cartera`, `t_decil`, `t_procesos`, `t_saldos` por `operacion` (`g_operacion.php:78-85`). **Patología:** como `t_asignacion` puede tener filas duplicadas por operación (ver Proceso 5), el JOIN puede devolver varias filas para una misma operación.

### 4.2 Registro de resultados (7 formularios)

Todos POSTean al mismo endpoint del nombre del formulario. El asesor avanza solo cuando completa los 6 procesos obligatorios (`gestion.js:274` exige `process_gest, process_tele, process_acue, process_esta, process_resu, process_aler`).

| Form | Endpoint (POST) | SQL ejecutado | Evidencia |
|---|---|---|---|
| Gestión libre | `g_gestiones` | `INSERT t_gestiones` **+ `UPDATE t_procesos SET fgestion, asesor` + `DELETE FROM t_alertas WHERE operacion`** | `g_gestiones.php:100-112` |
| Teléfono | `g_telefonos` | `INSERT t_telefonos (operacion, asesor, telefono, detalle)` | `g_telefonos.php:70-73` |
| Email | `g_email` | `INSERT t_email (operacion, email)` | `g_email.php:67-70` |
| Estado | `g_estados` | `UPDATE t_procesos SET estado, sub WHERE operacion` | `g_estados.php:78-79` |
| Acuerdo | `g_acuerdos` | `INSERT t_acuerdos (... estado='activo' ...)` | `g_acuerdos.php:66-68` |
| Resumen | `g_resumen` | `INSERT t_resumen` (16 columnas) | `g_resumen.php:107-108` |
| Alerta | `g_alertas` | `INSERT t_alertas (operacion, asesor, fecha, hora, num, alerta)` | `g_alertas.php:99-100` |

**Side-effect crítico (verificado):** registrar UNA gestión borra TODAS las alertas de esa operación, sin filtro por asesor ni fecha y sin confirmación (`g_gestiones.php:110`). Evidencia de impacto: `t_alertas` AUTO_INCREMENT=11.418 pero solo 22 filas vivas (`_evidence/tables.json`, `quality.json`).

**Defectos transversales:** todos los INSERT/UPDATE interpolan `$_POST` sin escape → SQL injection. Si el INSERT falla, el endpoint devuelve el SQL crudo en `data.data` (p. ej. `g_gestiones.php:114`).

---

## Proceso 5 — Asignación de cartera a asesor (vía carga masiva)

**Actor:** admin. **Vista:** `views/base.php`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Admin sube `ASIGNACION.csv` (`operacion;asesor`) a `upload.php`. | `api/UPDATE/ASIGNACION/` |
| 2 | `procesar.php` lee CSV con `fgetcsv(...,";")` e `INSERT INTO t_asignacion (id,operacion,asesor) VALUES (NULL,'$operacion','$asesor')`. | `api/UPDATE/ASIGNACION/procesar.php:12-13` |
| 3 | NO trunca ni reemplaza: re-importar **acumula** filas. | (sin TRUNCATE en el procesador) |

**Estado de datos hoy (verificado):** `t_asignacion` tiene 54.931 filas con 54.931 operaciones distintas → ratio **1.0×** (`_evidence/quality.json`). Es decir, hoy NO hay duplicación acumulada en `t_asignacion`, a diferencia de lo que advertía el doc previo (ver discrepancias). El riesgo de acumulación sigue latente en el código.

---

## Proceso 6 — Registro y consulta de acuerdos de pago

**Registro** (dentro de Proceso 4): `INSERT t_acuerdos` con `estado='activo'` fijo (`g_acuerdos.php:67`).

**Consulta** (`views/acuerdos.php`, `acuerdos.js`):

| Modo | Endpoint | SQL | Evidencia |
|---|---|---|---|
| Por operación | `b_acuerdos?t=o&v=<op>&a=<asesor>` | `WHERE operacion AND asesor AND estado='activo'` | `acuerdos.js:23`, `b_acuerdos.php:46` |
| Por mes | `b_acuerdos?t=m&v=<MM>&a=<asesor>` | `WHERE facuerdo BETWEEN '2023-MM-01' AND '2023-MM-31'` | `acuerdos.js:34`, `b_acuerdos.php:64-67` |
| Por fecha | `b_acuerdos?t=f&v=<fecha>&a=<asesor>` | `WHERE facuerdo='$v'` | `acuerdos.js:45`, `b_acuerdos.php:86` |

**Defecto crítico (verificado):** año `2023` hardcoded en el filtro mensual (`b_acuerdos.php:64-65`). El filtro por mes no funciona para otros años.

**No implementado:** no existe endpoint para cambiar `t_acuerdos.estado`. Verificado en datos: 10.902 acuerdos, el 100% en `estado='activo'` (`_evidence/column_profiles.json` → `t_acuerdos.estado` distinct=1). Nunca se marca cumplido/incumplido.

**Exportación (cadena de SQL injection):** `acuerdos.js:95-97` toma el string SQL devuelto por `b_acuerdos` en `data.acuerdos.sql` y lo pasa como parámetro a `file/sql/user/acue/index.php?sql=<sql>`, que ejecuta CUALQUIER SQL recibido (`file/sql/user/acue/index.php:12,36`). Este es el patrón de uso del endpoint de SQL arbitrario (responde la pregunta abierta previa #57).

---

## Proceso 7 — Cambio de estado de cartera

Dentro de Proceso 4 (form Estado). `UPDATE t_procesos SET estado='$e', sub='$s' WHERE operacion='$op'` (`g_estados.php:78-79`). Cambio inmediato, **sin historial** (el estado anterior se pierde).

Los catálogos para los dropdowns vienen de `t_estados` (5 estados) y `t_subs` (37 subs) vía `g_estados` (`g_estados.php:37-69`). El dropdown de sub cascadea según el estado elegido (`gestion.js:214-221`).

**Discrepancia datos↔catálogo:** `t_procesos.estado` real tiene **23 valores distintos** (no los 5 del catálogo), incluyendo tipos de proceso jurídico (`CIVIL`=200, `PENAL`=16, `ARRENDAMIENTO`=78, `VENTA`=48, `FAMILIA`, `LABORAL`, etc.), `'0'`=1036 filas basura y 46 NULL (`column_profiles.json` → `t_procesos.estado`). El catálogo NO se aplica como constraint.

---

## Proceso 8 — Filtrado de carga de trabajo

**Actor:** asesor. **Vistas:** `views/filtro.php` → `views/filtrotabla.php`.

| Paso | Acción | Endpoint | Evidencia |
|---|---|---|---|
| 1 | `filtro.php` carga catálogos | `g_estados`, `d_campana`, `d_cartera` | `filtro.js:7-9` |
| 2 | Asesor elige criterio → se guarda en `localStorage` | — | `filtro.js:158-196` |
| 3 | `filtrotabla.php` consulta según filtro | `b_datafilter?t=<modo>&a=<asesor>&...` | `filtrotabla.js:16,23,26` |
| 4a | Sin filtro (`t=null`) | top 20 más antiguas con `fgestion != hoy` | `b_datafilter.php:48-49` |
| 4b | Por estado | `+ estado='$e' AND sub='$s'` (sin LIMIT) | `b_datafilter.php:74` |
| 4c | Por cartera/campaña | JOIN con `t_cartera`/`t_campana` | `b_datafilter.php:98,122` |
| 5 | Seleccionar → `gestion?o=<op>` | — | `filtrotabla.js:112` |

`b_datafilter` siempre excluye operaciones ya gestionadas hoy (`fgestion != '$hoy'`, `b_datafilter.php:49`).

---

## Proceso 9 — Productividad / dashboard mensual

**Actor:** asesor. **Vista:** `views/resumen.php`. **JS:** `resumen.js`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Elegir mes → `GET b_resumen?v=<MM>&a=<asesor>` | `resumen.js:21` |
| 2 | `b_resumen` arma `f1='2023-MM-01'`, `f2='2023-MM-31'` y corre 6 queries: gestiones, acuerdos, pagos, resúmenes, base asignada, posición | `b_resumen.php:19-26` |
| 3 | Dashboard muestra 6 KPIs | — |

**Defectos verificados:**
- Año `2023` hardcoded (`b_resumen.php:19-20`). Además `resumen.js:78` tiene el label de año hardcoded en **2022**. Los dos no coinciden y ninguno usa el año actual → el filtro mensual está roto.
- KPI de pagos siempre 0: `SELECT SUM(pago) FROM t_pagos ...` sobre tabla vacía (`b_resumen.php:55`, `t_pagos`=0 filas).
- KPI "posición" siempre `'0'`: `t_usuarios.posicion` es `'0'` para los 65 usuarios (`column_profiles.json` → `t_usuarios.posicion` distinct=1). Nada en el código actualiza `posicion`.

---

## Proceso 10 — Reportes Excel (admin)

**Actor:** admin. **Vista:** `views/reportes.php`. **JS:** `reportes.js`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Elegir tipo (gest/acue/reca/base) + filtros | — | `reportes.js:10-41` |
| 2 | `window.open('http://.../api/file/sql/admin/<tipo>/index.php?t=<modo>&a=<asesor>&...')` | `reportes.js:138-151` |
| 3 | El PHP envía `Content-Type: application/vnd.ms-excel` y emite una `<table>` HTML disfrazada de `.xls` | p.ej. `file/sql/admin/gest/index.php:2-3,73-102` |

**Defectos:** todos los reportes interpolan `$_GET['a']`, `$_GET['f']`, etc. sin escape → SQL injection (`gest/index.php:24,28,...`). El reporte **RECAUDO** consulta `t_pagos` (vacía) → siempre vacío (`reca/index.php:26`). El `.xls` es HTML, no Excel real.

---

## Proceso 11 — Administración de asesores (admin)

**Actor:** admin. **Vista:** `views/asesores.php`. **JS:** `asesores.js`. **Endpoint:** `admin_asesor`.

| Acción | Llamada | SQL | Evidencia |
|---|---|---|---|
| Listar | `GET admin_asesor?t=all` | `SELECT * FROM t_usuarios WHERE usertype!=0` | `asesores.js:7`, `admin_asesor.php:74-75` |
| Buscar por nombre | `?t=texto&v=<n>` | `... LIKE '%$t%'` | `admin_asesor.php:94` |
| Buscar por cédula | `?t=cedula&v=<c>` | `... WHERE cedula='$c'` | `admin_asesor.php:114` |
| Crear | `POST ?t=insert` | `INSERT ... userpass=md5(0), usertype='1', estado='TRUE'` | `admin_asesor.php:165-168` |
| Editar | `POST ?t=update` | `UPDATE ... WHERE cedula='$cedula'` (cambia nombre/tel/username) | `admin_asesor.php:218-222` |
| Activar/desactivar | `POST ?t=estado` | `UPDATE ... SET estado WHERE cedula` | `admin_asesor.php:233-235` |
| Eliminar | `POST ?t=delete` | `DELETE FROM t_usuarios WHERE cedula` (borrado físico) | `admin_asesor.php:245` |
| Mensaje (set) | `POST ?t=mensaje_insert` | `DELETE t_mensaje WHERE asesor` + `INSERT t_mensaje` | `admin_asesor.php:184-187` |
| Mensaje (borrar) | `POST ?t=mensaje_delete` | `DELETE t_mensaje WHERE asesor` | `admin_asesor.php:196` |
| Exportar | `window.open('sql/asesores/index.php')` | **404: el archivo es `sql/admin/ases/index.php`** | `asesores.js:154` (ver defecto) |

**Defectos verificados:**
- Password inicial = `md5(0)` = `cfcd208495d565ef66e7dff9f98764da` → password `0` (`admin_asesor.php:165`). 6 usuarios productivos lo conservan (`quality.json`).
- Borrado físico deja gestiones/acuerdos huérfanos (la columna `asesor` referencia `username`, que no es FK; renombrar también rompe).
- Botón de exportar asesores apunta a `sql/asesores/index.php` que NO existe; la ruta real es `sql/admin/ases/index.php` (verificado con `test -f`). El export está roto (404). **Defecto nuevo.**
- Mensaje del coordinador: implementado en backend y UI, pero `t_mensaje` tiene 0 filas (nunca usado en producción).

---

## Proceso 12 — Perfil de usuario

**Vista:** `views/perfil.php`. **JS:** `perfil.js`.

| Acción | Llamada | SQL | Evidencia |
|---|---|---|---|
| Cargar mi día | `GET d_usuario?a=<user>` | count gestiones + acuerdos de hoy | `perfil.js:7`, `d_usuario.php:21-22` |
| Cambiar avatar | `GET d_usuario_cambio?a=<user>&t=avatar&v=<v>` | `UPDATE t_usuarios SET avatar` | `perfil.js:72`, `d_usuario_cambio.php:38` |
| Cambiar password | `GET d_usuario_cambio?a=<user>&t=password&v=<pass>` | `UPDATE t_usuarios SET userpass=md5($v)` | `perfil.js:114`, `d_usuario_cambio.php:54-56` |

**Defecto:** la nueva contraseña viaja por GET en claro (`perfil.js:114`). Validación JS prohíbe comillas/_/&/$ (mitiga injection del propio usuario, no de terceros). Sin verificación de contraseña actual.

---

## Proceso 13 — Alertas

**Creación** (dentro de Proceso 4): `g_alertas` POST → `INSERT t_alertas`. La hora se mapea desde `num` (08–18) a string fijo (`g_alertas.php:62-96`).

**Consulta** (`views/alertas.php`, campana del header):

| Modo | Endpoint | SQL | Evidencia |
|---|---|---|---|
| Todas del asesor | `b_alertas?t=all&a=<asesor>` | `WHERE asesor='$a'` | `b_alertas.php:44-47` |
| Por operación | `t=operacion&a=&o=` | `WHERE asesor AND operacion` | `b_alertas.php:64-68` |
| Por fecha | `t=fecha&a=&f=` | `WHERE asesor AND fecha` | `b_alertas.php:85-89` |
| De hoy (header) | `t=hoy&a=&h=<hora>` | UNION: alertas pasadas + de hoy hasta la hora actual | `b_alertas.php:104-113`, `main.js:23` |

El header dispara `b_alertas?...t=hoy` al cargar cada página (`main.js:23`).

**Borrado automático:** ver side-effect de Proceso 4 (`g_gestiones.php:110`). `b_alertas` devuelve el SQL crudo en `data.sql` (fuga de query: `b_alertas.php:50,71,92,116`).

---

## Procesos NO implementados o rotos

| Proceso | Evidencia | Estado real |
|---|---|---|
| Captura de pagos | `t_pagos`=0 filas; no hay endpoint POST; `views/pagos.php` y `action/pagos.js` 0 bytes | No implementado |
| Reporte de recaudo | `reca/index.php` consulta `t_pagos` vacía | Funcional pero siempre vacío |
| Cumplimiento de acuerdos | sin endpoint para cambiar `t_acuerdos.estado`; 100% `'activo'` | No implementado |
| Ranking/posición de asesor | `t_usuarios.posicion`='0' para los 65; nada lo actualiza | No implementado |
| Módulo Comunicación | `views/comunicacion.php` y `action/comunicacion.js` 0 bytes; ruta+método existen (`routes.php:6`, `MainController.php:7`), pero no está en menú | Esqueleto vacío |
| Módulo Clientes | `rest/clientes.php` consulta tabla `clientes` que NO existe en `data_cegroup` (verificado: 20 tablas, ninguna `clientes`); `clientes.js:29` usa variable `http` indefinida; sin ruta en API | Huérfano, roto en runtime |
| Endpoint `g_asesor` | `routes.php:5` mapea a método `g_asesor` que NO existe en `ApiController` (solo existe `asesor()`); `rest/asesor.php` consulta tabla `asesor` inexistente con typo `cedukla` | Ruta rota (lanza excepción → `error.php`) |
| Mensaje de coordinador | backend+UI completos, `t_mensaje`=0 filas | Implementado, sin uso |
| Componentes `app/component/*` | 9 archivos con `<h1>hola soy X</h1>` | Stubs nunca implementados |
| `UPDATEOPERACIONGESTION/` | directorio vacío (verificado) | Abandonado |
| Historial / auditoría | `t_procesos` y `t_acuerdos.estado` se sobrescriben sin guardar versiones | No diseñado |

---

## Diagrama del flujo principal (asesor)

```
LOGIN (index.php → login → session.php → public/)
  ↓
[Filtrar carga] filtro.php → filtrotabla.php (b_datafilter)
  ↓ seleccionar operación
GESTION (gestion.php)
  ├── Cargar: g_mensaje, g_operacion(JOIN x7), g_telefonos, g_gestiones(init),
  │           g_aportes(vacío), g_acuerdos, g_estados, g_resumen(init), g_alertas, g_email
  ├── Llamar al deudor (offline, no registrado)
  └── Registrar (6 procesos obligatorios + email opcional):
       ├── Gestión → INSERT t_gestiones + UPDATE t_procesos + DELETE t_alertas(¡todas!)
       ├── Teléfono → INSERT t_telefonos
       ├── Email    → INSERT t_email
       ├── Estado   → UPDATE t_procesos (sin historial)
       ├── Acuerdo  → INSERT t_acuerdos (estado='activo' fijo)
       ├── Resumen  → INSERT t_resumen (16 cols)
       └── Alerta   → INSERT t_alertas (se borrará en la próxima gestión)
  ↓
PERFIL (d_usuario) → gestiones_hoy, acuerdos_hoy
RESUMEN (b_resumen) → 6 KPIs [año 2023 hardcoded, pagos=0, posicion=0]
```

## Evidencia

- Endpoints REST: `cegroup/api/app/rest/*.php` (24 archivos).
- Procesadores de import: `cegroup/api/UPDATE/<dominio>/procesar.php` y `cegroup/api/file/update/<dominio>/procesar.php` (dos árboles paralelos).
- Reportes/SQL: `cegroup/api/file/sql/{admin,user}/*/index.php`.
- Vistas: `cegroup/app/views/*.php`; JS: `cegroup/app/action/*.js`.
- Datos: `_evidence/` (introspección read-only 2026-06-21, `data_cegroup`, MariaDB 10.11).
