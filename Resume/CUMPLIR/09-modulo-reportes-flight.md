# Módulo REPORTES (Flight MVC) — exclusivo de CUMPLIR

> Subsistema de reportería independiente del resto de CUMPLIR. Vive en
> `cumplir/REPORTES/REPORTES/` y corre sobre el microframework **Flight PHP**
> con un patrón MVC propio (rutas → controllers → tools → views).
> **No existe equivalente en CEGROUP.**
>
> Toda cita de código es relativa a `cumplir/` salvo indicación contraria.
> Toda cita de BD proviene de `Resume/CUMPLIR/_evidence/`.

---

## 1. Qué es y por qué existe

Reportería operativa por **asesor** y por **día del mes**, sobre tres dominios:

| Dominio | Mide | Tabla cache |
|---|---|---|
| Gestiones | nº de gestiones y nº de operaciones distintas gestionadas | `reporte_gestion` |
| Acuerdos | nº de acuerdos y suma de su valor (por fecha de **registro**) | `reporte_acuerdos` |
| Proyección | nº de acuerdos y suma de su valor (por fecha **pactada de pago**) | `reporte_proyeccion` |

El sistema **no consulta las tablas fuente al pintar el reporte**. En cambio
mantiene tres tablas de **cache pre-computado** (una fila por asesor, una columna
por día) que se recalculan por demanda al invocar endpoints `POST`. Las vistas
sólo hacen `SELECT * FROM reporte_*` y pintan HTML. Es un patrón de
**caché materializado**: las celdas se llenan corriendo procesos de recálculo,
no consultando en vivo.

Diferencia Acuerdos vs Proyección (verificada en código):
- Acuerdos filtra por `fregistro` — el histórico de lo que se pactó.
  `tools/Acuerdos.php:8,15,31,40`.
- Proyección filtra por `facuerdo` — la fecha futura de pago comprometida.
  `tools/Proyeccion.php:8,15,31,40`.

---

## 2. Versión de Flight y bootstrap

### Versión

No hay constante de versión en el framework empaquetado (`grep -i version`
sobre `app/flight/` sólo devuelve la cadena HTTP `505 HTTP Version Not Supported`
en `net/Response.php:92`). **No verificado: número exacto de versión.**

Indicadores que apuntan a **Flight v3.x** (no v1/v2 clásico):
- `app/flight/Engine.php:3` y `Flight.php:3` usan `declare(strict_types=1)`.
- Métodos del Engine con firmas tipadas estrictas: `_route(string $pattern, callable $callback, bool $pass_route = false)` (`Engine.php:443`), `_patch(...)` (`Engine.php:479`), `_delete(...)` (`Engine.php:491`).
- `_json($data, int $code = 200, bool $encode = true, string $charset = 'utf-8', int $option = 0)` con parámetro `$option` para bitmask JSON (`Engine.php:583-595`) — firma propia de la línea v3.
- Copyright "Copyright (c) 2011, Mike Cao" + licencia MIT (`Engine.php:6-9`).

### Bootstrap

`REPORTES/REPORTES/index.php:1-7` — orden exacto de arranque:

```php
require 'app/db/db.php';        // línea 2  → define class conn (mysqli)
require 'app/flight/Flight.php'; // línea 3 → fachada estática Flight
require 'app/flight/autoload.php'; // línea 4 → autoloader del framework
require 'src/routes/ApiStart.php'; // línea 5 → registra TODAS las rutas
Flight::start();                 // línea 6  → despacha el request
```

`.htaccess:1-4` reescribe cualquier request que no sea archivo/directorio real
hacia `index.php`, de modo que Flight reciba la URL completa:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

`src/routes/ApiStart.php:5-15` agrega los archivos de rutas en este orden:
`Index.php`, `Gestiones.php`, `Acuerdos.php`, `Proyeccion.php`, `Reportes.php`
y por último `Error.php` (el catch-all `*` debe registrarse al final para no
ensombrecer las rutas concretas).

### Conexión a BD (`app/db/db.php:1-10`)

```php
class conn extends mysqli{
  function __construct(){
    parent::__construct("localhost","u815310395_data","«REDACTADO»","u815310395_data");
    if (mysqli_connect_error()) { print("error de conexion"); }
  }
}
```

Credenciales **hardcodeadas en claro** (mismo `u815310395_data` que el resto de
CUMPLIR). Cada función de los `tools/` y de las vistas instancia `new conn` por
llamada — sin pool ni reutilización de conexión.

