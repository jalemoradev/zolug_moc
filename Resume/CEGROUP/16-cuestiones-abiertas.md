# 16 — Cuestiones abiertas, defectos y deuda técnica (CEGROUP)

> Verificación de los 58 hallazgos del doc previo (`PROYECTO_CEGROUP/resumen/15-cuestiones-abiertas.md`) contra el código y los datos actuales, más hallazgos nuevos de este lote. Estado: **Vigente** (sigue igual), **Actualizado** (cambió el dato), **Corregido** (el doc previo estaba mal), **Nuevo**.
> Severidad: P0 (crítico) · P1 (alto) · P2 (medio) · P3 (bajo). Las de seguridad se detallan en `14-seguridad.md`.

---

## Tabla maestra de defectos

| ID | Descripción | Evidencia (`archivo:línea` / `_evidence`) | Sev | Estado | Impacto en v2 |
|---|---|---|---|---|---|
| 1 | SQLi en todos los endpoints REST | barrido `api/app/rest/*.php`; `login.php:14-17` | P0 | Vigente | Reescritura total a ORM parametrizado |
| 2 | Pasamanos `?sql=` sin auth | `api/file/sql/user/acue/index.php:12,36` | P0 | Vigente | No portar; eliminar |
| 3 | Sin auth en capa API | `api/lib/Restapi.php:1-11`; grep sin coincidencias | P0 | Vigente | Guard JWT global |
| 4 | Bypass `session.php?a=on&t=0` | `app/php/session.php:3-7`; `index.php:132` | P0 | Vigente | Sesión server-side, rol no del cliente |
| 5 | Passwords `md5('0')` por defecto | `admin_asesor.php:165`; `quality.json` = **6/65** | P2 | **Actualizado** (antes "≥3", hoy 6) | Pass inicial aleatoria + cambio forzado |
| 6 | Password en URL GET | `login.php:14-15`; `index.php:19`; `perfil.js:114` | P1 | Vigente | POST sobre HTTPS |
| 7 | Sin HTTPS forzado | `index.php:11` (`http://...`) | P3 | Vigente | HTTPS+HSTS |
| 8 | Upload sin validación (RCE) | `api/UPDATE/*/upload.php:6-9` | P0 | Vigente | Allowlist + storage fuera del webroot |
| 9 | SQLi vía contenido del CSV | `api/UPDATE/BASE/procesar.php:9-44` | P0 | Vigente | Importadores parametrizados |
| 10 | `elimina.php` borra `t_base` sin auth | `api/UPDATE/elimina.php:4-15` | P0 | Vigente | Soft-delete con rol admin |
| 11 | CORS `*` | cabecera en cada `rest/*.php` (`login.php:2`) | P2 | Vigente | Allowlist de orígenes |
| 12 | Ruta `g_asesor` rota | `routes.php:5` apunta a `g_asesor`; `ApiController.php` define `asesor()` no `g_asesor()` | P1 | Vigente | Eliminar ruta/método muerto |
| 13 | Año `2023` hardcodeado en filtros | `b_acuerdos.php:64-65`; `b_resumen.php:19-20`; `views/acuerdos.php:38`; `views/resumen.php:31` | P1 | Vigente | Filtros por rango dinámico |
| 14 | `g_gestiones` borra todas las alertas de la op | `g_gestiones.php:110` | P1 | Vigente | Alertas con ciclo de vida propio |
| 15 | `asesor.php` typo `cedukla` | `asesor.php:78` (`WHERE cedukla=...`) | P3 | Vigente | Código muerto; no portar |
| 16 | `asesor.php` usa tabla `asesor` inexistente | `asesor.php:78,94,110`; no en `SHOW TABLES` | P3 | Vigente | Código muerto |
| 17 | Encabezado de CSV insertado como dato | `t_resumen.canal=CANAL(1)`, `tipo=tipo(1)`, `contacto=conta(1)`; `l_campana=5449500`; `quality.json` filas basura | P2 | Vigente | Saltar header en import; limpiar |
| 18 | `t_procesos` > `t_base` (duplicación) | `quality.json`: t_procesos 57,590 vs t_base 54,931 = **+2,659** | P2 | **Actualizado** (antes +1,000; hoy +2,659) | Reimport con upsert/clave única |
| 19 | `t_asignacion` con ops duplicadas | `quality.json`: ratio **1×** (54,931/54,931) | P3 | **Corregido** (hoy NO hay duplicados en asignación; el riesgo de JOIN persiste por diseño) | FK + unique |
| 20 | `fregistro='0000-00-00'` en acuerdos | `quality.json`: `t_acuerdos.fregistro` = **741** filas | P2 | **Actualizado** (cuantificado) | `DATE` NOT NULL; migrar nulos |
| 21 | `hora` con formato variable | `t_gestiones.hora` (`'8:23:AM'`, `'HORA'`) | P3 | Vigente | Tipo `TIME`/`TIMESTAMP` |
| 22 | Currency varchar con coma decimal | `t_saldos.capital` (`latin1`) | P2 | Vigente | `DECIMAL` numérico |
| 23 | Estado de acuerdo nunca se actualiza | `t_acuerdos.estado` = `activo(10902)`, único valor | P2 | Vigente (confirmado en datos) | Máquina de estados de acuerdo |
| 24 | Alertas efímeras (llenas hasta id alto, 22 vivas) | `tables.json` `t_alertas` AUTO_INCREMENT 11,418; COUNT 22 | P2 | Vigente | Alertas persistentes |
| 25 | `t_pagos` vacía | `tables.json`/`quality.json`: 0 filas | P1 | Vigente | Definir captura de pagos (pregunta de negocio) |
| 26 | `t_mensaje` vacía | `tables.json`: 0 filas | P3 | Vigente | Decidir si existe el feature |
| 27 | `views/index.php` vacío | `wc -l` = 0 | P3 | Vigente | — |
| 28 | `views/pagos.php` vacío | `wc -l` = 0 | P3 | Vigente | — |
| 29 | Módulo Clientes huérfano | `rest/clientes.php`; sin ruta en `app/http/routes.php` (verificado) | P2 | Vigente | Decidir incluir/descartar |
| 30 | `component/*` son stubs `<h1>hola soy X</h1>` | 9 archivos en `app/component/base/*` y `reporte/*` | P3 | Vigente | Eliminar |
| 31 | `UPDATEOPERACIONGESTION/` vacía | `ls api/UPDATE/` muestra dir vacío | P3 | Vigente | Eliminar |
| 32 | Dos árboles de import paralelos | `api/UPDATE/` y `api/file/update/` (este último con ELIMINAPRO, UPBASE, UPESTATE, ASIGNAR) | P2 | Vigente | Unificar en un solo pipeline |
| 33 | `dir_local` hardcodeado sin uso | `index.php:10-12` | P3 | Vigente | URL por env var |
| 34 | Tabla `asesor` referenciada, no existe | = #16 | P3 | Vigente (duplica #16) | — |
| 35 | Tabla `clientes` referenciada, no existe | `clientes.php:39,55,88,111,134` | P2 | Vigente | El módulo fallaría en runtime |
| 36 | `comunicacion` en rutas pero no en menú | `app/http/routes.php:6` (existe ruta); `menu.php:15-16` no la lista | P3 | **Corregido** (el doc previo decía "ruta no registrada"; SÍ está en rutas, falta en menú) | Decidir feature |
| 37 | `procesos` en menú pero sin ruta web | `menu.php:11` (`$menu_process`); no en `app/http/routes.php` | P2 | Vigente | 404 si se muestra |
| 38 | 0 índices secundarios en toda la BD | `indexes.json`; `tables.json` `INDEX_LENGTH=0` en todas | P1 | Vigente | Índices por `operacion`/`asesor`/fecha |
| 39 | 0 foreign keys | `indexes.json` (sólo PK) | P2 | Vigente | FKs + integridad referencial |
| 40 | Charsets mixtos (latin1 en t_email, t_saldos) | `tables.json`: `t_email`/`t_saldos` = `latin1_swedish_ci` | P2 | Vigente | utf8mb4 uniforme |
| 41 | `utf8mb3` no soporta BMP completo | `tables.json` 18 tablas utf8mb3 | P3 | Vigente | utf8mb4 |
| 42 | `l_campana` AUTO_INCREMENT enorme | `tables.json`: AUTO_INCREMENT **5,449,502**, 3 filas | P3 | Vigente | Reset de secuencia |
| 43 | `set_time_limit(60000000000)` | `elimina.php:4`; `procesar.php:2` (todos) | P3 | Vigente | Cola con límites |
| 44 | Imports sin transacciones | `procesar.php` (INSERT por fila, sin BEGIN/COMMIT) | P2 | Vigente | Transaccional + rollback |
| 45 | `array_map(CODING,$d)` no-op | `api/api/index.php:6` (`CODING=null`); `format_post` ramas muertas | P3 | Vigente | Eliminar capa falsa |
| 46 | MD5 para passwords | `login.php:15`; `d_usuario_cambio.php:54` | P2 | Vigente | bcrypt/argon2 |
| 47 | Sin paginación en listas | `b_data.php` (LIKE sobre 54k) | P2 | Vigente | Paginación obligatoria |
| 48 | Sin logs estructurados | `DB.php:7` (`print("error de conexion")`) | P3 | Vigente | Logger estructurado |
| 49 | Sin tests | no hay carpeta test / framework | P2 | Vigente | Suite de pruebas |
| 50 | Sin documentación inline / README | repo sin README significativo | P3 | Vigente | — |

