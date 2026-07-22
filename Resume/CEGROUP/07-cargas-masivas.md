# CEGROUP — Cargas masivas (importadores) y exportadores

> Fuente: `cegroup/api/UPDATE/`, `cegroup/api/file/update/`, `cegroup/api/file/sql/`. Citas `archivo:línea` relativas a `cegroup/`.
> Cruce de tablas/columnas destino contra `Resume/CEGROUP/_evidence/{tables.json,column_profiles.json}`.

---

## 1. Resumen ejecutivo

Tres subsistemas paralelos, todos fuera del API REST, todos **sin autenticación** y con **SQL por concatenación**:

| Subsistema | Acceso | Propósito | Auth |
|---|---|---|---|
| `api/UPDATE/` | UI HTML (`index.php`+`index.js`+`upload.php`) | Importar CSV con interfaz | No |
| `api/file/update/` | Ejecución directa por URL (`procesar.php`) | Importar/actualizar/borrar desde CSV ya en disco | No |
| `api/file/sql/` | URL directa → descarga `.xls` | Exportar reportes a Excel | No |

**Patrón común de todo importador** (`procesar.php`): `set_time_limit(60000000000)` (sin timeout efectivo) → `fopen` del CSV → `fgetcsv($fp, 100000000, ";")` (**delimitador `;`**) → loop fila a fila → SQL interpolado → `query`. **Ninguno** hace `TRUNCATE`/borra antes ni usa `UPSERT` → re-cargar el mismo CSV **duplica filas**. **Ninguno** usa transacción → un fallo a mitad deja filas parciales sin rollback.

---

## 2. `api/UPDATE/` — Importadores con interfaz

### 2.1 Menú y estructura

`UPDATE/index.php` lista 9 atajos: `ASIGNACION, BASE, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS, GESTIONES, TELEFONOS` (`UPDATE/index.php:16-43`). Adicionales no listados en el menú: `MAIL/` (sin UI), `elimina.php` (script ad-hoc), `UPDATEOPERACIONGESTION/` (**carpeta vacía**, verificado).

Cada subcarpeta del menú sigue:
```
<DOMINIO>/
├── index.php     formulario HTML (subir archivo) + carga index.js
├── index.js      AJAX: upload → procesar
├── upload.php    move_uploaded_file → file/<filename>
├── procesar.php  lee file/<DOMINIO>.csv → INSERT en DB
└── file/         destino del CSV subido
```

### 2.2 `upload.php` (idéntico en todas las carpetas)

```php
// UPDATE/BASE/upload.php:2-13 (mismo cuerpo en ASIGNACION, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS, GESTIONES, TELEFONOS)
if ($_FILES['file']['error'] > 0) echo 'error';
else {
  move_uploaded_file($_FILES['file']['tmp_name'], 'file/'.$_FILES['file']['name']);
  echo 'sucess';
}
```
**Sin validación de extensión, MIME ni tamaño.** Guarda con el nombre original → permite subir un `.php` y ejecutarlo después (RCE). Sin auth.

### 2.3 Procesadores — formato CSV y mapeo (delimitador `;`)

> Conexión: `include("../DB.php")` (`UPDATE/DB.php`, copia de `lib/DB.php`).

#### BASE — `UPDATE/BASE/procesar.php` → `t_base`
- Lee `file/BASE.csv`, **20 columnas** (`procesar.php:10-33`):

  | idx | col CSV | campo `t_base` | transformación |
  |---|---|---|---|
  | 0 | operacion | operacion | — |
  | 1 | cuenta | cuenta | — |
  | 2 | tcedula | tcedula | — |
  | 3 | tnombre | tnombre | `mb_strtoupper(utf8_encode(...))` |
  | 4-5 | ttel1, ttel2 | ttel1, ttel2 | — |
  | 6 | ccedula | ccedula | — |
  | 7 | cnombre | cnombre | `mb_strtoupper(utf8_encode(...))` |
  | 8-9 | ctel1, ctel2 | ctel1, ctel2 | — |
  | 10 | gcedula | gcedula | — |
  | 11 | gnombre | gnombre | `mb_strtoupper(utf8_encode(...))` |
  | 12-13 | gtel1, gtel2 | gtel1, gtel2 | — |
  | 14 | fvencimiento | fvencimiento | — |
  | 15 | fingreso | fingreso | — |
  | 16 | sucursal | sucursal | — |
  | 17 | dependencia | dependencia | — |
  | 18 | condicion | condicion | — |
  | 19 | **banco** | banco | — |

