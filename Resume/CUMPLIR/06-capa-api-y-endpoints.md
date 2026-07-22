# CUMPLIR — Capa API REST y Endpoints

> Documentación verificada por lectura directa de cada archivo. Rutas relativas a `cumplir/`.
> Fuente de verdad de columnas: `Resume/CUMPLIR/_evidence/column_profiles.json`, `tables.json`.
> **No verificado** se marca explícitamente donde no hay evidencia.

---

## 1. Framework REST (micro-router casero)

No hay framework de terceros. Es un router propio de ~5 clases.

### Punto de entrada

- **Entry real:** `api/api/index.php` (NO `api/index.php`, que no existe).
- `api/api/.htaccess:1-7`: `RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]` → toda ruta se reescribe a `index.php?url=<ruta>`. El nombre lógico del endpoint viaja en `?url=`.
- `api/api/index.php`:
  - `session_start()` (`:2`) — abre sesión PHP, pero **ningún endpoint la usa para autenticar**.
  - `chdir(dirname(__DIR__))` (`:3`) → el working dir pasa a `api/`, por eso `SYS_PATH="lib/"` y `APP_PATH="app/"` (`:4-5`) resuelven desde ahí.
  - `define("CODING", null)` (`:6`) → constante usada como callback en `array_map(CODING, $d)`. Con valor `null`, `array_map(null, $row)` devuelve la fila sin transformar. **Es código de codificación inerte.**
  - `format_post($value)` (`:8-22`): `switch` sobre `$micoding='null'` hardcodeado (`:9`) → solo la rama `'null'` (`mb_strtoupper`) es alcanzable; las ramas `encode`/`decode` son **código muerto**.
  - `new Api` (`:23`) arranca el dispatcher.

### Cadena de carga

`api/lib/initial.php:2-7` hace `require` en orden: `Put.php`, `Api.php`, `Router.php`, `app/http/routes.php`, `Restapi.php`, `DB.php`.

### Componentes

| Clase | Archivo | Rol |
|---|---|---|
| `Router` | `api/lib/Router.php` | Mapa estático `ruta → [controller, method]`. `getAction()` lanza `Exception` si la ruta no existe (`:12`). |
| `Api` | `api/lib/Api.php` | Lee `$_GET['url']` (`:4`), resuelve la acción, instancia el controller, invoca el método. Sin `?url` → renderiza `rest/index.php` (`:17`). En `Exception` → `rest/error.php` (`:14`). |
| `Restapi` | `api/lib/Restapi.php` | `render($view,$params)` hace `extract()` manual de params (`:5-7`) y `require "rest/$view.php"`. |
| `Put` | `api/lib/Put.php` | `get_data_put()` parsea `multipart/form-data` de peticiones PUT a mano vía regex sobre `php://input`. Solo lo consume `clientes.php` (no enrutado). |
| `DB` (`conn`) | `api/lib/DB.php` | Conexión MySQL — ver §2. |

### Flujo de una petición

```
GET /api/api/index.php?url=g_operacion&o=123
  → .htaccess reescribe → index.php?url=g_operacion&o=123
  → new Api → Router::getAction("g_operacion") → ["ApiController","g_operacion"]
  → require app/controllers/ApiController.php → $c->g_operacion()
  → Restapi::render("g_operacion") → require app/rest/g_operacion.php
  → el archivo rest hace switch($_SERVER['REQUEST_METHOD']) y echo json_encode(...)
```

El archivo `rest/*.php` es a la vez "controller HTTP" y "modelo": abre su propia conexión `new conn`, arma el SQL, ejecuta y emite el JSON. No hay capa de servicio.

---

## 2. Conexión a base de datos (`api/lib/DB.php`)

- Clase `conn extends mysqli`; el constructor (`DB.php:5`) llama `parent::__construct(host, user, pass, db)` con **credenciales hardcodeadas en claro** (host `localhost`, usuario y base `u815310395_data` — hosting Hostinger; la contraseña está embebida en el archivo, no se reproduce aquí).
- Si `mysqli_connect_error()`, hace `print("error de conexion")` (`:7`) y **continúa la ejecución** — no aborta; las queries posteriores fallan en cascada.
- **Cada función** de cada endpoint hace su propio `new conn` (una conexión por consulta, varias por request). Sin pool, sin reutilización.
- **Defecto:** credenciales versionadas en el repo. Misma cadena duplicada en `DATA/UPDATE/DB.php` y `DATA/OCULTAR/procesar.php` (ver doc 07).