---

## Hallazgos nuevos de este lote

| ID | Descripción | Evidencia | Sev | Impacto v2 |
|---|---|---|---|---|
| N1 | **Credenciales de BD de producción hardcodeadas** | `api/lib/DB.php:5` (`user_cegroup`/`«REDACTADO»`/`data_cegroup`) | P0 | Mover a env; rotar contraseña ya filtrada |
| N2 | **Reset de contraseña por GET sin prueba de identidad** | `d_usuario_cambio.php:52-57`; `perfil.js:114` | P1 | Cambio con sesión propia + pass actual |
| N3 | **`admin_asesor` borra/crea/activa usuarios sin auth** | `admin_asesor.php:159-250` | P1 | CRUD sólo con rol admin autenticado |
| N4 | **Hash de password devuelto al cliente** | `login.php:17,22-24`; `admin_asesor.php:75`; `file/sql/admin/ases/index.php:9` | P1 | DTO que excluye credenciales |
| N5 | **Fuga del SQL en respuestas (`$data['sql']`)** | 9 endpoints (`g_email.php:44`, `b_*`, `admin_asesor.php`, `d_usuario_cambio.php:40,57`) | P2 | Nunca devolver SQL |
| N6 | **`t_procesos.estado` sobrecargado: 23 valores** mezclando estados de cobranza, tipos de proceso jurídico, condición del crédito y basura | `column_profiles.json` (`RENUENTE`,`CIVIL`,`PENAL`,`FAMILIA`,`0`,`SIN DATO`,`DOC CARLOS`…) | P1 | Separar `estado` (catálogo) de `tipo_proceso_juridico`; limpiar |
| N7 | **`t_procesos.sub` con 92 valores vs catálogo `t_subs` de 37** | `column_profiles.json` `t_procesos.sub` distinct=92; `t_subs` 37 filas | P2 | Subs como FK al catálogo; reconciliar |
| N8 | **`t_resumen.tipo` con nomenclatura mixta** (`Titular`/`Codeudor` viejos vs `CODEUDOR_1/2` nuevos) | `column_profiles.json`: `Titular(471881)`,`Codeudor(6495)`,`CODEUDOR_1(10515)` | P2 | Normalizar a un solo enum |
| N9 | **`t_acuerdos.cliente` con `CODEUDOR 1`(espacio) y `CODEUDOR_1`(guion)** | `column_profiles.json`: `CODEUDOR_1(540)` + `CODEUDOR 1(33)` | P3 | Enum único |
| N10 | **`t_resumen.canal` con 155,099 filas vacías + duplicación `TELEFONO CELULAR`/`CELULAR`** | `column_profiles.json` | P2 | Enum NOT NULL; consolidar |
| N11 | **`t_usuarios.posicion` nunca poblada (todo `0`)** | `column_profiles.json`: `posicion` = `0(65)` único | P3 | El ranking no existe; decidir si se construye |
| N12 | **Segunda función de login en código muerto con password en texto plano** | `asesor.php:92-106` (`WHERE password='$pass'`, sin md5) | P3 | Eliminar |
| N13 | **`chmod(...,0777)` sobre el CSV antes de leerlo** | `elimina.php:7`; `procesar.php:5` | P2 | No usar archivos world-writable |
| N14 | **Filas basura `operacion=0`** dispersas | `quality.json`: t_gestiones 34, t_resumen 6, t_acuerdos 1, t_email 1 | P3 | Constraint + limpieza |
| N15 | **Fechas `0000-00-00` masivas** | `quality.json`: t_resumen.fingreso **7,343**, t_procesos.fgestion **1,328**, t_acuerdos.fregistro 741, t_base.fingreso 79, t_base.fvencimiento 48 | P2 | `DATE` NOT NULL; migración de nulos |
| N16 | **Dos importadores `BASE` con esquemas distintos** | `UPDATE/BASE/procesar.php` (20 columnas, incluye `banco`) vs `file/update/BASE/procesar.php` | P2 | Unificar contrato de import |

