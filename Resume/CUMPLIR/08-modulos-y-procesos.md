# Módulos de negocio y procesos — CUMPLIR

> Documenta lo que el código HACE, no lo que debería hacer. Cada afirmación cita `archivo:línea` o dato de evidencia (`_evidence/*.json`, introspección 2026-06-21). El modelo de negocio NO se infiere: se describe el comportamiento observable. Rutas relativas a `PROYECTO_CUMPLIR/cumplir/`.
>
> CUMPLIR es un sistema gemelo de CEGROUP (mismo mini-framework PHP, mismo esquema de cobranza), pero NO es el mismo despliegue: hosting distinto (Hostinger `srv450.hstgr.io`, base `u815310395_data`), datos distintos, equipo más pequeño (13 usuarios), y varios módulos propios. Las diferencias verificadas se marcan **[CUMPLIR-only]** o **[difiere de CEGROUP]**. No se asume identidad de negocio con CEGROUP.

## Contexto técnico mínimo

- App PHP "mini-framework" casero. Web en raíz `cumplir/` (sesión PHP) + API REST en `cumplir/api/` (sin sesión efectiva).
- Frontend: jQuery + `fetch` desde `app/action/*.js`. Markup en `app/views/*.php`.
- Backend API: dispatcher `api/lib/Api.php` → `Router` → `ApiController` → `Restapi::render()` que hace `require app/rest/<endpoint>.php`.
- BD: MariaDB `11.8.6-MariaDB-log`, base `u815310395_data` en `srv450.hstgr.io:3306` (`_evidence/connection.json`).
- URL base de producción **HTTPS** (`index.php:103-104`): `https://cumplir.net/api/api/` (datos) y `https://cumplir.net/api/file/` (archivos/reportes). **[difiere de CEGROUP]**, que usaba HTTP.
- App identificada en UI como **"COBRANZA / Versión 3.0"**, autor "AGENCIA CRAFT" (`index.php:31-32,12`).
- Tres subsistemas paralelos de carga/reporte:
  1. `DATA/UPDATE/*` — importadores con UI propia (admin) **[CUMPLIR-only este árbol]**.
  2. `api/file/update/*` — importadores sin UI (incluye `SUBIR_*` transaccionales).
  3. `api/file/sql/*` — exportadores Excel y el pasamanos SQL (heredados de CEGROUP, **SÍ existen** — verificado).
  4. `REPORTES/REPORTES/` — módulo Flight independiente de reportes mensuales **[CUMPLIR-only]**.

## Catálogo de módulos

| Módulo | Vista | JS de acción | Endpoints REST / scripts | Estado |
|---|---|---|---|---|
| Autenticación | `index.php` (raíz) | inline `index.php:110-118` | `login` | Funcional (inseguro) |
| Sesión | `app/php/session.php` | `main.js` | — | Funcional (bypass trivial) |
| Búsqueda | `views/buscar.php` (126) | `action/buscar.js` | `b_data` | Funcional |
| Gestión (núcleo) | `views/gestion.php` (669) | `action/gestion.js` | `g_operacion`, `g_telefonos`, `g_gestiones`, `g_acuerdos`, `g_estados`, `g_alertas`, `g_resumen`, `g_aportes`, `g_mensaje`, `b_datafilter`, **`phone`** | Funcional |
| Acuerdos (consulta) | `views/acuerdos.php` (138) | `action/acuerdos.js` | `b_acuerdos`, `file/sql/user/acue/` | Funcional (año 2026 hardcoded) |
| Alertas | `views/alertas.php` (90) | `action/alertas.js` | `b_alertas` | Funcional |
| Filtros de cartera | `views/filtro.php` (156), `views/filtrotabla.php` (66) | `action/filtro.js`, `action/filtrotabla.js` | `b_datafilter`, `d_campana`, `d_cartera`, `g_estados` | Funcional |
| Resumen / productividad | `views/resumen.php` (171) | `action/resumen.js` | `b_resumen` | Funcional (año 2026 hardcoded) |
| Perfil | `views/perfil.php` (221) | `action/perfil.js` | `d_usuario`, `d_usuario_cambio` | Funcional (password por GET) |
| Asesores (admin) | `views/asesores.php` (194) | `action/asesores.js` | `admin_asesor`, `file/sql/admin/ases/` | Funcional |
| Reportes Excel (admin) | `views/reportes.php` (218) | `action/reportes.js` | `file/sql/admin/{gest,acue,reca,base}/` | Funcional (recaudo vacío) |
| Base de datos / cargas | `views/base.php` (139) | `action/base.js` | `DATA/UPDATE/*`, `api/file/update/*` | Funcional (sin auth, sin tx) |
| Reportes mensuales (Flight) | `REPORTES/REPORTES/src/view/*` | (módulo aparte) | `REPORTES/REPORTES/{gestiones,acuerdos,proyeccion}/*` | Parcial (solo gestiones poblado) **[CUMPLIR-only]** |
| Comunicación | `views/comunicacion.php` (**0 bytes**) | `action/comunicacion.js` | — | No implementado |
| Clientes | `views/clientes.php` (137) | `action/clientes.js` | `clientes` (tabla inexistente) | Huérfano / roto |
| Pagos | `views/pagos.php` (**0 bytes**) | `action/pagos.js` | `g_aportes` (siempre vacío) | No implementado |
| Mensaje de coordinador | (modal en `gestion.php`) | `gestion.js`, `asesores.js` | `g_mensaje`, `admin_asesor?t=mensaje_*` | Implementado, sin uso (`t_mensaje`=0 filas) |

