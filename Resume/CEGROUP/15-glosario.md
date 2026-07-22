# 15 — Glosario (CEGROUP)

> Términos del dominio de cobranza tal como **el código y los datos los usan** — no como el negocio podría definirlos. Cada entrada cita tabla/columna y, donde existen, los **valores enumerados reales** de `_evidence/column_profiles.json` (con su frecuencia entre paréntesis). Rutas relativas a `cegroup/`.
>
> Convención de evidencia: `valor(n)` = el valor aparece n veces en producción. Las filas con `n=1` que coinciden con el nombre de la columna (`CANAL`, `tipo`, `conta`, `CAMPANA`…) son **encabezados de CSV insertados como datos** (ver 16-cuestiones-abiertas, defecto de calidad).

---

## A

**Acuerdo**
Compromiso de pago registrado por un asesor. Tabla `t_acuerdos` (10,902 filas / 8,734 operaciones distintas → ratio 1.248×, hay operaciones con varios acuerdos). Campos: `operacion`, `cliente`, `nombre`, `facuerdo` (fecha pactada), `fregistro` (fecha de registro), `valor`, `asesor`, `estado`. No existe flujo para marcarlo cumplido/roto: `t_acuerdos.estado` tiene **un solo valor real**: `activo(10902)`.

**Aporte**
Sinónimo de "pago" en el API (endpoint `g_aportes`, ruta `g_aportes` en `api/app/http/routes.php:9`). Consulta `t_pagos`, que está **vacía** (0 filas). La respuesta siempre es vacía.

**Asesor**
Usuario que gestiona cobranza. `t_usuarios` con `usertype != 0`. Identificado por `username`. En datos: `usertype` tiene sólo dos valores → `1(62)` (asesor) y `0(3)` (admin). Se le asignan operaciones vía `t_asignacion`. Su `username` es la clave en toda columna `asesor` de las tablas transaccionales.

**Asesores sintéticos / usuarios bucket**
Cuentas en `t_usuarios` que no representan personas, sino "cajones" de clasificación de cartera. Verificados en `_evidence/samples/t_usuarios.json` (con `cedula` 0/2/3, teléfono `0`):
- `GRUPO.INVESTIGACION`
- `CEGROUP.PAZYSALVO`
- `CEGROUP.FALLECIDOS`

Otros mencionados en el doc previo (`CEGROUP.RENUENTES*`, `COORDINADOR`, `ASESOR30`). Todos con `usertype=1` y `estado=TRUE`. Varios tienen contraseña `0` (ver `MASIVO` y P2-2 en 14-seguridad).

**MASIVO** (usuario sintético)
No es una fila de `t_usuarios` sino un **literal hardcodeado** que el importador de gestiones escribe en la columna `asesor`: `api/UPDATE/GESTIONES/procesar.php:18` → `VALUES (NULL,'$operacion','MASIVO',...)`. Distingue las gestiones cargadas por CSV de las hechas manualmente por un asesor.

**Asignación**
Relación operación → asesor. Tabla `t_asignacion` (54,931 filas / 54,931 operaciones distintas → ratio 1×, una asignación por operación). Se carga vía `UPDATE/ASIGNACION/` y `file/update/ASIGNAR/`.

## B

**Banco**
Entidad de origen del crédito. `t_base.banco` (98 valores distintos). Por alta cardinalidad no se enumeran; el doc previo cita `NO APLICA` como frecuente.

**Base de datos** (UI)
En `app/views/base.php` y el ítem de menú `BASE DE DATOS` (`app/parts/menu.php:10`) designa el **panel de cargas masivas** (admin), no el motor MySQL.

## C

**Campaña**
Agrupación de carteras por estrategia. Lookup `l_campana` (3 filas) + por operación en `t_campana` (54,931 filas, ratio 1×, 53 valores distintos en la columna `campana`). Valores del lookup: `CAMPANA 09 60`, `CAMPANA 10 60` y una fila basura `5449500` (encabezado/ID filtrado). `l_campana.campana`: `5449500(1) | CAMPANA 09 60(1) | CAMPANA 10 60(1)`.

