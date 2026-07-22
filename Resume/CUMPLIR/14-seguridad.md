# 14 — Seguridad (consolidado)

> Inventario de vulnerabilidades del sistema legacy **CUMPLIR** (`cumplir.net`), verificadas contra el código en `PROYECTO_CUMPLIR/cumplir/`. Rutas relativas a `cumplir/`. Cada hallazgo cita `archivo:línea` o evidencia de BD (`_evidence/`).
>
> **Veredicto global:** misma clase de sistema que CEGROUP, igual de comprometido. Sin autenticación en el API, SQL injection en prácticamente todos los endpoints (REST + Flight + importadores), un pasamanos de SQL arbitrario sin auth, toma de cualquier cuenta por GET, subida de archivos arbitrarios al webroot, y operaciones masivas destructivas (OCULTAR, REASIGNACION) sin auth ni auditoría. **Agravantes propios de CUMPLIR:** un módulo Flight adicional sin auth que además **imprime el SQL crudo** (`echo $sql`), y **10 de 13 cuentas** con la contraseña por defecto `0`. Cualquiera con la URL lee y destruye toda la base sin credenciales. Es una brecha activa.

## Clasificación de severidad

| Sev | Significado | Cantidad |
|---|---|---:|
| **P0** | Compromiso total / RCE / robo o destrucción de datos sin credenciales | 9 |
| **P1** | Escalada de privilegios, toma de cuenta, fuga masiva de datos | 6 |
| **P2** | Exposición de credenciales/criptografía débil, fuga parcial | 5 |
| **P3** | Endurecimiento / defensa en profundidad faltante | 3 |

---

## P0 — Compromiso total sin credenciales

### P0-1 · SQL injection en todos los endpoints REST
**Descripción.** Los 24 archivos de `api/app/rest/` construyen SQL concatenando `$_GET`/`$_POST` sin escapar ni parametrizar. No hay `mysqli_real_escape_string` ni prepared statements en ningún punto. La capa `format_post()`/`mb_strtoupper` no escapa (sube a mayúsculas, no sanea comillas).

**Evidencia (muestra; el patrón es universal):**
- `api/app/rest/login.php:14-17` — `$user=$_GET['u']; $pass=md5($_GET['p']); ... "WHERE username='$user' AND userpass='$pass'"`. Bypass clásico: `?u=admin'-- -&p=x`.
- `api/app/rest/admin_asesor.php:94,114,136,167-168,184-185,218-222,233-235,245` — el endpoint con más superficie (CRUD de usuarios).
- `api/app/rest/g_gestiones.php:100-110`, `g_resumen.php:107-108`, `g_acuerdos.php:66-68`, `g_alertas.php:99-100`, `g_telefonos.php:69-72`, `g_estados.php:78-79`, `phone.php:25`.
- Lecturas: `b_data.php:48,68,71,93,96,118,121`, `b_datafilter.php:49,74,98,122`, `b_acuerdos.php:46,67,86`, `b_alertas.php:47,68,89,111-113`, `b_resumen.php:33,44,55,73,83,93`, `d_usuario.php:40,59`, `g_operacion.php:87`, `g_mensaje.php:31`, `g_aportes.php:41,58`.

**Impacto.** Lectura completa de `u815310395_data` (PII de ~48.5k deudores + codeudores + garantes: cédulas, teléfonos, nombres), modificación y borrado arbitrario, y según permisos del usuario MySQL, escritura de archivos vía `INTO OUTFILE`. Bypass de autenticación trivial.

**Recomendación v2.** ORM con queries parametrizadas exclusivamente. Prohibir concatenación de SQL por lint. Usuario de BD con permisos mínimos (sin `FILE`).

---

### P0-2 · Pasamanos de SQL arbitrario sin autenticación
**Descripción.** `api/file/sql/user/acue/index.php:12` lee `$sql=$_GET['sql']` y lo ejecuta directo (`:36` → `$obj->query($sql)`). Sin validación, sin allowlist, sin sesión. **El subsistema `api/file/sql/` SÍ existe en CUMPLIR** (verificado con `find`; el doc previo lo daba por inexistente).