Tamaños verificados con `wc -l`: `comunicacion.php`, `index.php`, `pagos.php` = 0 bytes. Los 9 stubs de `app/component/base/*` y `app/component/reporte/*` están **vacíos (0 bytes)** **[difiere de CEGROUP]**, donde contenían `<h1>hola soy X</h1>`.

**Menú** (`app/parts/menu.php:15-16`): admin (`session_type==0`) → `[buscar, asesores, reportes, base]`; asesor → `[buscar, gestion, acuerdos, resumen]`. Las entradas `comunicacion` y `procesos` se definen (`menu.php:6,11`) pero **no aparecen en ningún menú**.

---

## Proceso 1 — Login

**Actor:** cualquier usuario (admin o asesor). **Vista:** `index.php` (raíz).

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Usuario abre la raíz `cumplir/index.php`. Al cargar: `session_start(); session_destroy();` + JS `localStorage.clear()`. | `index.php:2-3`, `index.php:99` |
| 2 | Submit del form → `fetch('https://cumplir.net/api/api/login?u=<user>&p=<pass>')` (GET, password en URL). | `index.php:115` |
| 3 | `login.php` ejecuta `SELECT * FROM t_usuarios WHERE username='$user' AND userpass='md5($pass)' AND estado='TRUE'`. | `api/app/rest/login.php:14-17` |
| 4 | Si `num==1`: JS guarda `session_name/user/type/avatar` en `localStorage` y `session='ACTIVE'`. | `index.php:132-139` |
| 5 | JS llama `app/php/session.php?a=on&t=<usertype>` que setea `$_SESSION["session"]='ACTIVE'` y `$_SESSION["session_type"]=$t`. | `index.php:134,140-142`, `session.php:4-7` |
| 6 | Redirección a `public/`. | `index.php:143` |

**Efecto observable:** sesión PHP activa + datos de usuario en `localStorage`. El menú depende de `$_SESSION['session_type']`: `0`→menú admin, distinto de 0→menú asesor (`menu.php:20-24`).

**Defectos verificados:**
- Password viaja en query string GET (`index.php:115`, `login.php:14-15`) → queda en logs/historial.
- `md5()` sin salt (`login.php:15`). **10 de 13 usuarios** tienen `userpass = md5('0')` (`_evidence/quality.json` → `password_md5_zero: total 13, md5_of_zero 10`). **[difiere de CEGROUP]**: aquí la proporción es mucho mayor (77% vs 9%).
- SQL injection: `$user`/`$pass` interpolados sin escape (`login.php:17`). Bypass clásico: `?u=admin'-- -&p=x`.
- El único gate del front (`public/index.php:3`, `isset($_SESSION["session"])`) lo activa `session.php?a=on` SIN credenciales (Proceso 2).

---

## Proceso 2 — Activación / bypass de sesión

**Actor:** cualquiera con la URL.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | `GET app/php/session.php?a=on&t=0` setea `$_SESSION["session"]='ACTIVE'` y `session_type=0` (admin) sin validar nada. | `session.php:3-7` |
| 2 | `public/index.php` solo verifica `isset($_SESSION["session"])`. Si existe → renderiza; si no → redirige a la raíz. | `public/index.php:3,11-12` |

**Efecto observable:** entrar como admin sin contraseña visitando `https://cumplir.net/app/php/session.php?a=on&t=0` y luego `public/`. El `session_type` (rol) llega del cliente (`index.php:134`). La API REST nunca verifica sesión (`api/lib/*.php` sin chequeo de auth), así que los endpoints son accesibles aun sin este paso.

---

## Proceso 3 — Búsqueda transversal

**Actor:** admin o asesor. **Vista:** `views/buscar.php`. **JS:** `buscar.js`.

