# 14 — Seguridad (consolidado)

> Inventario de vulnerabilidades del sistema legacy **CEGROUP** (`gestioncobranza.com`), verificadas contra el código en `PROYECTO_CEGROUP/cegroup/`. Todas las rutas son relativas a `cegroup/`. Cada hallazgo cita `archivo:línea` o evidencia de BD (`_evidence/`).
>
> **Veredicto global:** el sistema no tiene capa de autenticación en el API, sufre SQL injection en prácticamente todos sus endpoints, expone un pasamanos de SQL arbitrario sin auth, permite tomar control de cualquier cuenta por GET y admite subida de archivos arbitrarios al webroot. Cualquiera con la URL puede leer y destruir toda la base de datos sin credenciales. Esto es una brecha activa, no un riesgo teórico.

## Clasificación de severidad

| Sev | Significado | Cantidad |
|---|---|---:|
| **P0** | Compromiso total / RCE / robo o destrucción de datos sin credenciales | 8 |
| **P1** | Escalada de privilegios, toma de cuenta, fuga masiva de datos | 5 |
| **P2** | Exposición de credenciales/criptografía débil, fuga parcial | 4 |
| **P3** | Endurecimiento / defensa en profundidad faltante | 3 |

---

## P0 — Compromiso total sin credenciales

### P0-1 · SQL injection en todos los endpoints REST
**Descripción.** Los 24 archivos de `api/app/rest/` construyen SQL concatenando `$_GET`/`$_POST` sin escapar ni parametrizar. No se usa `mysqli_real_escape_string`, prepared statements ni binding en ningún punto.

**Evidencia (muestra; el patrón es universal):**
- `api/app/rest/login.php:14-17` — `$user=$_GET['u']; ... "WHERE username='$user' AND userpass='$pass'"`. Bypass de login clásico: `?u=admin'-- -&p=x`.
- `api/app/rest/admin_asesor.php:92-94,114,160-168,245` — 20 interpolaciones de `$_GET`/`$_POST` (el endpoint con más superficie).
- `api/app/rest/g_email.php:40`, `g_estados.php`, `g_acuerdos.php`, `g_alertas.php`, `g_telefonos.php`, `b_data.php`, `b_acuerdos.php`, `b_alertas.php`, `b_datafilter.php`, `b_resumen.php`, `d_usuario.php:40,58`, `d_usuario_cambio.php:38,56`, `clientes.php`, `g_gestiones.php`, `g_resumen.php` (16 interpolaciones).

Conteo de interpolaciones `$_GET`/`$_POST` por archivo (evidencia de barrido):
`admin_asesor.php` 20 · `g_resumen.php` 16 · `clientes.php` 10 · `b_datafilter.php` 9 · `b_alertas.php` 8 · `g_acuerdos.php` 7 · `g_gestiones.php` 6 · `g_alertas.php` 6 · `g_telefonos.php` 5 · `d_usuario_cambio.php` 5 · `asesor.php` 5 · `g_estados.php` 3 · `g_email.php` 3 · `b_acuerdos.php` 3 · `login.php` 2 · `b_resumen.php` 2 · `b_data.php` 2 · `g_operacion.php` 1 · `g_mensaje.php` 1 · `g_aportes.php` 1 · `d_usuario.php` 1.

**Impacto.** Lectura completa de `data_cegroup` (datos personales: cédulas, teléfonos, correos, nombres de ~55k deudores + codeudores + garantes), modificación y borrado arbitrario, y dependiendo de permisos del usuario MySQL, escritura de archivos vía `INTO OUTFILE`. Bypass de autenticación trivial.

**Recomendación v2.** Prisma/ORM con queries parametrizadas exclusivamente. Prohibir concatenación de SQL por lint. Usuario de BD con permisos mínimos (sin `FILE`).

---

### P0-2 · Pasamanos de SQL arbitrario sin autenticación
**Descripción.** `api/file/sql/user/acue/index.php:12` lee `$sql = $_GET['sql']` y lo ejecuta directamente (`api/file/sql/user/acue/index.php:36` → `$obj->query($sql)`). No hay validación, ni allowlist, ni sesión.