**Evidencia.** `api/file/sql/user/acue/index.php:12,36`. La conexión usa `api/lib/DB.php` (usuario de escritura).

**Impacto.** Ejecución de SQL arbitrario por cualquier visitante:
`https://cumplir.net/api/file/sql/user/acue/?sql=DROP%20TABLE%20t_base` o exfiltración de `t_usuarios` con hashes. Una sola URL roba o destruye toda la BD. Es el peor hallazgo del sistema.

**Recomendación v2.** Eliminar el archivo. Nunca exponer un parámetro `sql`. Exportaciones vía endpoints tipados con auth + filtros server-side.

---

### P0-3 · Sin autenticación en la capa API
**Descripción.** El dispatcher REST (`api/lib/Api.php`, `api/lib/Router.php`, `api/lib/Restapi.php`) no verifica sesión, token ni origen antes de servir un endpoint. `api/api/index.php` solo hace `session_start()`, sin chequear `$_SESSION`.

**Evidencia.** Barrido `grep auth|token|_SESSION` en `api/lib/*.php` → cero coincidencias de gate. `Restapi::render()` hace `require APP_PATH."rest/".$view.".php"`.

**Impacto.** Cada endpoint (`g_*`, `b_*`, `d_*`, `admin_asesor`, `login`, `phone`) es invocable por cualquiera que conozca la URL, sin login. La única "protección" es el gate del front (`public/index.php`), que no aplica al API.

**Recomendación v2.** Guard JWT global en todos los endpoints. Denegar por defecto.

---

### P0-4 · Bypass de sesión: `session.php?a=on&t=0`
**Descripción.** `app/php/session.php` activa una sesión confiando en parámetros GET: si `a=on`, marca `$_SESSION["session"]='ACTIVE'` y `$_SESSION["session_type"]=$_GET['t']`, sin verificar credenciales.

**Evidencia.** `app/php/session.php:3-7`:
```php
$action=$_GET['a'];
if($action=='on'){
  $type=$_GET['t'];
  $_SESSION["session"]='ACTIVE';
  $_SESSION["session_type"]=$type;
}
```
El front lo invoca con el `usertype` del login (`index.php:134`). El rol llega del cliente.

**Impacto.** Visitar `https://cumplir.net/app/php/session.php?a=on&t=0` otorga **sesión de administrador** sin login (`menu.php:20` muestra el menú admin). Bypass completo de la UI.

**Recomendación v2.** Sesión solo server-side tras validar credenciales. El rol nunca del cliente. Tokens firmados.

---

### P0-5 · Upload sin validación → RCE potencial
**Descripción.** Los `upload.php` de cada dominio mueven el archivo subido al directorio web `file/` conservando el nombre original, sin validar extensión, MIME ni tamaño.

**Evidencia.** `DATA/UPDATE/ASIGNACION/upload.php:6-8` (idéntico en BASE, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS, REASIGNACION):
```php
$src=$_FILES['file']['tmp_name'];
$filename=$_FILES['file']['name'];
move_uploaded_file($src,'file/'.$filename);
```
Variante adicional: `api/app/rest/clientes.php:142` — `move_uploaded_file($_FILES['file']['tmp_name'],'file/'.$cedula.'.pdf')` (sin validación de tipo).

**Impacto.** Subir `shell.php` lo deja servible en `DATA/UPDATE/BASE/file/shell.php` → ejecución remota de código. Combinado con P0-3 (sin auth), explotable desde cualquier origen.

**Recomendación v2.** Allowlist de extensiones (`.csv`/`.xlsx`), validar MIME real, renombrar a UUID, almacenar fuera del webroot (R2/S3), procesar en cola.

---

### P0-6 · SQL injection vía contenido del CSV (importadores)
**Descripción.** Los `procesar.php` leen el CSV celda a celda e interpolan cada una en un `INSERT`/`UPDATE` sin escapar. Aplica a los dos árboles (`DATA/UPDATE/*` y `api/file/update/*`, incl. `SUBIR_*`).

