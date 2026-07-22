# 15 — Glosario (CUMPLIR)

> Términos del dominio de cobranza tal como **el código y los datos los usan** — no como el negocio podría definirlos. Cada entrada cita tabla/columna y, donde existen, los **valores enumerados reales** de `_evidence/column_profiles.json` (frecuencia entre paréntesis). Rutas relativas a `cumplir/`. El negocio NO se infiere; lo no observable se marca `No verificado:`.
>
> Convención: `valor(n)` = el valor aparece n veces en producción. Valores `n=1` que coinciden con el nombre de la columna (`BANCO`, `canal`, `tipo`, `DECIL`, `REF`, `CONDICION`) son **encabezados de CSV insertados como datos** (ver doc 16). Los términos marcados **[CUMPLIR-only]** no existen en CEGROUP; **[difiere de CEGROUP]** señala cambios verificados.

---

## A

**Acuerdo**
Compromiso de pago registrado por un asesor. Tabla `t_acuerdos` (11.322 filas / 6.321 ops distintas → ratio **1.791×**, varios acuerdos por operación). Campos: `operacion`, `cliente`, `nombre`, `facuerdo` (fecha pactada), `fregistro` (fecha de registro), `valor`, `asesor`, `estado`. No hay flujo para marcarlo cumplido/roto: `t_acuerdos.estado` tiene **un solo valor**: `activo(11322)`. Ejemplo (`samples/t_acuerdos.json`): `{operacion:4475817, cliente:TITULAR, facuerdo:2022-11-26, valor:160000, asesor:LUISA.RIVERA}`.

**Acuerdo (sub-estado)**
En `t_subs` (catálogo de 9): `ACUERDO DE PAGO` y `ACUERDO INCUMPLIDO` (ambos bajo `estado=ACUERDO`). **[difiere de CEGROUP]**: CEGROUP tenía PAGO TOTAL / CUOTAS FIJAS / CONDONACION CAMPANA / CUOTAS PARCIALES como subs de acuerdo. En datos reales de `t_procesos.sub` aún aparecen esos viejos (`PAGO TOTAL(21)`, `CONDONACION CAPITAL(33)`, `CONDONACION CAMPANA(23)`, `CUOTAS PARCIALES(9)`) por arrastre histórico, fuera del catálogo actual.

**Alerta**
Recordatorio que un asesor agenda. Tabla `t_alertas` (304 filas vivas / 278 ops; AUTO_INCREMENT=43.120 → mayoría borradas). **[difiere de CEGROUP]**: 304 vivas vs 22 en CEGROUP. Se borra al registrar gestión sobre la misma operación (`g_gestiones.php:110`). El campo `alerta` suele ser el literal `LLAMADA PENDIENTE` (`samples/t_alertas.json`, autor `ANGELA.GALINDEZ`) → alertas template, no personalizadas.

**Aporte**
Sinónimo de "pago" en el API (endpoint `g_aportes`). Consulta `t_pagos`, **vacía** (0 filas). La respuesta siempre es vacía.

**Asesor**
Usuario que gestiona cobranza. `t_usuarios` con `usertype != 0`. Identificado por `username`. Datos: `usertype` → `1(10)` (asesor) / `0(3)` (admin). Solo **13 usuarios** totales **[difiere de CEGROUP]** (65 en CEGROUP). Su `username` es la clave de toda columna `asesor` de las tablas transaccionales.

**Asesores reales** (samples de `reporte_gestion` y transaccionales)
`ANDRES.ALVEAR`, `LUISA.RIVERA`, `ANGELA.GALINDEZ` y otros históricos (`TRINIDAD.BAOS`, `MARCELA.BURBANO`, etc.). `No verificado:` cuáles están activos hoy.

**Asesores sintéticos / admin** (samples de `t_usuarios`, `usertype=0`)
`JURIDICOS.CUMPLIR` (cedula 0), `UBINEY.CERON` (cedula 123456, nombre `0`), `CUENTA.CUMPLIR` (cedula 225544). Con `usertype=1`: `YESMIN.HOYOS`, `DANIEL.TROYANO`. Varios con contraseña `0` (ver `userpass`, P2-2). `No verificado:` el rol exacto de las cuentas sintéticas.