**Evidencia.** `api/file/sql/user/acue/index.php:12,36`. La conexión se abre con `include("../../../../lib/DB.php")` que apunta a `data_cegroup` con un usuario de escritura.

**Impacto.** Ejecución de SQL arbitrario por cualquier visitante:
`https://gestioncobranza.com/api/file/sql/user/acue/?sql=DROP%20TABLE%20t_base` o exfiltración de `t_usuarios` con hashes. Es el peor hallazgo del sistema: una sola URL destruye o roba toda la BD.

**Recomendación v2.** Eliminar este archivo. Nunca exponer un parámetro `sql`. Exportaciones vía endpoints tipados con auth + filtros server-side.

---

### P0-3 · Sin autenticación en la capa API
**Descripción.** El dispatcher del API (`api/lib/Restapi.php`, `api/lib/Router.php`, `api/lib/Api.php`) no verifica sesión, token ni origen antes de servir un endpoint. Barrido `grep -n "auth|token|session|_SESSION" api/lib/*.php` → **cero coincidencias**. `Restapi::render()` simplemente hace `require APP_PATH."rest/".$view.".php"` (`api/lib/Restapi.php:8`).

**Evidencia.** `api/lib/Restapi.php:1-11` (sin gate); `api/api/index.php:1-23` (sólo `session_start()`, sin chequeo de sesión).

**Impacto.** Cada endpoint (`g_*`, `b_*`, `d_*`, `admin_asesor`, `login`) es invocable por cualquiera que conozca la URL, sin login. La única "protección" del sistema es el gate del front (`public/index.php`), que no aplica al API.

**Recomendación v2.** Guard de JWT global en todos los endpoints (como el `JwtAuthGuard` global de la arquitectura objetivo). Denegar por defecto.

---

### P0-4 · Bypass de sesión: `session.php?a=on&t=0`
**Descripción.** `app/php/session.php` activa una sesión confiando ciegamente en parámetros GET. Si `a=on`, marca `$_SESSION["session"]='ACTIVE'` y `$_SESSION["session_type"]=$_GET['t']` — sin verificar credenciales previas.

**Evidencia.** `app/php/session.php:3-7`:
```php
$action=$_GET['a'];
if($action=='on'){
  $_SESSION["session"]='ACTIVE';
  $_SESSION["session_type"]=$_GET['t'];
}
```
El front lo invoca con el `usertype` que devuelve el login: `index.php:132` → `let url_session='app/php/session.php?a=on&t='+data_user['usertype']`. El tipo de sesión proviene del cliente.

**Impacto.** Visitar `https://gestioncobranza.com/app/php/session.php?a=on&t=0` otorga **sesión de administrador** (`session_type=0`) sin login. El gate del front (`public/index.php:3`, `if(isset($_SESSION["session"]))`) queda satisfecho, y `menu.php:20` muestra el menú admin. Bypass completo de autenticación de la UI.

**Recomendación v2.** La sesión se crea sólo server-side tras validar credenciales. El rol nunca llega del cliente. Tokens firmados.

---

### P0-5 · Upload sin validación → RCE potencial
**Descripción.** Los `upload.php` de cada dominio mueven el archivo subido al directorio web `file/` conservando el nombre original, sin validar extensión, MIME ni tamaño.

**Evidencia.** `api/UPDATE/BASE/upload.php:6-9` (idéntico en `PROCESOS/upload.php`, `CARTERA`, `SALDOS`, `TELEFONOS`, `CAMPANA`, `DECIL`, `ASIGNACION`, `GESTIONES`):
```php
$src=$_FILES['file']['tmp_name'];
$filename=$_FILES['file']['name'];
$output_dir='file/'.$filename;
move_uploaded_file($src,$output_dir);
```

**Impacto.** Subir `shell.php` lo deja servible en `api/UPDATE/BASE/file/shell.php` → ejecución remota de código en el servidor. Combinado con P0-3 (sin auth) y CORS `*`, explotable desde cualquier origen.

**Recomendación v2.** Allowlist de extensiones (`.csv`/`.xlsx`), validar MIME real, renombrar a UUID, almacenar fuera del webroot (R2/S3) y procesar en cola. Nunca conservar el nombre del cliente.

---