| Modo | Endpoint | SQL / tablas | Evidencia |
|---|---|---|---|
| OPERACION | `GET b_data?t=o&v=<op>` | `SELECT * FROM t_base WHERE operacion='$v'` | `b_data.php:48` |
| CÉDULA | `GET b_data?t=c&v=<ced>` | 3 queries: `t_base WHERE tcedula/ccedula/gcedula='$v'` | `b_data.php:68,93,118` |
| NOMBRE | `GET b_data?t=n&v=<txt>` | 3 queries `LIKE '%$v%'` sobre `tnombre`/`cnombre`/`gnombre` | `b_data.php:71,96,121` |

Resultado en 3 tablas (TITULAR / CODEUDOR / GARANTE); click → `gestion?o=<op>`.

**Defectos:** SQL injection en `$v` (todas las queries). `LIKE '%$v%'` sobre ~48.5k filas sin índice → full scan (0 índices secundarios en toda la BD, `_evidence/indexes.json`). Fuga del SQL en respuesta: `$data['sql']=$sql` (`b_data.php:58,83,108,133`).

---

## Proceso 4 — Gestión diaria del asesor (proceso central)

**Actor:** asesor. **Vista:** `views/gestion.php`. **JS:** `gestion.js`.

### 4.1 Carga de la operación

Al abrir `gestion?o=<op>`, `gestion.js` dispara los GET de carga. La diferencia clave con CEGROUP: **no hay panel ni endpoint Email** (`gestion.php` sin referencias a `email`/`correo`, verificado por grep; 669 líneas vs 781 en CEGROUP) **[difiere de CEGROUP]**.

| Endpoint | Efecto | Evidencia |
|---|---|---|
| `g_mensaje?a=<asesor>` | Si hay mensaje → modal "mensaje de coordinador" | `g_mensaje.php:31` |
| `g_operacion?o=<op>` | `INNER JOIN` de 7 tablas (`t_base`+`t_asignacion`+`t_campana`+`t_cartera`+`t_decil`+`t_procesos`+`t_saldos`) → datos del crédito, incluye `t_base.referencia` **[CUMPLIR-only]** | `g_operacion.php:48-87` |
| `g_telefonos?o=<op>` | teléfonos con su `status` **[CUMPLIR-only]** | `g_telefonos.php:41` |
| `g_gestiones?o=<op>&p=init` | últimas 3 gestiones (`LIMIT 3`) | `g_gestiones.php:48-51` |
| `g_aportes?o=<op>` | pagos (siempre vacío, `t_pagos`=0) | `g_aportes.php:41` |
| `g_acuerdos?o=<op>` | acuerdos de la operación | `g_acuerdos.php:41` |
| `g_estados` | catálogos estado/sub (dropdowns) | `g_estados.php` |
| `g_resumen?o=<op>&p=init` | últimos 3 resúmenes | `g_resumen.php:49` |
| `g_alertas?o=<op>` | alertas | `g_alertas.php:40` |

**Patología del JOIN:** `g_operacion` hace `INNER JOIN t_decil` (`g_operacion.php:84`). Como `t_asignacion`, `t_campana`, `t_cartera`, `t_decil` tienen filas duplicadas por operación (ratios 1.22×–2.11×, `_evidence/quality.json`), el JOIN puede devolver varias filas para una misma operación.

### 4.2 Registro de resultados

| Form | Endpoint (POST) | SQL ejecutado | Evidencia |
|---|---|---|---|
| Gestión libre | `g_gestiones` | `INSERT t_gestiones` **+ `UPDATE t_procesos SET fgestion,asesor` + `DELETE FROM t_alertas WHERE operacion`** | `g_gestiones.php:100-110` |
| Teléfono | `g_telefonos` | `INSERT t_telefonos (operacion,asesor,telefono,detalle)` | `g_telefonos.php:69-72` |
| **Phone status** **[CUMPLIR-only]** | `phone` | `UPDATE t_telefonos SET status='$status' WHERE id='$id'` | `phone.php:25` |
| Estado | `g_estados` | `UPDATE t_procesos SET estado,sub WHERE operacion` | `g_estados.php:78-79` |
| Acuerdo | `g_acuerdos` | `INSERT t_acuerdos (... estado='activo' ...)` | `g_acuerdos.php:66-68` |
| Resumen | `g_resumen` | `INSERT t_resumen` (16 columnas) | `g_resumen.php:107-108` |
| Alerta | `g_alertas` | `INSERT t_alertas (operacion,asesor,fecha,hora,num,alerta)` | `g_alertas.php:99-100` |

**Side-effect crítico (verificado):** registrar UNA gestión borra TODAS las alertas de esa operación, sin filtro por asesor ni fecha y sin confirmación (`g_gestiones.php:110`). Evidencia: `t_alertas` AUTO_INCREMENT=43.120 pero solo 304 filas vivas (`_evidence/tables.json`, `quality.json`).