**Asignación**
Relación operación → asesor. Tabla `t_asignacion` (58.648 filas / 47.955 ops → ratio **1.223×**). **[difiere de CEGROUP]**: aquí HAY duplicación (allí ratio 1×). Se carga con `DATA/UPDATE/ASIGNACION/` y `api/file/update/ASIGNAR/`, y se modifica en bulk con REASIGNACION.

## B

**Banco**
Código bancario de origen. `t_base.banco` (varchar(60) NOT NULL): `802020(49471) | 802030(834) | 802040(71) | 802060(4) | BANCO(1=basura)`. **Código numérico, no nombre.** **[difiere de CEGROUP]**: era varchar(30) DEFAULT NULL con 98 valores de nombre de banco.

**Base de datos** (UI)
En `views/base.php` y el ítem de menú `BASE DE DATOS` (`menu.php:10`) designa el **panel de cargas masivas** (admin), no el motor.

## C

**Campaña**
Agrupación estratégica. Lookup `l_campana` (7 filas). Muestra (`samples/l_campana.json`): `CAMPANA 09 (60%)`, `CAMPANA 12 (40%)`, `CAMPANA 11 (20%)`, `CAMPANA 10 (60%)`, `NUEVAS CARTERAS (40%)`. **[difiere de CEGROUP]**: el porcentaje va entre paréntesis (`(60%)`) en lugar de un número suelto, y hay 7 entradas vs 3. Por operación en `t_campana` (55.154 filas / 45.328 ops, ratio 1.217×).

**Canal**
Medio de contacto del cierre. `t_resumen.canal` (varchar(20), 6 distintos): `TELEFONO CELULAR(393748) | Telefono(68681) | WHATSAPP(25945) | TELEFONO FIJO(121) | MSN(88) | canal(1=basura)`. **Nota:** convive `TELEFONO CELULAR` (mayúsculas) con `Telefono` (capitalizado) — nomenclatura inconsistente.

**Capital**
Saldo de capital. `t_saldos.capital`. Decimal con coma española como string. **[difiere de CEGROUP]**: charset `utf8mb3` (CEGROUP era `latin1`).

**Cartera**
Lote de operaciones por mes de origen. Lookup `l_cartera` (62 filas) + por operación en `t_cartera` (56.444 filas / 46.249 ops, ratio 1.22×). Muestra (`samples/l_cartera.json`): `FMM Ene 2017`, `2 Cartera Mar 2016`, `3 Cartera Jun 2016`, `1 Cartera Dic 2015`. **[difiere de CEGROUP]**: 62 entradas vs 43.

**Castigada**
Estado de cartera observado en `t_procesos.estado=CASTIGADA(1959)`. `No verificado:` no está en el catálogo `t_estados`; jerga contable (crédito castigado/dado de baja).

**Codeudor**
Garante junto al titular. En `t_base`, columnas `c*` (`ccedula`, `cnombre`, `ctel1`, `ctel2`). UI: "CODEUDOR 1". (Ver **Garante** para el codeudor 2.)

**Cliente** (en acuerdos)
Persona que asume el pago. `t_acuerdos.cliente` (4 distintos): `TITULAR(10949) | CODEUDOR_2(186) | CODEUDOR_1(173) | TERCERO(14)`. **[difiere de CEGROUP]**: aquí el enum está limpio (sin variante `CODEUDOR 1` con espacio).

**Cliente** (módulo)
Vista y endpoint **huérfanos**: `views/clientes.php` (137 líneas), `api/app/rest/clientes.php`. Sin ruta web (`app/http/routes.php` no lo lista) ni API. Sus queries apuntan a una tabla `clientes` que **no existe** (23 tablas, ninguna `clientes`). `clientes.php:142` además sube un PDF sin validación.

**Condición**
Estado libre del crédito. `t_base.condicion` (4 distintos): `ACTIVO(49796) | 802020(507=basura, código de banco mal cargado) | 0(77) | CONDICION(1=header)`. La contaminación con `802020` evidencia drift de columnas en el import (ver doc 16).

