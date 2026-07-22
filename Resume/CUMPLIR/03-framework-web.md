# 03 · Framework web (`cumplir/lib/`) — CUMPLIR

> **Conclusión:** la capa web de CUMPLIR corre sobre un micro-framework PHP propio de 6 archivos. **Verificado byte-a-byte: los 6 archivos son idénticos a los de CEGROUP** (`diff -q` sin diferencias). También son **byte-idénticos** `public/index.php` y `public/.htaccess`. Es el mismo esqueleto heredado: routing por hashmap estático, sin parámetros dinámicos, sin métodos HTTP, sin middleware ni autenticación a nivel de framework. La sesión se chequea por fuera (en `public/index.php`), no dentro del framework.

---

## 1. Verificación de identidad con CEGROUP

`diff` byte a byte entre `cumplir/lib/*` y `cegroup/lib/*` (y `public/*`):

| Archivo | Resultado |
|---|---|
| `lib/init.php` | **IDÉNTICO** |
| `lib/Router.php` | **IDÉNTICO** |
| `lib/App.php` | **IDÉNTICO** |
| `lib/Response.php` | **IDÉNTICO** |
| `lib/Action.php` | **IDÉNTICO** |
| `lib/Css.php` | **IDÉNTICO** |
| `public/index.php` | **IDÉNTICO** |
| `public/.htaccess` | **IDÉNTICO** |

Comando ejecutado: `diff -q cumplir/lib/<f> cegroup/lib/<f>` para cada archivo → ningún output (cero diferencias). El único archivo del bootstrap web que **sí difiere** es el login (`cumplir/index.php`), que está **fuera** de `lib/` y se documenta en `04-autenticacion-sesion.md`.

> **Nota:** la capa API (`api/lib/`) tiene su propio esqueleto, también compartido con CEGROUP excepto credenciales en `DB.php`. Este doc cubre solo el framework **web** (`lib/`).

---

## 2. Mapa de archivos

| Archivo | Líneas | Propósito |
|---|---|---|
| `lib/init.php` | 8 | Bootstrap: require de `App`, `Action`, `Css`, `Router`, luego `app/http/routes.php`, luego `Response`. |
| `lib/Router.php` | 22 | Tabla estática de rutas `route → {controller, method}`. `add()` y `getAction()`. |
| `lib/App.php` | 27 | Dispatcher: lee `$_GET["url"]`, resuelve ruta, instancia controller, invoca método; en error → `views/error.php`. |
| `lib/Response.php` | 12 | `render(view, params)`: extrae params a variables y hace `require app/views/<view>.php`. |
| `lib/Action.php` | 25 | Según la ruta, emite `<script src="../app/action/<method>.js">`. |
| `lib/Css.php` | 26 | Según la ruta, emite `<link href="../app/assets/css/<method>.css">`. |

---

## 3. Detalle por archivo

### `lib/init.php` (`init.php:1-8`)

```php
require SYS_PATH."App.php";
require SYS_PATH."Action.php";
require SYS_PATH."Css.php";
require SYS_PATH."Router.php";
require APP_PATH."http/routes.php";   // registra las 13 rutas web
require SYS_PATH."Response.php";
```

Carga las clases y registra las rutas. `SYS_PATH="lib/"` y `APP_PATH="app/"` los define `public/index.php`.

### `lib/Router.php` (`Router.php:1-22`)

Clase estática, constructor privado. `$routes` es un hashmap. `getAction($route)` lanza `Exception("La ruta '$route' NO fue encontrada")` si no existe — capturada por `App` para renderizar `error.php`.

- Sin params dinámicos (`/user/:id` no existe). Sin distinción de método HTTP — solo la cadena `?url=`.

### `lib/App.php` (`App.php:1-27`)

```php
if (isset($_GET["url"])) {
  $url = $this->parseUrl();
  try {
    $action = Router::getAction($url);
    require APP_PATH."controllers/".$action["controller"].".php";
    $controller = new $action["controller"]();
    $controller->$action["method"]();
  } catch (Exception $e) {
    require APP_PATH."views/error.php";
  }
} else {
  require APP_PATH."views/index.php";
}
```

Sin `$_GET["url"]` → renderiza `views/index.php` directamente. `parseUrl()` solo devuelve `$_GET["url"]` (no sanitiza).

### `lib/Response.php` (`Response.php:1-12`)

`render($view, $params=[])` hace `extract` manual (`$$key = $value`) y `require app/views/<view>.php`. Sin escape de salida ni separación de plantilla.

### `lib/Action.php` (`Action.php:1-25`) y `lib/Css.php` (`Css.php:1-26`)

Ambas **re-implementan el mismo parser de URL** que `App` (duplicación del patrón `parseUrl()` + `Router::getAction()`). Diferencia entre ambas:

- `Action` emite `<script src="../app/action/<method>.js">` (en `footer.php`).
- `Css` emite `<link ... href="../app/assets/css/<method>.css">`.

Sin ruta → emiten el asset `index` (`action/index.js` / `css/index.css`). En `catch` no hacen nada (cuerpo vacío).

> **Observación:** solo `Action` se instancia en `footer.php` (`<?php $action = new Action; ?>`). `Css` está disponible pero su uso depende de si `header.php` la instancia (no se cubre aquí — fuera de `lib/`).

---

## 4. Ciclo de un request (resumen)

1. Browser → `https://cumplir.net/public/<ruta>` → Apache reescribe a `index.php?url=<ruta>` (`public/.htaccess`).
2. `public/index.php:3` valida `$_SESSION["session"]`. Sin sesión → `header("Location: ../")` (login).
3. Con sesión: `chdir(raíz)`, define `SYS_PATH`/`APP_PATH`, `require lib/init.php`.
4. `require app/parts/header.php` (HTML head + topbar + menu por rol).
5. `new App` → resuelve ruta → controller → `Response::render(view)` → vista HTML.
6. `require app/parts/footer.php` → `new Action` inyecta el `<script>` de la vista.

---

## 5. Limitaciones del framework (heredadas, sin cambios)

- **Routing estático** — sin params dinámicos, sin verbos HTTP.
- **Sin middleware** — la autenticación vive en `public/index.php`, no en el framework.
- **Sin sanitización** de `$_GET["url"]`.
- **Duplicación** del parser de URL en `App`, `Action`, `Css`.
- **Sin namespaces, sin autoloading** (require manual archivo por archivo).

---

## 6. Evidencia

- `cumplir/lib/init.php:1-8`
- `cumplir/lib/Router.php:1-22`
- `cumplir/lib/App.php:1-27`
- `cumplir/lib/Response.php:1-12`
- `cumplir/lib/Action.php:1-25`
- `cumplir/lib/Css.php:1-26`
- `cumplir/public/index.php:1-13`
- `cumplir/public/.htaccess:1-7`
- `diff -q` contra `cegroup/lib/*` y `cegroup/public/*` → cero diferencias (verificado en esta pasada).
