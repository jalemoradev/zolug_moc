# CUMPLIR — Cargas Masivas (importadores CSV)

> Verificado por lectura directa de cada `procesar.php` y cada CSV de muestra. Rutas relativas a `cumplir/`.
> Destinos cruzados con `Resume/CUMPLIR/_evidence/column_profiles.json` / `tables.json`.

---

## 1. Inventario real de `DATA/`

`DATA/` contiene **dos** grupos:

```
DATA/
├── UPDATE/          ← importadores activos (menú web)
│   ├── DB.php       ← conexión compartida (credenciales hardcodeadas)
│   ├── index.php    ← menú HTML
│   ├── styles.css
│   ├── ASIGNACION/  index.php + upload.php + procesar.php + index.js + file/
│   ├── BASE/        idem
│   ├── CAMPANA/     idem
│   ├── CARTERA/     idem
│   ├── DECIL/       idem
│   ├── PROCESOS/    idem
│   ├── SALDOS/      idem
│   └── REASIGNACION/ idem   ← presente pero NO enlazado en el menú
└── OCULTAR/         ← borrado masivo (sin UI)
    ├── OCULTAR.csv
    └── procesar.php
```

> Nota: existe un árbol paralelo más antiguo en `api/file/update/` (ASIGNAR, BASE, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS, SUBIR_*). Este documento cubre `DATA/` según el alcance solicitado; `api/file/update/` es legado redundante (mismos patrones, mismas tablas).

---

## 2. Mecánica común de todos los importadores (verificado)

**Flujo de 3 archivos por módulo** (`index.php` → `upload.php` → `procesar.php`):

1. **`index.php`** — página HTML con un `<form>` de subida y un botón "Procesar". Carga `index.js` y jQuery desde CDN.
2. **`index.js`** — `onSubmit()` envía el archivo por AJAX a `upload.php` (`FormData`, `multipart`); al éxito muestra el botón "Procesar". `onProcess()` llama por AJAX a `procesar.php` y pinta `data.process` (contador).
3. **`upload.php`** — `move_uploaded_file($_FILES['file']['tmp_name'], 'file/'.$filename)` (`BASE/upload.php:9`). **Guarda el archivo con el NOMBRE ORIGINAL que envía el cliente**, dentro de `file/`. No valida extensión, tipo MIME ni tamaño.
4. **`procesar.php`** — patrón idéntico en los 8 módulos:
   ```php
   set_time_limit(60000000000);      // sin timeout efectivo
   include("../DB.php"); $obj=new conn;
   chmod("file/<NOMBRE>.csv", 0777); // permisos world-writable
   $fp = fopen("file/<NOMBRE>.csv","r");
   $num=1;
   while ($d = fgetcsv($fp, 100000000, ";")) {  // delimitador ';'
       // $d[0], $d[1], ... → interpolación directa en SQL → query()
       if ($r) $num++;
   }
   echo json_encode(['process'=>$num,'result'=>'sucess']);
   ```

**Características compartidas (todas son defectos):**

- **Delimitador `;`** en `fgetcsv` (todos).
- **NO se salta la fila de cabecera.** Ningún `procesar.php` descarta la primera línea. Como los CSV reales **sí traen cabecera** (`NRO.CREDITO;ASESOR`, etc.), la cabecera se inserta como una fila basura más (operación = `"NRO.CREDITO"` o con BOM `"﻿NRO.CREDITO"`).
- **NO hay TRUNCATE/DELETE previo ni UPSERT.** Todos los `INSERT` son aditivos → **recargar el mismo CSV duplica todas las filas**. (Evidencia en BD: `t_asignacion`=58 648 filas frente a `t_base`=50 381 operaciones; `t_telefonos`=133 934; `t_cartera`=56 444 ≠ `t_campana`/`t_decil`/`t_saldos`=55 154 — los desajustes son consistentes con re-cargas acumuladas y limpiezas parciales por OCULTAR.)
- **NO hay transacción.** Un fallo a mitad deja filas parciales sin rollback.
- **SQL por interpolación** → inyección desde el contenido del CSV.
- **`fopen` sin comprobar existencia** → si falta el archivo, `fgetcsv` opera sobre `false` (warnings) y `$num` se queda en 1.
- **`chmod 0777`** sobre el CSV subido.
- Acceso por URL directa, **sin autenticación**.
- El nombre del archivo que `procesar.php` abre está **hardcodeado** (`file/BASE.csv`, etc.), pero `upload.php` guarda con el nombre del cliente → si el operador sube un archivo con otro nombre, la subida "tiene éxito" pero `procesar.php` lee un CSV viejo o inexistente. Acoplamiento frágil por convención de nombres.

