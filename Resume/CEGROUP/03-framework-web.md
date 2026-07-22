# 03 · Microframework web — `cegroup/lib/`

> **Conclusión:** Microframework propio en **6 archivos PHP**, ~100 líneas en total. Ruteo estático por hashmap (`ruta → {controller, method}`), controllers cero-lógica, vistas planas cargadas con `require`. Inyecta el JS de cada vista mediante la clase `Action`. La clase `Css` existe pero **nunca se usa** (código muerto). Sin métodos HTTP, sin parámetros dinámicos en rutas, sin middleware, sin autorización por rol.

---

## 1. Mapa de archivos

| Archivo | Líneas | Responsabilidad |
|---|---:|---|
| `lib/init.php` | 8 | Bootstrap: `require` de App, Action, Css, Router → `app/http/routes.php` → Response |
| `lib/Router.php` | 22 | Tabla estática `ruta → {controller, method}`; constructor privado (no instanciable) |
| `lib/App.php` | 27 | Dispatcher: lee `$_GET["url"]`, resuelve ruta, instancia controller, invoca método |
| `lib/Response.php` | 12 | `render(view, params)`: convierte params en variables y hace `require app/views/<view>.php` |
| `lib/Action.php` | 25 | Emite `<script src="../app/action/<method>.js">` según la ruta actual |
| `lib/Css.php` | 26 | Emitiría `<link ...<method>.css>` según la ruta — **NUNCA SE INSTANCIA (código muerto)** |

(Conteos de línea verificados con `cat -n` sobre cada archivo.)

---

## 2. API pública por archivo

### 2.1 `lib/init.php` — Bootstrap

Orden exacto de carga (`init.php:2-7`):
```php
require SYS_PATH."App.php";       // 2
require SYS_PATH."Action.php";    // 3
require SYS_PATH."Css.php";       // 4
require SYS_PATH."Router.php";    // 5
require APP_PATH."http/routes.php";   // 6  ← aquí se registran las 13 rutas
require SYS_PATH."Response.php";  // 7
```
`SYS_PATH` y `APP_PATH` se definen antes en `public/index.php:5-6` (`"lib/"` y `"app/"`). El orden importa: `Router` debe existir antes de `routes.php` porque éste llama `Router::add()`.

### 2.2 `lib/Router.php` — Tabla de rutas

```php
class Router {
  private static $routes = [];          // Router.php:5  estado global estático
  private function __construct(){}       // Router.php:6  no instanciable

  public static function add($route, $controller, $method){     // Router.php:8
    static::$routes[$route]=["controller"=>$controller, "method"=>$method];   // :9
  }
  public static function getAction($route){                     // Router.php:11
    if (array_key_exists($route, static::$routes)) {            // :12
      return static::$routes[$route];                           // :13
    }else{
      throw new Exception("La ruta '$route' NO fue encontrada");// :15
    }
  }
}
```

**API pública:**
- `Router::add(string $route, string $controller, string $method): void` — registra una entrada.
- `Router::getAction(string $route): array` — devuelve `["controller"=>..., "method"=>...]` o lanza `Exception` si la ruta no existe.

Las rutas se registran en `app/http/routes.php:2-14` (13 entradas), todas apuntando a `MainController`:
```php
Router::add("index",       "MainController", "index");      // routes.php:2
Router::add("buscar",      "MainController", "buscar");     // :3
Router::add("gestion",     "MainController", "gestion");    // :5
Router::add("acuerdos",    "MainController", "acuerdos");   // :5
... // comunicacion, asesores, reportes, resumen, base, perfil, alertas, filtro, filtrotabla
```

### 2.3 `lib/App.php` — Dispatcher

```php
class App {
  public function __construct(){
    if (isset($_GET["url"])) {                       // App.php:5
      $url = $this->parseUrl();                       // :6
      try {
        $action = Router::getAction($url);            // :8
        $controllerName=$action["controller"];        // :9
        $method=$action["method"];                    // :10
        require APP_PATH."controllers/".$controllerName.".php";  // :11
        $controller = new $controllerName();          // :12
        $controller->$method();                       // :13
      } catch (Exception $e) {
        require APP_PATH."views/error.php";           // :15  ← ruta inexistente → vista 404
      }
    } else {
      require APP_PATH."views/index.php";             // :18  ← sin url → vista por defecto (vacía)
    }
  }
  public function parseUrl() {                         // :21
    if (isset($_GET["url"])) { return $_GET["url"]; } // :22-24
  }
}
```

**Comportamiento:**
- Con `$_GET["url"]`: resuelve vía `Router`, `require` del controller, instancia y llama el método (método de instancia con nombre dinámico `$controller->$method()`).
- Sin `url`: carga `app/views/index.php` (verificado: **archivo vacío, 0 bytes**).
- Ruta no registrada: `Router::getAction` lanza `Exception`, capturada → `app/views/error.php` ("Lo siento, ruta no encontrada", `error.php:8`).
- Se instancia en `public/index.php:9` (`$app = new App`).

### 2.4 `lib/Response.php` — Renderer de vista

```php
class Response{
  private function __construct(){}                     // Response.php:3  no instanciable
  public static function render($view, $params=[]){     // :4
    foreach ($params as $key => $value) { $$key = $value; }  // :5-7  variables dinámicas
    require APP_PATH."views/".$view.".php";             // :8
  }
}
```

**API pública:**
- `Response::render(string $view, array $params=[]): void` — extrae cada par de `$params` como variable PHP (`$$key = $value`, variables variables) y luego hace `require app/views/<view>.php`.