**Phone status — está EN USO (corrige doc previo):** el botón de la tabla de teléfonos llama `phoneUpdateStatus('activo'|'',id)` (`gestion.js:451,456`) → POST `phone` con `status` (`gestion.js:22-26`). Los datos confirman uso real: `t_telefonos.status` = `ACTIVO(21772)` vs vacío(112162) (`_evidence/column_profiles.json`). Es un **toggle binario activo/inactivo**, no un catálogo VALIDO/INVALIDO. El doc previo afirmaba "feature implementada pero no usada" — **incorrecto**.

**Defectos transversales:** todos los INSERT/UPDATE interpolan `$_POST` sin escape → SQL injection. Los endpoints `g_*` POST devuelven el SQL crudo en `data.data` si el INSERT falla (`g_gestiones.php:114`, `g_resumen.php:114`, `g_telefonos.php:78`, `g_acuerdos.php:74`, `g_estados.php:85`, `g_alertas.php:105`, `phone.php:30`).

---

## Proceso 5 — Asignación de cartera (carga masiva)

**Actor:** admin. **Vista:** `views/base.php`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Admin sube `ASIGNACION.csv` (`operacion;asesor`) a `upload.php` (mueve a `file/` con nombre original, sin validación). | `DATA/UPDATE/ASIGNACION/upload.php:6-8` |
| 2 | `procesar.php` lee CSV con `fgetcsv(...,";")` e `INSERT INTO t_asignacion (operacion,asesor) VALUES ('$operacion','$asesor')`. | `DATA/UPDATE/ASIGNACION/procesar.php:11-16` |
| 3 | NO trunca: re-importar **acumula** filas. | (sin TRUNCATE) |

**Estado de datos hoy (verificado):** `t_asignacion` 58.648 filas / 47.955 ops distintas → ratio **1.223×** (`_evidence/quality.json`). HAY duplicación acumulada **[difiere de CEGROUP]**, donde el ratio era 1×. Las dimensionales `t_campana`/`t_cartera`/`t_decil`/`t_saldos` también están infladas (ratios 1.217×–1.22×).

Existe un segundo árbol sin UI en `api/file/update/` (verificado): `ASIGNAR`, `BASE`, `CAMPANA`, `CARTERA`, `DECIL`, `PROCESOS`, `SALDOS` + los `SUBIR_*` (Proceso 5b).

### 5b — Carga masiva de datos transaccionales [CUMPLIR-only]

`api/file/update/SUBIR_*/procesar.php` (5 scripts, verificados): cargan filas transaccionales por CSV, no solo dimensionales.

| Script | Tabla destino | Columnas CSV | Evidencia |
|---|---|---|---|
| `SUBIR_RESUMEN` | `t_resumen` | 15 | `SUBIR_RESUMEN/procesar.php:10-29` |
| `SUBIR_ALERTAS` | `t_alertas` | 6 | `SUBIR_ALERTAS/procesar.php:10-18` |
| `SUBIR_ACUERDOS` | `t_acuerdos` | 8 | `SUBIR_ACUERDOS/procesar.php:10-20` |
| `SUBIR_GESTION` | `t_gestiones` | 6 | `SUBIR_GESTION/procesar.php:10-18` |
| `SUBIR_TELEFONOS` | `t_telefonos` | 4 (sin `status`) | `SUBIR_TELEFONOS/procesar.php:10-17` |

Defectos: SQLi por celda, sin TRUNCATE, `set_time_limit(60000000000)`, `chmod 0777` sobre el CSV. `SUBIR_TELEFONOS` no inserta `status` → las filas quedan con `status=''` (coherente con los 112.162 vacíos). Bucle de contador roto en los `SUBIR_*` (el contador `$num` se sobrescribe con la columna del CSV — ver doc 16).

---

## Proceso 6 — "Ocultar" operación [CUMPLIR-only]

**Actor:** admin (ejecución manual ad-hoc). **Script:** `DATA/OCULTAR/procesar.php`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Lee `OCULTAR.csv` (columna `operacion`), `chmod 0777`. | `DATA/OCULTAR/procesar.php:13` |
| 2 | Por cada fila: `DELETE FROM t_decil WHERE t_decil.operacion='$operacion'`. | `procesar.php:17-18` |

**Efecto observable (corrige doc previo):** `g_operacion` hace `INNER JOIN t_decil` (`g_operacion.php:84`), de modo que borrar el decil **hace desaparecer la operación de la pantalla de gestión** (la consulta no devuelve filas). PERO `b_datafilter` (la cola de trabajo del asesor) **NO usa `t_decil`** — solo `t_procesos` + `t_asignacion`/`t_cartera`/`t_campana` (verificado por grep: cero referencias a `t_decil` en `b_datafilter.php`). El doc previo afirmaba que `b_datafilter` usa INNER JOIN con `t_decil` — **incorrecto**. Por tanto OCULTAR oculta del detalle de operación, no necesariamente de la cola del filtro.