- `INSERT INTO t_base (...20 cols...)` (`procesar.php:37-43`). Responde JSON `{process:<n_filas>, result:'sucess'}`.
- **Cruce BD OK:** las 20 columnas existen en `t_base` (incl. `banco varchar(30)`). `t_base.operacion` es PK bigint.

#### ASIGNACION — `UPDATE/ASIGNACION/procesar.php` → `t_asignacion`
- `file/ASIGNACION.csv`, **2 cols**: `operacion;asesor` (`procesar.php:10-11`).
- `INSERT INTO t_asignacion (id,operacion,asesor) VALUES (NULL,...)` (`:12-13`).

#### CAMPANA — `UPDATE/CAMPANA/procesar.php` → `t_campana`
- `file/CAMPANA.csv`, **2 cols**: `operacion;campana` (`:10-11`).
- `INSERT INTO t_campana (id,operacion,campana)` (`:13`).

#### CARTERA — `UPDATE/CARTERA/procesar.php` → `t_cartera`
- `file/CARTERA.csv`, **2 cols**: `operacion;cartera` (`:10-11`).
- `INSERT INTO t_cartera (id,operacion,cartera)` (`:12-13`).

#### DECIL — `UPDATE/DECIL/procesar.php` → `t_decil`
- `file/DECIL.csv`, **2 cols**: `operacion;decil` (`:10-11`).
- `INSERT INTO t_decil (id,operacion,decil)` (`:13-14`).

#### SALDOS — `UPDATE/SALDOS/procesar.php` → `t_saldos`
- `file/SALDOS.csv`, **3 cols**: `operacion;capital;total` (`:9-11`).
- `INSERT INTO t_saldos (id,operacion,capital,total)` (`:12-13`).

#### PROCESOS — `UPDATE/PROCESOS/procesar.php` → `t_procesos`
- `file/PROCESOS.csv`, **5 cols**: `operacion;estado;sub;fgestion;asesor` (`:10-14`).
- `INSERT INTO t_procesos (id,operacion,estado,sub,fgestion,asesor)` (`:17`).

#### TELEFONOS — `UPDATE/TELEFONOS/procesar.php` → `t_telefonos`
- `file/TELEFONOS.csv`, **4 cols**: orden `operacion;asesor;telefono;detalle` → mapeo `$d[0]=operacion, $d[1]=asesor, $d[2]=telefono, $d[3]=detalle` (`:9-12`).
- `INSERT INTO t_telefonos (id,operacion,asesor,telefono,detalle)` (`:14-15`).
- **Defecto:** `echo $sql` antes de ejecutar (`:14`) → fuga del SQL al output. Hay un bloque comentado (`:23-34`) con esquema alterno de 3 columnas (sin asesor) — versión previa.

#### GESTIONES — `UPDATE/GESTIONES/procesar.php` → `t_gestiones`
- `file/GESTIONES.csv`, **2 cols**: `operacion;gestion` → `$d[0]=operacion, $d[1]=gestion` (`:10`,`:14`).
- `INSERT INTO t_gestiones (id,operacion,asesor,nombre,fecha,hora,gestion)` con **`asesor='MASIVO'` fijo**, `nombre=''`, `fecha=date('Ymd')`, `hora=date('h:i:s A')` (`:17-18`). Gestiones atribuidas a usuario sintético `MASIVO`.

#### MAIL — `UPDATE/MAIL/procesar.php` → `t_email`
- **Sin UI ni upload** (no aparece en el menú). Lee `MAIL.csv` **en la raíz de `MAIL/`** (no en `file/`), **2 cols**: `operacion;mail` (`:6`,`:10-11`).
- `INSERT INTO t_email (id,operacion,email)` (`:12`).

### 2.4 `UPDATE/elimina.php` — borrado masivo (deja huérfanos)

```php
// UPDATE/elimina.php:8-14
$fp = fopen("ELIMINAR.csv","r");
while ($d=fgetcsv($fp,100000000,";")){
  $sql="DELETE FROM `t_base` WHERE `t_base`.`operacion` = '$d[0]'";
  $obj->query($sql);
}
```
- Borra **solo de `t_base`**. NO toca las demás tablas relacionadas (`t_asignacion, t_campana, t_cartera, t_decil, t_procesos, t_saldos, t_acuerdos, t_gestiones, t_telefonos, t_alertas, t_email, t_resumen, t_pagos`).
- **Confirmado por modelo de datos:** no hay claves foráneas (relaciones por convención sobre `operacion`) → cada borrado deja **huérfanos en hasta 13 tablas**. Tras `elimina.php`, los endpoints con INNER JOIN (`g_operacion`) dejan de ver el crédito, pero sus pagos/acuerdos/gestiones quedan vivos y sueltos.