**Evidencia.** `DATA/UPDATE/BASE/procesar.php:10-44` (21 columnas, incluye `referencia`); `DATA/UPDATE/ASIGNACION/procesar.php:11-16`; `api/file/update/SUBIR_RESUMEN/procesar.php:10-29`; `api/file/update/SUBIR_GESTION/procesar.php:10-18`; etc.

**Impacto.** Un CSV con una celda `'); DROP TABLE t_base;-- ` ejecuta SQL arbitrario durante la importación. Como el upload no valida (P0-5) y no hay auth (P0-3), el vector es accesible.

**Recomendación v2.** Importadores con parámetros enlazados, validación de esquema por columna, procesamiento transaccional en worker.

---

### P0-7 · OCULTAR borra `t_decil` sin auth ni auditoría
**Descripción.** `DATA/OCULTAR/procesar.php` lee `OCULTAR.csv` del disco (`chmod 0777`) y borra cada operación de `t_decil`. Sin sesión, sin confirmación, sin log de quién/cuándo.

**Evidencia.** `DATA/OCULTAR/procesar.php:13,17-18`:
```php
chmod("OCULTAR.csv",0777);
... $sql="DELETE FROM `t_decil` WHERE `t_decil`.`operacion` = '$operacion'";
```

**Impacto.** Combinado con P0-5, un atacante sube su `OCULTAR.csv` y vacía `t_decil`, ocultando operaciones del detalle de gestión (`g_operacion` hace `INNER JOIN t_decil`). SQLi adicional por celda. `chmod 0777` deja el archivo world-writable.

**Recomendación v2.** Operaciones destructivas solo con rol admin autenticado, soft-delete + auditoría, confirmación explícita. Nunca leer rutas fijas world-writable.

---

### P0-8 · REASIGNACION actualiza `t_asignacion` en masa sin auth
**Descripción.** `DATA/UPDATE/REASIGNACION/procesar.php` lee `REASIGNACION.csv` y ejecuta `UPDATE t_asignacion SET asesor WHERE operacion`, sin auth, con `set_time_limit(0)` e `ini_set('max_execution_time',0)`.

**Evidencia.** `REASIGNACION/procesar.php:3-5,14-21`:
```php
ini_set('max_execution_time',0); set_time_limit(0);
... $sql="UPDATE `t_asignacion` SET `asesor`='$asesor' WHERE `operacion`='$operacion';";
```

**Impacto.** Cualquiera (sin auth) puede reasignar toda la cartera a un asesor (o a un valor inválido), interrumpiendo la operación. Como `t_asignacion` tiene filas duplicadas por operación (ratio 1.223×), actualiza todas las filas de la operación. SQLi por celda; sin log.

**Recomendación v2.** Reasignación solo con rol admin autenticado, transaccional, con auditoría y FK/unique en `t_asignacion`.

---

### P0-9 · Credenciales de BD hardcodeadas en el repo (3 ubicaciones)
**Descripción.** Usuario y contraseña de producción de MySQL embebidos en el código fuente, **duplicados en tres archivos**.

**Evidencia (idéntica línea en los tres):**
- `api/lib/DB.php:5` — `parent::__construct("localhost","u815310395_data","«REDACTADO»","u815310395_data");`
- `DATA/UPDATE/DB.php:5` — misma cadena.
- `REPORTES/REPORTES/app/db/db.php:4` — misma cadena.
- (`DATA/OCULTAR/procesar.php` define su propia clase de conexión local.)

**Impacto.** Cualquiera con acceso al código (repo, backup, LFI) obtiene credenciales válidas de producción. La contraseña (`«REDACTADO»`) es débil y predecible. Tres copias = tres puntos de fuga y de drift.

**Recomendación v2.** Credenciales en variables de entorno (`DATABASE_URL`), una sola fuente. Rotar la contraseña ya filtrada. Usuario de BD de permisos mínimos.

---

## P1 — Escalada de privilegios / toma de cuenta / fuga masiva