---

## 3. Todas las rutas

Registradas en `src/routes/*.php` (cargadas por `ApiStart.php`). **18 rutas en
total**: 17 concretas + 1 catch-all.

> Corrección al doc previo: contaba "11 total" y daba `Reportes.php` como
> "no registra rutas". Es falso — `routes/Reportes.php:5-27` registra **6 rutas GET**.

| # | Método | Path | Archivo de ruta | Acción (función) | Controlador |
|---|---|---|---|---|---|
| 1 | GET | `/` | `routes/Index.php:5` | `Init()` | `controllers/Index.php` |
| 2 | GET | `/reportes` | `routes/Index.php:8` | `Init()` | `controllers/Index.php` |
| 3 | POST | `/gestiones/date` | `routes/Gestiones.php:5` | `postGestionesDate()` | `controllers/Gestiones.php` |
| 4 | POST | `/gestiones/all` | `routes/Gestiones.php:9` | `postGestionesAll()` | `controllers/Gestiones.php` |
| 5 | DELETE | `/gestiones/all` | `routes/Gestiones.php:13` | `deleteGestionesAll()` | `controllers/Gestiones.php` |
| 6 | POST | `/acuerdos/date` | `routes/Acuerdos.php:5` | `postAcuerdosDate()` | `controllers/Acuerdos.php` |
| 7 | POST | `/acuerdos/all` | `routes/Acuerdos.php:9` | `postAcuerdosAll()` | `controllers/Acuerdos.php` |
| 8 | DELETE | `/acuerdos/all` | `routes/Acuerdos.php:13` | `deleteAcuerdosAll()` | `controllers/Acuerdos.php` |
| 9 | POST | `/proyeccion/date` | `routes/Proyeccion.php:5` | `postProyeccionDate()` | `controllers/Proyeccion.php` |
| 10 | POST | `/proyeccion/all` | `routes/Proyeccion.php:9` | `postProyeccionAll()` | `controllers/Proyeccion.php` |
| 11 | DELETE | `/proyeccion/all` | `routes/Proyeccion.php:13` | `deleteProyeccionAll()` | `controllers/Proyeccion.php` |
| 12 | GET | `/reportes/gestiones` | `routes/Reportes.php:5` | `gestiones()` | `controllers/Reportes.php` |
| 13 | GET | `/reportes/acuerdos` | `routes/Reportes.php:9` | `acuerdos()` | `controllers/Reportes.php` |
| 14 | GET | `/reportes/proyeccion` | `routes/Reportes.php:13` | `proyeccion()` | `controllers/Reportes.php` |
| 15 | GET | `/reportes/gestiones/file` | `routes/Reportes.php:17` | `gestionesFile()` | `controllers/Reportes.php` |
| 16 | GET | `/reportes/acuerdos/file` | `routes/Reportes.php:21` | `acuerdosFile()` | `controllers/Reportes.php` |
| 17 | GET | `/reportes/proyeccion/file` | `routes/Reportes.php:25` | `proyeccionFile()` | `controllers/Reportes.php` |
| 18 | `*` | (cualquiera no resuelta) | `routes/Error.php:3` | JSON `{status:false, message:'Ruta no encontrada '}` | — |

Las rutas POST/DELETE son **mutaciones de cache** (recálculo / reseteo). Las GET
son lectura (vistas HTML). Las `/file` apuntan a vistas Excel que **están vacías**
(ver §6).

---

## 4. Controladores y tools

### 4.1 `controllers/Index.php`

`Init()` (`controllers/Index.php:2-4`) hace `require 'src/view/Index.php'` — la
portada con tres botones (GESTIONES / ACUERDOS / PROYECCION).

### 4.2 `controllers/Reportes.php` (renderers)

Seis funciones, cada una incluye una vista (`controllers/Reportes.php:3-25`):
`gestiones()`→`view/gestiones.php`, `acuerdos()`→`view/acuerdos.php`,
`proyeccion()`→`view/proyeccion.php`, `gestionesFile()`→`view/gestionesFile.php`,
`acuerdosFile()`→`view/acuerdosFile.php`, `proyeccionFile()`→`view/proyeccionFile.php`.

> Nota: el controller incluye `view/acuerdosFile.php` (con F mayúscula,
> `controllers/Reportes.php:20`) pero en disco el archivo es `acuerdosfile.php`
> (minúscula). En Linux (case-sensitive) ese `require` fallaría. **No verificado:
> comportamiento en producción** (depende del FS del servidor). El archivo de
> todos modos está vacío.