## D

**Decil**
Ranking de prioridad/riesgo por operación. `t_decil.decil` (varchar(10), 12 distintos): `2(7357) | 3(6646) | 10(6330) | 5(5927) | 9(5229) | 7(4943) | 1(4918) | 6(4751) | 4(4661) | 8(3919) | SIN DECIL(472) | DECIL(1=header)`. **[difiere de CEGROUP]**: mucho más limpio (deciles 1–10 reales) vs los 79 valores sucios de CEGROUP. Borrar entradas de `t_decil` oculta la operación del detalle de gestión (proceso OCULTAR, `g_operacion.php:84`).

**Dependencia**
Sub-clasificación. `t_base.dependencia`. Sample: `SIN DATO`.

## E

**Estado** (catálogo maestro)
Clasificación general. Catálogo `t_estados` (5 filas; AUTO_INCREMENT=8): `ACUERDO`, `ILOCALIZADO`, **`JURIDICO`** **[CUMPLIR-only]**, `RENUENTE`, `PAZ_Y_SALVO` (`samples/t_estados.json`). **[difiere de CEGROUP]**: `INVESTIGACION` salió del catálogo; entró `JURIDICO`.

**Estado** (valor real por operación)
`t_procesos.estado` (varchar(60), **14 valores distintos**): `ILOCALIZADO(64530) | LOCALIZADO(17207) | RENUENTE(8514) | 0(5591=basura) | PAZ_Y_SALVO(4971) | INVESTIGACION(4767) | ACUERDO(1981) | CASTIGADA(1959) | SIN DATO(322) | PAZ Y SALVO(19) | D(1) | S(1) | JURIDICO(1) | X(1)`. **Lectura:** el catálogo no se aplica como constraint; `LOCALIZADO`, `INVESTIGACION`, `CASTIGADA` están en datos pero no en el catálogo de 5; `JURIDICO` casi no se usa todavía (1 fila); hay basura (`0`, `D`, `S`, `X`).

## F

**fgestion (Fecha de gestión)**
Última fecha de gestión de la operación. `t_procesos.fgestion`. Se actualiza al insertar en `t_gestiones` (`g_gestiones.php:108`). Usada para no re-mostrar operaciones gestionadas hoy (`b_datafilter.php:49`). **5.838 filas** con `0000-00-00` (`_evidence/quality.json`).

**facuerdo**
Fecha pactada del pago. `t_acuerdos.facuerdo`. Distinta de `fregistro`. 2 filas con `0000-00-00`.

**fregistro**
Fecha de registro. En `t_acuerdos`, `t_resumen`.

## G

**Garante** (= Codeudor 2)
Tercera persona vinculada. En `t_base`, columnas `g*` (`gcedula`, `gnombre`, `gtel1`, `gtel2`). UI: "CODEUDOR 2".

**Gestión**
Cada interacción registrada. Tabla `t_gestiones` (**801.051 filas** / 96.599 ops → ratio 8.293×; tabla más grande). **[difiere de CEGROUP]**: ratio 8.3× vs 14.75×. Campos: `operacion`, `asesor`, `nombre`, `fecha`, `hora`, `gestion`. Insertar dispara `UPDATE t_procesos.fgestion` + `DELETE FROM t_alertas WHERE operacion`. 21 filas basura `operacion=0`.

## H

**hora** (en gestiones)
`t_gestiones.hora`. Al insertar se genera con `date('h:i:s A')` (`g_gestiones.php:94`). En datos viejos hay corrupción (`12:23 p.?m.`, no-ASCII truncado).

## I

**ILOCALIZADO**
Estado: no se localiza al deudor. En catálogo `t_estados` y el valor más frecuente de `t_procesos.estado` (64.530) y `t_procesos.sub` (64.525).

**INVESTIGACION**
Estado de búsqueda. **[difiere de CEGROUP]**: ya NO está en el catálogo `t_estados` de CUMPLIR, pero persiste en datos (`t_procesos.estado=INVESTIGACION(4767)`).

## J