### P1-1 · Reset de contraseña por GET sin prueba de identidad
**Descripción.** `d_usuario_cambio` cambia la contraseña de **cualquier** usuario recibiendo solo el username destino y la nueva contraseña por GET. No exige contraseña actual, ni sesión, ni que el solicitante sea ese usuario.

**Evidencia.** `api/app/rest/d_usuario_cambio.php:53-56`:
```php
$a=$_GET['a']; $p=md5($_GET['v']);
$sql="UPDATE `t_usuarios` SET `userpass`='$p' WHERE `t_usuarios`.`username`='$a'";
```

**Impacto.** Toma de cuenta total: `GET d_usuario_cambio?a=JURIDICOS.CUMPLIR&t=password&v=x` fija la contraseña del admin. Igual `t=avatar` (`:38`) modifica avatares ajenos.

**Recomendación v2.** Cambio de contraseña con sesión del propio usuario + contraseña actual; reset administrativo solo con rol admin y flujo de un solo uso. POST/PATCH, nunca GET.

---

### P1-2 · `admin_asesor` crea, edita, borra y activa usuarios sin auth
**Descripción.** Un único endpoint expone CRUD completo de `t_usuarios` (incluyendo borrado físico y activación) sin verificar rol ni sesión.

**Evidencia.** `api/app/rest/admin_asesor.php`: `INSERT_ASESOR` (165-168, `usertype='1'`, `estado='TRUE'`), `UPDATE_ASESOR` (218-222), `ESTADO_ASESOR` (233-235), `DELETE_ASESOR` (245, `DELETE FROM t_usuarios WHERE cedula`). Selectores (74-147) hacen `SELECT *` (incluye `userpass`).

**Impacto.** Cualquiera crea un usuario y entra; o borra todos los asesores; o reactiva cuentas. Sin auditoría.

**Recomendación v2.** CRUD de usuarios solo con rol admin autenticado, métodos HTTP correctos, nunca devolver el hash.

---

### P1-3 · Hash de contraseña devuelto al cliente
**Descripción.** El login y los listados de usuarios devuelven la fila completa de `t_usuarios`, incluyendo `userpass` (hash MD5).

**Evidencia.** `api/app/rest/login.php:17` (`SELECT * FROM t_usuarios ...` y la fila íntegra al cliente — usada en `index.php:133`); `admin_asesor.php:75,82` (`SELECT *`); `api/file/sql/admin/ases/index.php` exporta usuarios a XLS.

**Impacto.** El hash MD5 sin sal viaja al navegador y a `localStorage`/historial. Con P2-2 (md5 de `0`) y P1-1, compromiso de cuentas.

**Recomendación v2.** Nunca seleccionar ni serializar el campo de contraseña. DTO de respuesta que excluye credenciales.

---

### P1-4 · Exportadores admin que vuelcan tablas sin filtro
**Descripción.** Endpoints de exportación a XLS ejecutan `SELECT *` sin límite ni filtro de propiedad, e interpolan `$_GET` (SQLi).

**Evidencia.** `api/file/sql/admin/base/all.php` (`SELECT * FROM t_base`, ~48.5k filas con PII); `admin/gest/index.php` (`t_gestiones`, ~801k filas); `admin/ases/index.php` (usuarios con hash); `admin/acue/index.php`, `admin/reca/index.php` (interpolan `$_GET['a']/$_GET['f']`).

**Impacto.** Descarga masiva de PII por cualquiera (sin auth, P0-3). DoS por exportar 801k filas en una request.

**Recomendación v2.** Exportaciones con auth + autorización por rol, paginación/streaming, columnas mínimas.

---

### P1-5 · Login y cambio de datos sensibles vía GET (credenciales en URL)
**Descripción.** El login viaja por GET con usuario y contraseña en query string; igual el cambio de contraseña/avatar.

**Evidencia.** `api/app/rest/login.php:14-15`; `index.php:115` (`'login?u='+user+'&p='+pass`); `app/action/perfil.js` (nueva contraseña en query).