---

## 3. Patrones transversales de los endpoints

Verificado en todos los `rest/*.php`:

1. **CORS abierto total:** cada archivo emite `Access-Control-Allow-Origin: *` y permite `GET, POST, PUT, DELETE` (cabeceras al inicio de cada archivo).
2. **Sin autenticación.** Ningún endpoint valida sesión, token ni rol. `session_start()` se invoca pero no se consulta. Cualquiera con la URL ejecuta cualquier endpoint.
3. **SQL por interpolación de strings.** Todo parámetro (`$_GET`, `$_POST`) se concatena directo en el SQL. **Inyección SQL en el 100% de los endpoints.** No hay `prepare`/`bind` en ninguna parte.
4. **`http_response_code(200)` fijo** al inicio (salvo `error.php` → 404). Los errores de negocio/SQL devuelven 200.
5. **Forma de respuesta inconsistente.** No hay envoltura uniforme `{data,meta}`. Cada endpoint arma su propio array y hace `json_encode`. Patrón frecuente: `{ process, <entidad>: { num, data } }` donde `data` es `array` de filas o `false` si no hubo resultados.
6. **Fuga del SQL en la respuesta.** Varios endpoints incluyen `$data['sql']=$sql` en el JSON (`b_acuerdos`, `b_alertas`, `b_datafilter`, `b_resumen`, `d_usuario_cambio`, `admin_asesor`), y otros lo devuelven cuando la query falla (`b_data`, `phone`, `g_*` INSERT que retornan `$sql` como `data`). Expone esquema y facilita explotación.
7. **`array_map(CODING, $d)`** con `CODING=null` → no transforma; es no-op.

---

## 4. Catálogo de endpoints — índice

Las 23 rutas registradas en `app/http/routes.php` + métodos del `ApiController.php`. La columna "Dif. vs CEGROUP" marca lo que distingue a CUMPLIR.

| # | Ruta (`?url=`) | Archivo rest | Métodos | Auth | Tablas | Efecto | Dif. vs CEGROUP |
|---|---|---|---|---|---|---|---|
| 1 | `index` | `index.php` | GET | No | — | Ping JSON | = |
| 2 | `login` | `login.php` | GET | No | `t_usuarios` | Lectura (auth débil) | = |
| 3 | `g_asesor` | **(roto)** | — | — | — | — | **Ruta sin método** (ver §6) |
| 4 | `g_operacion` | `g_operacion.php` | GET | No | JOIN 7 tablas | Lectura | **Incluye `referencia`** (col extra en `t_base`) |
| 5 | `g_telefonos` | `g_telefonos.php` | GET, POST | No | `t_telefonos` | Lectura / INSERT | = |
| 6 | `g_gestiones` | `g_gestiones.php` | GET, POST | No | `t_gestiones`,`t_procesos`,`t_alertas` | Lectura / INSERT + 2 efectos colaterales | = |
| 7 | `g_aportes` | `g_aportes.php` | GET | No | `t_pagos` | Lectura + SUM | = |
| 8 | `g_acuerdos` | `g_acuerdos.php` | GET, POST | No | `t_acuerdos` | Lectura / INSERT | = |
| 9 | `g_estados` | `g_estados.php` | GET, POST | No | `t_estados`,`t_subs`,`t_procesos` | Lectura / UPDATE | = |
| 10 | `g_alertas` | `g_alertas.php` | GET, POST | No | `t_alertas` | Lectura / INSERT | = |
| 11 | `g_resumen` | `g_resumen.php` | GET, POST | No | `t_resumen` | Lectura / INSERT | = |
| 12 | `g_mensaje` | `g_mensaje.php` | GET | No | `t_mensaje` | Lectura | = |
| — | *(no existe `g_email`)* | — | — | — | — | — | **CUMPLIR NO tiene `g_email`** (CEGROUP sí) |
| 13 | `b_data` | `b_data.php` | GET | No | `t_base` | Búsqueda | = |
| 14 | `b_acuerdos` | `b_acuerdos.php` | GET | No | `t_acuerdos` | Lectura | **Año 2026 hardcodeado** (CEGROUP: 2023) |
| 15 | `b_alertas` | `b_alertas.php` | GET | No | `t_alertas` | Lectura | = |
| 16 | `b_datafilter` | `b_datafilter.php` | GET | No | `t_procesos`+joins,`t_usuarios` | Lectura | = |
| 17 | `b_resumen` | `b_resumen.php` | GET | No | 6 tablas | Lectura | **Año 2026 hardcodeado** (CEGROUP: 2023) |
| 18 | `d_cartera` | `d_cartera.php` | GET | No | `l_cartera` | Lectura catálogo | = |
| 19 | `d_campana` | `d_campana.php` | GET | No | `l_campana` | Lectura catálogo | = |
| 20 | `d_usuario` | `d_usuario.php` | GET | No | `t_gestiones`,`t_acuerdos` | Lectura (métricas hoy) | = |
| 21 | `d_usuario_cambio` | `d_usuario_cambio.php` | GET | No | `t_usuarios` | UPDATE (avatar/pass) vía GET | = |
| 22 | `admin_asesor` | `admin_asesor.php` | GET, POST | No | `t_usuarios`,`t_mensaje` | CRUD usuarios | = |
| 23 | `phone` | `phone.php` | POST | No | `t_telefonos` | UPDATE `status` | **Endpoint NUEVO, no existe en CEGROUP** |

