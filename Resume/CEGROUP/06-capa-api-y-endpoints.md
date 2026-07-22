# CEGROUP — Capa API REST y catálogo de endpoints

> Fuente: `cegroup/api/`. Todas las citas `archivo:línea` son relativas a `cegroup/`.
> Documento verificado leyendo cada archivo del framework y los 24 archivos de `app/rest/`.
> Los defectos de seguridad se anotan por endpoint; la consolidación va en el documento de seguridad.

---

## 1. Resumen ejecutivo

API REST en PHP plano (sin framework de terceros), réplica minimalista del framework web del proyecto. Características transversales **verificadas en todos los endpoints**:

- **Sin autenticación ni autorización.** Ningún archivo del framework ni de `app/rest/` lee sesión, cookie, header `Authorization` ni token. Cualquiera que conozca el nombre del endpoint lo invoca.
- **SQL por concatenación de strings** con valores de `$_GET`/`$_POST` → **inyección SQL en todos los endpoints**.
- **CORS totalmente abierto**: `Access-Control-Allow-Origin: *` en cada `rest/*.php`.
- **Conexión MySQL con credenciales hardcodeadas** (`lib/DB.php:5`).
- **Respuesta JSON** sin envoltura estándar: cada endpoint arma su propio `$data` y hace `echo json_encode($return)`.

---

## 2. Framework REST — cómo se enruta y responde

### 2.1 Punto de entrada (`api/api/index.php`)

```
api/api/index.php:2   session_start()
api/api/index.php:3   chdir(dirname(__DIR__))   → CWD pasa a api/
api/api/index.php:4-6 define SYS_PATH="lib/", APP_PATH="app/", CODING=null
api/api/index.php:7   require lib/initial.php
api/api/index.php:8-22 función global format_post($value)
api/api/index.php:23  $app = new Api
```

- `session_start()` se llama pero **ninguna ruta usa `$_SESSION`** (verificado: ningún `rest/*.php` lee `$_SESSION`).
- `format_post($value)` (`api/api/index.php:8-22`): el `switch` opera sobre `$micoding = 'null'` hardcodeado, por lo que solo la rama `'null'` (`mb_strtoupper`) es alcanzable; las ramas `encode`/`decode` son **código muerto**.
- `CODING = null` (`api/api/index.php:6`): se usa en casi todos los endpoints como `array_map(CODING, $d)`, que en PHP es un **no-op** (mapeo con callback `null` devuelve el array igual). Es decir, no transforma nada.

### 2.2 Bootstrap (`api/lib/initial.php`)

Orden de carga (`api/lib/initial.php:2-7`): `Put.php` → `Api.php` → `Router.php` → `app/http/routes.php` → `Restapi.php` → `DB.php`. **No se conecta a la base aquí**; cada endpoint instancia `new conn()` por su cuenta.

### 2.3 Dispatcher (`api/lib/Api.php`)

```php
// api/lib/Api.php:3-19
if (isset($_GET["url"])) {
  $action = Router::getAction($url);            // lanza Exception si no existe
  require APP_PATH."controllers/".$controllerName.".php";
  (new $controllerName())->$method();
} else {
  require APP_PATH."rest/index.php";            // sin ?url → index
}
// catch Exception → require APP_PATH."rest/error.php"
```

La URL se pasa como `?url=<ruta>` (`api/lib/Api.php:20-24`). Ejemplo real: `…/api/api/index.php?url=g_operacion&o=123`.

### 2.4 Router (`api/lib/Router.php`)

Tabla estática hashmap (`Router::$routes`). `add($route,$controller,$method)` registra; `getAction($route)` busca y lanza `Exception("La ruta '$route' NO fue encontrada")` si no existe (`api/lib/Router.php:8-14`). **Sin rutas dinámicas, sin distinción de método HTTP** — el método HTTP se decide dentro de cada `rest/*.php`.

### 2.5 Renderer (`api/lib/Restapi.php`)

`Restapi::render($view, $params=[])` (`api/lib/Restapi.php:4-9`): expande `$params` a variables (`$$key = $value`) y hace `require app/rest/<view>.php`. **En la práctica los controllers nunca pasan params** — cada `rest/*.php` lee `$_GET`/`$_POST`/`php://input` directo.

### 2.6 Controller (`api/app/controllers/ApiController.php`)