La operación sigue existiendo en `t_base`, `t_acuerdos`, etc. Sin auth, sin log de quién/cuándo. Caso de uso inferido (`No verificado:` el negocio no lo documenta): retirar operaciones del flujo sin borrarlas físicamente.

---

## Proceso 7 — Reasignación masiva de cartera [CUMPLIR-only]

**Actor:** admin. **Script:** `DATA/UPDATE/REASIGNACION/procesar.php`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Lee `REASIGNACION.csv` (`operacion;asesor`). `ini_set('max_execution_time',0)`, `set_time_limit(0)`. | `REASIGNACION/procesar.php:3-5` |
| 2 | Por cada fila: `UPDATE t_asignacion SET asesor='$asesor' WHERE operacion='$operacion'`. | `REASIGNACION/procesar.php:14-21` |

**Side-effect (verificado):** como `t_asignacion` tiene múltiples filas por operación (ratio 1.223×), el UPDATE actualiza **todas** las filas de esa operación al nuevo asesor; no distingue "asignación viva" de "histórica". SQLi por celda; sin auth; sin log. Caso de uso inferido: redistribuir carga al salir/entrar asesores.

---

## Proceso 8 — Cambio de estado de cartera

Dentro del Proceso 4 (form Estado): `UPDATE t_procesos SET estado='$e', sub='$s' WHERE operacion='$op'` (`g_estados.php:78-79`). Cambio inmediato, **sin historial**.

Catálogos para los dropdowns: `t_estados` (5 estados: `ACUERDO`, `ILOCALIZADO`, **`JURIDICO`** **[CUMPLIR-only]**, `RENUENTE`, `PAZ_Y_SALVO`; `_evidence/samples/t_estados.json`) y `t_subs` (9 subs; `_evidence/tables.json`). **[difiere de CEGROUP]**: el catálogo de subs pasó de 37 a 9, y `INVESTIGACION` ya no está en el catálogo de estados (aunque sí en datos viejos).

**Discrepancia datos↔catálogo:** `t_procesos.estado` real tiene **14 valores distintos**: `ILOCALIZADO(64530) | LOCALIZADO(17207) | RENUENTE(8514) | 0(5591=basura) | PAZ_Y_SALVO(4971) | INVESTIGACION(4767) | ACUERDO(1981) | CASTIGADA(1959) | SIN DATO(322) | PAZ Y SALVO(19) | D | S | JURIDICO(1) | X` (`column_profiles.json`). El catálogo no se aplica como constraint; `LOCALIZADO` e `INVESTIGACION` y `CASTIGADA` aparecen en datos pero no en el catálogo actual; `JURIDICO` casi no se usa todavía (1 fila). `t_procesos.sub` tiene 39 valores distintos.

---

## Proceso 9 — Filtrado de carga de trabajo

**Actor:** asesor. **Vistas:** `views/filtro.php` → `views/filtrotabla.php`. **JS:** `filtro.js`, `filtrotabla.js`, `gestion.js:279-294`.

| Modo | Endpoint | SQL | Evidencia |
|---|---|---|---|
| Sin filtro | `b_datafilter?a=<asesor>` | top 20 `t_procesos⋈t_asignacion` con `fgestion != hoy`, `LIMIT 20` | `b_datafilter.php:49` |
| Por estado | `?t=...&e=&s=&a=` | `+ estado='$e' AND sub='$s'` (sin LIMIT) | `b_datafilter.php:74` |
| Por cartera | `?t=...&c=&a=` | JOIN `t_cartera` | `b_datafilter.php:98` |
| Por campaña | `?t=...&c=&a=` | JOIN `t_campana` | `b_datafilter.php:122` |

`b_datafilter` excluye operaciones gestionadas hoy (`fgestion != '$hoy'`, `date('Ymd')`). SQLi en `$a/$e/$s/$c`; fuga del SQL en respuesta.

---

## Proceso 10 — Productividad / dashboard mensual

**Actor:** asesor. **Vista:** `views/resumen.php`. **JS:** `resumen.js`. **Endpoint:** `b_resumen`.

`b_resumen` arma `f1='2026-MM-01'`, `f2='2026-MM-31'` y corre 6 queries: gestiones, acuerdos, pagos, resúmenes, base asignada, posición (`b_resumen.php:19-20,33,44,55,73,83,93`).