**Canal**
Medio de contacto del cierre. `t_resumen.canal` (varchar(20), 7 distintos): `TELEFONO CELULAR(336633) | (vacío)(155099) | TELEFONO FIJO(905) | CELULAR(453) | WHATSAPP(160) | MSN(3) | CANAL(1=basura)`. **Nota de calidad:** 155,099 filas tienen canal vacío y existe duplicación de concepto (`TELEFONO CELULAR` vs `CELULAR`).

**Capital**
Saldo de capital. `t_saldos.capital` (charset `latin1`). Decimal con coma española (p. ej. `2681441,76`). Requiere `REPLACE(',','.')`+`CAST` para sumar.

**Cartera**
Lote de operaciones por mes de origen. Lookup `l_cartera` (43 filas; 40 valores distintos en `cartera`) + por operación en `t_cartera` (54,930 filas, ratio 1×). Ejemplos reales: `FMM Ene 2017`, `1 Cartera Dic 2015`, `37 Cartera Jun 2020`, `nov-22(4)`. Hay filas basura (`303586`) e IDs altos (`5449500/5449501`) de encabezado.

**Codeudor**
Garante junto al titular. En `t_base`, columnas con prefijo `c*`: `ccedula`, `cnombre`, `ctel1`, `ctel2`. La UI lo etiqueta "CODEUDOR 1". (Ver **Garante** para el codeudor 2.)

**Condición**
Estado libre del crédito en `t_base.condicion` (7 distintos): `ACTIVO(54751) | ARRENDAMIENTO(106) | VENTA(61) | 0(7=basura) | SIN DATOS(4) | INACTIVO(1) | ARCHIVO(1)`.

**Contacto**
Si el asesor logró contacto. `t_resumen.contacto` (varchar(5), 3 distintos): `NO(396036) | SI(97217) | conta(1=basura)`.

**Cliente** (en acuerdos)
Persona a la que se formaliza el pago. `t_acuerdos.cliente` (5 distintos): `TITULAR(9975) | CODEUDOR_1(540) | CODEUDOR_2(305) | TERCERO(49) | CODEUDOR 1(33)`. **Nota:** `CODEUDOR 1` (con espacio) es variante inconsistente de `CODEUDOR_1`.

**Cliente** (módulo)
Vista y endpoint **huérfanos**: `app/views/clientes.php`, `api/app/rest/clientes.php`. **No tiene ruta** en `app/http/routes.php` (verificado: la lista de rutas web no incluye `clientes`). Sus queries apuntan a una tabla `clientes` que **no existe** en `data_cegroup` (`clientes.php:39,55,88,111,134`). Módulo en desarrollo o descartado.

## D

**Decil**
Ranking de prioridad/riesgo por operación. `t_decil.decil` (varchar(10), **79 valores distintos** — no 1–10 como sugería el doc previo). Muestras reales (`_evidence/samples/t_decil.json`): `7`, `6`, `4`. La cardinalidad alta indica datos sucios además de los deciles 1–10 (encabezados, valores libres).

**Dependencia**
Sub-clasificación de sucursal. `t_base.dependencia`. El doc previo cita placeholder `0000-00000`.

## E

**Email**
Correo asociado a operación. Tabla `t_email` (11,728 filas / 9,762 operaciones distintas → ratio 1.201×, varios correos por operación; charset `latin1`). Endpoint `g_email`. El backend aplica `mb_strtoupper` al insertar (`g_email.php:64-65`). 1 fila basura con `operacion=0`.

**Estado** (catálogo maestro)
Clasificación general de la cobranza. Catálogo `t_estados` (5 filas, todas únicas): `ACUERDO | PAZ_Y_SALVO | RENUENTE | INVESTIGACION | ILOCALIZADO` (`t_estados.estado`).