---

## 3. `api/file/update/` — Importadores sin UI (ejecución directa)

10 subcarpetas, cada una **solo `procesar.php`** (sin `index.php`/`upload.php`), que lee un CSV ya residente junto al script. Conexión: `include("../../../lib/DB.php")`.

### 3.1 Importadores duplicados de `UPDATE/`

Mismo destino y formato que sus equivalentes en `UPDATE/`, con dos diferencias de comportamiento: imprimen el contador/SQL a HTML (`echo $num."<br>"` / `echo $sql` en error) y **no** devuelven JSON.

| Carpeta | CSV | Cols (`;`) | Destino | Equivale a |
|---|---|---|---|---|
| `ASIGNAR/` | `ASIGNACION.csv` | operacion;asesor | `t_asignacion` | `UPDATE/ASIGNACION` (nombre distinto) |
| `BASE/` | `BASE.csv` | **19 cols (sin `banco`)** | `t_base` | `UPDATE/BASE` (¡difiere!) |
| `CAMPANA/` | `CAMPANA.csv` | operacion;campana | `t_campana` | `UPDATE/CAMPANA` |
| `CARTERA/` | `CARTERA.csv` | operacion;cartera | `t_cartera` | `UPDATE/CARTERA` |
| `DECIL/` | `DECIL.csv` | operacion;decil | `t_decil` | `UPDATE/DECIL` |
| `PROCESOS/` | `PROCESOS.csv` | operacion;estado;sub;fgestion;asesor | `t_procesos` | `UPDATE/PROCESOS` |
| `SALDOS/` | `SALDOS.csv` | operacion;capital;total | `t_saldos` | `UPDATE/SALDOS` |

> **Diferencia crítica BASE:** `file/update/BASE/procesar.php` inserta **19 columnas (omite `banco`)** (`procesar.php:36-42`), mientras `UPDATE/BASE/procesar.php` inserta **20 (incluye `banco`)**. La columna `banco` existe en la base (evidencia). Usar el importador antiguo deja `banco` en NULL.

### 3.2 Procesadores exclusivos de `file/update/`

- **`ELIMINAPRO/procesar.php`** — `ELIMINAPRO.csv` (1 col `operacion`) → `DELETE FROM t_procesos WHERE operacion='$op'` por fila (`:10`,`:14`). Limpieza masiva del registro de procesos.
- **`UPBASE/procesar.php`** — `UPBASE.csv` (**3 cols**: `operacion;sucursal;dependencia`) → `UPDATE t_base SET sucursal='$su', dependencia='$de' WHERE operacion='$op'` con `mb_strtoupper(utf8_encode())` en su/de (`:10-12`,`:16`).
- **`UPESTATE/procesar.php`** — `ESTADOS.csv` (**3 cols**: `operacion;estado;sub`) → `UPDATE t_procesos SET estado='$es', sub='$su' WHERE operacion='$op'` (`:10-12`,`:16`).

### 3.3 ¿Por qué dos árboles?

`No verificado` (requiere confirmación con el dueño). Indicios: `file/update/` es más antiguo (BASE de 19 cols sin `banco`, sin UI), `UPDATE/` es la reescritura con interfaz y `banco` añadido. `file/update/` conserva además operaciones que `UPDATE/` no expone (`ELIMINAPRO`, `UPBASE`, `UPESTATE`). Ambos siguen accesibles en producción.

---

## 4. `api/file/sql/` — Exportadores a Excel

### 4.1 Patrón de exportación

Cada `index.php` (`file/sql/.../index.php`):
1. Setea headers `Content-Type: application/vnd.ms-excel` + `Content-Disposition: attachment; filename=<X>.xls`.
2. Ejecuta una `SELECT`.
3. Emite una **tabla HTML** que Excel abre como hoja de cálculo (no es XLSX real; es HTML con extensión `.xls`).

### 4.2 Exportadores admin (`file/sql/admin/`)