**Impacto.** Credenciales en logs de servidor web, proxies, historial del navegador y cabecera `Referer`. Persisten en texto plano fuera de la app (aunque CUMPLIR usa HTTPS, no protege logs ni historial).

**Recomendación v2.** Autenticación por POST sobre HTTPS; nunca credenciales en query string.

---

### P1-6 · Módulo Flight sin auth: cálculo/reset de reportes y `echo $sql`
**Descripción.** El módulo `REPORTES/REPORTES/` (Flight) no verifica sesión en ninguna ruta. Sus endpoints recalculan (`POST /<dom>/all`) y **resetean** (`DELETE /<dom>/all`, `TRUNCATE`) tablas de reporte; además uno de sus tools **imprime el SQL crudo** (`echo $sql`), y todos sufren SQLi vía `$_POST['date']`.

**Evidencia.**
- Sin auth: `grep session|auth|_SESSION` en `REPORTES/REPORTES/src/*` → cero coincidencias.
- `echo $sql`: `REPORTES/REPORTES/src/tools/Gestiones.php:11` — `echo $sql = "SELECT DISTINCT operacion FROM t_gestiones WHERE asesor='$asesor' AND fecha='$date'";`.
- SQLi: `src/controllers/{Gestiones,Acuerdos,Proyeccion}.php:10` (`$date=$_POST['date']`) → interpolado en `tools/*.php`.
- `TRUNCATE` sin auth: `tools/Gestiones.php:36`, `tools/Acuerdos.php:68`, `tools/Proyeccion.php:69`.

**Impacto.** Cualquiera invoca `DELETE https://cumplir.net/REPORTES/REPORTES/gestiones/all` y borra los reportes; o inyecta SQL por `date`; el `echo $sql` revela estructura de queries y facilita afinar inyecciones.

**Recomendación v2.** Auth en el módulo (o eliminarlo y migrar a endpoints tipados). Quitar todo `echo` de SQL. Parametrizar.

---

## P2 — Criptografía débil / exposición de credenciales

### P2-1 · Hashing MD5 sin sal para contraseñas
**Descripción.** Las contraseñas se almacenan como `md5($pass)` (32 hex, sin sal, sin pepper, sin costo).

**Evidencia.** `login.php:15`, `d_usuario_cambio.php:54`, `admin_asesor.php:165`. Confirmado: `_evidence/column_profiles.json` → `t_usuarios.userpass` 32 hex.

**Impacto.** MD5 se quiebra por GPU a miles de millones de hashes/seg; sin sal, rainbow tables y deduplicación aplican. Una fuga de `t_usuarios` revela contraseñas de inmediato.

**Recomendación v2.** `bcrypt`/`argon2id` con sal por usuario.

---

### P2-2 · Contraseñas por defecto `md5('0')` en producción (10 de 13)
**Descripción.** `admin_asesor` crea cada usuario con `userpass=md5(0)`=`cfcd208495d565ef66e7dff9f98764da` (contraseña literal `0`). Nunca se fuerza cambio.

**Evidencia.** `admin_asesor.php:165`. Datos: `_evidence/quality.json` → `password_md5_zero: total 13, md5_of_zero 10`. **10 de 13 usuarios tienen contraseña `0` hoy.** **[difiere de CEGROUP]**: allí eran 6/65 (9%); aquí es el 77%.

**Impacto.** Acceso directo con `?u=<username>&p=0` para 10 cuentas reales, incluidos admins (`usertype=0`: `JURIDICOS.CUMPLIR`, `UBINEY.CERON`, `CUENTA.CUMPLIR` — `_evidence/samples/t_usuarios.json`). Sin flag `mustChangePassword`.

**Recomendación v2.** Contraseña inicial aleatoria por correo + cambio obligatorio al primer login.

---

### P2-3 · CORS abierto a cualquier origen (`*`)
**Descripción.** Todos los endpoints REST emiten `Access-Control-Allow-Origin: *` y permiten todos los métodos.