**Estado** (en proceso — valor real por operación)
`t_procesos.estado` (varchar(60), **23 valores distintos** — discrepancia importante con el doc previo, que asumía sólo los 5 del catálogo). Valores reales y frecuencias:
`RENUENTE(29419) | INVESTIGACION(25407) | 0(1036=basura) | ACUERDO(758) | PAZ_Y_SALVO(449) | CIVIL(200) | ARRENDAMIENTO(78) | SIN DATO(76) | VENTA(48) | (NULL)(46) | PENAL(16) | CASTIGADA(14) | FAMILIA(12) | LABORAL(11) | ADMINISTRATIVO(8) | SIN DATOS(4) | POLICIVO(1) | SUCESION(1) | DOC CARLOS(1) | CIVIL-PENAL(1) | NO APLICA(1) | EJECUTIVO(1) | PENDIENTE(1) | QUERRELLA(1)`.
**Lectura:** `t_procesos.estado` mezcla (a) los 5 estados del catálogo, (b) **tipos de proceso jurídico** (`CIVIL`, `PENAL`, `FAMILIA`, `LABORAL`, `ADMINISTRATIVO`, `POLICIVO`, `SUCESION`, `EJECUTIVO`…), (c) la `condicion` del crédito (`ARRENDAMIENTO`, `VENTA`), y (d) basura (`0`, `SIN DATO`, `DOC CARLOS`, `QUERRELLA`). La columna está sobrecargada con conceptos distintos.

## F

**fgestion (Fecha de gestión)**
Última fecha de gestión de la operación. `t_procesos.fgestion`. Se actualiza al insertar en `t_gestiones`. Usada para no re-mostrar operaciones ya gestionadas hoy (`d_usuario.php:38-40` cuenta gestiones del día por asesor). 1,328 filas tienen `0000-00-00` (`_evidence/quality.json`).

## G

**Garante** (= Codeudor 2)
Tercera persona vinculada. En `t_base`, columnas con prefijo `g*`: `gcedula`, `gnombre`, `gtel1`, `gtel2`. La UI lo etiqueta "CODEUDOR 2". El exportador `file/sql/admin/base/index.php:21-24` los mapea como columnas `G CEDULA/G NOMBRE/G TEL1/G TEL2`.

**Gestión**
Cada interacción registrada con la operación. Tabla `t_gestiones` (**1,838,261 filas** / 124,604 operaciones distintas → ratio 14.75×). Campos: `operacion`, `asesor`, `nombre`, `fecha`, `hora`, `gestion` (texto). Insertar dispara `UPDATE t_procesos.fgestion` y `DELETE FROM t_alertas WHERE operacion=...` (`g_gestiones.php:110`). 34 filas basura con `operacion=0`.

## H

**hora** (en gestiones)
`t_gestiones.hora`, formato inconsistente (`'8:23:AM'` sin espacio, placeholder `'HORA'` en filas de prueba).

## I

**ILOCALIZADO**
Estado: no se localiza al deudor. En catálogo `t_estados` y como sub-estado.

**INVESTIGACION**
Estado: gestión de búsqueda/localización activa. Es el estado con más sub-estados en `t_subs` (`estado=INVESTIGACION` agrupa 19 subs).

## M

**Mensaje (de coordinador)**
Texto que un admin envía a un asesor. Tabla `t_mensaje` (**vacía**, 0 filas). Definido en backend (`admin_asesor.php` funciones `INSERT_MENSAJE`/`DELETE_MENSAJE`/`SELECT_ASESORES_MENSAJE`, líneas 133-201) y en UI, pero sin uso en producción.

## N

**ncuotas**
Número de cuotas pactadas en un resumen. `t_resumen.ncuotas` (varchar(5)).

## O

**Operación**
ID del crédito en gestión. PK de `t_base` (bigint). Clave que une todas las tablas. `t_base` tiene 54,931 operaciones distintas (`_evidence/quality.json`, `t_base_distinct_operacion`). Ejemplos: `102238802020`, `109298802020`.