23 métodos, todos de una línea `Restapi::render("<vista>")` (`ApiController.php:3-25`). Sin lógica.

### 2.7 Conexión DB (`api/lib/DB.php`)

```php
// api/lib/DB.php:2-9
class conn extends mysqli {
  function __construct(){
    parent::__construct("localhost","user_cegroup","<password>","data_cegroup");
    if (mysqli_connect_error()) print("error de conexion");
  }
}
```

- Credenciales **hardcodeadas** en `api/lib/DB.php:5` (host `localhost`, user `user_cegroup`, base `data_cegroup`; la contraseña está en claro en el archivo — no se reproduce aquí). Línea comentada `api/lib/DB.php:4` con credenciales alternas (`root`/`db_cegroup`).
- Error de conexión: solo imprime el string `"error de conexion"` al output — sin logging, sin excepción, sin corte de ejecución.
- **No se fija charset** (`set_charset('utf8mb4')` ausente). Las tablas usan `utf8mb3` (evidencia BD). De ahí los `utf8_encode`/`utf8_decode` dispersos por el código.
- **Hay dos copias** de esta clase: `api/lib/DB.php` (usada por la API REST) y `api/UPDATE/DB.php` (usada por las cargas). Contenido idéntico.

### 2.8 Parser PUT (`api/lib/Put.php`)

`get_data_put(&$a_data)` (`api/lib/Put.php:2-37`): parser manual de `multipart/form-data` para requests PUT (PHP no expone `$_PUT`). Lee `php://input`, extrae el boundary del header `CONTENT_TYPE`, separa bloques y llena `$a_data[name]=value`. **Solo lo usa `rest/clientes.php`** (endpoint huérfano — ver §4.6).

### 2.9 Forma de respuesta — patrón real

Cada `rest/*.php` repite este patrón (verificado en los 24):

```php
header('Access-Control-Allow-Origin: *');                 // + Headers/Methods CORS
header('content-type: application/json; charset=utf-8');
http_response_code(200);                                   // SIEMPRE 200, incluso en error
$return = null;
switch ($_SERVER['REQUEST_METHOD']) { case 'GET': $return = SELECT_X(); ... }
echo json_encode($return);
```

- **Siempre HTTP 200**, incluso cuando la query falla o no hay resultados (`error.php` es la única excepción, que devuelve 404 vía el dispatcher).
- La función interna construye un array `$data` y lo retorna; el `switch` lo asigna a `$return`; el archivo hace `echo json_encode($return)`. Por tanto **la forma del JSON es la del `$data` que arma cada función** (documentado endpoint por endpoint abajo).
- Patrón común de bloque de datos: `{ num: <int>, data: [filas...] | false }`. Cuando no hay filas, `data` es `false` (booleano), no `[]`.
- Varios endpoints filtran fugas: incluyen el SQL ejecutado en la respuesta (`$data['sql'] = $sql`) — expone la estructura de la query y facilita inyección.

---

## 3. Índice de endpoints