### 4.3 `controllers/Gestiones.php` / `Acuerdos.php` / `Proyeccion.php`

Los tres tienen la misma estructura de 3 acciones. Patrón (ejemplo Gestiones,
`controllers/Gestiones.php:9-41`):

- **`post<Dominio>Date()`** — lee `$_POST['date']` (formato `YYYY-MM-DD`),
  extrae el día con `date('d', strtotime($date))`, recorre `getAsesores()` y
  para cada asesor calcula dos métricas del día y llama al `insertReport...`
  que actualiza las columnas `_<dia>`.
- **`post<Dominio>All()`** — lee `$_POST['date']`, extrae el mes con `date('m', ...)`,
  recorre los asesores y actualiza las columnas de total (`_t`).
- **`delete<Dominio>All()`** — `TRUNCATE` de la tabla y re-inserta una fila vacía
  (todo en `'-'`) por cada asesor activo. Es el **reseteo / inicialización**.

Ninguna de las 9 acciones de mutación devuelve respuesta JSON ni status — sólo
ejecutan los `UPDATE`/`INSERT` y terminan (Flight cierra con respuesta vacía).

### 4.4 `tools/GetAsesores.php`

`getAsesores()` (`tools/GetAsesores.php:2-12`):

```sql
SELECT `username` FROM `t_usuarios` WHERE `usertype` = 1 ORDER BY `username` ASC
```

Devuelve un array de usernames. Define **"asesor activo" = `usertype = 1`**.
Se invoca al inicio de cada acción para iterar fila por fila.

### 4.5 `tools/Gestiones.php` — fuente: `t_gestiones`

| Función | SQL (líneas) | Devuelve |
|---|---|---|
| `getGestionAsesorDate($asesor,$date)` | `SELECT id FROM t_gestiones WHERE asesor='$asesor' AND fecha='$date'` (`:4`) | `mysqli_num_rows` (nº gestiones del día) |
| `getGestionAsesorDateOpe($asesor,$date)` | `SELECT DISTINCT operacion FROM t_gestiones WHERE asesor='$asesor' AND fecha='$date'` (`:11`) | nº operaciones distintas. **`echo $sql` — ver §7** |
| `getGestionAsesorAll($asesor,$date)` | `... fecha BETWEEN '2026-$date-01' AND '2026-$date-31'` (`:17-20`) | nº gestiones del mes. **Año `2026` hardcoded** |
| `getGestionAsesorAllOpe($asesor,$date)` | `SELECT DISTINCT operacion ... BETWEEN '2026-...-01' AND '2026-...-31'` (`:26-29`) | nº operaciones distintas del mes |
| `cleanReportGestiones()` | `TRUNCATE reporte_gestion` (`:36`) | — |
| `initReportGestiones($asesor)` | `INSERT INTO reporte_gestion (...66 cols...) VALUES (NULL,'$asesor','-',...,'-')` (`:42-44`) | fila inicial con 64 celdas en `'-'` |
| `insertReportGestion($asesor,$ges,$ope,$dia)` | `UPDATE reporte_gestion SET ges_<dia>='$ges', ope_<dia>='$ope' WHERE asesor='$asesor'` (`:51-52`) | — |
| `insertReportGestionTotal($asesor,$ges,$ope)` | `UPDATE reporte_gestion SET ges_t='$ges', ope_t='$ope' WHERE asesor='$asesor'` (`:58-59`) | — |

Hay una función comentada `getGestionAsesorAllSum` (`:65-75`) con año `2023` y
formato de fecha `01-$date-2023` — código muerto.

### 4.6 `tools/Acuerdos.php` — fuente: `t_acuerdos`, filtra por `fregistro`