---

## 3. Importadores `DATA/UPDATE/*` — detalle

Cabeceras CSV verificadas con `head` sobre los `file/*.csv` reales (incluyen BOM UTF-8 al inicio).

### ASIGNACION — `DATA/UPDATE/ASIGNACION/procesar.php`
- **CSV:** `file/ASIGNACION.csv`, delimitador `;`. Cabecera real: `NRO.CREDITO;ASESOR`. Ejemplo: `4790226;ANDRES.ALVEAR`.
- **Columnas leídas:** `$d[0]=operacion`, `$d[1]=asesor` (`:11-12`).
- **SQL (`:15-16`):** `INSERT INTO t_asignacion (operacion, asesor) VALUES ('$operacion','$asesor')`.
- **Modo:** INSERT aditivo. (El UPDATE alternativo está comentado, `:18-20`.)
- **Destino:** `t_asignacion` (cols reales: `id, operacion, asesor` — el INSERT omite `id`, autoincremental).
- **Riesgos:** duplica asignaciones al recargar (un crédito puede quedar con varias filas → `g_operacion` con INNER JOIN devolvería filas múltiplicadas). Cabecera insertada como fila basura.

### BASE — `DATA/UPDATE/BASE/procesar.php`
- **CSV:** `file/BASE.csv`, `;`. **21 columnas.** Cabecera: `OPERACION;CUENTA;CEDULA;NOMBRE;TEL1;TEL2;CEDULA;NOMBRE;TEL1;TEL2;CEDULA;NOMBRE;TEL1;TEL2;FV;FI;SUCURSAL;DEPENDENCIA;CONDICION;BANCO;REFERENCIA`.
- **Mapeo `$d[0..20]` (`:10-34`):** operacion, cuenta, tcedula, tnombre, ttel1, ttel2, ccedula, cnombre, ctel1, ctel2, gcedula, gnombre, gtel1, gtel2, fvencimiento, fingreso, sucursal, dependencia, condicion, banco, **referencia**.
- Nombres pasan por `mb_strtoupper(utf8_encode(...))` (`:14,19,24`).
- **SQL (`:38-44`):** INSERT de las 21 columnas en `t_base`.
- **Destino:** `t_base` (21 columnas reales — coinciden 1:1, incluida `referencia`).
- **Dif. vs CEGROUP:** `t_base` aquí tiene `referencia` (columna 21). Mapeo completo verificado contra BD.
- **Riesgos:** INSERT aditivo (recarga duplica créditos); cabecera basura; sin validación de formato de fechas.

### CAMPANA — `DATA/UPDATE/CAMPANA/procesar.php`
- **CSV:** `file/CAMPANA.csv`, `;`. Cabecera: `NRO.CREDITO;CAMPANA`. Ej.: `4790226;SEGMENTO 5 a 7 aNos (60%)`.
- **Columnas:** `$d[0]=operacion`, `$d[1]=campana` (`:10-11`).
- **SQL (`:13`):** `INSERT INTO t_campana (id, operacion, campana) VALUES (NULL, …)`.
- **Destino:** `t_campana` (`id, operacion, campana`).
- **Nota:** existe también `file/CAMPAÑA.csv` (con ñ) en disco, pero `procesar.php` lee `CAMPANA.csv` (sin ñ).
- **Riesgos:** aditivo; el `;` dentro de valores no aparece, pero los paréntesis/porcentajes en `campana` se insertan crudos.