**Archivos rest presentes pero NO enrutados** (código muerto, ver §6): `asesor.php`, `clientes.php`, `error.php`(*), `index.php`(*). (* `error.php`/`index.php` los invoca `Api.php` directamente, no por ruta.)

---

## 5. Catálogo detallado por endpoint

### `index` — `rest/index.php`
- GET. `new conn` (`:6`) abre conexión y no la usa. Responde `{success:"true", message:"Esta este es el index", sql:null, num_res:null, data:null}`. Ping de salud.

### `login` — `rest/login.php`
- **GET** params `u`,`p`. SQL (`:17`):
  ```sql
  SELECT * FROM `t_usuarios` WHERE `username`='$user' AND `userpass`='$pass' AND `estado`='TRUE'
  ```
  con `$pass = md5($_GET['p'])` (`:15`).
- **Respuesta:** `{ num:<int>, data: [filas]|false }` (`:23-30`). Devuelve **toda la fila** de `t_usuarios`, incluido `userpass` (hash MD5).
- **Defectos:** credenciales por GET (quedan en logs/historial); hash **MD5 sin sal**; SQLi en `$user`; expone el hash en la respuesta; auth por igualdad de string sin protección anti-fuerza-bruta.

### `g_operacion` — `rest/g_operacion.php`  ⟵ **DIFERENCIA CLAVE**
- **GET** param `o`. SQL (`:48-87`): `INNER JOIN` de 7 tablas (`t_base` + `t_asignacion` + `t_campana` + `t_cartera` + `t_decil` + `t_procesos` + `t_saldos`) `WHERE t_base.operacion='$operacion'`. Vista 360° del crédito.
- **Columnas seleccionadas (24):** de `t_base` → `operacion, cuenta, tcedula, tnombre, ttel1, ttel2, ccedula, cnombre, ctel1, ctel2, gcedula, gnombre, gtel1, gtel2, fvencimiento, fingreso, sucursal, dependencia, condicion, banco, referencia`; `t_asignacion.asesor`; `t_campana.campana`; `t_cartera.cartera`; `t_decil.decil`; `t_procesos.estado/sub/fgestion`; `t_saldos.capital/total`.
- **Diferencia vs CEGROUP:** CUMPLIR selecciona `t_base.referencia` (`:69`) — columna que existe en `t_base` (verificado en evidencia: `t_base` tiene 21 columnas incluida `referencia`). CEGROUP no la incluye.
- **Riesgo de los INNER JOIN:** si la operación carece de fila en cualquiera de las 6 tablas relacionadas (p. ej. `t_decil` tras pasar por OCULTAR — ver doc 07), el JOIN devuelve **0 filas** y la ficha "desaparece" aunque el crédito exista en `t_base`.
- **Respuesta:** `{ process:'Select Operacion', operacion:{ num, data:[fila]|false } }`.