| Función | SQL (líneas) | Devuelve |
|---|---|---|
| `getAcuerdosAsesorDate` | `SELECT id FROM t_acuerdos WHERE asesor='$asesor' AND fregistro='$date'` (`:8`) | nº acuerdos del día |
| `getAcuerdosAsesorDateVal` | `SELECT SUM(valor) AS total FROM t_acuerdos WHERE asesor='$asesor' AND fregistro='$date'` (`:15`) | total valor (0 si `<1`, `:18-22`) |
| `getAcuerdosAsesorAll` | `... fregistro BETWEEN '2025-$date-01' AND '2025-$date-31'` (`:28-31`) | nº acuerdos del mes. **Año `2025` hardcoded** |
| `getAcuerdosAsesorAllOpe` | `SUM(valor) ... fregistro BETWEEN '2025-...'` (`:37-40`) | total valor del mes |
| `cleanReportAcuerdos` | `TRUNCATE reporte_acuerdos` (`:68`) | — |
| `initReportAcuerdos` | `INSERT INTO reporte_acuerdos (...66 cols...) VALUES (NULL,'$asesor','-',...)` (`:74-76`) | fila inicial |
| `insertReportAcuerdos($asesor,$acu,$val,$dia)` | `UPDATE reporte_acuerdos SET acu_<dia>='$acu', val_<dia>='$val' WHERE asesor='$asesor'` (`:84-85`) | — |
| `insertReportAcuerdosTotal` | `UPDATE reporte_acuerdos SET acu_t='$acu', val_t='$val' WHERE asesor='$asesor'` (`:91-92`) | — |

### 4.7 `tools/Proyeccion.php` — fuente: `t_acuerdos`, filtra por `facuerdo`

Estructura idéntica a Acuerdos; la única diferencia funcional es que filtra por
`facuerdo` en lugar de `fregistro`, y escribe columnas `pro_*`:

| Función | SQL (líneas) |
|---|---|
| `getProyeccionAsesorDate` | `SELECT id FROM t_acuerdos WHERE asesor='$asesor' AND facuerdo='$date'` (`:8`) |
| `getProyeccionAsesorDateVal` | `SUM(valor) ... facuerdo='$date'` (`:15`) |
| `getProyeccionAsesorAll` | `... facuerdo BETWEEN '2025-$date-01' AND '2025-$date-31'` (`:28-31`) — **Año `2025` hardcoded** |
| `getProyeccionAsesorAllOpe` | `SUM(valor) ... facuerdo BETWEEN '2025-...'` (`:37-40`) |
| `cleanReportProyeccion` | `TRUNCATE reporte_proyeccion` (`:68`) |
| `initReportProyeccion` | `INSERT INTO reporte_proyeccion (...pro_* / val_*...)` (`:74-76`) |
| `insertReportProyeccion($asesor,$acu,$val,$dia)` | `UPDATE reporte_proyeccion SET pro_<dia>='$acu', val_<dia>='$val' WHERE asesor='$asesor'` (`:84-85`) |
| `insertReportProyeccionTotal` | `UPDATE reporte_proyeccion SET pro_t='$acu', val_t='$val' WHERE asesor='$asesor'` (`:91-92`) |

### 4.8 `tools/headExcel.php`

**Archivo vacío — 0 bytes** (verificado: `wc -c tools/headExcel.php` → `0`).
No define ninguna función. Nadie lo incluye (`require` no aparece en ningún
controller/tool leído). Es un stub muerto.

> Corrección al doc previo: lo describía como "Excel headers — no leído al
> detalle". Realidad: está completamente vacío.

---

## 5. Modelo de datos cache (tablas `reporte_*`)

Tres tablas "anchas" en la BD de CUMPLIR (no existen en CEGROUP). DDL en
`_evidence/ddl/reporte_*.sql`.

### Estructura

Las tres siguen el mismo molde: `id` + `asesor` + **31 pares día** + **1 par total**.

- **`reporte_gestion`** (66 columnas): `id`, `asesor`, luego `ges_01`/`ope_01` …
  `ges_31`/`ope_31`, y `ges_t`/`ope_t`. Confirmado en `ddl/reporte_gestion.sql`:
  los pares van de `ges_01`/`ope_01` a `ges_31`/`ope_31`. Cada `ges_*`/`ope_*` es
  `varchar(6)`.
  → Sí: **`ges_01..ges_31` = los 31 días del mes**; `ope_*` = operaciones distintas
  de ese día; `*_t` = total mensual.
- **`reporte_acuerdos`** (66 columnas): `acu_01`/`val_01` … `acu_31`/`val_31`,
  `acu_t`/`val_t`. `acu_*` es `varchar(6)` (conteo), `val_*` es `varchar(20)`
  (monto). `ddl/reporte_acuerdos.sql`.
- **`reporte_proyeccion`** (66 columnas): `pro_01`/`val_01` … `pro_31`/`val_31`,
  `pro_t`/`val_t`. Mismos tipos. `ddl/reporte_proyeccion.sql`.