## P

**Pago**
Abono al crédito. Tabla `t_pagos` **vacía** (0 filas). Endpoints `g_aportes` y `file/sql/admin/reca/` consultan `t_pagos` y devuelven vacío.

**PAZ_Y_SALVO**
Estado terminal: operación liquidada. En `t_estados`, en `t_subs` (`PAZ_Y_SALVO(1)`), y en `t_procesos.estado` (449 ops).

**Posición**
Ranking del asesor. `t_usuarios.posicion` (varchar(10)). En datos: **un único valor** `0(65)` — el ranking nunca se calculó/pobló.

## R

**Recaudo**
Pagos cobrados. Reporte `file/sql/admin/reca/index.php` sobre `t_pagos` (vacía) → siempre vacío.

**RENUENTE**
Estado: el deudor evita pagar. Es el estado más frecuente en `t_procesos.estado` (29,419 ops). Sub-estados (de `t_subs` con `estado=RENUENTE`, 8 subs): `SIN INTENSION DE PAGO`, `INTENSION DE PAGO`, `INSOLVENTE`, `FALLECIDO`, `MENSAJE EXTERNO`, `RECLAMACION`, `POR TERCEROS`, etc.

**Resumen**
Registro de cierre de un contacto/llamada. Tabla `t_resumen` (**493,254 filas** / 90,617 operaciones distintas → ratio 5.44×). Incluye `canal`, `contacto`, `tipo`, `ncuotas`, valores monetarios, condonación. 6 filas basura con `operacion=0`; 7,343 filas con `fingreso='0000-00-00'`.

## S

**Saldo**
Monto adeudado por operación. Tabla `t_saldos` (54,931 filas, ratio 1×, charset `latin1`). Columnas `capital` y `total` (capital+intereses), decimal con coma española.

**Sub-estado (`sub`)**
Subcategoría del estado. Catálogo `t_subs` (37 filas; columna `estado` agrupa los subs por estado padre: `INVESTIGACION(19) | RENUENTE(8) | ACUERDO(8) | PAZ_Y_SALVO(1) | ILOCALIZADO(1)`). Valores de `t_subs.sub` (37): incluyen `PAGO TOTAL`, `CUOTAS FIJAS`, `CUOTAS PARCIALES`, `CONDONACION CAMPANA`, `CONDONACION NORMAL`, `CONDONACION CAPITAL`, `AGENDADO`, `SEGUNDO BARRIDO`, `SIN CONTACTO`, `TIMBRA / NO CONTESTA`, `NUMERO APAGADO`, `NUMERO EQUIVOCADO`, `NUMERO FUERA DE SERVCIO` (sic), `INTENSION DE PAGO`, `SIN INTENSION DE PAGO`, `FALLECIDO`, `INSOLVENTE`, `RECLAMACION`, `POR TERCEROS`, `MENSAJE EXTERNO`, `INCUMPLIDO`, y 14 variantes `DATOS EXTERNOS CAMPANA 1..14`.
**Nota:** `t_procesos.sub` tiene 92 valores distintos (la columna real por operación supera el catálogo de 37 → divergencia catálogo vs uso).

**Sucursal**
Origen del crédito. `t_base.sucursal` (397 valores distintos; alta cardinalidad).

## T

**Teléfono**
Número de contacto. Tabla `t_telefonos` (**119,431 filas** / 63,602 operaciones distintas → ratio 1.88×, append-only). Campos: `operacion`, `asesor`, `telefono`, `detalle` (3,709 valores distintos de texto libre).

**Tipo (de cliente, en resumen)**
`t_resumen.tipo` (varchar(20), 6 distintos): `Titular(471881) | CODEUDOR_1(10515) | Codeudor(6495) | CODEUDOR_2(4313) | TERCERO(49) | tipo(1=basura)`. **Nota de inconsistencia:** convive nomenclatura vieja capitalizada (`Titular`, `Codeudor`) con la nueva en mayúsculas con sufijo (`CODEUDOR_1`, `CODEUDOR_2`). El doc previo asumía sólo la nueva.