### `g_telefonos` — `rest/g_telefonos.php`
- **GET** `o` → `SELECT * FROM t_telefonos WHERE operacion='$o' ORDER BY id DESC` (`:40`).
- **POST** `operacion,asesor,telefono,detalle` → `INSERT INTO t_telefonos (id,operacion,asesor,telefono,detalle) VALUES (NULL,…)` (`:69-72`). `detalle` pasa por `format_post` (mayúsculas).
- **Nota:** el INSERT no asigna `status`; la columna `status` se gestiona aparte vía `phone` (§ phone).
- **Respuesta:** `{ process:'Select Telefono', telefonos:{num,data} }` (GET) · `{data:true|$sql}` (POST).

### `phone` — `rest/phone.php`  ⟵ **ENDPOINT NUEVO (no existe en CEGROUP)**
- **POST** params `id`, `status` (ambos `mb_strtoupper`). SQL (`:25`):
  ```sql
  UPDATE `t_telefonos` SET `status` = '$status' WHERE `t_telefonos`.`id` = '$id';
  ```
- **Respuesta:** `{data:true}` si OK, o `{data:"<sql>"}` si falla (fuga del SQL, `:30`).
- **Propósito (inferido del esquema):** marcar el estado de un teléfono individual (la columna `t_telefonos.status` existe en la BD, verificado). **No verificado:** el conjunto de valores válidos de `status` (no hay catálogo ni `enum` en el código; el valor llega libre desde el cliente).
- **Defectos:** sin auth; SQLi en `id` y `status` (un `id` malicioso afecta múltiples filas o ejecuta sub-sentencias); no valida que el teléfono exista; el UPDATE sin `LIMIT` confía en que `id` sea único.
- **Registrado en** `routes.php:26` y `ApiController.php:27`. Es la única adición real de CUMPLIR sobre el conjunto de rutas de CEGROUP.

### `g_gestiones` — `rest/g_gestiones.php`
- **GET** `o`,`p`. `p='init'` → últimas 3 (`LIMIT 3`, `:46-51`); `p='all'` → todas, `ORDER BY id DESC` (`:65-69`).
- **POST** `gestion,operacion,nombre,asesor`. Fecha/hora server con TZ `America/Bogota` (`:92-94`). INSERT en `t_gestiones` (`:100-103`) y, si OK, **dos efectos colaterales** (`:108-110`):
  1. `UPDATE t_procesos SET fgestion='$fecha', asesor='$asesor' WHERE operacion='$operacion'` (marca la gestión y reasigna el asesor del proceso).
  2. `DELETE FROM t_alertas WHERE operacion='$operacion'` (**borra TODAS las alertas de la operación** al gestionar).
- **Respuesta:** `{process, gestion:{num,data}}` (GET) · `{data:true|$sql}` (POST).
- **Defecto de negocio:** el DELETE de alertas es incondicional; gestionar elimina recordatorios futuros sin confirmación.

### `g_aportes` — `rest/g_aportes.php`
- **GET** `o`. Dos consultas a `t_pagos`: filas (`:40`) y `SELECT SUM(pago) AS aporte_total` (`:57`).
- **Respuesta:** `{process, aportes:{num,data}, suma:{num,data}}`. Solo lectura (no hay INSERT de pagos por API; los pagos entran por carga masiva o externamente).

### `g_acuerdos` — `rest/g_acuerdos.php`
- **GET** `o` → `SELECT * FROM t_acuerdos WHERE operacion='$o' ORDER BY facuerdo DESC` (`:40`).
- **POST** `fecha_pago,tipo_cliente,nombre_cliente,valor,operacion,asesor`. INSERT (`:66-68`) con `estado='activo'` y `fregistro=date('Ymd')` (TZ Bogotá). Mapeo: `facuerdo=$fecha_pago`, `cliente=$tipo_cliente`.
- **Respuesta:** `{process, acuerdos:{num,data}}` / `{data:true|$sql}`.

