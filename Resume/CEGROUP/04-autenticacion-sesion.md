# 04 · Autenticación y sesión — CEGROUP

> **Conclusión:** Login client-side por `fetch` GET a la API (usuario y contraseña **en la URL**), contraseña hasheada con **md5 sin sal**, SQL **interpolado** (inyectable). La sesión server-side se activa por un endpoint independiente (`session.php`) que **no verifica nada** — cualquiera puede crear una sesión admin con `session.php?a=on&t=0`. La identidad del usuario vive solo en `localStorage`. No hay tokens, ni expiración, ni autorización por rol en el backend. Defectos confirmados en código y en datos (`quality.json`: 6/65 contraseñas = `md5('0')`).

---

## 1. Flujo de login paso a paso

### Paso 1 — Carga del login (`cegroup/index.php`)
- `session_start()` + `session_destroy()` inmediatos (`index.php:2-3`): toda sesión previa se borra al abrir la página.
- Formulario con `#username` y `#userpass` (`index.php:56-78`).
- En `$(document).ready` (`index.php:99-103`): `reset()` del form, `localStorage.clear()`, y registra el handler `login()`.
- Bases API hardcoded (`index.php:104-106`):
  ```js
  let dir_local  = "http://localhost/server/cobranza_api/api/";   // :104  (no usada)
  let dir_server = "http://gestioncobranza.com/api/api/";          // :105  (activa)
  let http_data  = dir_server;                                     // :106
  ```

### Paso 2 — Submit (`index.php:108-116`)
```js
$('#frm').on('submit', function(e){
  e.preventDefault();
  let user = $("#username").val();
  let pass = $("#userpass").val();
  let url = http_data + 'login?u=' + user + '&p=' + pass;   // index.php:113
  select_asesor(url);
});
```
La contraseña viaja **en el query string** (`?u=...&p=...`), sin escape y sobre **HTTP** (la base empieza con `http://`).

### Paso 3 — Llamada y procesamiento de respuesta (`index.php:118-148`)
- `select_asesor(url)` hace `fetch(url)` y parsea JSON (`index.php:118-126`).
- Si `data.num == 1` (`index.php:130`):
  ```js
  let data_user = data.data[0];
  localStorage.setItem('session_name',   data_user['nombre']);    // :133
  localStorage.setItem('session_user',   data_user['username']);  // :134
  localStorage.setItem('session_type',   data_user['usertype']);  // :135
  localStorage.setItem('session_avatar', data_user['avatar']);    // :136
  localStorage.setItem('session', 'ACTIVE');                       // :137
  $.ajax({ url: 'app/php/session.php?a=on&t='+data_user['usertype'] })  // :132,138-139
    .done(()=> window.location = "public/");                       // :141
  ```
- Si `data.num != 1` → `error(data)` muestra toast "Los datos de acceso no son correctos" (`index.php:145-146,168`).

### Paso 4 — Endpoint `login` (`api/app/rest/login.php`)
```php
function SELECT_LOGIN(){
  $user = $_GET['u'];                 // login.php:14
  $pass = md5($_GET['p']);            // login.php:15   md5 SIN SAL
  $obj  = new conn;
  $sql  = "SELECT * FROM `t_usuarios`
           WHERE `username`= '$user' AND `userpass` = '$pass' AND `estado` = 'TRUE' ";  // :17
  $con  = $obj->query($sql);
  $num  = mysqli_num_rows($con);
  $data['num'] = $num;
  if($num >= 1){ while($d = mysqli_fetch_assoc($con)){ $data['data'][] = $d; } }  // :21-24
  else { $data['data'] = FALSE; }
  return $data;
}
echo json_encode($return);            // login.php:30
```
Características verificadas:
- Solo acepta `GET` (`login.php:8-12`).
- `$user` y `$pass` se **interpolan directamente** en el SQL → **SQL injection** en ambos campos.
- Contraseña = `md5($_GET['p'])` — md5 sin sal, sin `password_hash`.
- Filtra `estado = 'TRUE'` → usuarios deshabilitados no entran (en BD hay 1 con `estado='FALSE'`, `column_profiles.json`).
- Devuelve **el registro completo** del usuario (`SELECT *`), incluido el hash `userpass`, al cliente en JSON.
- Cabeceras CORS abiertas: `Access-Control-Allow-Origin: *` (`login.php:2`).