### P0-6 · SQL injection vía contenido del CSV (importaciones)
**Descripción.** Los `procesar.php` leen el CSV línea a línea e interpolan cada celda en un `INSERT` sin escapar.

**Evidencia.** `api/UPDATE/BASE/procesar.php:9-44` (mismo patrón en todos los `procesar.php`):
```php
while($d=fgetcsv($fp,100000000,";")){
  $operacion=$d[0]; ...
  $sql="INSERT INTO t_base (...) VALUES ('$operacion', ... '$banco');";
  $obj->query($sql);
}
```
`api/UPDATE/GESTIONES/procesar.php:17-18` interpola `$d[...]` con asesor fijo `'MASIVO'`.

**Impacto.** Un CSV con una celda como `'); DROP TABLE t_base;-- ` ejecuta SQL arbitrario durante la importación. Como el upload no valida (P0-5) y no hay auth (P0-3), el vector es accesible.

**Recomendación v2.** Importadores con parámetros enlazados, validación de esquema por columna, y procesamiento transaccional en worker (BullMQ).

---

### P0-7 · Borrado masivo de `t_base` sin auth: `elimina.php`
**Descripción.** `api/UPDATE/elimina.php` lee `ELIMINAR.csv` del disco y borra cada operación de `t_base`. Sin sesión, sin confirmación, `set_time_limit(60000000000)` (sin timeout).

**Evidencia.** `api/UPDATE/elimina.php:4,7-15`:
```php
set_time_limit(60000000000);
chmod("ELIMINAR.csv",0777);
while($d=fgetcsv($fp,...,";")){
  $sql="DELETE FROM t_base WHERE operacion='$d[0]'"; $obj->query($sql);
}
```
Existe el gemelo `api/file/update/ELIMINAPRO/procesar.php` (segundo árbol de import).

**Impacto.** Combinado con P0-5, un atacante sube su propio `ELIMINAR.csv` y luego invoca `elimina.php` para vaciar la base de deudores. Además `chmod(...,0777)` deja el archivo world-writable.

**Recomendación v2.** Operaciones destructivas sólo con rol admin autenticado, soft-delete + auditoría, y confirmación explícita. Nunca leer rutas de disco fijas world-writable.

---

### P0-8 · Credenciales de BD hardcodeadas en el repo
**Descripción.** Usuario y contraseña de producción de MySQL embebidos en el código fuente versionado.

**Evidencia.** `api/lib/DB.php:5`:
```php
parent::__construct("localhost","user_cegroup","«REDACTADO»","data_cegroup");
```
La línea 4 deja además comentada la credencial de dev (`root`/sin pass). La conexión de producción real (de `_evidence/connection.json`) usa el mismo `user_cegroup` con permisos de escritura.

**Impacto.** Cualquiera con acceso al código (repo, backup, archivo `.php` filtrado, o LFI) obtiene credenciales válidas de la BD de producción. La contraseña (`«REDACTADO»`) es débil y predecible.

**Recomendación v2.** Credenciales en variables de entorno (`DATABASE_URL`), nunca en el repo. Rotar la contraseña ya filtrada. Usuario de BD de permisos mínimos.

---

## P1 — Escalada de privilegios / toma de cuenta / fuga masiva

### P1-1 · Reset de contraseña por GET sin prueba de identidad
**Descripción.** `d_usuario_cambio` cambia la contraseña de **cualquier** usuario recibiendo sólo el username destino y la nueva contraseña por GET. No exige contraseña actual, ni sesión, ni que el solicitante sea ese usuario.

**Evidencia.** `api/app/rest/d_usuario_cambio.php:52-57`:
```php
function PROCESS_PASS(){
  $a=$_GET['a']; $p=md5($_GET['v']);
  $sql="UPDATE t_usuarios SET userpass='$p' WHERE username='$a'";
}
```
El front lo llama por GET: `app/action/perfil.js:114` → `d_usuario_cambio?a='+perfil_user+'&t=password&v='+pass_1`. Como no hay auth (P0-3), el `a` puede ser cualquier username.