Las tres con `PRIMARY KEY (id)`, `ENGINE=InnoDB`, `utf8mb4_unicode_ci`,
`AUTO_INCREMENT=11` (→ 10 filas insertadas históricamente).

> Detalle de tipos: TODO se guarda como `varchar`, incluidos conteos y montos.
> El valor "vacío" es el literal `'-'` (no NULL, no 0). Las vistas tratan `'-'`
> como caso especial para mostrar guion en vez de número (`view/acuerdos.php:33-37`).

### Estado real en producción (evidencia)

| Tabla | Filas | `UPDATE_TIME` | ¿Datos reales? |
|---|---|---|---|
| `reporte_gestion` | **10** | `2026-06-18 19:43:23` | **Sí** — 38 columnas con valores numéricos reales (p.ej. `ges_02='46'`, `ges_04='41'`) |
| `reporte_acuerdos` | **10** | `2026-06-02 09:37:10` | **No** — todas las celdas de día/total siguen en `'-'` (0 columnas con valor real) |
| `reporte_proyeccion` | **10** | `2026-06-02 09:37:15` | **No** — todas en `'-'` (0 columnas con valor real) |

Fuentes: `_evidence/RESUMEN.md` (filas y update_time), `_evidence/column_profiles.json`
(`row_count: 10` por tabla; perfil de valores por columna),
`_evidence/samples/reporte_*.json` (muestra: `reporte_gestion` trae números,
`reporte_acuerdos`/`reporte_proyeccion` traen `'-'` en `acu_01`/`val_01`/`pro_01`…).

> **Corrección al doc previo y a la premisa del encargo.**
> El doc previo (`resumen/10-modulo-reportes.md`) afirmaba 10 filas en las tres
> tablas, sin distinguir contenido. La premisa del encargo decía "2 de 3 estaban
> vacías". Ninguna de las dos es exacta:
> - **Las tres tablas tienen 10 filas** (no hay tablas "vacías" a nivel de filas).
> - Pero **sólo `reporte_gestion` contiene métricas reales recalculadas.**
>   `reporte_acuerdos` y `reporte_proyeccion` están **inicializadas pero nunca
>   recalculadas**: sus filas existen con todos los valores en `'-'`.
> En la práctica funcional, Acuerdos y Proyección no muestran datos — pero el
> motivo es "init sin recálculo", no "tabla vacía".

### Frecuencia de recálculo (inferida de update_time)

No hay cron ni scheduler en el subsistema (no se encontró ningún proceso
programado; el recálculo depende de invocar los `POST` manualmente). Los
`UPDATE_TIME` sugieren ejecuciones manuales aisladas:
- `reporte_gestion` tocada por última vez el **2026-06-18** → se sigue usando.
- `reporte_acuerdos`/`reporte_proyeccion` tocadas el **2026-06-02** (mismas ~6 s
  de diferencia) → probablemente sólo se corrió el `DELETE /…/all` (init) ese día
  y nunca el recálculo de datos.
**No verificado: existencia de cron externo** que dispare estos POST.

---

## 6. Vistas y exportación

### Vistas HTML (conectadas a rutas, funcionan)

| Vista | Ruta que la sirve | Estado |
|---|---|---|
| `view/Index.php` | GET `/`, GET `/reportes` | Portada con 3 botones (`view/Index.php:46-51`) |
| `view/gestiones.php` | GET `/reportes/gestiones` | Tabla HTML completa, `SELECT * FROM reporte_gestion` |
| `view/acuerdos.php` | GET `/reportes/acuerdos` | Tabla HTML completa, `SELECT * FROM reporte_acuerdos` |
| `view/proyeccion.php` | GET `/reportes/proyeccion` | Tabla HTML, `SELECT * FROM reporte_proyeccion` |
| `view/style.css` | (estática, 547 bytes) | Estilos |

Las tres vistas de tabla comparten estructura: helpers `diasMes()` (días reales
del mes vía `cal_days_in_month`, zona `America/Bogota`), `tittleDay()` /
`tittleTipe()` (cabeceras), `dataAsesores()`, `dataGestion()`, `dataTotal()`
que leen la tabla cache y pintan `<div>`s.