**JURIDICO** **[CUMPLIR-only]**
Estado nuevo (en `t_estados`), indica paso a proceso legal. Apenas usado en datos (`t_procesos.estado=JURIDICO(1)`). Nombre de la cuenta admin `JURIDICOS.CUMPLIR` sugiere área jurídica. `No verificado:` el flujo jurídico exacto.

## L

**LLAMADA PENDIENTE**
Texto literal repetido en `t_alertas.alerta` (`samples/t_alertas.json`). Alertas template.

**LOCALIZADO**
Valor frecuente de `t_procesos.estado(17207)` y `t_procesos.sub(17207)`. **[CUMPLIR, no en catálogo]**. `No verificado:` su definición; complementa a ILOCALIZADO.

## M

**Mensaje**
Texto admin → asesor. Tabla `t_mensaje` (**vacía**; AUTO_INCREMENT=12 → se usó y se vació). Backend (`admin_asesor.php` mensaje_*) y UI completos, sin uso actual.

## N

**ncuotas**
Número de cuotas pactadas. `t_resumen.ncuotas` (varchar(5)).

## O

**Ocultar** **[CUMPLIR-only]**
Acción admin de retirar una operación del detalle de gestión vía `DATA/OCULTAR/procesar.php` (`DELETE FROM t_decil WHERE operacion`). Como `g_operacion` hace `INNER JOIN t_decil`, la operación deja de aparecer en la pantalla de gestión. **No afecta** la cola del filtro (`b_datafilter` no usa `t_decil`). Sin auth ni log.

**Operación**
ID del crédito en gestión. PK de `t_base` (**bigint(40)**) **[difiere de CEGROUP]** (era bigint(30)). 50.381 ops distintas (`_evidence/quality.json`, `t_base_distinct_operacion`). Ejemplos: `2`, `362884`, `4475817`. IDs **más cortos** que CEGROUP (que usaba 11–13 dígitos).

## P

**Pago**
Abono al crédito. Tabla `t_pagos` **vacía**. `g_aportes` y `file/sql/admin/reca/` consultan `t_pagos` → vacío.

**PAZ_Y_SALVO**
Estado terminal: operación liquidada. En `t_estados` y `t_procesos.estado(4971)`. Convive con la variante sucia `PAZ Y SALVO(19)` (sin guiones bajos).

**Phone status** **[CUMPLIR-only]**
Columna `t_telefonos.status` (varchar(20) NOT NULL). Modificable vía endpoint `phone` (`phone.php:25`), botón en `gestion.js:451,456`. **EN USO** (corrige doc previo): valores reales `ACTIVO(21772)` vs vacío(112162) (`column_profiles.json`). Es un **toggle binario activo/inactivo** (`gestion.js` envía `'activo'` o `''`), no un catálogo VALIDO/INVALIDO. Los registros cargados por `SUBIR_TELEFONOS` quedan con `status=''`.

**Posición**
Ranking del asesor. `t_usuarios.posicion` (varchar(10)). Datos: `0(13)` único — nunca poblada.

**Proyección** **[CUMPLIR-only]**
Reporte de acuerdos futuros por `facuerdo`. Módulo Flight `REPORTES/REPORTES/` tiene `/proyeccion/{date,all}` que pueblan `reporte_proyeccion`. Hoy todas las celdas en `-` (nunca computado de verdad, ver `reporte_*`).

## R

**r_acuerdo** **[CUMPLIR-only]**
Tabla nueva, **0 filas**. Columnas: `asesor(varchar40)`, `ope(varchar40)`, `acu(varchar10)`, `valor(varchar20)`, `fecha(date)`. Charset `utf8mb4_unicode_ci`. `No verificado:` propósito (¿predecesora de `reporte_acuerdos`? ¿backup? ¿feature abandonada?). `fecha date NOT NULL` sin default → cualquier INSERT sin fecha fallaría.

**Reasignación** **[CUMPLIR-only]**
Acción admin de cambiar el asesor de operaciones en bulk. `DATA/UPDATE/REASIGNACION/procesar.php` → `UPDATE t_asignacion SET asesor WHERE operacion`. Sin auth ni log; afecta todas las filas duplicadas de la operación.