### `g_estados` — `rest/g_estados.php`
- **GET** → catálogos: `SELECT * FROM t_estados` (`:39`) y `SELECT * FROM t_subs` (`:57`).
- **POST** `operacion,estado,sub` → `UPDATE t_procesos SET estado=…, sub=… WHERE operacion=…` (`:78`).
- **Respuesta:** `{process, estados:{...}, subs:{...}}` / `{data:true|$sql}`.

### `g_alertas` — `rest/g_alertas.php`
- **GET** `o` → `SELECT * FROM t_alertas WHERE operacion='$o' ORDER BY fecha DESC` (`:39`).
- **POST** `fecha,num,detalle,operacion,asesor`. `num` (08–18) se mapea a una hora literal vía `switch` (`:62-96`); si `num` está fuera de rango, `$hora` queda **indefinida** (warning + valor vacío). INSERT en `t_alertas` (`:99-100`).
- **Respuesta:** `{process, alertas:{num,data}}` / `{data:true|$sql}`.

### `g_resumen` — `rest/g_resumen.php`
- **GET** `o`,`p` (`init`=`LIMIT 3` / `all`) sobre `t_resumen` (`:46-67`).
- **POST** 14 campos (`fingreso,cedula,nombre,operacion,tipo,canal,telefono,contacto,acuerdo,ncuotas,vcredito,vnegociado,condonado,asesor`) → INSERT en `t_resumen` con `fregistro=date('Ymd')` (`:107-108`).
- **Respuesta:** `{process, resumen:{num,data}}` / `{data:true|$sql}`.

### `g_mensaje` — `rest/g_mensaje.php`
- **GET** `a` → `SELECT * FROM t_mensaje WHERE asesor='$a'` (`:31`). Mensaje motivacional/instructivo por asesor.

### `b_data` — `rest/b_data.php`
- **GET** `t`,`v`. Buscador. `t='o'` → por operación en `t_base` (`:48`). Otros `t` (`c`=cédula, `n`=nombre) → busca en paralelo titular/codeudor/garante:
  - titular: `tcedula='$v'` o `tnombre LIKE '%$v%'` (`:68-71`)
  - codeudor: `ccedula` / `cnombre LIKE` (`:93-96`)
  - garante: `gcedula` / `gnombre LIKE` (`:118-121`)
- **Respuesta:** `{operacion:{...}}` o `{result:{titular,codeudor,garante}}`. Incluye `$sql` cuando no hay resultados (fuga parcial).

### `b_acuerdos` — `rest/b_acuerdos.php`  ⟵ **AÑO 2026 HARDCODEADO**
- **GET** `t`,`v`,`a`. `t='o'` (por operación, `:46`), `t='m'` (por mes), `t='f'` (por fecha exacta). Todas filtran `asesor='$a' AND estado='activo'`.
- En `t='m'` el rango es `$f1='2026-'.$v.'-01'` … `$f2='2026-'.$v.'-31'` (`:64-65`). **Año 2026 fijo en código.** En CEGROUP el equivalente está fijo a 2023. Cualquier consulta mensual fuera de 2026 devuelve vacío.
- Devuelve `$data['sql']` siempre (fuga del SQL, `:50`).

### `b_alertas` — `rest/b_alertas.php`
- **GET** `t` ∈ {`all`,`operacion`,`fecha`,`hoy`}, param `a` (asesor) en todos.
  - `all`: `WHERE asesor='$a' ORDER BY fecha ASC, num` (`:47`).
  - `operacion`: + `AND operacion='$o'` (`:68`).
  - `fecha`: + `AND fecha='$f'` (`:89`).
  - `hoy`: `UNION` de alertas pasadas (`fecha<$f`) + las de hoy hasta la hora `$h` (`num<=$h`) — fecha server TZ Bogotá (`:111-113`).
- Devuelve `$data['sql']` (fuga).

### `b_datafilter` — `rest/b_datafilter.php`
- **GET** `t` ∈ {`null`,`estado`,`cartera`,`campana`}, param `a`. Lista operaciones por asesor con JOIN `t_procesos` ⋈ `t_asignacion` (+ `t_cartera`/`t_campana` según filtro), excluyendo las gestionadas hoy (`fgestion != '$hoy'`), `ORDER BY fgestion ASC`. El caso `null` aplica `LIMIT 20` (`:49`); los demás no tienen límite.
- **Nota bug (`:122`):** en `SELECT_DATA_CAMPANA` falta un espacio antes de `WHERE` (`…t_procesos.operacion`+`WHERE`) → concatenación `operacionWHERE`. **No verificado en runtime** si MySQL lo tolera; aparenta romper la query de filtro por campaña.
- Devuelve `$data['sql']` y `$data['tipo']`.

