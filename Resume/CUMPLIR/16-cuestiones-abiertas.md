# 16 — Cuestiones abiertas, defectos y deuda técnica (CUMPLIR)

> Verificación de los **64 hallazgos** del doc previo (`PROYECTO_CUMPLIR/resumen/16-cuestiones-abiertas.md`) contra el código y los datos actuales, más hallazgos nuevos de este lote. Estado: **Vigente** (sigue igual), **Actualizado** (cambió el dato), **Corregido** (el doc previo estaba mal), **Nuevo**.
> Severidad: P0 (crítico) · P1 (alto) · P2 (medio) · P3 (bajo). Las de seguridad se detallan en `14-seguridad.md`. Rutas relativas a `cumplir/`.

---

## Verificación de los 64 hallazgos previos

| # | Descripción (resumen) | Evidencia (`archivo:línea` / `_evidence`) | Sev | Estado | Impacto v2 |
|---|---|---|---|---|---|
| 1 | SQLi en 24 REST + Flight + importadores + OCULTAR | barrido `api/app/rest/*.php`; `login.php:17`; `REPORTES/.../tools/*` | P0 | Vigente | ORM parametrizado |
| 2 | Sin auth en REST y Flight | `api/lib/*` sin gate; `REPORTES/.../src/*` sin auth | P0 | Vigente | Guard JWT global |
| 3 | Bypass `session.php?a=on&t=0` | `app/php/session.php:3-7`; `index.php:134` | P0 | Vigente | Sesión server-side |
| 4 | Passwords `md5('0')` por defecto | `admin_asesor.php:165`; `quality.json` = **10/13** | P2 | **Actualizado** (antes "3", hoy 10) | Pass inicial aleatoria + cambio forzado |
| 5 | Password en URL GET | `login.php:14-15`; `index.php:115`; `perfil.js` | P1 | Vigente | POST sobre HTTPS |
| 6 | md5 sin salt | `login.php:15`; `d_usuario_cambio.php:54` | P2 | Vigente | bcrypt/argon2 |
| 7 | CORS `*` | cabecera en cada `rest/*.php` (`login.php:2`) | P2 | Vigente | Allowlist de orígenes |
| 8 | Upload sin validación (RCE) | `DATA/UPDATE/*/upload.php:6-8`; `clientes.php:142` | P0 | Vigente | Allowlist + storage fuera del webroot |
| 9 | Credenciales DB hardcoded (3 sitios) | `api/lib/DB.php:5`; `DATA/UPDATE/DB.php:5`; `REPORTES/.../app/db/db.php:4` | P0 | **Actualizado** (pass real `«REDACTADO»`) | Env vars; rotar |
| 10 | `echo $sql` en Flight | `REPORTES/.../src/tools/Gestiones.php:11` | P2 | **Confirmado** (verificado el `echo`) | Quitar echo |
| 11 | `g_gestiones` borra todas las alertas | `g_gestiones.php:110` | P1 | Vigente | Alertas con ciclo de vida propio |
| 12 | Ruta `g_asesor` rota | `api/.../routes.php:5` → método `g_asesor` inexistente en `ApiController` (solo `asesor()`) | P1 | Vigente | Eliminar ruta/método muerto |
| 13 | `asesor.php` tabla `asesor` inexistente | `asesor.php:78,94,110`; no en `SHOW TABLES` | P3 | Vigente | Código muerto |
| 14 | Typo `cedukla` en `asesor.php` | `asesor.php:78` | P3 | Vigente | Código muerto |
| 15 | Años hardcoded inconsistentes (Flight) | `tools/Gestiones.php:17-18`=**2026**; `tools/Acuerdos.php:28-29`=**2025**; `tools/Proyeccion.php:28-29`=**2025** | P1 | **Confirmado** | Filtros por rango dinámico |
| 16 | Vistas `acuerdos.php`/`resumen.php` muestran 2023 | `views/acuerdos.php:38`=**2026**; `views/resumen.php:31`=**2026** | P1 | **Corregido** (hoy dicen **2026**, coinciden con backend; el doc previo decía 2023) | Año dinámico |
| 17 | `d_usuario` usa `Y-m-d` (correcto) | `d_usuario.php:19` | — | Vigente (mejora real vs CEGROUP) | — |
| 18 | `t_procesos` duplicado masivo | `quality.json`: 109.865/97.109 = **1.131×** (no 2.18×) | P2 | **Corregido** (el ratio real es 1.131×, no 2.18×; +12.756 filas sobre ops distintas) | Upsert/clave única |
| 19 | Fila basura `operacion=0` | `quality.json`: t_procesos 47, t_gestiones 21, t_resumen 17, t_asignacion 12, t_telefonos 10, t_cartera 7, otros 1 | P3 | **Actualizado** (cuantificado) | Saltar header; limpiar |
| 20 | Estados huérfanos en `t_procesos` | `column_profiles.json`: `LOCALIZADO(17207)`, `INVESTIGACION(4767)`, `CASTIGADA(1959)` no en catálogo `t_estados` | P1 | **Confirmado/Ampliado** | Reconciliar catálogo |
| 21 | `reporte_*` parcialmente computados | `samples/`: `reporte_gestion` totales sí (`ges_t=175`), días `-`; `reporte_acuerdos`/`reporte_proyeccion` **todo `-`** | P2 | **Confirmado** | Decidir si el feature se mantiene |
| 22 | `t_telefonos.status` vacío | `column_profiles.json`: `ACTIVO(21772)` + vacío(112162) | P2 | **Corregido** (SÍ se usa; 21.772 ACTIVO; toggle activo/'' vía `gestion.js:451,456`) | Modelar como enum |
| 23 | `r_acuerdo` vacía | `tables.json`: 0 filas; cols `asesor,ope,acu,valor,fecha` | P2 | Vigente | Pregunta de negocio |
| 24 | Hora corrupta en `t_gestiones` | datos viejos `12:23 p.?m.` | P3 | Vigente | Tipo `TIME` |
| 25 | Charset mixto utf8mb3/utf8mb4 | `tables.json`: 4 utf8mb4 (`r_acuerdo`, `reporte_*`) | P2 | Vigente | utf8mb4 uniforme |
| 26 | Side-effect OCULTAR sin auditoría | `DATA/OCULTAR/procesar.php:17-18` | P1 | Vigente | Soft-delete + auditoría |
| 27 | Side-effect REASIGNACION sobre duplicados | `REASIGNACION/procesar.php:14-21`; `t_asignacion` ratio 1.223× | P1 | Vigente | FK/unique + auditoría |
| 28 | `SUBIR_TELEFONOS` no inserta `status` | `SUBIR_TELEFONOS/procesar.php:10-17` (4 cols, sin status) | P3 | **Confirmado** (status='' por default; coherente con 112.162 vacíos) | Incluir status |
| 29 | `SUBIR_ALERTAS` bucle `$num` roto | `SUBIR_ALERTAS/procesar.php:14` (`$num=$d[4]` sobrescribe el contador) | P3 | **Confirmado** | Reescribir contador |
| 30 | Doble base URL en login (`dir_data`+`dir_file`) | `index.php:103-107` | P3 | **Corregido** (`dir_file_server` SÍ se usa: `reportes.js:138`, `acuerdos.js`, `asesores.js`, `main.js`) | URL por env |
| 31 | `views/index.php`/`pagos.php`/`comunicacion.php` vacíos | `wc -l` = 0 | P3 | Vigente | — |
| 32 | Clientes huérfano (sin ruta + sin tabla) | `rest/clientes.php`; no en `app/http/routes.php`; sin tabla `clientes` | P2 | Vigente | Decidir incluir/descartar |
| 33 | `rest/asesor.php` inalcanzable | `app/http/routes.php` no mapea `asesor`; `g_asesor` roto (#12) | P3 | Vigente | Eliminar |
| 34 | 9 stubs en `app/component/` | `wc -l` = **0 bytes** | P3 | **Corregido** (están vacíos, no `<h1>hola soy X</h1>`) | Eliminar |
| 35 | Botones `tel:` placeholder en `DATA/UPDATE/index.php` | `DATA/UPDATE/index.php` | P3 | Vigente (verificar) | Limpiar |
| 36 | `api/file/update/BASE` sin `referencia` (drift) | `api/file/update/BASE/procesar.php` (19 cols) vs `DATA/UPDATE/BASE/procesar.php` (21 cols, incl. referencia) | P2 | **Confirmado** | Unificar contrato de import |
| 37 | `api/file/sql/` no existe | `find` → **SÍ existe** (`admin/{gest,acue,reca,base,ases}`, `user/acue`) | P2 | **Corregido** (el subsistema Excel sí existe; el UI admin lo usa) | Reemplazar por endpoints tipados |
| 38 | `r_acuerdo` no usada | = #23 | P2 | Vigente | — |
| 39 | Vistas `*File.php` (Flight) sin ruta | rutas SÍ existen (`src/routes/Reportes.php:17,21,25`) pero los archivos son **0 bytes** | P3 | **Corregido** (sí están ruteadas; son código muerto por estar vacías) | Eliminar |
| 40 | Funciones renderer `Reportes.php` no invocadas | rutas SÍ las invocan (`src/routes/Reportes.php`); las vistas que cargan están vacías | P3 | **Corregido** | Eliminar |
| 41 | 0 índices secundarios | `indexes.json`; `INDEX_LENGTH=0` en todas | P1 | Vigente | Índices |
| 42 | 0 foreign keys | `indexes.json` | P2 | Vigente | FKs |
| 43 | Currency varchar con coma | `t_saldos.capital/total` | P2 | Vigente | DECIMAL |
| 44 | `t_acuerdos.valor`, `t_resumen.v*` varchar(20) | `column_profiles.json` | P2 | Vigente | DECIMAL |
| 45 | md5 sin salt | = #6 | P2 | Vigente | bcrypt |
| 46 | Sin paginación | `b_data.php` (LIKE sobre 50k) | P2 | Vigente | Paginación |
| 47 | Sin logs estructurados | `DB.php` (`print("error de conexion")`) | P3 | Vigente | Logger |
| 48 | Sin tests | sin carpeta test | P2 | Vigente | Suite |
| 49 | Sin README | repo sin README | P3 | Vigente | — |
| 50 | Sin variables de entorno | todo hardcoded | P2 | Vigente | Env vars |
| 51 | `set_time_limit(60000000000)` | `DATA/UPDATE/*/procesar.php`; REASIGNACION `set_time_limit(0)` | P3 | Vigente | Cola con límites |
| 52 | `array_map(CODING)` no-op | `api/api/index.php` (`CODING=null`) | P3 | Vigente | Eliminar |
| 53 | Subsistemas redundantes (UPDATE/file_update; b_resumen/reporte_*) | 2 árboles de import + 2 vías de métricas | P2 | Vigente | Unificar |
| 54 | `reporte_*` anti-pattern wide (65 cols) | `samples/reporte_gestion.json` (ges_01..ges_31 + totales) | P3 | Vigente | Modelo long |
| 55 | Módulo REPORTES sin auth | `REPORTES/.../src/*` sin gate; `.htaccess` solo rewrite | P0 | Vigente | Auth o eliminar |
| 56 | ¿Por qué se removió email? | `gestion.php` sin email; sin `g_email`/`t_email` | — | Vigente | Pregunta de negocio |
| 57 | ¿Por qué `r_acuerdo`? | tabla vacía con nombres abreviados | — | Vigente | Pregunta de negocio |
| 58 | ¿Quién usa REPORTES/REPORTES? | no enlazado desde el menú principal; se alcanza directo | — | Vigente | Pregunta de negocio |
| 59 | ¿Para qué `t_telefonos.status`? | hoy es toggle `activo`/'' (#22) | — | **Parcialmente resuelto** (es activo/inactivo; falta confirmar semántica de negocio) | Pregunta de negocio |
| 60 | ¿Sincronización CUMPLIR↔CEGROUP? | mismo skeleton, hosting/datos distintos | — | Vigente | Pregunta de negocio |
| 61 | ¿OCULTAR automático? | sin cron visible | — | Vigente | Pregunta de negocio |
| 62 | ¿Versión de Flight? | `app/flight/Flight.php` sin nº de versión en cabecera | — | Vigente | Verificar versión/CVE |
| 63 | ¿`r_acuerdo.fecha` NOT NULL sin default? | `ddl`/`column_profiles`: `fecha date NOT NULL` | P3 | Vigente | Default o nullable |
| 64 | ¿REPORTES Flight tiene cron? | celdas en `-` → nadie ejecuta los POST | — | Vigente | Pregunta de negocio |

---

## Hallazgos nuevos de este lote

| ID | Descripción | Evidencia | Sev | Impacto v2 |
|---|---|---|---|---|
| N1 | **`t_base.referencia` duplica `banco`** (mismos códigos 802020/...) | `column_profiles.json`: `referencia` y `banco` con idénticos valores | P3 | Definir si "referencia" es un concepto real o drift |
| N2 | **`t_base.condicion` contaminada con `802020(507)`** (código de banco mal cargado) | `column_profiles.json`: `ACTIVO(49796) | 802020(507) | 0(77)` | P2 | Drift de columnas en import BASE; validar esquema |
| N3 | **Pasamanos `?sql=` SÍ existe en CUMPLIR** | `api/file/sql/user/acue/index.php:12,36` | P0 | Eliminar (el doc previo lo daba por inexistente) |
| N4 | **`reporte_acuerdos`/`reporte_proyeccion` 100% en `-`** (incluidos totales) | `samples/reporte_acuerdos.json` (`acu_t=-`, `val_t=-`) | P2 | El feature de acuerdos/proyección mensual nunca operó |
| N5 | **`clientes.php` sube PDF sin validación** | `clientes.php:142` (`move_uploaded_file(...,'file/'.$cedula.'.pdf')`) | P1 | Aunque huérfano, es vector de RCE si se alcanza |
| N6 | **`t_procesos.estado` con `D/S/X` de 1 fila** (basura de un solo carácter) | `column_profiles.json` | P3 | Limpieza + constraint |
| N7 | **`PAZ Y SALVO`(19) vs `PAZ_Y_SALVO`(4971)** duplicación de concepto | `column_profiles.json` `t_procesos.estado` | P3 | Normalizar enum |
| N8 | **3 admins (`usertype=0`) con datos sintéticos** (`JURIDICOS.CUMPLIR` cedula 0, `UBINEY.CERON` nombre `0`) | `samples/t_usuarios.json` | P3 | Limpiar cuentas; cualquiera con pass `0` entra como admin |
| N9 | **`SUBIR_*` sin TRUNCATE** → acumulación en tablas transaccionales | `api/file/update/SUBIR_*/procesar.php` | P2 | Importadores idempotentes (upsert) |
| N10 | **`reporte_*` y `r_acuerdo` en utf8mb4 vs resto utf8mb3** → riesgo de error en JOIN por collation | `tables.json` | P2 | utf8mb4 uniforme |
| N11 | **Fechas `0000-00-00` masivas** | `quality.json`: `t_procesos.fgestion` **5.838**, `t_resumen.fingreso` 1.088, `t_base.fingreso/fvencimiento` 44 c/u, `t_acuerdos.facuerdo` 2 | P2 | `DATE` NOT NULL; migración de nulos |
| N12 | **SALDOS/procesar.php sin `;` final** (inconsistencia) | `DATA/UPDATE/SALDOS/procesar.php:13` | P3 | Cosmético |
| N13 | **`menu.php` define `comunicacion` y `procesos` no usados en ningún menú** | `menu.php:6,11,15-16` | P3 | Limpiar |
| N14 | **`procesos` sin ruta web** (no en `app/http/routes.php`) | `app/http/routes.php` | P3 | Eliminar entrada de menú o crear ruta |

---

## Cambios respecto al doc previo (resumen de discrepancias) — críticos

- **#4** Passwords `md5('0')`: el doc decía "3"; el dato real es **10 de 13** (77%) — `quality.json`. Mucho más grave de lo documentado.
- **#16** Años en vistas: el doc decía "siguen mostrando 2023"; verificado **dicen 2026** (`views/acuerdos.php:38`, `views/resumen.php:31`) → coinciden con el backend. No hay inconsistencia visual.
- **#18** `t_procesos` duplicación: el doc decía ratio **2.18×**; el real es **1.131×** (`quality.json`). Hay duplicación pero moderada, no "mucho peor que CEGROUP".
- **#22** `t_telefonos.status`: el doc decía "vacío en todas las filas, no usado"; **SÍ se usa** — `ACTIVO(21772)` + vacío(112162). Es un toggle activo/inactivo cableado en `gestion.js`.
- **#34** Stubs de `component/`: el doc decía `<h1>hola soy X</h1>`; verificado **0 bytes** (vacíos).
- **#37** `api/file/sql/`: el doc decía que NO existe y que todo pasa por Flight; **SÍ existe** y el UI admin de reportes lo usa (no el Flight).
- **#39/#40** Vistas/renderers `*File.php` del Flight: el doc decía "sin ruta/no invocados"; **sí están ruteados**, pero las vistas están vacías → código muerto por otra razón.
- **#30** `dir_file_server`: el doc decía "no se usa"; **sí se usa** (`reportes.js:138`, etc.).
- **OCULTAR** (#26): el doc afirmaba que `b_datafilter` usa INNER JOIN con `t_decil`; **no lo usa** — OCULTAR solo afecta `g_operacion` (el detalle), no la cola del filtro.
- **t_base ops**: el doc decía 50.351; el real es **50.381** (`quality.json`).
- **`t_estados`**: catálogo real = ACUERDO, ILOCALIZADO, JURIDICO, RENUENTE, PAZ_Y_SALVO (5; sin INVESTIGACION, que sí está en datos viejos).

---

## Años hardcoded inconsistentes (consolidado)

| Ubicación | Año | Evidencia |
|---|---:|---|
| `api/app/rest/b_acuerdos.php:64-65` | 2026 | filtro mensual de acuerdos |
| `api/app/rest/b_resumen.php:19-20` | 2026 | filtro mensual del dashboard |
| `views/acuerdos.php:38`, `views/resumen.php:31` (UI) | 2026 | labels de botón |
| `REPORTES/.../src/tools/Gestiones.php:17-18` | **2026** | rango Flight gestiones |
| `REPORTES/.../src/tools/Acuerdos.php:28-29` | **2025** | rango Flight acuerdos |
| `REPORTES/.../src/tools/Proyeccion.php:28-29` | **2025** | rango Flight proyección |

El API REST y la UI principal usan 2026 de forma coherente. **El módulo Flight es el inconsistente:** gestiones=2026 pero acuerdos/proyección=2025 — un reporte mensual de acuerdos/proyección apuntaría al año equivocado. Bug crítico para producción multi-año.

---

## Preguntas abiertas que requieren al negocio

> No se infieren respuestas. Cada una necesita confirmación de CUMPLIR antes de diseñar la v2.

1. **¿Por qué `t_pagos` está vacía?** ¿Los pagos se gestionan en otro sistema y la plataforma solo registra acuerdos? Define si "recaudo" existe en v2.
2. **¿Qué significa `t_base.referencia`?** Hoy duplica `banco`. ¿Es un campo real con propósito o quedó mal cargado?
3. **¿Qué es exactamente el "phone status"?** Es un toggle `activo`/inactivo. ¿Significa "número vigente"? ¿Quién y cuándo lo marca?
4. **¿Para qué se creó `r_acuerdo`?** Tabla vacía con columnas abreviadas. ¿Predecesora de `reporte_acuerdos`? ¿Backup? ¿Abandonada?
5. **¿Quién consume el módulo Flight `REPORTES/REPORTES/`?** No está en el menú; se alcanza por URL directa. ¿Hay cron? Hoy acuerdos/proyección nunca se computaron.
6. **¿Por qué se removió el email?** `t_email`, `g_email` y el panel de gestión desaparecieron vs CEGROUP. ¿Compliance? ¿Cambio de estrategia?
7. **¿`JURIDICO` reemplaza un flujo legal?** Es un estado nuevo casi sin uso (1 fila). ¿Hay un proceso jurídico separado (cuenta `JURIDICOS.CUMPLIR`)?
8. **¿`OCULTAR` y `REASIGNACION` son procesos oficiales?** Se ejecutan manualmente vía script, sin auth ni log. ¿Quién los corre y bajo qué política?
9. **¿Por qué dos árboles de import (`DATA/UPDATE/` vs `api/file/update/`) y dos importers de BASE con esquemas distintos?** ¿Cuál es la fuente de verdad?
10. **¿`LOCALIZADO`/`CASTIGADA`/`INVESTIGACION` deben estar en el catálogo de estados?** Están en datos pero no en `t_estados`. ¿Cuál es el catálogo autoritativo?
11. **¿Las alertas deben sobrevivir a una gestión?** Hoy se borran todas al gestionar. ¿Bug o comportamiento deseado?
12. **¿CUMPLIR y CEGROUP son del mismo operador?** Mismo skeleton, hosting/datos distintos. Determina si se unifican en v2.

---

## Resumen de severidad (este lote)

| Severidad | Cantidad (de #1–64 con sev asignada + N1–N14) |
|---|---:|
| P0 | 7 |
| P1 | 9 |
| P2 | 24 |
| P3 | 26 |
| (Pregunta de negocio, sin sev) | 12 |
| **Total con severidad** | **66** |

> El conteo previo era 64 (sin clasificación P0–P3). Este lote verifica esos 64 (con **11 reclasificados/corregidos**: #4, #16, #18, #22, #30, #34, #37, #39, #40 y la corrección de OCULTAR + t_base ops) y suma **14 nuevos** (N1–N14). Los hallazgos de seguridad se detallan y reconcilian en `14-seguridad.md`.