**Defectos verificados:**
- Año **`2026`** hardcoded (`b_resumen.php:19-20`). El label del botón de la vista también muestra **`2026`** (`views/resumen.php:31`) → **coinciden** (corrige doc previo, que decía "2023" en la UI; verificado: dice 2026).
- KPI de pagos siempre 0: `SUM(pago) FROM t_pagos` sobre tabla vacía (`b_resumen.php:55`, `t_pagos`=0).
- KPI "posición" siempre `0`: `t_usuarios.posicion` es `0` para los 13 usuarios (`column_profiles.json` → `posicion=0(13)`).

---

## Proceso 11 — Reportes Excel (admin)

**Actor:** admin. **Vista:** `views/reportes.php` (REPORTE GESTION / REPORTE ACUERDOS / base / recaudo). **JS:** `reportes.js`.

| Paso | Detalle | Evidencia |
|---|---|---|
| 1 | Elegir tipo (`gest/acue/reca/base`) + filtros | `reportes.php:34-98` |
| 2 | `window.open('https://cumplir.net/api/file/sql/admin/<tipo>/index.php?t=...&a=...&f1=...&f2=...')` | `reportes.js:138-150` |
| 3 | El PHP emite `Content-Type: application/vnd.ms-excel` con una `<table>` HTML disfrazada de `.xls` | `api/file/sql/admin/reca/index.php:26-53` |

**Corrige doc previo:** `api/file/sql/` **SÍ existe** en CUMPLIR (verificado con `find`): `admin/{gest,acue,reca,base,ases}/index.php` + `user/acue/index.php`. El doc previo afirmaba que "CUMPLIR no tiene ese subsistema" y que "todos los reportes pasan por el módulo Flight" — **incorrecto**. El UI de admin usa el subsistema Excel viejo, NO el Flight.

**Defectos:** SQLi en `$_GET['a']/$_GET['f']` interpolados. El reporte **RECAUDO** consulta `t_pagos` (vacía) → siempre vacío (`reca/index.php:26-53`). El `.xls` es HTML. **Pasamanos SQL arbitrario:** `api/file/sql/user/acue/index.php:12` lee `$sql=$_GET['sql']` y lo ejecuta (`:36` `$obj->query($sql)`) — ejecución de SQL arbitrario sin auth (ver doc 14, P0).

---

## Proceso 12 — Administración de asesores (admin)

**Actor:** admin. **Vista:** `views/asesores.php`. **JS:** `asesores.js`. **Endpoint:** `admin_asesor`.

| Acción | Llamada | SQL | Evidencia |
|---|---|---|---|
| Listar | `GET ?t=all` | `SELECT * FROM t_usuarios WHERE usertype!=0` | `admin_asesor.php:74-75` |
| Buscar nombre | `?t=...&v=<n>` | `... nombre LIKE '%$t%'` | `admin_asesor.php:94` |
| Buscar cédula | `?t=cedula&v=<c>` | `... WHERE cedula='$c'` | `admin_asesor.php:114` |
| Crear | `POST ?t=insert` | `INSERT ... userpass=md5(0), usertype='1', estado='TRUE'` | `admin_asesor.php:165-168` |
| Editar | `POST ?t=update` | `UPDATE ... WHERE cedula='$cedula'` | `admin_asesor.php:218-222` |
| Activar/desactivar | `POST ?t=estado` | `UPDATE ... SET estado WHERE cedula` | `admin_asesor.php:233-235` |
| Eliminar | `POST ?t=delete` | `DELETE FROM t_usuarios WHERE cedula` (físico) | `admin_asesor.php:245` |
| Mensaje (set/borrar) | `POST ?t=mensaje_*` | `DELETE/INSERT t_mensaje WHERE asesor` | `admin_asesor.php:184-196` |

**Defectos verificados:**
- Password inicial = `md5(0)` = `cfcd208495d565ef66e7dff9f98764da` → password `0` (`admin_asesor.php:165`). **10 de 13 usuarios** lo conservan (`quality.json`).
- Borrado físico deja gestiones/acuerdos huérfanos (`asesor` referencia `username`, sin FK).
- Listados devuelven `SELECT *` incluyendo `userpass` (hash al cliente).
- Mensaje del coordinador: implementado en backend+UI, pero `t_mensaje`=0 filas (AUTO_INCREMENT=12 → se usó y se vació).

---

## Proceso 13 — Perfil de usuario

**Vista:** `views/perfil.php`. **JS:** `perfil.js`.

| Acción | Llamada | SQL | Evidencia |
|---|---|---|---|
| Cargar mi día | `GET d_usuario?a=<user>` | count gestiones + acuerdos de hoy (`fecha=date('Y-m-d')`) | `d_usuario.php:40,59` |
| Cambiar avatar | `GET d_usuario_cambio?a=&t=avatar&v=` | `UPDATE t_usuarios SET avatar` | `d_usuario_cambio.php:38` |
| Cambiar password | `GET d_usuario_cambio?a=&t=password&v=` | `UPDATE t_usuarios SET userpass=md5($v)` | `d_usuario_cambio.php:54-56` |