### CARTERA — `DATA/UPDATE/CARTERA/procesar.php`
- **CSV:** `file/CARTERA.csv`, `;`. Cabecera: `NRO.CREDITO;CARTERA`. Ej.: `4790226;40 Cartera SEP 2020`.
- **Columnas:** `$d[0]=operacion`, `$d[1]=cartera` (`:10-11`).
- **SQL (`:12-13`):** `INSERT INTO t_cartera (id, operacion, cartera) VALUES (NULL, …)`.
- **Destino:** `t_cartera` (`id, operacion, cartera`).
- **Nota:** la carpeta `file/` también contiene un `CAMPANA.csv` residual; `procesar.php` solo abre `CARTERA.csv`.

### DECIL — `DATA/UPDATE/DECIL/procesar.php`
- **CSV:** `file/DECIL.csv`, `;`. Cabecera: `NRO.CREDITO;DECIL`. Ej.: `4790226;0`.
- **Columnas:** `$d[0]=operacion`, `$d[1]=decil` (`:10-11`).
- **SQL (`:13-14`):** `INSERT INTO t_decil (id, operacion, decil) VALUES (NULL, …)`.
- **Destino:** `t_decil` (`id, operacion, decil`).
- **Relación con OCULTAR:** OCULTAR borra de `t_decil`; este importador es el que la repuebla. Si una operación se "oculta" (borra su decil) y luego no se recarga DECIL, `g_operacion` (INNER JOIN con `t_decil`) **no la mostrará**.

### PROCESOS — `DATA/UPDATE/PROCESOS/procesar.php`  ⟵ **desajuste de columnas**
- **CSV:** `file/PROCESOS.csv`, `;`. La cabecera real tiene **7 columnas**: `NRO.CREDITO;0;0;0;0;0;0`. Las filas de muestra traen ceros: `4790226;0;0;0;0;0;0`.
- **Columnas leídas (solo 5, `:10-14`):** `$d[0]=operacion`, `$d[1]=estado`, `$d[2]=sub`, `$d[3]=fgestion`, `$d[4]=asesor`. **`$d[5]` y `$d[6]` se ignoran.**
- **SQL (`:17`):** `INSERT INTO t_procesos (id, operacion, estado, sub, fgestion, asesor) VALUES (NULL, …)`.
- **Destino:** `t_procesos` (`id, operacion, estado, sub, fgestion, asesor`).
- **Riesgos:** el CSV de muestra inserta `estado/sub/fgestion/asesor` = `'0'`. Si la plantilla real de producción no respeta este orden de 7 columnas, se cargan estados inválidos. Aditivo (BD: `t_procesos`=109 865 filas ≈ 2× operaciones → indicio de recargas duplicadas).

### SALDOS — `DATA/UPDATE/SALDOS/procesar.php`
- **CSV:** `file/SALDOS.csv`, `;`. Cabecera: `NRO.CREDITO;SALDO CAPITAL;SALDO TOTAL`. Ej.: `4790226;11349632,4;13722518,32`.
- **Columnas:** `$d[0]=operacion`, `$d[1]=capital`, `$d[2]=total` (`:9-11`).
- **SQL (`:12-13`):** `INSERT INTO t_saldos (id, operacion, capital, total) VALUES (NULL, …)`.
- **Destino:** `t_saldos` (`id, operacion, capital, total`).
- **Riesgo de formato:** los importes usan **coma decimal** (`11349632,4`). Si la columna en BD es numérica, MySQL truncará en la coma (guardaría `11349632`); si es texto, conserva la coma pero rompe sumas (`g_aportes`/`b_resumen` hacen `SUM`). **No verificado** el tipo exacto de `capital`/`total` en BD.

### REASIGNACION — `DATA/UPDATE/REASIGNACION/procesar.php`  ⟵ **MODO UPDATE, oculto del menú**
- **CSV:** `file/REASIGNACION.csv`, `;`. Cabecera: `OP;NUEVO ASESOR`. Ej.: `4544001;TATIANA.GAVIRIA`.
- **Columnas:** `$d[0]=operacion`, `$d[1]=asesor` (`:14-15`).
- **SQL (`:19-21`):**
  ```sql
  UPDATE `t_asignacion` SET `asesor` = '$asesor' WHERE `operacion` = '$operacion';
  ```