| Carpeta | Archivo .xls | Tabla | Modos (`?t=`) | Params |
|---|---|---|---|---|
| `gest/` | GESTION.xls | `t_gestiones` | asesor / fecha / rango | `a` (o `all`), `f`, `f1`,`f2` |
| `acue/` | ACUERDOS.xls | `t_acuerdos` | asesor / fecha / rango | igual (`facuerdo`) |
| `reca/` | RECAUDO.xls | `t_pagos` | asesor / fecha / rango | igual (`fecha`) |
| `base/index.php` | BASE.xls | JOIN 7 tablas | — | `a` (asesor) — replica el JOIN de `g_operacion`, 28 cols, `WHERE t_asignacion.asesor='$a'` |
| `base/all.php` | BASE.xls | `t_base` | — | `SELECT * FROM t_base` (dump completo, sin filtro; `all.php:10`) |
| `ases/index.php` | asesores.xls | `t_usuarios` | — | `SELECT * FROM t_usuarios WHERE usertype != 0` (`ases/index.php:9`) |

- Los modos `asesor/fecha/rango` (`gest`,`acue`,`reca`) aceptan `a='all'` para no filtrar por asesor (`gest/index.php:25-26`, etc.).
- Todos interpolan `$_GET` en el SQL → **inyectables** (p.ej. `gest/index.php:28` `WHERE asesor='$asesor'`).

### 4.3 Exportador `user/acue/index.php` — **SQL arbitrario (severidad máxima)**

```php
// file/sql/user/acue/index.php:12
$sql = $_GET['sql'];
// :36
$con = $obj->query($sql);
```

**Ejecuta como SQL el contenido literal del parámetro `?sql=`**, sin auth ni filtro. Cualquiera que llegue a la URL ejecuta **cualquier sentencia** sobre `data_cegroup`: `SELECT` arbitrario, `UPDATE`, `DELETE`, `DROP TABLE`, lectura de `t_usuarios` completa, etc. Es el defecto de seguridad de **mayor severidad** del proyecto. (Nota: `include` de `lib/DB.php` está duplicado en `:2` y `:10` — irrelevante para la vulnerabilidad.)

---

## 5. Tabla resumen — importador → CSV → destino → modo → riesgos

| Importador | Archivo | Cols CSV (orden, sep `;`) | Tabla destino | Modo | Riesgos |
|---|---|---|---|---|---|
| `UPDATE/BASE` | file/BASE.csv | operacion,cuenta,tcedula,tnombre,ttel1,ttel2,ccedula,cnombre,ctel1,ctel2,gcedula,gnombre,gtel1,gtel2,fvencimiento,fingreso,sucursal,dependencia,condicion,banco | `t_base` | INSERT | Duplica si recarga; sin tx; SQLi por CSV; upload sin validar |
| `UPDATE/ASIGNACION` | file/ASIGNACION.csv | operacion,asesor | `t_asignacion` | INSERT | Duplica; sin tx |
| `UPDATE/CAMPANA` | file/CAMPANA.csv | operacion,campana | `t_campana` | INSERT | Duplica; sin tx |
| `UPDATE/CARTERA` | file/CARTERA.csv | operacion,cartera | `t_cartera` | INSERT | Duplica; sin tx |
| `UPDATE/DECIL` | file/DECIL.csv | operacion,decil | `t_decil` | INSERT | Duplica; sin tx |
| `UPDATE/SALDOS` | file/SALDOS.csv | operacion,capital,total | `t_saldos` | INSERT | Duplica; sin tx |
| `UPDATE/PROCESOS` | file/PROCESOS.csv | operacion,estado,sub,fgestion,asesor | `t_procesos` | INSERT | Duplica; sin tx |
| `UPDATE/TELEFONOS` | file/TELEFONOS.csv | operacion,asesor,telefono,detalle | `t_telefonos` | INSERT | Duplica; fuga `echo $sql` |
| `UPDATE/GESTIONES` | file/GESTIONES.csv | operacion,gestion | `t_gestiones` | INSERT (asesor='MASIVO') | Duplica; atribuye a usuario sintético |
| `UPDATE/MAIL` | MAIL.csv (raíz) | operacion,mail | `t_email` | INSERT | Duplica; sin UI; sin auth |
| `UPDATE/elimina.php` | ELIMINAR.csv | operacion | `t_base` (DELETE) | DELETE | **Huérfanos en 13 tablas** |
| `file/update/ASIGNAR` | ASIGNACION.csv | operacion,asesor | `t_asignacion` | INSERT | Duplica; duplicado de UPDATE |
| `file/update/BASE` | BASE.csv | (19 cols, **sin banco**) | `t_base` | INSERT | `banco` queda NULL; duplica |
| `file/update/CAMPANA` | CAMPANA.csv | operacion,campana | `t_campana` | INSERT | Duplica |
| `file/update/CARTERA` | CARTERA.csv | operacion,cartera | `t_cartera` | INSERT | Duplica |
| `file/update/DECIL` | DECIL.csv | operacion,decil | `t_decil` | INSERT | Duplica |
| `file/update/PROCESOS` | PROCESOS.csv | operacion,estado,sub,fgestion,asesor | `t_procesos` | INSERT | Duplica |
| `file/update/SALDOS` | SALDOS.csv | operacion,capital,total | `t_saldos` | INSERT | Duplica |
| `file/update/ELIMINAPRO` | ELIMINAPRO.csv | operacion | `t_procesos` (DELETE) | DELETE | Borra estado del proceso |
| `file/update/UPBASE` | UPBASE.csv | operacion,sucursal,dependencia | `t_base` (UPDATE) | UPDATE | Sin tx; SQLi |
| `file/update/UPESTATE` | ESTADOS.csv | operacion,estado,sub | `t_procesos` (UPDATE) | UPDATE | Sin tx; SQLi |