`d_usuario` usa formato de fecha `Y-m-d` (`d_usuario.php:19`) **[difiere de CEGROUP]**, que tenía bug con `Ymd`. Nueva contraseña por GET en claro (`perfil.js`). Sin verificación de contraseña actual; `d_usuario_cambio` cambia el password de cualquier `username` sin auth (toma de cuenta, ver doc 14).

---

## Proceso 14 — Alertas

**Creación** (Proceso 4): `g_alertas` POST → `INSERT t_alertas`. La hora se mapea desde `num` (08–18) a string fijo (`g_alertas.php:62-96`).

**Consulta** (`views/alertas.php`, campana del header `main.js`):

| Modo | Endpoint | SQL | Evidencia |
|---|---|---|---|
| Todas | `b_alertas?t=all&a=` | `WHERE asesor='$a'` | `b_alertas.php:47` |
| Por operación | `t=operacion&a=&o=` | `WHERE asesor AND operacion` | `b_alertas.php:68` |
| Por fecha | `t=fecha&a=&f=` | `WHERE asesor AND fecha` | `b_alertas.php:89` |
| De hoy | `t=hoy&a=&h=` | UNION: pasadas + de hoy hasta la hora actual (`date('Ymd')`) | `b_alertas.php:111-113` |

**Borrado automático:** ver Proceso 4. `b_alertas` devuelve el SQL crudo (`b_alertas.php:50,71,92,116`). CUMPLIR mantiene **304 alertas vivas** vs 22 de CEGROUP **[difiere de CEGROUP]**.

---

## Proceso 15 — Reportes mensuales (módulo Flight) [CUMPLIR-only]

**Actor:** desconocido (`No verificado:` el menú principal NO enlaza a este módulo; se alcanza directo en `https://cumplir.net/REPORTES/REPORTES/`). Sistema **independiente** del API REST, basado en Flight (micro-framework PHP). Detalle técnico exhaustivo en doc 09; aquí solo el flujo de negocio.

**Rutas** (`REPORTES/REPORTES/src/routes/*`, cargadas por `index.php` → `ApiStart.php` → `Flight::start()`):

| Dominio | Rutas | Handler |
|---|---|---|
| Gestiones | `POST /gestiones/date`, `POST /gestiones/all`, `DELETE /gestiones/all` | `controllers/Gestiones.php` |
| Acuerdos | `POST /acuerdos/date`, `POST /acuerdos/all`, `DELETE /acuerdos/all` | `controllers/Acuerdos.php` |
| Proyección | `POST /proyeccion/date`, `POST /proyeccion/all`, `DELETE /proyeccion/all` | `controllers/Proyeccion.php` |
| Render | `GET /reportes/{gestiones,acuerdos,proyeccion}`, `.../file` | `controllers/Reportes.php` |

**Comportamiento:**
- `POST /<dominio>/date` recalcula un día; `POST /<dominio>/all` recalcula el mes completo (TRUNCATE + INSERT 1 fila/asesor + UPDATE por celda); `DELETE /<dominio>/all` resetea.
- Tablas destino: `reporte_gestion`, `reporte_acuerdos`, `reporte_proyeccion` (wide, una fila por asesor, columnas por día `ges_01..ges_31` + totales).

**Estado actual (datos):**
- `reporte_gestion`: poblado, `UPDATE_TIME=2026-06-18` (`_evidence/tables.json`).
- `reporte_acuerdos`, `reporte_proyeccion`: `UPDATE_TIME=2026-06-02` (recalculados una vez en junio). Verificar contenido real de celdas en doc 09.
- Las vistas `gestionesFile.php`, `acuerdosfile.php`, `proyeccionFile.php` están **vacías (0 bytes)** → rutas `.../file` cargan páginas en blanco (código muerto).

**Defectos:** sin auth; SQLi vía `$_POST['date']` (`controllers/*.php:10`); **`echo $sql` filtra la query** (`tools/Gestiones.php:11`); años hardcoded **inconsistentes** entre dominios: Gestiones=**2026** (`tools/Gestiones.php:17-18`), Acuerdos=**2025** (`tools/Acuerdos.php:28-29`), Proyección=**2025** (`tools/Proyeccion.php:28-29`).

---

## Procesos NO implementados o rotos