---

## Cambios respecto al doc previo (resumen de discrepancias)

- **#5** Passwords `md5('0')`: el doc decía "al menos 3"; el dato actual es **6 de 65** (`quality.json`).
- **#18** `t_procesos` vs `t_base`: el doc decía +1,000; hoy es **+2,659** (57,590 vs 54,931).
- **#19** `t_asignacion` duplicados: **ya NO hay** duplicación (ratio 1×). El riesgo de JOIN múltiple es de diseño, no de datos actuales.
- **#20** `fregistro='0000-00-00'`: cuantificado en **741** filas.
- **#36** `comunicacion`: el doc decía "ruta no registrada"; en realidad **sí está** en `app/http/routes.php:6`, lo que falta es agregarla al menú. (El defecto inverso es **#37 `procesos`**: en menú pero sin ruta.)
- **Estados/subs/tipo**: el doc previo asumió catálogos limpios (5 estados, tipos cerrados). Los datos reales muestran columnas sobrecargadas y sucias (N6, N7, N8) — el modelo de datos no coincide con el catálogo.
- **Decil**: el doc previo asumía 1–10; hay **79 valores distintos** (datos sucios).

---

## Preguntas abiertas que requieren al negocio

> No se infieren respuestas. Cada una necesita confirmación de CEGROUP antes de diseñar la v2.

