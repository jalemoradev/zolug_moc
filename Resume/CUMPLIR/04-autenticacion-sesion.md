# 04 · Autenticación y sesión — CUMPLIR

> **Conclusión:** mismo modelo que CEGROUP — login client-side por JavaScript que llama al endpoint REST `login`, sesión PHP server-side activada por un endpoint **sin validación**, metadatos en `localStorage`, sin tokens ni JWT. Diferencias verificadas vs CEGROUP: el login usa **HTTPS** y define **dos base-URLs** (`dir_data_server` + `dir_file_server`) con `const`, y los textos están acentuados. `app/php/session.php` es **byte-idéntico a CEGROUP**. Debilidades confirmadas en BD: **10 de 13 contraseñas son `md5('0')`** = password `0` (`quality.json`). El alta de asesor crea siempre password `0`. Credenciales viajan en query string GET.

---

## 1. Flujo de login paso a paso

### Paso 1 — Carga del login (`cumplir/index.php:1-104`)

- `session_start()` + `session_destroy()` inmediatamente (`index.php:2-3`): borra cualquier sesión previa al abrir el login.
- Formulario `#frm` con `#username`, `#userpass` y submit (`index.php:55-77`).
- En `$(document).ready` (`index.php:97-101`): `$('#frm')[0].reset()` + `localStorage.clear()` + `login()`.
- Base-URLs definidas con **`const`** (`index.php:103-107`):
  ```js
  const dir_data_server = "https://cumplir.net/api/api/";
  const dir_file_server = "https://cumplir.net/api/file/";
  const http_data = dir_data_server;
  const http_file = dir_file_server;
  ```

### Paso 2 — Submit (`index.php:110-118`)

```js
const login = () => {
  $('#frm').on('submit', function(e) {
    e.preventDefault();
    let user = $("#username").val();
    let pass = $("#userpass").val();
    let url = http_data + 'login?u=' + user + '&p=' + pass;   // credenciales en query string
    select_asesor(url);
  });
}
```

> **Vulnerabilidad:** usuario y contraseña viajan en la query string GET (`login?u=...&p=...`). Quedan en logs de Apache, historial, proxies. Idéntico a CEGROUP.

### Paso 3 — Llamada y procesamiento (`index.php:120-150`)

- `select_asesor(url)` hace `fetch(url)` y parsea JSON (`index.php:120-128`).
- `process_data(data)` (`index.php:130-150`): si `data.num == 1`, guarda 5 claves en `localStorage`:
  ```js
  localStorage.setItem('session_name',   data_user['nombre']);
  localStorage.setItem('session_user',   data_user['username']);
  localStorage.setItem('session_type',   data_user['usertype']);
  localStorage.setItem('session_avatar', data_user['avatar']);
  localStorage.setItem('session', 'ACTIVE');
  ```
  Luego AJAX a `app/php/session.php?a=on&t=<usertype>` y al terminar `window.location = "public/"` (`index.php:140-144`).
- Si `num != 1` → `error(data)`: toastr "Los datos de acceso no son correctos" + `console.log(data.sql)` (`index.php:152-173`). **El SQL se loguea en consola** — la respuesta del endpoint incluye `sql`.

### Paso 4 — Endpoint `login` (`api/app/rest/login.php`)

```php
function SELECT_LOGIN(){
  $user = $_GET['u'];
  $pass = md5($_GET['p']);
  $obj = new conn;
  $sql = "SELECT * FROM `t_usuarios`
          WHERE `username`= '$user' AND `userpass` = '$pass' AND `estado` = 'TRUE' ";
  $con = $obj->query($sql);
  $num = mysqli_num_rows($con);
  ...
}
```
(`api/app/rest/login.php:13-29`)

- Contraseña hasheada con **`md5()` sin salt**.
- Filtro `estado = 'TRUE'` exige usuario activo.
- **SQL injection**: `$user` se interpola sin escape (`$pass` está protegido por `md5()`, pero `$user` no). Cabeceras CORS abiertas (`Access-Control-Allow-Origin: *`, `login.php:2`).
- **Verificado idéntico a CEGROUP** en lógica y en el `WHERE ... estado='TRUE'`.

### Paso 5 — Activación de sesión server-side (`app/php/session.php`)

**Byte-idéntico a CEGROUP** (`diff -q` sin diferencias). 11 líneas:

```php
session_start();
$action = $_GET['a'];
if($action=='on'){
  $type = $_GET['t'];
  $_SESSION["session"]='ACTIVE';
  $_SESSION["session_type"]=$type;
}else{
  session_destroy();
}
```