**Impacto.** Toma de cuenta total: `GET d_usuario_cambio?a=COORDINADOR&t=password&v=hola` fija la contraseña del coordinador y permite entrar como él. Lo mismo con el mismo endpoint `t=avatar` para modificar avatares ajenos (`d_usuario_cambio.php:34-47`).

**Recomendación v2.** Cambio de contraseña: requiere sesión válida del propio usuario + contraseña actual; reset administrativo sólo con rol admin y flujo de un solo uso. Método POST/PATCH, nunca GET.

---

### P1-2 · `admin_asesor` crea, edita, borra usuarios y cambia su estado sin auth
**Descripción.** Un único endpoint expone CRUD completo de `t_usuarios` (incluyendo borrado y activación) sin verificar rol ni sesión.

**Evidencia.** `api/app/rest/admin_asesor.php`:
- `INSERT_ASESOR()` líneas 159-173 — crea usuario con `usertype='1'`, `estado='TRUE'`.
- `UPDATE_ASESOR()` 212-227, `ESTADO_ASESOR()` 229-240 (activa/desactiva), `DELETE_ASESOR()` 242-250 (`DELETE FROM t_usuarios WHERE cedula='$cedula'`).
- Selectores 73-150 devuelven todos los usuarios con `SELECT *` (incluye `userpass`).

**Impacto.** Cualquiera puede crear un usuario nuevo y entrar; o borrar todos los asesores; o reactivar cuentas desactivadas. Sin auditoría.

**Recomendación v2.** CRUD de usuarios sólo con rol admin autenticado (`@Roles('admin')`), métodos HTTP correctos, y nunca devolver el hash de contraseña.

---

### P1-3 · Hash de contraseña devuelto al cliente
**Descripción.** El login y los listados de usuarios devuelven la fila completa de `t_usuarios`, incluyendo `userpass` (hash MD5).

**Evidencia.**
- `api/app/rest/login.php:17,22-24` — `SELECT * FROM t_usuarios ...` y luego `$data['data'][]=$d` (fila íntegra, con `userpass`).
- `api/app/rest/admin_asesor.php:75,82` — `SELECT *` y devuelve `array_map(CODING,$d)` con `CODING=null` (no-op, ver P3-3): el hash sale tal cual.
- `api/file/sql/admin/ases/index.php:9` — exporta a XLS todos los asesores con `SELECT *`.

**Impacto.** El hash MD5 sin sal viaja al navegador y queda en `localStorage`/historial. MD5 de un password corto se rompe por diccionario/rainbow en segundos. Combinado con P2-2 (md5 de `0`) y P1-1, compromiso de cuentas.

**Recomendación v2.** Nunca seleccionar ni serializar el campo de contraseña. DTO de respuesta explícito que excluye credenciales.

---

### P1-4 · Exportadores admin que vuelcan toda la tabla sin filtro
**Descripción.** Varios endpoints de exportación a XLS ejecutan `SELECT *` sin límite ni filtro de propiedad.

**Evidencia.**
- `api/file/sql/admin/base/all.php:10` — `SELECT * FROM t_base` (todos los deudores, ~55k filas con PII).
- `api/file/sql/admin/gest/index.php:26` — `SELECT * FROM t_gestiones` (~1.8M filas).
- `api/file/sql/admin/ases/index.php:9` — todos los usuarios (con hash, ver P1-3).
- `api/file/sql/admin/acue/index.php`, `reca/index.php` — exportan acuerdos/recaudo con `$_GET['a']`/`$_GET['f']` interpolados (SQLi adicional).

**Impacto.** Descarga masiva de PII y de toda la base de gestión por cualquiera (sin auth, P0-3). `gest/index.php?t=asesor&a=all` baja 1.8M filas en una request (DoS + fuga).

**Recomendación v2.** Exportaciones con auth + autorización por propiedad/rol, paginación/streaming server-side, y columnas mínimas.

---

### P1-5 · Login y cambio de datos sensibles vía GET (credenciales en URL)
**Descripción.** El login viaja por GET con usuario y contraseña en query string; igual el cambio de contraseña/avatar.

**Evidencia.**
- `api/app/rest/login.php:14-15` (método GET, `$_GET['u']`, `$_GET['p']`).
- `index.php:19` — `let url=http_data+'login?u='+user+'&p='+pass`.
- `app/action/perfil.js:114` — nueva contraseña en query (`...&v='+pass_1`).