**Exportadores** (lectura → `.xls` HTML): `file/sql/admin/{gest,acue,reca,base,ases}` y `file/sql/user/acue` (⚠ SQL arbitrario).

---

## 6. Cruce con la base de datos

- Todas las tablas destino de los importadores existen: `t_base, t_asignacion, t_campana, t_cartera, t_decil, t_saldos, t_procesos, t_telefonos, t_gestiones, t_email` (tables.json). Las de exportación también: `t_acuerdos, t_pagos, t_usuarios`.
- `t_base` (54.931 filas, `operacion` PK bigint) confirma `operacion` como clave del crédito y la presencia de `banco`.
- **`t_pagos` existe pero tiene 0 filas y no hay importador ni endpoint de escritura** en este código. `No verificado:` cómo se cargan los recaudos (¿proceso externo, carga SQL directa, o sistema separado?). El exportador `reca/` y `g_aportes`/`b_resumen` lo leen, pero nada lo escribe aquí.

---

## 7. Hallazgos clave (importadores/exportadores)

1. **`file/sql/user/acue/index.php?sql=` ejecuta SQL arbitrario sin auth** — severidad máxima (`:12`,`:36`).
2. **`upload.php` sin validación** (extensión/MIME/tamaño), guarda con nombre original → riesgo de subir y ejecutar `.php` (RCE) — todas las carpetas de `UPDATE/`.
3. **`elimina.php` deja huérfanos en 13 tablas** (borra solo `t_base`, sin FKs que propaguen).
4. **Sin TRUNCATE/UPSERT en ningún importador** → recargar el mismo CSV duplica datos.
5. **Sin transacción** → fallo a mitad deja carga parcial irreversible.
6. **Dos árboles divergentes** (`UPDATE/` vs `file/update/`): `BASE` difiere en `banco` (20 vs 19 cols) → usar el árbol equivocado corrompe el dato.
7. **`GESTIONES` masivas se atribuyen a `MASIVO`** (no a un asesor real) — afecta métricas de productividad.
8. **SQL injection vía contenido del CSV y vía `$_GET`** en todos los importadores/exportadores; varios filtran el SQL al output (`echo $sql`).
9. **Sin autenticación** en los tres subsistemas (URLs públicas).

---

## 8. Evidencia

- `cegroup/api/UPDATE/index.php`, `UPDATE/DB.php`, `UPDATE/elimina.php`.
- `cegroup/api/UPDATE/<DOMINIO>/{upload.php,procesar.php}` (BASE, ASIGNACION, CAMPANA, CARTERA, DECIL, SALDOS, PROCESOS, TELEFONOS, GESTIONES) + `UPDATE/MAIL/procesar.php`.
- `cegroup/api/file/update/<DOMINIO>/procesar.php` × 10 (ASIGNAR, BASE, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS, ELIMINAPRO, UPBASE, UPESTATE).
- `cegroup/api/file/sql/admin/{gest,acue,reca,ases}/index.php`, `file/sql/admin/base/{index.php,all.php}`, `file/sql/user/acue/index.php`.
- Cruce BD: `Resume/CEGROUP/_evidence/tables.json`, `column_profiles.json` (`t_base`, `t_pagos`).