### `b_resumen` — `rest/b_resumen.php`  ⟵ **AÑO 2026 HARDCODEADO**
- **GET** `v` (mes), `a` (asesor). Rango `2026-$v-01`..`2026-$v-31` (`:19-20`). **Año 2026 fijo.** Agrega 6 métricas del mes: conteo de gestiones, acuerdos, `SUM(pago)`, conteo de resumen, base asignada (`t_asignacion`), y `posicion` del usuario (`t_usuarios`). Devuelve `$data['sql']` en cada sub-bloque (fuga).

### `d_cartera` / `d_campana` — catálogos
- **GET** sin params. `SELECT * FROM l_cartera ORDER BY cartera ASC` (`d_cartera.php:30`) / `SELECT * FROM l_campana ORDER BY campana ASC` (`d_campana.php:30`). Solo lectura de catálogos.

### `d_usuario` — `rest/d_usuario.php`
- **GET** `a`. Métricas del día (`date('Y-m-d')`, TZ Bogotá): conteo de gestiones (`fecha='$f'`, `:40`) y de acuerdos (`fregistro='$f'`, `:59`) del asesor. **Nota:** `t_gestiones.fecha` se carga como `Ymd` desde la API de gestión, pero aquí compara contra `Y-m-d` — posible desajuste de formato. **No verificado** el formato real almacenado.

### `d_usuario_cambio` — `rest/d_usuario_cambio.php`  ⟵ **MUTACIÓN POR GET**
- **GET** `t` ∈ {`avatar`,`password`}, params `a`,`v`.
  - `avatar`: `UPDATE t_usuarios SET avatar='$v' WHERE username='$a'` (`:38`).
  - `password`: `UPDATE t_usuarios SET userpass='<md5($v)>' WHERE username='$a'` (`:56`).
- **Defectos:** cambio de contraseña vía **GET** (queda en logs/historial); sin auth → cualquiera cambia la clave de cualquier usuario conociendo su `username`; MD5 sin sal; devuelve `$data['sql']` (fuga).

### `admin_asesor` — `rest/admin_asesor.php`
- **GET** `t` ∈ {`all`,`texto`,`cedula`,`mensaje`}:
  - `all`: `SELECT * FROM t_usuarios WHERE usertype != 0 ORDER BY estado DESC` (`:75`).
  - `texto`: + `AND nombre LIKE '%$t%'` (`:94`).
  - `cedula`: `WHERE cedula='$c'` (`:114`).
  - `mensaje`: `SELECT * FROM t_mensaje WHERE asesor='$a'` (`:136`).
- **POST** `t` ∈ {`insert`,`update`,`estado`,`delete`,`mensaje_insert`,`mensaje_delete`}:
  - `insert` (`:159-172`): crea usuario con `userpass=md5(0)`, `usertype='1'`, `posicion='0'`, `estado='TRUE'`. **Contraseña inicial = `md5(0)` constante para todos.**
  - `update` (`:212`): `nombre,telefono,username` por `cedula`.
  - `estado` (`:229`): activa/desactiva por `cedula`.
  - `delete` (`:242-245`): `DELETE FROM t_usuarios WHERE cedula='$c'`. **Borrado físico**, deja gestiones/acuerdos huérfanos (referencian `asesor` por string, sin FK).
  - `mensaje_insert` (`:180-187`): `DELETE` + `INSERT` (reemplazo) del mensaje del asesor.
  - `mensaje_delete` (`:193-197`): borra el mensaje del asesor.
- **Respuesta:** devuelve `$data['sql']` y `$data['result']` (fuga del SQL en todas las mutaciones).
- **Defectos:** sin auth para administración de usuarios; SQLi; contraseña inicial trivial y pública (`md5(0)`); todo el SQL expuesto.

---

## 6. Código muerto y rutas rotas (verificado)