**Impacto.** Credenciales en logs de servidor web, proxies intermedios, historial del navegador y cabecera `Referer`. Persisten en texto plano fuera de la aplicación.

**Recomendación v2.** Autenticación por POST sobre HTTPS; nunca credenciales en query string.

---

## P2 — Criptografía débil / exposición de credenciales

### P2-1 · Hashing MD5 sin sal para contraseñas
**Descripción.** Las contraseñas se almacenan como `md5($pass)` (32 hex, sin sal, sin pepper, sin trabajo de cómputo).

**Evidencia.** `api/app/rest/login.php:15` (`md5($_GET['p'])`), `d_usuario_cambio.php:54`, `admin_asesor.php:165` (`md5(0)`). Confirmado en datos: `_evidence/quality.json` → `t_usuarios.userpass` 32 chars hex.

**Impacto.** MD5 es quebrable por GPU a miles de millones de hashes/seg; sin sal, las rainbow tables y la deduplicación de hashes iguales aplican. Una fuga de `t_usuarios` revela contraseñas casi de inmediato.

**Recomendación v2.** `bcrypt`/`argon2id` con sal por usuario (la arquitectura objetivo ya define `bcryptjs`).

---

### P2-2 · Contraseñas por defecto `md5('0')` en producción
**Descripción.** `admin_asesor` crea cada usuario con `userpass=md5(0)`= `cfcd208495d565ef66e7dff9f98764da` (contraseña literal `0`). Nunca se fuerza cambio.

**Evidencia.** `api/app/rest/admin_asesor.php:165` — `$userpass=md5(0)`. Datos: `_evidence/quality.json` → `password_md5_zero: total 65, md5_of_zero 6`. **6 de 65 usuarios tienen contraseña `0` hoy** (el doc previo afirmaba "al menos 3"; el dato actual es 6).

**Impacto.** Acceso directo con `?u=<username>&p=0` para 6 cuentas reales, incluidos usuarios sintéticos privilegiados (`GRUPO.INVESTIGACION`, `CEGROUP.PAZYSALVO`, `CEGROUP.FALLECIDOS` — ver glosario). No hay flag `mustChangePassword`.

**Recomendación v2.** Contraseña inicial aleatoria enviada por correo (Resend) + flag de cambio obligatorio al primer login.

---

### P2-3 · CORS abierto a cualquier origen (`*`)
**Descripción.** Todos los endpoints REST emiten `Access-Control-Allow-Origin: *` y permiten todos los métodos.

**Evidencia.** Cabecera presente en cada `api/app/rest/*.php`, p. ej. `login.php:2-4`, `admin_asesor.php:2-4`, `g_gestiones.php:2-4`.

**Impacto.** Cualquier sitio web puede invocar el API desde el navegador de una víctima. Combinado con la ausencia de auth (P0-3) y de protección CSRF, cualquier página maliciosa ejecuta acciones contra la BD.

**Recomendación v2.** Allowlist explícita de orígenes confiables; `credentials` sólo donde aplique; CSRF tokens para mutaciones.

---

### P2-4 · Fuga de la consulta SQL en las respuestas (`$data['sql']`)
**Descripción.** Múltiples endpoints devuelven el SQL ejecutado dentro del JSON de respuesta.

**Evidencia.** Archivos que exponen `$data['sql']`: `admin_asesor.php`, `clientes.php`, `b_resumen.php`, `d_usuario_cambio.php` (líneas 40,57), `b_datafilter.php`, `b_alertas.php`, `g_email.php:44`, `b_data.php`, `b_acuerdos.php`. En error, `g_email.php:76` devuelve el SQL completo del INSERT.

**Impacto.** Revela nombres de tablas/columnas y estructura de queries → facilita afinar inyecciones (P0-1). Es fuga de información que acelera el resto de ataques.

**Recomendación v2.** Nunca devolver SQL. Mensajes de error genéricos; detalle sólo en logs server-side.

---

## P3 — Endurecimiento / defensa en profundidad

### P3-1 · Sin HTTPS forzado
**Descripción.** La URL base del API está hardcodeada en `http://` (no `https://`).