**Titular**
Deudor principal. En `t_base`, columnas con prefijo `t*`: `tcedula`, `tnombre`, `ttel1`, `ttel2`.

**TT**
Jerga del campo `detalle` de `t_telefonos`. El doc previo lo interpreta como "Titular Teléfono" — `No verificado:` el significado exacto no consta en el código; sólo aparece como texto libre en datos (`INVES CAMP 4 TT`).

## U

**username**
Login del usuario. `t_usuarios.username` (varchar(30), **sin UNIQUE**). Es la clave de toda columna `asesor`. Renombrarlo rompe referencias (no hay FK).

**userpass**
Hash MD5 del password. `t_usuarios.userpass` (32 hex, sin sal). Default al crear: `md5(0)`=`cfcd208495d565ef66e7dff9f98764da`. Estado real: **6 de 65 usuarios** con ese hash (`_evidence/quality.json`). Ver 14-seguridad (P2-1, P2-2).

**usertype**
Tipo de usuario. `t_usuarios.usertype` (int(1)): `0`=admin, `!=0`=asesor (en la práctica `1`). Datos: `1(62) | 0(3)`. Gobierna el menú: `menu.php:20` muestra admin si `session_type==0`. **El valor llega del cliente vía `session.php?a=on&t=`** (ver bypass P0-4).

**estado** (de usuario)
`t_usuarios.estado` (varchar(10)): `TRUE(64) | FALSE(1)`. Login exige `estado='TRUE'` (`login.php:17`).

## V

**vcredito / vnegociado / condonado**
En `t_resumen` (varchar(20)): `vcredito` valor original, `vnegociado` valor a pagar, `condonado` valor perdonado. Almacenados como texto.

---

## Tablas y volumen (resumen, `_evidence/tables.json` + `quality.json`)

| Tabla | Filas (COUNT) | Ops distintas | Ratio | Charset | Notas |
|---|---:|---:|---:|---|---|
| `t_base` | 54,931 | 54,931 | 1× | utf8mb3_spanish_ci | maestro de operaciones |
| `t_asignacion` | 54,931 | 54,931 | 1× | utf8mb3 | op→asesor |
| `t_campana` | 54,931 | 54,931 | 1× | utf8mb3 | |
| `t_cartera` | 54,930 | 54,930 | 1× | utf8mb3 | |
| `t_decil` | 54,931 | 54,931 | 1× | utf8mb3 | 79 valores de decil |
| `t_saldos` | 54,931 | 54,931 | 1× | **latin1** | decimal con coma |
| `t_procesos` | 57,590 | 57,590 | 1× | utf8mb3 | **+2,659 vs t_base** (duplicación) |
| `t_acuerdos` | 10,902 | 8,734 | 1.25× | utf8mb3 | estado siempre `activo` |
| `t_email` | 11,728 | 9,762 | 1.20× | **latin1** | |
| `t_telefonos` | 119,431 | 63,602 | 1.88× | utf8mb3 | append-only |
| `t_resumen` | 493,254 | 90,617 | 5.44× | utf8mb3 | |
| `t_gestiones` | 1,838,261 | 124,604 | 14.75× | utf8mb3 | tabla más grande |
| `t_alertas` | 22 | 22 | 1× | utf8mb3 | efímera (borrado por gestión) |
| `t_pagos` | 0 | 0 | — | utf8mb3 | **vacía** |
| `t_mensaje` | 0 | 0 | — | utf8mb3 | **vacía** |
| `t_usuarios` | 65 | — | — | utf8mb3 | 6 con pass `0` |
| `t_estados` | 5 | — | — | utf8mb3 | catálogo |
| `t_subs` | 37 | — | — | utf8mb3 | catálogo |
| `l_campana` | 3 | — | — | utf8mb3 | lookup |
| `l_cartera` | 43 | — | — | utf8mb3 | lookup |