- **Modo:** **UPDATE** (no INSERT). Reasigna en bloque el asesor de operaciones ya asignadas. Es la contraparte de ASIGNACION (que inserta) para mover cartera entre asesores.
- **Qué actualiza exactamente:** solo el campo `t_asignacion.asesor`, filtrando por `operacion`. No toca `t_procesos.asesor` (que se actualiza por separado al gestionar, ver doc 06 `g_gestiones`). **Consecuencia:** tras una reasignación masiva, `t_asignacion.asesor` y `t_procesos.asesor` pueden quedar **desincronizados** hasta la siguiente gestión.
- **Diferencias de configuración vs el resto:** usa `ini_set('max_execution_time',0)` + `memory_limit 1024M` + `set_time_limit(0)` (`:3-5`), no el `set_time_limit(60000000000)` común.
- **Exposición:** el archivo **existe pero NO está enlazado** en el menú `DATA/UPDATE/index.php` (los enlaces son ASIGNACION, BASE, CAMPANA, CARTERA, DECIL, PROCESOS, SALDOS). Su `index.php` tiene el `<title>ASIGNACION</title>` (copia de ASIGNACION) pero muestra "REASIGNACION". Solo se alcanza tecleando la URL → herramienta "semi-oculta".
- **Riesgos:** si una operación del CSV no existe en `t_asignacion`, el UPDATE afecta 0 filas silenciosamente (no avisa). Si `operacion` aparece duplicada en `t_asignacion` (por recargas de ASIGNACION), el UPDATE las cambia todas. SQLi desde el CSV.

---

## 4. OCULTAR — `DATA/OCULTAR/procesar.php`  ⟵ **BORRADO MASIVO**

- **Sin UI:** la carpeta solo tiene `OCULTAR.csv` y `procesar.php`. No hay `index.php`/`upload.php`/`index.js`. Se ejecuta por **URL directa** a `procesar.php`. La conexión `conn` está **embebida en el propio `procesar.php`** (`:2-10`, credenciales hardcodeadas idénticas a `DB.php`), no la incluye.
- **CSV:** `OCULTAR.csv`, delimitador `;`, **1 columna**. Cabecera: `OPERACIÓN` (con BOM/encoding roto: `OPERACI�N`). Filas: una operación por línea (`10598`, …). ~8 705 líneas en la muestra.
- **Columna:** `$d[0]=operacion` (`:17`).
- **SQL (`:18`):**
  ```sql
  DELETE FROM `t_decil` WHERE `t_decil`.`operacion` = '$operacion'
  ```
- **Qué borra exactamente:** **solo filas de `t_decil`**. No borra de `t_base` ni de ninguna otra tabla. NO hay tabla de auditoría ni respaldo.
- **Efecto de negocio ("ocultar"):** como `g_operacion` y los filtros de `b_datafilter` hacen **INNER JOIN con `t_decil`**, borrar el decil de una operación la **excluye de esas vistas** aunque el crédito siga en `t_base`, `t_saldos`, etc. Es un "ocultamiento" por ruptura de JOIN, no un borrado lógico con bandera. Reversible solo recargando DECIL.
- **Dif. vs CEGROUP:** en CEGROUP el equivalente de eliminación masiva (`UPDATE/elimina.php`) hace `DELETE FROM t_base` (borra el crédito y deja huérfanos en 13 tablas), y `ELIMINAPRO` borra `t_procesos`. En **CUMPLIR, OCULTAR borra `t_decil`** — mecanismo distinto y menos destructivo (oculta vía JOIN en vez de borrar la base).
- **Riesgos:** sin auth (cualquiera con la URL borra deciles en masa); sin confirmación; sin auditoría; SQLi desde el CSV; cabecera `OPERACIÓN` se intenta borrar como operación (no-op, 0 filas).

---

## 5. Tabla resumen: importador → columnas → tabla → modo → riesgos