### Paso 5 — Activación de sesión server-side (`cegroup/app/php/session.php`)
```php
session_start();                       // session.php:2
$action = $_GET['a'];                  // :3
if($action=='on'){
  $type = $_GET['t'];                  // :5
  $_SESSION["session"]='ACTIVE';        // :6
  $_SESSION["session_type"]=$type;      // :7
}else{
  session_destroy();                    // :9
}
```
Es un script de **12 líneas** que **no valida nada**: confía en que el cliente ya autenticó. Acepta cualquier `t` (tipo de sesión) directamente del query string.

### Paso 6 — Gate de cada request autenticado (`cegroup/public/index.php`)
```php
session_start();                       // public/index.php:2
if (isset($_SESSION["session"])) {     // :3   único chequeo
  ... // arranca framework + vista
} else {
  header("Location: ../");             // :11-12  → login
}
```
El **único** control es la *existencia* de `$_SESSION["session"]`. No verifica valor, ni firma, ni expiración, ni identidad.

---

## 2. Dos almacenes de sesión en paralelo

| Dato | `localStorage` (cliente) | `$_SESSION` (servidor) |
|---|---|---|
| `session` (= `"ACTIVE"`) | sí (`index.php:137`) | sí (`session.php:6`) |
| `session_type` (rol) | sí (`index.php:135`) | sí (`session.php:7`) |
| `session_name` (nombre) | sí (`index.php:133`) | — |
| `session_user` (username) | sí (`index.php:134`) | — |
| `session_avatar` | sí (`index.php:136`) | — |

El servidor solo conoce que hay sesión y el tipo. La **identidad** del usuario (nombre, username, avatar) vive **únicamente en `localStorage`**, donde es legible y editable por cualquier script. La app la lee en `main.js:18-19` para pintar el nombre y para construir llamadas (`b_alertas?a=<session_user>`, `main.js:22`).

---

## 3. Logout

Verificado en `app/assets/js/main.js`:
```js
$(document).ready(function(){
  let userSession = localStorage.getItem("session");
  userSession === "ACTIVE" ? sessionData() : sessionOff();   // main.js:2-3
});

const sessionOff = () => {
  $.ajax({ url: "../app/php/session.php?a=off" })             // main.js:9-10
    .done(()=>{ localStorage.clear(); window.location = "../"; });  // :11-13
};
```
- El botón "CERRAR SESIÓN" (`header.php:61`) llama `sessionOff()`.
- `session.php?a=off` cae en el `else` y ejecuta `session_destroy()` (`session.php:8-9`).
- Además, en cada carga, si `localStorage.session !== "ACTIVE"` se hace logout automático (`main.js:3`) — protección **solo del lado cliente**: borrar la cookie de PHP a mano deja al cliente creyendo que sigue logueado hasta el próximo `main.js`.

---

## 4. Roles y gestión de usuarios

### Roles
`session_type` solo tiene dos valores (verificado en BD: `column_profiles.json` → `t_usuarios.usertype` distinct = 2):

| `usertype` | Rol | Menú (`menu.php:15-24`) | Nº en BD |
|---|---|---|---|
| `0` | Admin | BUSQUEDA, ASESORES, REPORTES, BASE DE DATOS | 3 |
| `1` | Asesor | BUSQUEDA, GESTION, ACUERDOS, RESUMEN | 62 |

El rol **solo** afecta el sidebar (`menu.php:18-24`). **Ningún** Router, Controller ni endpoint REST verifica el rol — un asesor puede navegar a `public/asesores` o llamar cualquier endpoint API directamente.

> **Discrepancia con doc previo:** `resumen/04` describía el asesor como `usertype = 1+`. **Falso según datos:** solo existen `0` y `1`.

### Alta de asesor (`api/app/rest/admin_asesor.php`)
```php
function INSERT_ASESOR(){
  $cedula = mb_strtoupper($_POST['cedula']); ... 
  $userpass = md5(0);                                      // admin_asesor.php:165
  $sql="INSERT INTO `t_usuarios`
        (`cedula`,`nombre`,`telefono`,`avatar`,`username`,`userpass`,`usertype`,`posicion`,`estado`)
        VALUES ('$cedula','$nombre','$telefono','$avatar','$username','$userpass','1','0','TRUE')"; // :167-168
}
```
- **Contraseña por defecto = `md5(0)`** (= contraseña `"0"`), `usertype='1'`, `estado='TRUE'`, `posicion='0'`.
- Esto **explica directamente** la evidencia `quality.json`: **6 de 65** usuarios conservan `userpass = md5('0')` → 6 cuentas con contraseña trivial `"0"` sin cambiar.
- SQL interpolado con `$_POST` → inyectable.