En la práctica **todos** los controllers llaman `Response::render("<nombre>")` **sin params** (`MainController.php:3-15`); los datos se cargan después vía JS contra la API. El soporte de `$params` está implementado pero no se usa.

### 2.5 `lib/Action.php` — Inyector de JS por ruta

```php
class Action {
  public function __construct(){
    if (isset($_GET["url"])) {                          // Action.php:5
      $url = $this->parseUrl();
      try {
        $action = Router::getAction($url);              // :8
        $method=$action["method"];                      // :10
        echo '<script src="../app/action/'.$method.'.js"></script>';  // :11
      } catch (Exception $e) { }                        // :12  silencioso
    } else {
      echo '<script src="../app/action/index.js"></script>';          // :16
    }
  }
  public function parseUrl() { ... }                    // :19-23  idéntico a App
}
```

**Comportamiento:**
- Emite un `<script src="../app/action/<method>.js">` correspondiente a la ruta actual.
- Sin `url` → `index.js`. Ruta inexistente → `catch` vacío (no emite nada).
- Se instancia en `footer.php:28` (`<?php $action = new Action; ?>`), de modo que el `<script>` queda al final del `<body>`, tras las libs base.

### 2.6 `lib/Css.php` — CÓDIGO MUERTO

```php
class Css {
  public function __construct(){
    if (isset($_GET["url"])) {
      ...
      echo '<link rel="stylesheet" ... href="../app/assets/css/'.$method.'.css">';  // Css.php:11
    } else {
      echo '<link rel="stylesheet" ... href="../app/assets/css/index.css">';        // Css.php:17
    }
  }
  public function parseUrl() { ... }
}
```

**Estado verificado:** `grep -rn "new Css" cegroup/` no devuelve **ninguna** coincidencia. La clase se define y se carga (`init.php:4`) pero **nunca se instancia**. El CSS de cada página se carga estáticamente en `header.php:11-16` (bootstrap, datatables, icons, toastr, app, main), no por ruta.

> **Discrepancia con doc previo:** `resumen/03` afirmaba que `Action` y `Css` "se instancian dentro de `header.php` y/o `footer.php` para inyectar los assets correctos por ruta". **Parcialmente falso:** `Action` se instancia solo en `footer.php:28`; `Css` no se instancia en ningún sitio. La carga de CSS por ruta descrita **no ocurre**.

---

## 3. Ciclo de un request (resumen)

```
public/index.php
   │ session_start(); if(!$_SESSION["session"]) → redirect ../   (public/index.php:2-3,11-12)
   │ define SYS_PATH, APP_PATH                                   (:5-6)
   ├── require lib/init.php
   │      └── App, Action, Css, Router, routes.php (×13), Response
   ├── require app/parts/header.php   (CSS estático + topbar + menu por rol)
   ├── $app = new App
   │      └── parseUrl() → Router::getAction(url) → require controllers/X.php
   │                     → $controller->method() → Response::render("vista")
   │                                              → require app/views/<vista>.php
   └── require app/parts/footer.php
          └── scripts base + new Action → <script src="../app/action/<method>.js">
```

---

## 4. Cómo se registran rutas y acciones

1. **Ruta web:** añadir línea en `app/http/routes.php`: `Router::add("nueva", "MainController", "nueva");`.
2. **Controller:** añadir método en `app/controllers/MainController.php`: `public function nueva(){ Response::render("nueva"); }`.
3. **Vista:** crear `app/views/nueva.php` (HTML).
4. **JS de la vista:** crear `app/action/nueva.js` (el inyector `Action` lo sirve automáticamente porque usa `<method>.js`).
5. **Menú (opcional):** agregar entrada en `app/parts/menu.php` y asociarla al array de rol correspondiente (`$menu_session_user` o `$menu_session_admin`, `menu.php:15-16`).

El nombre de ruta, método de controller, vista y archivo JS **deben coincidir** (convención por nombre). No hay configuración adicional.

---

## 5. Limitaciones del framework (verificadas)

- **Sin verbos HTTP:** el routing solo conoce el query param `url`; ignora GET/POST/PUT/DELETE. Toda interacción de datos es vía la API JS.
- **Sin parámetros dinámicos en rutas:** no existe `/usuario/:id`; las rutas son strings literales (`Router.php` usa `array_key_exists` exacto).
- **Sin middleware ni hooks:** la autenticación está hardcoded en `public/index.php:3` (chequeo de `$_SESSION["session"]`), fuera del framework.
- **Sin autorización por rol en el framework:** `menu.php` distingue rol al pintar el sidebar, pero **ningún Router/Controller enforza** `session_type`. Un asesor puede pedir `public/asesores` directamente y obtener la vista (la vista solo está oculta del menú).
- **Duplicación del parser de URL:** `parseUrl()` repetido en `App`, `Action` y `Css` (`App.php:21`, `Action.php:19`, `Css.php:20`) sin extracción a base común.
- **`require` no `include`:** un controller o vista faltante produce fatal, no degradación elegante.

---

## 6. Evidencia

- `cegroup/lib/init.php:1-8`
- `cegroup/lib/Router.php:1-22`
- `cegroup/lib/App.php:1-27`
- `cegroup/lib/Response.php:1-12`
- `cegroup/lib/Action.php:1-25`
- `cegroup/lib/Css.php:1-26`
- `cegroup/app/http/routes.php:1-15`
- `cegroup/app/controllers/MainController.php:1-17`
- `cegroup/app/parts/header.php:11-16,67`, `footer.php:28`, `menu.php:15-24`
- `cegroup/public/index.php:1-13`
- `cegroup/app/views/index.php` (0 bytes), `app/views/error.php:1-21`
- `grep -rn "new Css" cegroup/` → sin resultados (Css no instanciado)