Particularidad de `view/proyeccion.php`: pinta **desde el día de hoy hasta fin de
mes** (`$hoy = date('j')`, bucles `for ($i=$hoy; $i <= $dias; $i++)`,
`view/proyeccion.php:46,57,103`) — coherente con "proyección futura". Gestiones y
Acuerdos pintan el mes completo desde el día 1.

### Vistas Excel (huérfanas / rotas)

| Vista | Ruta | Estado real |
|---|---|---|
| `view/gestionesFile.php` | GET `/reportes/gestiones/file` | **0 bytes — vacío** |
| `view/acuerdosfile.php` | GET `/reportes/acuerdos/file` | **0 bytes — vacío** |
| `view/proyeccionFile.php` | GET `/reportes/proyeccion/file` | **0 bytes — vacío** |

Verificado: `wc -c` sobre los tres → `0`; fecha de archivo `Feb 16 2023`. Las
rutas `/file` existen y están conectadas a controllers (`controllers/Reportes.php:15-25`),
pero al incluir un archivo vacío producen **respuesta en blanco**: la exportación
Excel **no está implementada**. Sumado a que `tools/headExcel.php` también está
vacío, la funcionalidad de exportar a Excel quedó como esqueleto sin contenido.

> Corrección al doc previo: especulaba que estos archivos "probablemente generen
> los Excel descargables". Realidad verificada: están **vacíos** y no generan nada.

---

## 7. Defectos verificados

1. **`echo $sql` filtra la query SQL al output.** `tools/Gestiones.php:11`:
   `echo $sql = "SELECT DISTINCT operacion FROM t_gestiones WHERE asesor='$asesor' AND fecha='$date'";`
   En cada `POST /gestiones/date`, por cada asesor, la consulta se imprime en la
   respuesta HTTP. Fuga de estructura de BD y de datos.

2. **Años hardcodeados e inconsistentes entre dominios.**
   | Archivo | Año en `BETWEEN` mensual | Líneas |
   |---|---|---|
   | `tools/Gestiones.php` | **2026** | `:17-18`, `:26-27` |
   | `tools/Acuerdos.php` | **2025** | `:28-29`, `:37-38` |
   | `tools/Proyeccion.php` | **2025** | `:28-29`, `:37-38` |
   El reporte mensual de Gestiones cuenta 2026; el de Acuerdos y Proyección,
   2025. Con la fecha actual del entorno (2026), los totales mensuales de
   Acuerdos/Proyección consultan un año que ya pasó → dan 0. Explica por qué
   esas dos tablas nunca se llenaron con datos reales.

3. **Día 31 fijo en el rango mensual.** Todos los `BETWEEN` usan
   `'<año>-<mes>-01'` … `'<año>-<mes>-31'` (`tools/Gestiones.php:17-18`,
   `tools/Acuerdos.php:28-29`, `tools/Proyeccion.php:28-29`). Para meses de 28/29/30
   días el límite superior es una fecha inexistente; en MySQL el `BETWEEN` con
   `'2026-02-31'` no error-ea pero el comportamiento de comparación de cadena/fecha
   es frágil. **No verificado: impacto exacto en MySQL del servidor.**

4. **Sin autenticación.** No hay guard, middleware, ni verificación de sesión en
   `index.php`, `ApiStart.php` ni en ningún controller. Cualquiera que alcance la
   URL puede leer reportes (GET) y, peor, disparar `DELETE /<dominio>/all`
   (TRUNCATE + reinit) o reescribir el cache (POST). Flight no añade ningún
   `before` filter de auth.

5. **SQL injection en todas las queries.** `$asesor`, `$date` y `$dia` se
   interpolan sin escapar ni parametrizar:
   - `$asesor` viene de `getAsesores()` (BD interna) — riesgo bajo pero presente.
   - `$date` viene directo de `$_POST['date']` (controllers `:10`, `:21`) — entrada
     de usuario sin validar, interpolada en `fecha='$date'` / `fregistro='$date'` /
     `facuerdo='$date'`.
   - `$dia`/`$mes` derivan de `$date` vía `date()`, pero `$dia` se concatena al
     **nombre de columna** (`$upGes = 'ges_'.$dia`, `tools/Gestiones.php:49`) — si
     `$date` no es fecha válida, `strtotime` puede producir valores inesperados.
   No hay un solo `prepare`/`bind_param` en el subsistema.

6. **TRUNCATE irreversible sin confirmación.** `DELETE /<dominio>/all` ejecuta
   `TRUNCATE` (`tools/Gestiones.php:36`, etc.) y reinicia el cache. Combinado con
   la falta de auth (defecto 4), un request anónimo borra el cache.