**Evidencia.** `index.php:11` — `let dir_server="http://gestioncobranza.com/api/api/"`. No hay redirección a HTTPS ni HSTS.

**Impacto.** Todo (login por GET, hashes, PII) viaja en claro; interceptable por MITM en la red.

**Recomendación v2.** HTTPS obligatorio, HSTS, redirección 301 de http→https. URL base por variable de entorno (`VITE_API_URL`).

---

### P3-2 · `set_time_limit(60000000000)` en importadores destructivos
**Descripción.** Los `procesar.php` y `elimina.php` deshabilitan de hecho el timeout.

**Evidencia.** `api/UPDATE/elimina.php:4`, `api/UPDATE/BASE/procesar.php:2` (y todos los `procesar.php`).

**Impacto.** Procesos colgados consumen conexiones a BD indefinidamente; amplifica el impacto de un DoS o de un import malicioso (P0-6).

**Recomendación v2.** Procesamiento en cola con límites y reintentos acotados; sin timeouts infinitos en el request HTTP.

---

### P3-3 · Capa de "codificación" (`CODING`) es un no-op que aparenta protección
**Descripción.** `array_map(CODING,$d)` se usa por todo el código como si transformara/saneara la salida, pero `CODING` está definido como `null`.

**Evidencia.** `api/api/index.php:6` — `define("CODING",null)`. `array_map(null,$array)` devuelve el array sin cambios. Igual `format_post()` (`api/api/index.php:8-22`): sólo entra la rama `'null'` que hace `mb_strtoupper`, las ramas `encode`/`decode` son código muerto.

**Impacto.** Falsa sensación de saneamiento. No protege contra nada (la PII y el hash salen intactos, ver P1-3). Es deuda que oculta el riesgo real.

**Recomendación v2.** Eliminar la capa muerta. Saneamiento/serialización explícita con DTOs.

---

## Cadena de explotación de referencia (sin credenciales)

1. `GET app/php/session.php?a=on&t=0` → sesión admin en la UI (P0-4).
2. `GET api/file/sql/user/acue/?sql=SELECT username,userpass FROM t_usuarios` → volcado de hashes (P0-2 + P1-3).
3. `GET api/api/d_usuario_cambio?a=COORDINADOR&t=password&v=x` → toma de cuenta del coordinador (P1-1).
4. POST a `api/UPDATE/BASE/upload.php` con `shell.php` → `GET api/UPDATE/BASE/file/shell.php` → RCE (P0-5).
5. `GET api/UPDATE/elimina.php` (tras subir `ELIMINAR.csv`) → destrucción de `t_base` (P0-7).

Cada paso es independiente; basta uno (P0-2) para comprometer todo.

## Resumen

| ID | Hallazgo | Sev |
|---|---|---|
| P0-1 | SQLi en todos los endpoints REST | P0 |
| P0-2 | Pasamanos `?sql=` sin auth | P0 |
| P0-3 | Sin autenticación en la capa API | P0 |
| P0-4 | Bypass de sesión `session.php?a=on&t=0` | P0 |
| P0-5 | Upload sin validación → RCE | P0 |
| P0-6 | SQLi vía contenido del CSV | P0 |
| P0-7 | `elimina.php` borra `t_base` sin auth | P0 |
| P0-8 | Credenciales de BD hardcodeadas | P0 |
| P1-1 | Reset de contraseña por GET sin identidad | P1 |
| P1-2 | `admin_asesor` CRUD de usuarios sin auth | P1 |
| P1-3 | Hash de contraseña devuelto al cliente | P1 |
| P1-4 | Exportadores `SELECT *` sin filtro | P1 |
| P1-5 | Login/credenciales por GET en la URL | P1 |
| P2-1 | MD5 sin sal | P2 |
| P2-2 | Contraseñas por defecto `md5('0')` (6/65) | P2 |
| P2-3 | CORS `*` | P2 |
| P2-4 | Fuga del SQL en respuestas | P2 |
| P3-1 | Sin HTTPS forzado | P3 |
| P3-2 | `set_time_limit` infinito en importadores | P3 |
| P3-3 | Capa `CODING` no-op (falsa protección) | P3 |