| Item | Evidencia | Estado |
|---|---|---|
| Ruta `g_asesor` | `routes.php:5` registra `g_asesor → ApiController::g_asesor`, pero `ApiController.php` **no tiene método `g_asesor`** (tiene `asesor()`, `:5`, que no está enrutado). | **Ruta rota:** invocar `?url=g_asesor` → fatal error / 500 (método inexistente). No verificado en runtime. |
| `asesor.php` | Archivo presente; método `ApiController::asesor()` existe pero **ninguna ruta lo mapea**. Consulta tablas `asesor` con typo `cedukla` (`:78`) y `password` en claro (`:94`). | **Inalcanzable.** La tabla `asesor` **no existe** en la BD (no está en `tables.json`). Código legacy huérfano. |
| `clientes.php` | Archivo presente, sin ruta ni método en `ApiController`. CRUD completo (GET/POST/PUT/DELETE) sobre tabla `clientes` con subida de PDF a `file/<cedula>.pdf`. | **Inalcanzable.** Tabla `clientes` **no existe** en la BD. Funcionalidad nunca activa. |
| `Put.php` (`get_data_put`) | Solo lo usa `clientes.php` (PUT). | Cargado pero efectivamente sin uso. |
| Constante `CODING` / `format_post` ramas `encode`/`decode` | `index.php:6,9-19` | Código muerto (ver §1). |

---

## 7. Resumen de defectos de seguridad por endpoint

> La consolidación/severidad global va en otro documento. Aquí solo el inventario por endpoint.

| Defecto | Endpoints afectados |
|---|---|
| Sin autenticación | **Todos** (los 22 alcanzables) |
| Inyección SQL (interpolación directa) | **Todos** los que reciben params |
| Mutación de datos por **GET** | `d_usuario_cambio` (avatar y password) |
| Cambio de contraseña sin auth | `d_usuario_cambio` (password), `admin_asesor` (insert/reset) |
| Fuga del SQL en la respuesta | `b_acuerdos`, `b_alertas`, `b_datafilter`, `b_resumen`, `b_data`(parcial), `d_usuario_cambio`, `admin_asesor`, `phone`(en fallo) |
| Contraseña en hash MD5 sin sal | `login`, `d_usuario_cambio`, `admin_asesor` |
| Hash de contraseña expuesto en respuesta | `login` (devuelve `userpass`) |
| Contraseña inicial trivial pública | `admin_asesor` (`md5(0)`) |
| Año hardcodeado (filtros mensuales rotos fuera de 2026) | `b_acuerdos`, `b_resumen` |
| Borrado físico sin protección de huérfanos | `admin_asesor` (delete usuario), `g_gestiones` (delete alertas) |
| Credenciales DB hardcodeadas | `lib/DB.php:5` (base de todos los endpoints) |
| CORS `*` total | **Todos** |

---

## 8. Diferencias verificadas frente a CEGROUP

| Aspecto | CEGROUP | CUMPLIR | Evidencia |
|---|---|---|---|
| Endpoint `phone` | No existe | **Existe** (UPDATE `t_telefonos.status`) | `routes.php:26`, `rest/phone.php`, col `status` en BD |
| Endpoint `g_email` | Existe (`t_email`) | **No existe** (no hay `g_email.php` ni ruta) | `routes.php` sin `g_email`; no hay archivo |
| `g_operacion` columnas | Sin `referencia` | **Incluye `t_base.referencia`** | `g_operacion.php:69`; `t_base` tiene `referencia` en BD |
| Año hardcodeado en filtros mensuales | 2023 | **2026** | `b_acuerdos.php:64-65`, `b_resumen.php:19-20` |
| Carga REASIGNACION | No documentada en CEGROUP | **Presente** (ver doc 07) | `DATA/UPDATE/REASIGNACION/` |
| Carga OCULTAR | Borra `t_base` (`elimina.php`) | **Borra `t_decil`** (ver doc 07) | `DATA/OCULTAR/procesar.php:18` |
| Resto del framework, CORS, SQLi, sin-auth, MD5 | Igual | Igual | idéntico patrón |

**No verificado:** valores válidos de `t_telefonos.status`; comportamiento runtime de `g_asesor` (ruta rota) y del bug de espacio en `b_datafilter`; formato real de fecha en `t_gestiones`.