> **Bypass de sesión (verificado):** `app/php/session.php?a=on&t=0` activa una sesión **admin** sin autenticar — el endpoint no valida nada, toma `t` directamente de la query string. Quien acierte la URL queda con `$_SESSION["session"]='ACTIVE'` y `session_type=0`. Mismo defecto que CEGROUP.

### Paso 6 — Gate de cada request autenticado (`public/index.php`)

**Byte-idéntico a CEGROUP**:

```php
session_start();
if (isset($_SESSION["session"])) {
  chdir( dirname(__DIR__) );
  define("SYS_PATH","lib/");
  define("APP_PATH","app/");
  require SYS_PATH."init.php";
  require APP_PATH."parts/header.php";
  $app = new App;
  require APP_PATH."parts/footer.php";
} else {
  header("Location: ../");
}
```
(`public/index.php:1-13`)

Solo chequea `isset($_SESSION["session"])` — no valida tipo, ni expiración, ni firma.

---

## 2. Dos almacenes de sesión en paralelo

| Almacén | Contenido | Uso |
|---|---|---|
| **PHP `$_SESSION`** (server) | `session='ACTIVE'`, `session_type` | Gate de `public/index.php` y menú por rol (`menu.php:18`). |
| **`localStorage`** (cliente) | `session_name`, `session_user`, `session_type`, `session_avatar`, `session` | El front lo lee para pintar nombre/avatar y para construir llamadas API (`main.js:2-26`). |

- En cada carga de la capa web, `main.js:1-4` chequea `localStorage.getItem("session") === "ACTIVE"` → si no, llama `sessionOff()`.
- **Inconsistencia de fuentes:** el `session_type` que decide el menú (`menu.php:18`) es el de **`$_SESSION`** (lo fija `session.php`), pero el front confía en el `localStorage`. Como `session.php` acepta cualquier `t`, ambos almacenes pueden manipularse desde el cliente.

> **Las base-URLs se redefinen** en `main.js:31-35` con los mismos valores que el login (`dir_data_server`/`dir_file_server`, HTTPS). El front siempre apunta a `https://cumplir.net/api/api/`.

---

## 3. Logout (`app/assets/js/main.js:8-15`)

```js
const sessionOff = () => {
  $.ajax({ url: "../app/php/session.php?a=off" })
   .done(function(e) {
     localStorage.clear();
     window.location = "../";
   });
};
```

`session.php?a=off` → `session_destroy()` (rama `else`). Luego limpia `localStorage` y vuelve al login.

---

## 4. Roles y gestión de usuarios

### Roles

- `usertype = 0` → **admin**; cualquier otro valor → **asesor** (`app/parts/menu.php:20-24`, `if($menu_session_type ==0)`).
- En BD (`t_usuarios`, sample) se observan solo `0` y `1`. **No verificado** que existan otros valores; el código trata todo `≠0` como asesor.
- Esquema `t_usuarios` (DDL, `_evidence/ddl/t_usuarios.sql`): `cedula` PK bigint, `nombre`, `telefono`, `avatar` varchar(6), `userpass` varchar(100), `username` varchar(30) NOT NULL, `usertype` int(1), `posicion` varchar(10) default `'0'`, `estado` varchar(10) default `'FALSE'`.

### Alta de asesor (`api/app/rest/admin_asesor.php`)

`POST ...?url=admin_asesor&t=insert` → `INSERT_ASESOR()` (`admin_asesor.php:159-173`):

```php
$userpass = md5(0);   // ← password fijo "0"
$sql = "INSERT INTO `t_usuarios`
        (cedula, nombre, telefono, avatar, username, userpass, usertype, posicion, estado)
        VALUES ('$cedula','$nombre','$telefono','$avatar','$username','$userpass','1','0','TRUE')";
```

> **Origen del defecto de passwords triviales:** cada asesor nuevo nace con `userpass = md5(0)` (password `0`), `usertype='1'`, `estado='TRUE'`. Esto explica directamente los **10 de 13** registros con `md5('0')` en `quality.json`.

Otras operaciones del mismo endpoint (todas con SQL interpolado, sin escape):
- `t=update` → `UPDATE_ASESOR()` (nombre, telefono, username).
- `t=estado` → `ESTADO_ASESOR()` (activa/desactiva por `estado`).
- `t=delete` → `DELETE_ASESOR()` (`DELETE FROM t_usuarios WHERE cedula=...`).
- `t=mensaje_insert` / `mensaje_delete` → CRUD sobre `t_mensaje`.
- Lecturas: `t=all|texto|cedula|mensaje`. Todas devuelven `$data['sql']` en la respuesta — **filtran el SQL**.