### Cambio de contraseña (`api/app/rest/d_usuario_cambio.php`)
```php
function PROCESS_PASS(){
  $a = $_GET['a'];                  // d_usuario_cambio.php:53  username
  $p = md5($_GET['v']);             // :54  nueva contraseña, md5 sin sal, EN LA URL
  $sql = "UPDATE `t_usuarios` SET `userpass` = '$p'
          WHERE `t_usuarios`.`username` = '$a' ";   // :56
}
```
Nueva contraseña viaja en `?v=` (GET, en la URL) e interpolada en SQL.

> **Zona horaria operativa:** `d_usuario.php:18` fija `America/Bogota` → operación en Colombia (consistente con teléfonos colombianos en `t_usuarios.telefono`, max `3508434157`).

---

## 5. Debilidades de seguridad (verificadas)

1. **Bypass total de autenticación** — `session.php?a=on&t=0` activa una sesión **admin** sin login. No requiere llamar antes a `login`. `public/index.php:3` solo comprueba que `$_SESSION["session"]` exista. (`session.php:4-7`, `public/index.php:3`).
2. **Credenciales en URL GET** — usuario y contraseña viajan en el query string (`index.php:113`), quedando en historiales de navegador, logs de Apache/proxy y `Referer`.
3. **Sin HTTPS** — base API en `http://` (`index.php:105`, `main.js:33`). Tráfico (incluida contraseña) viaja en claro.
4. **md5 sin sal** — `login.php:15`, `admin_asesor.php:165`, `d_usuario_cambio.php:54`. md5 es roto y sin sal permite rainbow tables; `md5('0')` es público.
5. **Contraseña por defecto trivial** — nuevos asesores nacen con contraseña `"0"`; **6/65** cuentas siguen así (`quality.json` password_md5_zero).
6. **SQL injection en login y gestión de usuarios** — `$user`/`$pass`/`$a`/`$_POST` interpolados sin escape ni prepared statements (`login.php:17`, `d_usuario_cambio.php:56`, `admin_asesor.php:167`).
7. **CORS totalmente abierto** — `Access-Control-Allow-Origin: *` en todos los endpoints (`login.php:2`, etc.). Cualquier origen puede invocar la API.
8. **`SELECT *` devuelve el hash al cliente** — la respuesta de `login` incluye `userpass` (`login.php:17,23`).
9. **Sin autorización por rol en backend** — ningún endpoint REST valida `session_type`; el rol solo oculta menús (`menu.php`).
10. **Identidad solo en `localStorage`** — `session_user`/`session_name` son editables por el cliente; un script puede suplantar a otro asesor en las llamadas que usan `session_user` como parámetro (p.ej. `main.js:22`).

> Estas debilidades son **observaciones verificadas**, no recomendaciones. Su priorización para la versión reconstruida pertenece a otro documento.

---

## 6. Evidencia

- `cegroup/index.php:2-3,99-148` (login client-side)
- `cegroup/api/app/rest/login.php:1-30` (endpoint login, md5, SQLi, CORS, SELECT *)
- `cegroup/app/php/session.php:1-11` (activación/destrucción de sesión, bypass)
- `cegroup/public/index.php:2-3,11-12` (gate de sesión)
- `cegroup/app/parts/menu.php:15-24` (roles 0 / 1)
- `cegroup/app/assets/js/main.js:2-13,18-22,33-34` (logout, identidad, base API)
- `cegroup/api/app/rest/admin_asesor.php:163-170` (alta asesor, password md5(0))
- `cegroup/api/app/rest/d_usuario_cambio.php:52-57` (cambio password GET)
- `cegroup/api/app/rest/d_usuario.php:18` (zona horaria America/Bogota)
- Evidencia BD: `connection.json` (server MariaDB 10.11.16, HTTP), `quality.json` password_md5_zero (6/65), `column_profiles.json` t_usuarios (usertype distinct=2, estado TRUE=64/FALSE=1)