**Recaudo**
Pagos cobrados. Reporte `api/file/sql/admin/reca/index.php` sobre `t_pagos` (vacía) → siempre vacío. **Corrige doc previo:** este reporte SÍ existe en CUMPLIR.

**Referencia** **[CUMPLIR-only]**
Columna `t_base.referencia` (varchar(100) NOT NULL). Datos: `802020(48964) | 802030(834) | (vacío)(507) | 802040(71) | 802060(4) | REF(1=header)`. **Duplica el código de `banco`** (mismos valores 802020/802030/...) → el negocio de "referencia" no está claro; en la práctica replica `banco`. `No verificado:` su propósito real.

**reporte_gestion / reporte_acuerdos / reporte_proyeccion** **[CUMPLIR-only]**
Tablas cache wide (una fila por asesor; columnas `ges_01..ges_31`/`ope_*`/totales `_t`; charset `utf8mb4`). Pobladas por el módulo Flight. Estado real (`samples/`): `reporte_gestion` tiene totales reales (`ges_t=175`) pero celdas diarias mayormente `-`; `reporte_acuerdos` y `reporte_proyeccion` están **todas en `-`** (nunca computadas, incluidos los totales). 10 filas cada una (`tables.json`).

**Resumen**
Registro de cierre de un contacto. Tabla `t_resumen` (**488.584 filas** / 80.595 ops → ratio 6.062×). Incluye `canal`, `contacto`, `tipo`, `ncuotas`, valores monetarios, condonación. 17 filas basura `operacion=0`; 1.088 con `fingreso='0000-00-00'`.

**RENUENTE**
Estado: el deudor evita pagar. En catálogo y en `t_procesos.estado(8514)`.

## S

**Saldo**
Monto adeudado. Tabla `t_saldos` (55.154 filas / 45.328 ops, ratio 1.217×). Columnas `capital`, `total`, decimal con coma. **[difiere de CEGROUP]**: charset `utf8mb3` (CEGROUP `latin1`).

**Status** (de teléfono)
Ver **Phone status**.

**Sub-estado (`sub`)**
Subcategoría del estado. Catálogo `t_subs` (**9 filas**; AUTO_INCREMENT=33). Verificados (`samples/t_subs.json`): `ACUERDO DE PAGO`, `ACUERDO INCUMPLIDO`, `ILOCALIZADO`, `FALLECIDO`, `NO CONTESTA`. **[difiere de CEGROUP]**: 9 vs 37 (muy simplificado). **Discrepancia catálogo↔uso:** `t_procesos.sub` tiene **39 valores distintos** (incluye `ILOCALIZADO(64525)`, `LOCALIZADO(17207)`, `PRIMER/SEGUNDO BARRIDO`, `INSOLVENTE`, `CONDONACION *`, basura `0(5591)`).

**SUBIR_*** **[CUMPLIR-only]**
Prefijo de 5 scripts en `api/file/update/SUBIR_*/procesar.php`: `RESUMEN`, `ALERTAS`, `ACUERDOS`, `GESTION`, `TELEFONOS`. Bulk import de datos **transaccionales** (no solo dimensionales). `SUBIR_TELEFONOS` no inserta `status`.

**Sucursal**
Origen del crédito. `t_base.sucursal`. Sample: `AVENIDA ORIENTAL`.

## T

**Teléfono**
Número de contacto. Tabla `t_telefonos` (**133.934 filas** / 63.342 ops → ratio 2.114×, append-only). Campos: `operacion`, `asesor`, `telefono`, `detalle`, **`status`** **[CUMPLIR-only]**. `detalle` es texto libre (`ES TIA`, `SUPER GIROS`).

**Titular**
Deudor principal. En `t_base`, columnas `t*` (`tcedula`, `tnombre`, `ttel1`, `ttel2`).

**Tipo** (de cliente, en resumen)
`t_resumen.tipo` (varchar(20), 6 distintos): `Titular(452469) | CODEUDOR_1(16624) | CODEUDOR_2(15686) | Codeudor(3775) | TERCERO(29) | tipo(1=basura)`. Nomenclatura mixta (`Titular`/`Codeudor` viejos vs `CODEUDOR_1/2` nuevos), igual que en CEGROUP.