### Cambio de contraseña (`api/app/rest/d_usuario_cambio.php`)

`GET ...?url=d_usuario_cambio&t=password&a=<username>&v=<nueva>` → `PROCESS_PASS()` (`d_usuario_cambio.php:52-65`):

```php
$a = $_GET['a'];
$p = md5($_GET['v']);
$sql = "UPDATE `t_usuarios` SET `userpass` = '$p' WHERE `username` = '$a' ";
```

> **Vulnerabilidades del cambio de contraseña:** nueva contraseña en **query string GET**; hash **md5 sin salt**; sin verificación de la contraseña actual; `$a` (username) interpolado → SQL injection. También existe `t=avatar` (`PROCESS_AVATAR()`) por GET.

---

## 5. Diferencias vs CEGROUP (verificadas)

| Aspecto | CEGROUP | CUMPLIR | Evidencia |
|---|---|---|---|
| Protocolo del API | HTTP (`gestioncobranza.com`) | **HTTPS** (`cumplir.net`) | `index.php:103` |
| Declaración base-URL | `let dir_server`, + `dir_local` (dev, sin uso) | **`const`**, sin `dir_local` | `index.php:103-107` |
| Nº de base-URLs | 1 (`dir_server`) | **2** (`dir_data_server` + `dir_file_server`) | `index.php:103-104` |
| Texto botón | "INGRESO." | "INGRESO" | `index.php:73` |
| Label password | "Contrasena" | "Contraseña" (acentuada) | `index.php:63` |
| Versión visible | "Version 3.0" | "Versión 3.0" (acentuada) | `index.php:32` |
| `session.php` | — | **byte-idéntico** | `diff -q` sin diferencias |
| `login.php` (lógica + `estado='TRUE'`) | — | **igual** | `login.php:17` |

**Mejora real en CUMPLIR:** el API se sirve por **HTTPS**, lo que protege las credenciales en tránsito (no en logs del servidor, donde igual quedan por ir en la query string).

---

## 6. Debilidades de seguridad (verificadas)

1. **Passwords triviales:** `quality.json` → `t_usuarios.userpass` = `md5('0')` en **10 de 13** usuarios. Vector: `username=<cualquier admin>&password=0`. Causa raíz: `INSERT_ASESOR()` crea password `0` (`admin_asesor.php:165`).
2. **Credenciales en URL GET:** login (`index.php:115`) y cambio de password (`d_usuario_cambio.php:53-54`).
3. **Bypass de sesión:** `session.php?a=on&t=0` activa admin sin autenticar (`session.php:4-7`).
4. **md5 sin salt** para todas las contraseñas (`login.php:15`, `d_usuario_cambio.php:54`, `admin_asesor.php:165`).
5. **Sin token de API:** ningún endpoint REST valida sesión; el gate solo existe en `public/index.php`.
6. **CORS totalmente abierto:** `Access-Control-Allow-Origin: *` en todos los endpoints (`login.php:2`, `admin_asesor.php:2`, `d_usuario_cambio.php:2`).
7. **SQL injection** por interpolación sin escape en login, alta/edición de usuarios y cambio de password.
8. **Fuga de SQL** en respuestas (`$data['sql']`) y en consola del cliente (`index.php:172`).

---

## 7. Evidencia

- `cumplir/index.php:1-176` (login completo, base-URLs, submit)
- `cumplir/api/app/rest/login.php:1-30`
- `cumplir/app/php/session.php:1-11` (byte-idéntico a CEGROUP)
- `cumplir/public/index.php:1-13` (byte-idéntico a CEGROUP)
- `cumplir/app/assets/js/main.js:1-35` (sesión cliente, logout, base-URLs)
- `cumplir/app/parts/menu.php:15-24` (roles)
- `cumplir/api/app/rest/admin_asesor.php:159-250` (alta/edición/borrado de asesor; password `0`)
- `cumplir/api/app/rest/d_usuario_cambio.php:52-65` (cambio de contraseña por GET, md5)
- `_evidence/quality.json` → `password_md5_zero: { total:13, md5_of_zero:10 }`
- `_evidence/samples/t_usuarios.json`, `_evidence/ddl/t_usuarios.sql`