**Evidencia.** Cabecera en cada `api/app/rest/*.php`, p. ej. `login.php:2`, `admin_asesor.php:2`, `g_gestiones.php:2`.

**Impacto.** Cualquier web invoca el API desde el navegador de una víctima. Con P0-3 (sin auth) y sin CSRF, cualquier página maliciosa ejecuta acciones contra la BD.

**Recomendación v2.** Allowlist explícita de orígenes; CSRF tokens para mutaciones.

---

### P2-4 · Fuga de la consulta SQL en las respuestas (`$data['sql']` / `data.data`)
**Descripción.** Múltiples endpoints devuelven el SQL ejecutado dentro del JSON de respuesta.

**Evidencia.** `$data['sql']`: `b_data.php:58,83,108,133`, `b_acuerdos.php:50,58,72,79,90,97`, `b_alertas.php:50,71,92,116`, `b_datafilter.php:52,78,102,126`, `b_resumen.php:36`, `admin_asesor.php:78,86,97,...,247`, `d_usuario_cambio.php:40,58`, `d_usuario.php:44`. Los `g_*` POST devuelven el SQL en `data.data` al fallar: `g_gestiones.php:114`, `g_resumen.php:114`, `g_telefonos.php:78`, `g_acuerdos.php:74`, `g_estados.php:85`, `g_alertas.php:105`, `phone.php:30`. El front incluso loguea `data.sql` en consola (`index.php:172`).

**Impacto.** Revela tablas/columnas y estructura → facilita afinar inyecciones (P0-1).

**Recomendación v2.** Nunca devolver SQL. Errores genéricos; detalle solo en logs server-side.

---

### P2-5 · `echo $sql` en el módulo Flight (fuga directa al output)
**Descripción.** Más grave que P2-4: un tool de Flight **imprime** el SQL como salida cruda de la respuesta HTTP (no dentro de un JSON, sino `echo` directo).

**Evidencia.** `REPORTES/REPORTES/src/tools/Gestiones.php:11` — `echo $sql = "SELECT DISTINCT operacion FROM t_gestiones WHERE asesor='$asesor' AND fecha='$date'";`.

**Impacto.** Cualquier llamada al cálculo de gestiones imprime la query con los parámetros → fuga de estructura y de valores, y ruido que confirma inyecciones.

**Recomendación v2.** Eliminar el `echo`. Logging server-side si se necesita depurar.

---

## P3 — Endurecimiento / defensa en profundidad

### P3-1 · `set_time_limit(60000000000)` (e `ini_set max_execution_time 0`) en importadores
**Descripción.** Los `procesar.php` deshabilitan de hecho el timeout; REASIGNACION usa además `ini_set('max_execution_time',0)`.

**Evidencia.** `DATA/UPDATE/*/procesar.php` (línea 3), `api/file/update/*/procesar.php` (línea 2), `DATA/UPDATE/REASIGNACION/procesar.php:3-5`.

**Impacto.** Procesos colgados consumen conexiones indefinidamente; amplifica un DoS o un import malicioso (P0-6).

**Recomendación v2.** Cola con límites y reintentos acotados; sin timeouts infinitos en el request HTTP.

---

### P3-2 · `chmod(...,0777)` sobre el CSV antes de leerlo
**Descripción.** Los importadores hacen world-writable el CSV en disco antes de procesarlo.

**Evidencia.** `DATA/OCULTAR/procesar.php:13`, `api/file/update/*/procesar.php` (línea 5), `DATA/UPDATE/*/procesar.php`.

**Impacto.** Archivo world-writable en el webroot; otro proceso/usuario del shared hosting puede manipularlo.

**Recomendación v2.** No usar archivos world-writable; almacenamiento fuera del webroot.

---

### P3-3 · Capa de "codificación" (`CODING`) no-op que aparenta protección
**Descripción.** `array_map(CODING,$d)` y `format_post()` se usan como si sanearan, pero `CODING` está definido como `null` y `format_post` solo hace `mb_strtoupper`.

**Evidencia.** `api/api/index.php` (`define("CODING",null)` y `format_post`). `array_map(null,$array)` devuelve el array sin cambios.