| # | Ruta (`?url=`) | Archivo `rest/` | Métodos reales | Auth | Tabla(s) | Mutación / efecto colateral |
|---|---|---|---|---|---|---|
| 1 | `index` | `index.php` | GET (cualquiera) | No | — | Ninguna (responde fijo) |
| 2 | `login` | `login.php` | GET | No | `t_usuarios` | Lectura. md5 sin salt, pass en query |
| 3 | `g_asesor` ⚠ROTA | `asesor.php` | GET (POST/PUT/DELETE stubs) | No | `asesor` (no existe) | Inalcanzable + tabla inexistente |
| 4 | `g_operacion` | `g_operacion.php` | GET | No | JOIN 7 tablas | Lectura |
| 5 | `g_telefonos` | `g_telefonos.php` | GET, POST | No | `t_telefonos` | INSERT |
| 6 | `g_gestiones` | `g_gestiones.php` | GET, POST | No | `t_gestiones`,`t_procesos`,`t_alertas` | **INSERT + UPDATE t_procesos + DELETE t_alertas** |
| 7 | `g_aportes` | `g_aportes.php` | GET | No | `t_pagos` | Lectura + SUM |
| 8 | `g_acuerdos` | `g_acuerdos.php` | GET, POST | No | `t_acuerdos` | INSERT (estado 'activo' fijo) |
| 9 | `g_estados` | `g_estados.php` | GET, POST | No | `t_estados`,`t_subs`,`t_procesos` | POST = UPDATE t_procesos |
| 10 | `g_alertas` | `g_alertas.php` | GET, POST | No | `t_alertas` | INSERT |
| 11 | `g_resumen` | `g_resumen.php` | GET, POST | No | `t_resumen` | INSERT |
| 12 | `g_mensaje` | `g_mensaje.php` | GET | No | `t_mensaje` | Lectura |
| 13 | `g_email` | `g_email.php` | GET, POST | No | `t_email` | INSERT |
| 14 | `b_data` | `b_data.php` | GET | No | `t_base` | Lectura |
| 15 | `b_acuerdos` | `b_acuerdos.php` | GET | No | `t_acuerdos` | Lectura. **Año 2023 hardcodeado** |
| 16 | `b_alertas` | `b_alertas.php` | GET | No | `t_alertas` | Lectura |
| 17 | `b_datafilter` | `b_datafilter.php` | GET | No | `t_procesos`+JOINs | Lectura |
| 18 | `b_resumen` | `b_resumen.php` | GET | No | 6 tablas | Lectura. **Año 2023 hardcodeado** |
| 19 | `d_cartera` | `d_cartera.php` | GET | No | `l_cartera` | Lectura (catálogo) |
| 20 | `d_campana` | `d_campana.php` | GET | No | `l_campana` | Lectura (catálogo) |
| 21 | `d_usuario` | `d_usuario.php` | GET | No | `t_gestiones`,`t_acuerdos` | Lectura (métricas de hoy) |
| 22 | `d_usuario_cambio` | `d_usuario_cambio.php` | GET | No | `t_usuarios` | **UPDATE (avatar / password)** vía GET |
| 23 | `admin_asesor` | `admin_asesor.php` | GET, POST | No | `t_usuarios`,`t_mensaje` | INSERT/UPDATE/**DELETE físico** |
| — | (sin ruta) | `error.php` | — | — | — | Responde 404 (lo invoca el dispatcher) |
| — | (huérfano) | `clientes.php` | GET/POST/PUT/DELETE | No | `clientes` (no existe) | CRUD + upload PDF. Inalcanzable |

> **Discrepancia ruta↔método confirmada:** `routes.php:5` registra ruta `g_asesor` → método `ApiController::g_asesor()`, que **no existe** (el controller solo tiene `asesor()`, `ApiController.php:5`, **sin ruta**). Invocar `?url=g_asesor` produce `Call to undefined method` (fatal PHP) capturado como `Exception`→ pero la fatal de método indefinido NO es `Exception` capturable en PHP, así que es error 500. `rest/asesor.php` queda **inalcanzable** por el routing actual.

---

## 4. Catálogo detallado por endpoint

### 4.1 Autenticación

#### `login` (`rest/login.php`)
- **Ruta:** `?url=login` · **Método:** GET (`login.php:8-12`).
- **Params GET:** `u` (username), `p` (password en claro).
- **SQL** (`login.php:17`):
  ```sql
  SELECT * FROM `t_usuarios`
  WHERE `username`='$user' AND `userpass`=md5('$pass') AND `estado`='TRUE'
  ```
  (`$pass = md5($_GET['p'])` en `login.php:15`).
- **Respuesta:** `{ num:<int>, data:[fila_usuario...] | false }` (`login.php:20-28`). Devuelve **toda la fila** de `t_usuarios` (incluye `userpass` md5).
- **Defectos:** inyección SQL en `$user`; password viaja en query string (queda en logs/historial); md5 sin salt; expone hash en la respuesta.

#### `g_asesor` → `asesor.php` (ROTA / código muerto)
- **Ruta declarada:** `?url=g_asesor` (`routes.php:5`) → método inexistente → **no ejecuta**.
- **Lógica del archivo** (`asesor.php`): GET con `switch($_GET['d'])`: `data`+`c` → `SELECT_ASESOR_CEDULA`; `login`+`u`,`p` → `SELECT_ASESOR_LOGIN`; default → `SELECT_ASESOR_ALL`. POST/PUT/DELETE retornan `null` (stubs, `asesor.php:52-65`).
- **Tabla destino:** `asesor` — **no existe en la base** (evidencia: tables.json no la contiene). Todas las queries fallarían.
- **Bug adicional:** `SELECT_ASESOR_CEDULA` usa columna `cedukla` (typo, `asesor.php:78`).
- **Conclusión:** doblemente muerto — ruta rota + tabla inexistente.

### 4.2 Operación / gestión de un crédito

> Una "operacion" es la **clave del crédito** (`t_base.operacion`, PK bigint, 54.931 filas — evidencia BD). Los prefijos `t`/`c`/`g` en `t_base` = **t**itular / **c**odeudor / **g**arante.

#### `g_operacion` (`rest/g_operacion.php`)
- **Método:** GET · **Param:** `o` (operacion).
- **SQL** (`g_operacion.php:48-85`): INNER JOIN de 7 tablas (`t_base` + `t_asignacion` + `t_campana` + `t_cartera` + `t_decil` + `t_procesos` + `t_saldos`) `WHERE t_base.operacion='$operacion'`. Vista 360° del crédito (28 columnas: datos de titular/codeudor/garante, fechas, sucursal, asesor, campaña, cartera, decil, estado, sub, fgestion, capital, total).
- **Respuesta:** `{ process:'Select Operacion', operacion:{ num, data:[fila] | false } }`.
- **Nota:** al ser INNER JOIN, una operación sin fila en **cualquiera** de las 6 tablas relacionadas **no aparece** (riesgo de invisibilidad si falta una carga).

#### `g_telefonos` (`rest/g_telefonos.php`)
- **Métodos:** GET, POST.
- **GET** `o` → `SELECT * FROM t_telefonos WHERE operacion='$o' ORDER BY id DESC` (`g_telefonos.php:40-41`). Respuesta `{ process, telefonos:{num,data} }`.
- **POST** campos `operacion`,`asesor`,`telefono`,`detalle` → `INSERT INTO t_telefonos (id,operacion,asesor,telefono,detalle)` (`g_telefonos.php:70-73`). Respuesta `{ data:true | <sql> }`.

#### `g_gestiones` (`rest/g_gestiones.php`) — **efecto colateral crítico**
- **Métodos:** GET, POST.
- **GET** `o`+`p`: `init` → últimas 3 (`LIMIT 3`, `g_gestiones.php:48-51`); `all` → todas `ORDER BY id DESC` (`:67-69`).
- **POST** campos `gestion`,`operacion`,`nombre`,`asesor` → tres queries (`g_gestiones.php:100-112`):
  1. `INSERT INTO t_gestiones (...,fecha,hora,gestion)` (fecha `Ymd`, hora `h:i:s A`, zona `America/Bogota`).
  2. `UPDATE t_procesos SET fgestion='$fecha', asesor='$asesor' WHERE operacion='$operacion'`.
  3. **`DELETE FROM t_alertas WHERE operacion='$operacion'`** — **confirmado**: registrar una gestión **borra todas las alertas de esa operación** (`g_gestiones.php:110`). No hay aviso al usuario.
- **Respuesta:** `{ process, gestion:{num,data} }` en GET; `{ data:true | <sql> }` en POST.

#### `g_aportes` (`rest/g_aportes.php`)
- **Método:** GET · **Param:** `o`.
- Dos queries sobre `t_pagos` (`g_aportes.php:40-41`,`:57-58`): lista por operación + `SELECT SUM(pago) AS aporte_total`.
- **Respuesta:** `{ process, aportes:{num,data}, suma:{num,data} }`.
- **Nota BD:** `t_pagos` existe pero está **vacía (0 filas)** y **no tiene importador** ni endpoint de escritura en este código. `No verificado:` cómo se pueblan los pagos.

#### `g_acuerdos` (`rest/g_acuerdos.php`)
- **Métodos:** GET, POST.
- **GET** `o` → `SELECT * FROM t_acuerdos WHERE operacion='$o' ORDER BY facuerdo DESC` (`g_acuerdos.php:40-41`).
- **POST** campos `fecha_pago`,`tipo_cliente`,`nombre_cliente`,`valor`,`operacion`,`asesor` → `INSERT INTO t_acuerdos (operacion,cliente,nombre,facuerdo,fregistro,asesor,estado,valor)` con **`estado='activo'` fijo** y `fregistro=date('Ymd')` (`g_acuerdos.php:66-68`).
- **Confirmado por BD:** `t_acuerdos.estado` tiene un único valor `'activo'` en 10.902 filas → no hay transición de estado en ningún endpoint.

#### `g_estados` (`rest/g_estados.php`)
- **Métodos:** GET, POST.
- **GET** (sin params): dos catálogos paralelos `SELECT * FROM t_estados` + `SELECT * FROM t_subs` (`g_estados.php:39`,`:57`). Respuesta `{ process, estados:{num,data}, subs:{num,data} }`.
- **POST** campos `operacion`,`estado`,`sub` → `UPDATE t_procesos SET estado='$estado', sub='$sub' WHERE operacion='$operacion'` (`g_estados.php:78-79`).

#### `g_alertas` (`rest/g_alertas.php`)
- **Métodos:** GET, POST.
- **GET** `o` → `SELECT * FROM t_alertas WHERE operacion='$o' ORDER BY fecha DESC` (`g_alertas.php:39-40`).
- **POST** campos `fecha`,`num`,`detalle`,`operacion`,`asesor`. `num` (`'08'`…`'18'`) se mapea por `switch` a hora literal: `'08'→'08:00 AM'` … `'12'→'12:00 MM'`, `'13'→'01:00 PM'` … `'18'→'06:00 PM'` (`g_alertas.php:62-96`). Luego `INSERT INTO t_alertas (operacion,asesor,fecha,hora,num,alerta)` (`:99-100`).
- **Nota:** un `num` fuera de `'08'..'18'` deja `$hora` indefinida (warning + valor vacío).

#### `g_resumen` (`rest/g_resumen.php`)
- **Métodos:** GET, POST.
- **GET** `o`+`p`: `init` → últimas 3 (`LIMIT 3`); `all` → todas (`g_resumen.php:48-50`,`:66-67`).
- **POST** 14 campos del body → `INSERT INTO t_resumen` (`g_resumen.php:107-108`): `fingreso,cedula,nombre,operacion,tipo,canal,telefono,contacto,acuerdo,ncuotas,vcredito,vnegociado,condonado,asesor` + `fregistro=date('Ymd')`.
- **Confirmado por BD:** `t_resumen` tiene 493.254 filas — es la tabla de registro de contactos/llamadas más voluminosa.

#### `g_mensaje` (`rest/g_mensaje.php`)
- **Método:** GET · **Param:** `a` (asesor).
- `SELECT * FROM t_mensaje WHERE asesor='$a'` (`g_mensaje.php:31`). Respuesta `{ num, data }`.

#### `g_email` (`rest/g_email.php`)
- **Métodos:** GET, POST.
- **GET** `o` → `SELECT * FROM t_email WHERE operacion='$o' ORDER BY id DESC` (`g_email.php:40`). **Fuga:** incluye `$data['sql']=$sql` en la respuesta (`g_email.php:44`).
- **POST** campos `operacion`,`email` → `INSERT INTO t_email (id,operacion,email)` (`g_email.php:67-70`).

### 4.3 Búsqueda / dashboard

#### `b_data` (`rest/b_data.php`)
- **Método:** GET · **Params:** `t`,`v`.
- `t='o'` → `SELECT * FROM t_base WHERE operacion='$v'` (`b_data.php:48`).
- default (`t='c'`|`'n'`) → tres queries paralelas sobre `t_base` para titular/codeudor/garante:
  - `t='c'`: `tcedula`/`ccedula`/`gcedula` `= '$v'` (`:68`,`:93`,`:118`).
  - `t='n'`: `tnombre`/`cnombre`/`gnombre` `LIKE '%$v%'` (`:71`,`:96`,`:121`).
- **Respuesta:** `{ titular:{num,data}, codeudor:{num,data}, garante:{num,data} }` (o `{operacion:{...}}` si `t='o'`).

#### `b_acuerdos` (`rest/b_acuerdos.php`)
- **Método:** GET · **Params:** `t`,`v`,`a` (asesor). Todos filtran `asesor='$a' AND estado='activo'`.
- `t='o'` → por `operacion='$v'` (`b_acuerdos.php:46`).
- `t='m'` (mes) → `facuerdo BETWEEN '2023-$v-01' AND '2023-$v-31'` — **año 2023 hardcodeado** (`b_acuerdos.php:64-65`).
- `t='f'` (fecha) → `facuerdo='$v'` (`:86`).

#### `b_alertas` (`rest/b_alertas.php`)
- **Método:** GET · **Param base:** `t`,`a`.
- `t='all'` → todas del asesor (`b_alertas.php:47`).
- `t='operacion'`+`o` → del asesor para esa operación (`:68`).
- `t='fecha'`+`f` → del asesor en esa fecha (`:89`).
- `t='hoy'`+`h` → **UNION**: `fecha<hoy` (atrasadas) UNION `fecha=hoy AND num<='$h'` (las de hoy hasta la hora `h`) (`b_alertas.php:111-113`).

#### `b_datafilter` (`rest/b_datafilter.php`) — workload del asesor
- **Método:** GET · **Param base:** `t`,`a`. Todos excluyen lo ya gestionado hoy (`t_procesos.fgestion != '$hoy'`).
- `t='null'` → top 20 más antiguas por `fgestion ASC LIMIT 20` (`b_datafilter.php:49`).
- `t='estado'`+`e`,`s` → filtra por `estado` y `sub` (`:74`).
- `t='cartera'`+`c` → JOIN `t_cartera`, filtra `cartera='$c'` (`:98`).
- `t='campana'`+`c` → JOIN `t_campana`, filtra `campana='$c'` (`:122`). **Bug menor:** falta espacio antes de `WHERE` en `:122` (`…t_procesos.operacion`WHERE`…`) — funciona porque MySQL tolera, pero es frágil.
- Cada modo expone `$data['sql']` (fuga).

#### `b_resumen` (`rest/b_resumen.php`) — productividad mensual
- **Método:** GET · **Params:** `v` (mes), `a` (asesor). Rango `2023-$v-01`..`2023-$v-31` — **año 2023 hardcodeado** (`b_resumen.php:19-20`).
- 6 sub-consultas (`b_resumen.php:31-101`):
  - `gestiones` → `COUNT id FROM t_gestiones` por fecha.
  - `acuerdos` → `COUNT id FROM t_acuerdos` por `fregistro`.
  - `pagos` → `SUM(pago) FROM t_pagos`.
  - `resumen` → `COUNT id FROM t_resumen` por `fregistro`.
  - `base` → `SELECT * FROM t_asignacion WHERE asesor='$a'` (total asignado).
  - `posicion` → `SELECT posicion FROM t_usuarios WHERE username='$a'` (ranking).
- Cada bloque expone `$data['sql']` (fuga).

### 4.4 Catálogos y métricas

#### `d_cartera` (`rest/d_cartera.php`)
- GET sin params → `SELECT * FROM l_cartera ORDER BY cartera ASC` (`d_cartera.php:30`). Catálogo.

#### `d_campana` (`rest/d_campana.php`)
- GET sin params → `SELECT * FROM l_campana ORDER BY campana ASC` (`d_campana.php:30`). Catálogo.

#### `d_usuario` (`rest/d_usuario.php`)
- GET `a` → métricas de HOY del asesor: `COUNT id FROM t_gestiones WHERE asesor='$a' AND fecha='$hoy'` + `COUNT id FROM t_acuerdos WHERE asesor='$a' AND fregistro='$hoy'` (`d_usuario.php:40`,`:58`).

### 4.5 Mutaciones administrativas

#### `d_usuario_cambio` (`rest/d_usuario_cambio.php`) — **mutación vía GET**
- **Método:** GET (¡mutaciones por GET!) · **Param:** `t`.
- `t='avatar'`+`a`,`v` → `UPDATE t_usuarios SET avatar='$v' WHERE username='$a'` (`d_usuario_cambio.php:38`). Devuelve `$data['sql']` y `avatar`.
- `t='password'`+`a`,`v` → `UPDATE t_usuarios SET userpass=md5('$v') WHERE username='$a'` (`d_usuario_cambio.php:56`).
- **Defectos:** cambia password con un simple GET, sin auth, sin verificar identidad → cualquiera resetea la clave de cualquier usuario conociendo el `username`. Devuelve el SQL (fuga).

#### `admin_asesor` (`rest/admin_asesor.php`)
- **Métodos:** GET, POST. Selector `?t=`.
- **GET:** `all` → `usertype != 0 ORDER BY estado DESC` (`admin_asesor.php:75`); `texto`+`v` → `nombre LIKE '%$v%'` (`:94`); `cedula`+`v` → `cedula='$c'` (`:114`); `mensaje`+`v` → `t_mensaje WHERE asesor='$v'` (`:136`).
- **POST:**
  - `insert` → nuevo usuario: `usertype='1'`, `userpass=md5(0)` (¡clave inicial = md5 de cero!), `posicion='0'`, `estado='TRUE'` (`admin_asesor.php:167-168`).
  - `update` → `UPDATE t_usuarios SET nombre,telefono,username WHERE cedula='$cedula'` (`:218-222`).
  - `estado` → `UPDATE t_usuarios SET estado='$estado' WHERE cedula='$cedula'` (`:233-235`).
  - `delete` → **`DELETE FROM t_usuarios WHERE cedula='$cedula'` (borrado físico)** (`:245`).
  - `mensaje_insert` → patrón replace: `DELETE FROM t_mensaje WHERE asesor=` + `INSERT` (`:184-185`).
  - `mensaje_delete` → `DELETE FROM t_mensaje WHERE asesor=` (`:196`).
- **Defectos:** sin auth (cualquiera crea/borra usuarios admin); todos exponen `$data['sql']`; clave inicial predecible (`md5(0)`).

### 4.6 Sistema / huérfanos

#### `index` (`rest/index.php`)
- Respuesta fija JSON 200: `{success:"true", message:"Esta este es el index", sql:null, num_res:null, data:null}` (`index.php:8-13`). Instancia `new conn` innecesariamente.

#### `error` (`rest/error.php`)
- Respuesta fija JSON 404: `{success:"error", message:"Ruta no encontrada", ...}` (`error.php:8-13`). La invoca el dispatcher cuando `Router::getAction` lanza `Exception` (`Api.php:13-15`).

#### `clientes` (`rest/clientes.php`) — huérfano
- **Sin ruta ni método en el controller.** Solo alcanzable invocando el archivo directo (`…/app/rest/clientes.php`), saltando el dispatcher.
- CRUD completo sobre tabla `clientes` (**no existe en la base** — tables.json no la contiene → todas las queries fallarían):
  - GET `c` → por cédula; sin `c` → all (`clientes.php:37-67`).
  - POST: con `?f=` → solo guarda PDF; sin `?f=` → INSERT + `SAVE_FILE` (`clientes.php:70-95`).
  - PUT: usa `get_data_put()` (único consumidor de `lib/Put.php`) → UPDATE (`:97-122`).
  - DELETE `c` → `DELETE FROM clientes WHERE cedula='$cedula'` (`:131-139`).
  - `SAVE_FILE($cedula)` → `move_uploaded_file(..., 'file/'.$cedula.'.pdf')` (`:141-143`).
- **Conclusión:** módulo de clientes con upload de PDF, **muerto**: ni ruta ni tabla.

---

## 5. Hallazgos de seguridad por endpoint (resumen)

| Defecto | Dónde | Severidad |
|---|---|---|
| Inyección SQL (concatenación) | **TODOS** los endpoints | Crítica |
| Sin autenticación | **TODOS** | Crítica |
| Mutación de password por GET sin verificar identidad | `d_usuario_cambio` (`:56`) | Crítica |
| Creación/borrado de usuarios sin auth | `admin_asesor` (`:167`,`:245`) | Crítica |
| Borrado físico de usuarios | `admin_asesor` (`:245`) | Alta |
| Borrado oculto de alertas al gestionar | `g_gestiones` (`:110`) | Alta (negocio) |
| Password en query string + md5 sin salt + hash en respuesta | `login` (`:15-28`) | Alta |
| Clave inicial predecible `md5(0)` | `admin_asesor` (`:165`) | Media |
| Fuga del SQL en la respuesta | `g_email`,`b_*`,`d_usuario_cambio`,`admin_asesor` | Media |
| CORS `*` | TODOS | Media |
| Credenciales DB hardcodeadas | `lib/DB.php:5` | Alta |
| Año 2023 hardcodeado (filtros mensuales rotos) | `b_acuerdos`,`b_resumen` | Media (negocio) |

---

## 6. Evidencia

- Framework: `cegroup/api/api/index.php`, `cegroup/api/lib/{initial,Api,Router,Restapi,DB,Put}.php`, `cegroup/api/app/controllers/ApiController.php`, `cegroup/api/app/http/routes.php`.
- Endpoints (24 leídos completos): `cegroup/api/app/rest/*.php`.
- Cruce BD: `Resume/CEGROUP/_evidence/tables.json` (existencia de tablas), `column_profiles.json` (`t_base`, `t_pagos`, `t_acuerdos`, `t_resumen`).
- `No verificado:` origen de datos de `t_pagos` (existe, 0 filas, sin importador en este código).