| Importador | CSV (delim `;`) | Columnas CSV (orden) | Tabla destino | Modo | Riesgos clave |
|---|---|---|---|---|---|
| ASIGNACION | `ASIGNACION.csv` | `operacion; asesor` | `t_asignacion` | INSERT | Duplica al recargar; cabecera basura; SQLi |
| BASE | `BASE.csv` | `operacion;cuenta;tcedula;tnombre;ttel1;ttel2;ccedula;cnombre;ctel1;ctel2;gcedula;gnombre;gtel1;gtel2;fvencimiento;fingreso;sucursal;dependencia;condicion;banco;referencia` (21) | `t_base` | INSERT | Duplica créditos; sin validación fechas; cabecera basura |
| CAMPANA | `CAMPANA.csv` | `operacion; campana` | `t_campana` | INSERT | Aditivo; archivo `CAMPAÑA.csv` residual ignorado |
| CARTERA | `CARTERA.csv` | `operacion; cartera` | `t_cartera` | INSERT | Aditivo; `CAMPANA.csv` residual en carpeta |
| DECIL | `DECIL.csv` | `operacion; decil` | `t_decil` | INSERT | Aditivo; repuebla lo que OCULTAR borra |
| PROCESOS | `PROCESOS.csv` | `operacion;estado;sub;fgestion;asesor;[+2 ignoradas]` (7 en CSV, 5 leídas) | `t_procesos` | INSERT | **Lee 5 de 7 cols**; muestra carga ceros; duplica |
| SALDOS | `SALDOS.csv` | `operacion; capital; total` | `t_saldos` | INSERT | **Coma decimal** rompe numérico/SUM; aditivo |
| REASIGNACION | `REASIGNACION.csv` | `operacion; asesor` | `t_asignacion` (solo `asesor`) | **UPDATE** | Oculto del menú; desincroniza con `t_procesos.asesor`; UPDATE silencioso si 0 filas |
| OCULTAR | `OCULTAR.csv` | `operacion` (1) | `t_decil` (**DELETE**) | **DELETE** | Sin UI ni auth ni auditoría; "oculta" rompiendo INNER JOIN; SQLi |

---

## 6. Cruce con evidencia de BD (verificado)

Todos los destinos existen en `tables.json`. Row counts de `column_profiles.json` (síntomas de los patrones aditivos/parciales):

| Tabla | Filas BD | Lectura |
|---|---|---|
| `t_base` | 50 381 | Línea base de operaciones |
| `t_asignacion` | 58 648 | > base → recargas ASIGNACION duplicadas + REASIGNACION |
| `t_procesos` | 109 865 | ≈ 2× base → recargas PROCESOS duplicadas |
| `t_telefonos` | 133 934 | Multi-teléfono por operación + inserts manuales API |
| `t_cartera` | 56 444 | > campana/decil/saldos → recarga parcial distinta |
| `t_campana` | 55 154 | — |
| `t_decil` | 55 154 | < base (OCULTAR borró ~? deciles) y < cartera |
| `t_saldos` | 55 154 | — |

> El patrón `t_decil = t_campana = t_saldos = 55 154` pero `t_cartera = 56 444` y `t_base = 50 381` es coherente con: cargas masivas aditivas sin TRUNCATE + limpiezas selectivas de `t_decil` por OCULTAR. **No verificado** el conteo exacto de operaciones eliminadas por OCULTAR (requiere comparar `t_base` vs `t_decil` por `operacion`, fuera de alcance).

---

## 7. Diferencias verificadas frente a CEGROUP

| Aspecto | CEGROUP | CUMPLIR |
|---|---|---|
| Eliminación masiva | `UPDATE/elimina.php` → `DELETE FROM t_base` (+ huérfanos); `ELIMINAPRO` → `DELETE FROM t_procesos` | **`OCULTAR` → `DELETE FROM t_decil`** (oculta vía ruptura de INNER JOIN) |
| Reasignación masiva | No documentada | **`REASIGNACION` → `UPDATE t_asignacion.asesor`** (presente, oculta del menú) |
| Columna `referencia` en BASE | No | **Sí** (`BASE.csv` col 21 → `t_base.referencia`) |
| Patrón importador (delim `;`, sin TRUNCATE/UPSERT, sin transacción, sin auth, SQLi, no salta cabecera, `chmod 0777`) | Igual | Igual |

**No verificado:** tipo SQL de `t_saldos.capital/total` (impacto de la coma decimal); número real de operaciones afectadas por OCULTAR; si la plantilla de producción de PROCESOS usa las 7 columnas; comportamiento runtime de subir un CSV con nombre distinto al hardcodeado.