**Impacto.** Falsa sensación de saneamiento; no protege contra nada (PII y hash salen intactos). Deuda que oculta el riesgo real.

**Recomendación v2.** Eliminar la capa muerta. Saneamiento/serialización explícita con DTOs.

---

## Cadena de explotación de referencia (sin credenciales)

1. `GET app/php/session.php?a=on&t=0` → sesión admin en la UI (P0-4).
2. `GET api/file/sql/user/acue/?sql=SELECT username,userpass FROM t_usuarios` → volcado de hashes (P0-2 + P1-3), o directamente `?u=JURIDICOS.CUMPLIR&p=0` (P2-2).
3. `GET api/api/d_usuario_cambio?a=<admin>&t=password&v=x` → toma de cuenta (P1-1).
4. POST a `DATA/UPDATE/BASE/upload.php` con `shell.php` → `GET DATA/UPDATE/BASE/file/shell.php` → RCE (P0-5).
5. `DELETE REPORTES/REPORTES/gestiones/all` o subir `OCULTAR.csv` + `DATA/OCULTAR/procesar.php` → destrucción de datos (P1-6 / P0-7).

Cada paso es independiente; basta uno (P0-2 o P2-2) para comprometer todo.

## Resumen

| ID | Hallazgo | Sev |
|---|---|---|
| P0-1 | SQLi en todos los endpoints REST | P0 |
| P0-2 | Pasamanos `?sql=` sin auth (`file/sql/user/acue`) | P0 |
| P0-3 | Sin autenticación en la capa API | P0 |
| P0-4 | Bypass de sesión `session.php?a=on&t=0` | P0 |
| P0-5 | Upload sin validación → RCE | P0 |
| P0-6 | SQLi vía contenido del CSV (2 árboles + SUBIR_*) | P0 |
| P0-7 | OCULTAR borra `t_decil` sin auth ni log | P0 |
| P0-8 | REASIGNACION actualiza `t_asignacion` en masa sin auth | P0 |
| P0-9 | Credenciales de BD hardcodeadas (3 ubicaciones) | P0 |
| P1-1 | Reset de contraseña por GET sin identidad | P1 |
| P1-2 | `admin_asesor` CRUD de usuarios sin auth | P1 |
| P1-3 | Hash de contraseña devuelto al cliente | P1 |
| P1-4 | Exportadores `SELECT *` sin filtro | P1 |
| P1-5 | Login/credenciales por GET en la URL | P1 |
| P1-6 | Módulo Flight sin auth (cálculo/reset/SQLi) | P1 |
| P2-1 | MD5 sin sal | P2 |
| P2-2 | Contraseñas por defecto `md5('0')` (10/13) | P2 |
| P2-3 | CORS `*` | P2 |
| P2-4 | Fuga del SQL en respuestas REST | P2 |
| P2-5 | `echo $sql` en Flight (fuga directa) | P2 |
| P3-1 | `set_time_limit` infinito en importadores | P3 |
| P3-2 | `chmod 0777` sobre el CSV | P3 |
| P3-3 | Capa `CODING` no-op (falsa protección) | P3 |

## Diferencias de seguridad vs CEGROUP

- **Peor:** contraseñas por defecto **10/13 (77%)** vs 6/65 (9%); módulo Flight adicional sin auth con `echo $sql` (P1-6/P2-5); operaciones destructivas nuevas sin auth (OCULTAR P0-7, REASIGNACION P0-8); tercera copia de credenciales (Flight).
- **Igual:** SQLi universal, sin auth API, bypass de sesión, upload sin validación, MD5, CORS `*`, pasamanos `?sql=`, fuga de SQL.
- **Mejor (marginal):** API por HTTPS (`cumplir.net`), no HTTP. Mitiga MITM pero NO mitiga login por GET en logs/historial (P1-5). No hay `elimina.php` de `t_base` visible en CUMPLIR (existía en CEGROUP); su equivalente destructivo es OCULTAR sobre `t_decil`.