7. **Comparación de monto sobre varchar.** `if ($row["total"]<1) return 0;`
   (`tools/Acuerdos.php:18`, `tools/Proyeccion.php:18`) compara el `SUM(valor)`
   con `1`; el valor se almacena luego como `varchar(20)`. La coerción
   PHP/MySQL funciona aquí, pero deja montos como texto en la tabla.

8. **`require` con casing inconsistente.** `controllers/Reportes.php:20` incluye
   `view/acuerdosFile.php` (F mayúscula) pero el archivo en disco es
   `acuerdosfile.php`. En un FS case-sensitive falla el `require`. **No verificado**
   en el servidor real.

---

## 8. Sincronización del cache con las tablas fuente — y riesgos

**No hay sincronización automática.** El cache (`reporte_*`) y las tablas fuente
(`t_gestiones`, `t_acuerdos`) están **completamente desacopladas**:

- Las fuentes cambian en tiempo real cuando los asesores registran gestiones y
  acuerdos en CUMPLIR.
- El cache sólo cambia cuando alguien invoca manualmente los endpoints `POST`
  (`/date` para un día, `/all` para totales) o `DELETE` (reset). No hay trigger
  de BD, no hay cron en el subsistema, no hay invalidación.

Flujo operativo real (inferido del código + update_time):

```
1. DELETE /<dominio>/all   → TRUNCATE + 1 fila '-' por asesor (init)
2. POST   /<dominio>/date  → recalcula columnas _<dia> de un día concreto
   (repetir por cada día que se quiera reflejar)
3. POST   /<dominio>/all   → recalcula totales _t del mes
4. GET    /reportes/<dominio> → la vista lee el cache y lo pinta
```

### Riesgos derivados

- **Datos obsoletos por diseño.** El reporte muestra lo que se calculó la última
  vez que alguien corrió los POST, no el estado actual. `reporte_gestion` se
  actualizó por última vez el 2026-06-18; cualquier gestión posterior no aparece.
- **Acuerdos y Proyección muestran todo en blanco** en producción: nunca se
  recalcularon con datos reales (sólo init el 2026-06-02), agravado por el año
  `2025` hardcodeado que hace que el recálculo mensual de 2026 dé 0.
- **Pérdida total con un request.** `DELETE /all` sin auth borra el cache; para
  reconstruirlo hay que recorrer manualmente día a día con `POST /date`.
- **No idempotente entre dominios.** Gestiones (2026) y Acuerdos/Proyección (2025)
  nunca cuadrarán para el mismo periodo.
- **Exportación Excel inexistente** pese a tener rutas publicadas (`/file` → archivos
  vacíos), lo que puede dar al usuario páginas en blanco sin error claro.

---

## 9. Evidencia citada

**Código** (`cumplir/REPORTES/REPORTES/`):
- `index.php:1-7`, `.htaccess:1-4`, `app/db/db.php:1-10`
- `app/flight/Engine.php:3,6-9,443,479,491,583-595`, `app/flight/Flight.php:3`
- `src/routes/{ApiStart:5-15, Index:5-10, Gestiones:5-15, Acuerdos:5-15, Proyeccion:5-15, Reportes:5-27, Error:3-7}.php`
- `src/controllers/{Index:2-4, Gestiones:9-41, Acuerdos:9-41, Proyeccion:9-41, Reportes:3-25}.php`
- `src/tools/{GetAsesores:2-12, Gestiones:4-59, Acuerdos:8-92, Proyeccion:8-92}.php`; `tools/headExcel.php` (0 bytes)
- `src/view/{Index:46-51, gestiones, acuerdos:33-37, proyeccion:46-103}.php`; `view/{gestionesFile, acuerdosfile, proyeccionFile}.php` (0 bytes)

**BD** (`Resume/CUMPLIR/_evidence/`):
- `ddl/reporte_gestion.sql`, `ddl/reporte_acuerdos.sql`, `ddl/reporte_proyeccion.sql`
- `column_profiles.json` (`reporte_gestion`/`reporte_acuerdos`/`reporte_proyeccion`: `row_count:10`)
- `samples/reporte_gestion.json` (números reales), `samples/reporte_acuerdos.json` / `samples/reporte_proyeccion.json` (`'-'`)
- `RESUMEN.md` (filas + UPDATE_TIME), `tables.json`