**TT**
Jerga del campo `detalle` de `t_telefonos`. `No verificado:` significado exacto; solo aparece como texto libre.

## U

**username**
Login del usuario. `t_usuarios.username` (sin UNIQUE). Clave de toda columna `asesor`. Patrón `NOMBRE.APELLIDO` (`ANDRES.ALVEAR`). Renombrar rompe referencias (no hay FK).

**userpass**
Hash MD5 del password (32 hex, sin sal). Default al crear: `md5(0)`=`cfcd208495d565ef66e7dff9f98764da`. Estado real: **10 de 13 usuarios** con ese hash (`_evidence/quality.json`). Ver doc 14 (P2-1, P2-2).

**usertype**
Tipo de usuario. `t_usuarios.usertype` (int(1)): `0`=admin / `!=0`=asesor. Datos: `1(10) | 0(3)`. Gobierna el menú (`menu.php:20`). **El valor llega del cliente** vía `session.php?a=on&t=` (bypass P0-4).

**estado** (de usuario)
`t_usuarios.estado` (varchar(10)): `TRUE(13)` único. Login exige `estado='TRUE'` (`login.php:17`).

## V

**vcredito / vnegociado / condonado**
En `t_resumen` (varchar(20)): valor original / valor a pagar / valor perdonado. Texto, pesos colombianos.

---

## Tablas y volumen (resumen, `_evidence/tables.json` + `quality.json`)

| Tabla | Filas (COUNT) | Ops distintas | Ratio | Charset | Notas |
|---|---:|---:|---:|---|---|
| `t_base` | 50.381 | 50.381 | 1× | utf8mb3_spanish_ci | maestro; +`referencia` |
| `t_asignacion` | 58.648 | 47.955 | 1.223× | utf8mb3 | op→asesor, duplicada |
| `t_campana` | 55.154 | 45.328 | 1.217× | utf8mb3 | |
| `t_cartera` | 56.444 | 46.249 | 1.22× | utf8mb3 | |
| `t_decil` | 55.154 | 45.328 | 1.217× | utf8mb3 | deciles 1–10 |
| `t_saldos` | 55.154 | 45.328 | 1.217× | utf8mb3 | decimal con coma |
| `t_procesos` | 109.865 | 97.109 | **1.131×** | utf8mb3 | duplicación moderada |
| `t_acuerdos` | 11.322 | 6.321 | 1.791× | utf8mb3 | estado siempre `activo` |
| `t_telefonos` | 133.934 | 63.342 | 2.114× | utf8mb3 | +`status` (activo/'') |
| `t_resumen` | 488.584 | 80.595 | 6.062× | utf8mb3 | |
| `t_gestiones` | 801.051 | 96.599 | 8.293× | utf8mb3 | tabla más grande |
| `t_alertas` | 304 | 278 | 1.094× | utf8mb3 | efímera |
| `t_pagos` | 0 | 0 | — | utf8mb3 | **vacía** |
| `t_mensaje` | 0 | 0 | — | utf8mb3 | **vacía** |
| `t_usuarios` | 13 | — | — | utf8mb3 | 10 con pass `0` |
| `t_estados` | 5 (IS dice 6) | — | — | utf8mb3 | +JURIDICO, −INVESTIGACION |
| `t_subs` | 9 | — | — | utf8mb3 | catálogo (vs 37) |
| `l_campana` | 7 | — | — | utf8mb3 | `(60%)` en nombre |
| `l_cartera` | 62 | — | — | utf8mb3 | lookup |
| `r_acuerdo` | 0 | — | — | **utf8mb4** | vacía, propósito desconocido |
| `reporte_gestion` | 10 | — | — | **utf8mb4** | wide; totales sí, días `-` |
| `reporte_acuerdos` | 10 | — | — | **utf8mb4** | wide; todo `-` |
| `reporte_proyeccion` | 10 | — | — | **utf8mb4** | wide; todo `-` |

**Charset mixto:** 19 tablas `utf8mb3`, **4 tablas `utf8mb4`** (`r_acuerdo`, `reporte_*`) — JOINs entre collations distintas pueden fallar.