| Proceso | Evidencia | Estado real |
|---|---|---|
| Captura de pagos | `t_pagos`=0 filas; sin endpoint POST; `views/pagos.php` 0 bytes | No implementado |
| Reporte de recaudo | `reca/index.php` consulta `t_pagos` vacía | Funcional pero siempre vacío |
| Cumplimiento de acuerdos | sin endpoint para cambiar `t_acuerdos.estado`; `estado=activo(11322)` único valor (`column_profiles.json`) | No implementado |
| Ranking/posición | `t_usuarios.posicion=0(13)`; nada lo actualiza | No implementado |
| Módulo Comunicación | `views/comunicacion.php` 0 bytes; ruta existe (`app/http/routes.php:6`); no en menú | Esqueleto vacío |
| Módulo Clientes | `rest/clientes.php` consulta tabla `clientes` inexistente; sin ruta API ni web | Huérfano, roto en runtime |
| Endpoint `g_asesor`/`asesor` | `app/http/routes.php` no tiene método para `asesor.php`; `api/.../routes.php:5` mapea `g_asesor`→método `g_asesor` que NO existe en `ApiController` (solo `asesor()`); `asesor.php` usa tabla `asesor` inexistente y typo `cedukla` | Ruta rota |
| Mensaje de coordinador | backend+UI completos, `t_mensaje`=0 filas | Implementado, sin uso |
| `r_acuerdo` (tabla) | 0 filas; columnas `asesor,ope,acu,valor,fecha` | Creada, nunca poblada **[CUMPLIR-only]** |
| `reporte_acuerdos`/`reporte_proyeccion` | computados solo 1 vez (jun-02); estado de celdas a verificar en doc 09 | Parcial |
| Componentes `app/component/*` | 9 archivos de 0 bytes | Stubs vacíos |
| Vistas `*File.php` (Flight) | 0 bytes; rutas existen | Código muerto |

---

## Diagrama del flujo principal (asesor) — CUMPLIR

```
LOGIN (index.php → login [HTTPS GET] → session.php → public/)
  ↓
[Filtrar carga] filtro.php → filtrotabla.php (b_datafilter)
  ↓ seleccionar operación
GESTION (gestion.php — SIN panel Email)
  ├── Cargar: g_mensaje, g_operacion(JOIN x7, +referencia), g_telefonos(+status),
  │           g_gestiones(init), g_aportes(vacío), g_acuerdos, g_estados,
  │           g_resumen(init), g_alertas
  ├── Llamar al deudor (offline)
  └── Registrar:
       ├── Gestión   → INSERT t_gestiones + UPDATE t_procesos + DELETE t_alertas(¡todas!)
       ├── Teléfono  → INSERT t_telefonos
       ├── Phone status → UPDATE t_telefonos.status (activo/'')   ← CUMPLIR-only, EN USO
       ├── Estado    → UPDATE t_procesos (sin historial)
       ├── Acuerdo   → INSERT t_acuerdos (estado='activo' fijo)
       ├── Resumen   → INSERT t_resumen (16 cols)
       └── Alerta    → INSERT t_alertas (se borrará en la próxima gestión)
  ↓
PERFIL (d_usuario, Y-m-d) → gestiones_hoy, acuerdos_hoy
RESUMEN (b_resumen) → 6 KPIs [año 2026 backend+UI; pagos=0; posicion=0]

[Admin]
  ├── DATA/UPDATE/*        → import dimensional (BASE incluye referencia)   ← CUMPLIR-only árbol
  ├── DATA/UPDATE/REASIGNACION → reasignar en bulk (UPDATE t_asignacion)    ← CUMPLIR-only
  ├── DATA/OCULTAR         → DELETE t_decil (oculta del detalle)            ← CUMPLIR-only
  ├── api/file/update/SUBIR_* → import transaccional (resumen/alertas/...)  ← CUMPLIR-only
  ├── api/file/sql/admin/* → exportar Excel (gest/acue/reca/base/ases)      (heredado)
  └── REPORTES/REPORTES/ (Flight, standalone)                               ← CUMPLIR-only
        ├── POST /<dominio>/date → recalc día
        ├── POST /<dominio>/all  → recalc mes
        └── DELETE /<dominio>/all → reset
```

## Evidencia

- Endpoints REST: `api/app/rest/*.php` (24 archivos), rutas en `api/app/http/routes.php`, métodos en `api/app/controllers/ApiController.php`.
- Importadores: `DATA/UPDATE/<dominio>/{upload,procesar}.php` (8 dominios), `api/file/update/<dominio>/procesar.php` (incl. 5 `SUBIR_*`), `DATA/OCULTAR/procesar.php`.
- Reportes: `api/file/sql/{admin,user}/*/index.php` (Excel + passthrough), `REPORTES/REPORTES/` (Flight).
- Vistas: `app/views/*.php` (16 con contenido + 3 vacías); JS: `app/action/*.js`.
- Datos: `_evidence/` (introspección read-only 2026-06-21, `u815310395_data`, MariaDB 11.8.6).