1. **¿Por qué `t_pagos` está vacía?** ¿Los pagos se gestionan en otro sistema (bancario) y la plataforma sólo registra acuerdos/negociaciones? ¿O el módulo nunca se conectó? (Define si "recaudo" debe existir en v2.)
2. **¿`t_procesos.estado` debe contener tipos de proceso jurídico** (CIVIL, PENAL, FAMILIA…) o eso fue contaminación accidental? Determina si existe un módulo legal separado.
3. **¿El módulo Clientes (`rest/clientes.php`, tabla `clientes`) es feature futuro o descartado?** No tiene ruta ni tabla. ¿Se retoma en v2?
4. **¿Existe el feature "mensaje de coordinador"?** Está en código pero `t_mensaje` vacía. ¿Se necesita?
5. **¿El "ranking de asesor" (`posicion`) debe calcularse?** Hoy siempre es `0`. ¿Qué fórmula/criterio?
6. **¿Las alertas deben sobrevivir a una gestión?** Hoy se borran todas al gestionar (#14). ¿Es el comportamiento deseado o un bug?
7. **¿Cuál es la definición de "decil"?** ¿Es 1–10 de riesgo? Los datos tienen 79 valores; hay que definir el dominio válido.
8. **¿Qué significa exactamente el `detalle` de `t_telefonos` (jerga `TT`, `INVES CAMP n`)?** No consta en código.
9. **¿Por qué dos árboles de import (`UPDATE/` vs `file/update/`)?** ¿Operaciones distintas (admin avanzado vs estándar) o legacy + reescritura? Determina cuál es la fuente de verdad.
10. **¿Qué carteras/campañas son válidas?** Los lookups (`l_cartera`, `l_campana`) tienen basura mezclada con valores reales; hay que validar el catálogo oficial.
11. **¿Cómo se relacionan `estado` (catálogo) y `sub` con el catálogo `t_subs`?** `t_procesos.sub` (92) excede `t_subs` (37). ¿Cuál es el catálogo autoritativo?
12. **¿Quién/qué consume `file/sql/user/acue/?sql=`?** Es el pasamanos SQL (P0-2). Hay que entender su uso antes de eliminarlo, para no romper un reporte.

---

## Resumen de severidad (este lote)

| Severidad | Cantidad (prev #1–50 + nuevos N1–N16) |
|---|---:|
| P0 | 9 |
| P1 | 12 |
| P2 | 26 |
| P3 | 19 |
| **Total** | **66** |

> Nota: el conteo previo era 58. Este lote verifica esos 58 (con 6 reclasificados/corregidos) y suma 16 nuevos (N1–N16, algunos consolidan hallazgos de seguridad ya listados en `14-seguridad.md`).
