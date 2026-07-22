# CEGROUP · DB — Diccionario de datos (por columna)

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 10.11.16-MariaDB-cll-lve · BD: `data_cegroup` · charset BD: latin1 / latin1_swedish_ci.
> Introspección original: 2026-06-21T03:51:46-05:00.

Para cada tabla: cada columna con tipo real, nullability, default, clave, **nº de valores distintos**, nulos/vacíos, min/max (numéricos/fechas) y, para columnas de baja cardinalidad (≤50 distintos), **la distribución completa de valores con su frecuencia**. Más abajo, filas de muestra reales.

> Las contraseñas se enmascaran. Donde un cómputo no se pudo completar por tamaño (p. ej. `COUNT(DISTINCT)` sobre texto libre de tablas de millones de filas), aparece `n/d`.

## l_campana  ·  3 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(10) | no | ∅ | PRI | 3 | 0 | — | 1799702 | 5449501 |
| 2 | `campana` | varchar(30) | sí | NULL | — | 3 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (3 valores): `1799702` ×1 · `5449500` ×1 · `5449501` ×1
- **campana** (3 valores): `5449500` ×1 · `CAMPANA 09 60` ×1 · `CAMPANA 10 60` ×1

<details><summary>Filas de muestra (l_campana)</summary>

| id | campana |
|---|---|
| 1799702 | CAMPANA 09 60 |
| 5449500 | CAMPANA 10 60 |
| 5449501 | 5449500 |

</details>

## l_cartera  ·  43 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(10) | no | ∅ | PRI | 43 | 0 | — | 1 | 5449501 |
| 2 | `cartera` | varchar(30) | sí | NULL | — | 40 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (43 valores): `14` ×1 · `30` ×1 · `15` ×1 · `31` ×1 · `16` ×1 · `32` ×1 · `1` ×1 · `17` ×1 · `33` ×1 · `2` ×1 · `18` ×1 · `34` ×1 · `3` ×1 · `19` ×1 · `35` ×1 · `4` ×1 · `20` ×1 · `36` ×1 · `5` ×1 · `21` ×1 · `37` ×1 · `6` ×1 · `22` ×1 · `38` ×1 · `7` ×1 · `23` ×1 · `62405` ×1 · `8` ×1 · `24` ×1 · `358963` ×1 · `9` ×1 · `25` ×1 · `358964` ×1 · `10` ×1 · `26` ×1 · `5449500` ×1 · `11` ×1 · `27` ×1 · `5449501` ×1 · `12` ×1 · `28` ×1 · `13` ×1 · `29` ×1
- **cartera** (40 valores): `nov-22` ×4 · `17 Cartera Oct 2018` ×1 · `8 Cartera Dic 2017` ×1 · `12 Cartera May 2018` ×1 · `37 Cartera Jun 2020` ×1 · `303586` ×1 · `32 Cartera Ene 2020` ×1 · `28 Cartera Spt 2019` ×1 · `23 Cartera Abr 2019` ×1 · `19 Cartera Dic 2018` ×1 · `FMM Ene 2017` ×1 · `14 Cartera Jul 2018` ×1 · `5 Cartera Dic 2016` ×1 · `1 Cartera Dic 2015` ×1 · `34 Cartera Mar 2020` ×1 · `3 Cartera Jun 2016` ×1 · `25 Cartera Jun 2019` ×1 · `20 Cartera Ene 2019` ×1 · `16 Cartera May 2018` ×1 · `7 Cartera Jun 2017` ×1 · `11 Cartera Mar 2018` ×1 · `36 Cartera May 2020` ×1 · `31 Cartera Dic 2019` ×1 · `27 Cartera Agst 2019` ×1 · `22 Cartera Mar 2019` ×1 · `18 Cartera Nov 2018` ×1 · `9 Cartera Dic 2017` ×1 · `13 Cartera Jun 2018` ×1 · `4 Cartera Spt 2016` ×1 · `33 Cartera Feb 2020` ×1 · `29 Cartera Oct 2019` ×1 · `24 Cartera May 2019` ×1 · `2 Cartera Mar 2016` ×1 · `15 Cartera May 2018` ×1 · `6 Cartera Mar 2017` ×1 · `10 Cartera Mar 2018` ×1 · `35 Cartera Abr 2020` ×1 · `30 Cartera Nov 2019` ×1 · `26 Cartera Jul 2019` ×1 · `21 Cartera Feb 2019` ×1

<details><summary>Filas de muestra (l_cartera)</summary>

| id | cartera |
|---|---|
| 1 | FMM Ene 2017 |
| 2 | 9 Cartera Dic 2017 |
| 3 | 8 Cartera Dic 2017 |
| 4 | 7 Cartera Jun 2017 |
| 5 | 6 Cartera Mar 2017 |

</details>

## t_acuerdos  ·  10,902 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 10,902 | 0 | — | 2 | 10931 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 8,734 | 0 | — | 0 | 82391850802020 |
| 3 | `cliente` | varchar(30) | sí | NULL | — | 5 | 0 | 0 | — | — |
| 4 | `nombre` | varchar(60) | sí | NULL | — | 8,292 | 0 | 0 | — | — |
| 5 | `facuerdo` | date | sí | NULL | — | 1,390 | 0 | — | 0000-00-00 | 2027-04-18 |
| 6 | `fregistro` | date | sí | NULL | — | 1,190 | 0 | — | 0000-00-00 | 2026-06-19 |
| 7 | `asesor` | varchar(30) | sí | NULL | — | 72 | 0 | 1 | — | — |
| 8 | `estado` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 9 | `valor` | varchar(20) | sí | NULL | — | 2,295 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **cliente** (5 valores): `TITULAR` ×9,975 · `CODEUDOR_1` ×540 · `CODEUDOR_2` ×305 · `TERCERO` ×49 · `CODEUDOR 1` ×33
- **estado** (1 valores): `activo` ×10,902

<details><summary>Filas de muestra (t_acuerdos)</summary>

| id | operacion | cliente | nombre | facuerdo | fregistro | asesor | estado | valor |
|---|---|---|---|---|---|---|---|---|
| 2 | 4369829802020 | TITULAR | SIN NOMBRE | 2022-01-31 | 0000-00-00 | COORDINADOR | activo | 127000 |
| 3 | 4369829802020 | TITULAR | SIN NOMBRE | 2022-02-28 | 0000-00-00 | COORDINADOR | activo | 127000 |
| 4 | 4369829802020 | TITULAR | SIN NOMBRE | 2022-03-30 | 0000-00-00 | COORDINADOR | activo | 127000 |
| 5 | 3390508 | TITULAR | SIN NOMBRE | 2022-01-08 | 0000-00-00 | FARIDE.VERA | activo | 148300 |
| 6 | 4999692802020 | TITULAR | SIN NOMBRE | 2022-01-28 | 0000-00-00 | RUBI.CARDENAS | activo | 1260000 |

</details>

## t_alertas  ·  22 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 22 | 0 | — | 9390 | 11417 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 22 | 0 | — | 8466802030 | 7232674802020 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 11 | 0 | 0 | — | — |
| 4 | `fecha` | date | sí | NULL | — | 20 | 0 | — | 2024-10-24 | 2026-06-28 |
| 5 | `hora` | varchar(20) | no | ∅ | — | 8 | 0 | 0 | — | — |
| 6 | `num` | varchar(5) | no | ∅ | — | 8 | 0 | 0 | — | — |
| 7 | `alerta` | varchar(1000) | sí | NULL | — | 18 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (22 valores): `11264` ×1 · `11268` ×1 · `11269` ×1 · `9390` ×1 · `11286` ×1 · `9496` ×1 · `11408` ×1 · `9899` ×1 · `11411` ×1 · `9936` ×1 · `11412` ×1 · `10146` ×1 · `11416` ×1 · `10172` ×1 · `11417` ×1 · `10209` ×1 · `10993` ×1 · `10994` ×1 · `11008` ×1 · `11074` ×1 · `11235` ×1 · `11243` ×1
- **operacion** (22 valores): `6850263802020` ×1 · `1853715802020` ×1 · `5789947802020` ×1 · `8466802030` ×1 · `7232674802020` ×1 · `132760802020` ×1 · `2490244802020` ×1 · `286103802030` ×1 · `5814070802020` ×1 · `5378311802020` ×1 · `6915211802020` ×1 · `4642756802020` ×1 · `6195082802020` ×1 · `975060802020` ×1 · `6487047802020` ×1 · `3328897802020` ×1 · `6193635802020` ×1 · `4213203802020` ×1 · `5806492802020` ×1 · `174537802020` ×1 · `361216802030` ×1 · `133573802020` ×1
- **asesor** (11 valores): `JULIETH.PINEDA` ×5 · `ALYSSON.VEGA` ×3 · `MARIA.PERDOMO` ×3 · `JAIRO.VARGAS` ×2 · `PAULA.MEDINA` ×2 · `JESSICA.JIMENEZ` ×2 · `FERNANDA.RUGELES` ×1 · `MARISOL.AREVALO` ×1 · `TATIANA.OVALLE` ×1 · `LAURA.VARGAS` ×1 · `DAVID.QUEVEDO` ×1
- **fecha** (20 valores): `2025-11-28` ×3 · `2025-12-10` ×1 · `2026-06-18` ×1 · `2024-10-24` ×1 · `2026-06-28` ×1 · `2024-11-12` ×1 · `2026-06-20` ×1 · `2025-01-27` ×1 · `2026-06-25` ×1 · `2025-01-29` ×1 · `2026-06-22` ×1 · `2025-03-07` ×1 · `2025-03-18` ×1 · `2025-03-17` ×1 · `2025-09-26` ×1 · `2025-09-29` ×1 · `2025-09-30` ×1 · `2025-10-08` ×1 · `2025-11-14` ×1 · `2025-11-20` ×1
- **hora** (8 valores): `09:00 AM` ×4 · `08:00 AM` ×4 · `02:00 PM` ×4 · `10:00 AM` ×3 · `03:00 PM` ×2 · `04:00 PM` ×2 · `11:00 AM` ×2 · `12:00 MM` ×1
- **num** (8 valores): `09` ×4 · `08` ×4 · `14` ×4 · `10` ×3 · `11` ×2 · `15` ×2 · `16` ×2 · `12` ×1
- **alerta** (18 valores): `LLAMAR ` ×4 · `POSIBLE ACUERDO ` ×2 · `HACER EPS A SUPUESTO HIJO DE LA COD ROSA` ×1 · `LLAMAR POSIBLE SENORA TT ` ×1 · `LLAMAR COBRAR` ×1 · `COBRARLE AL TT ` ×1 · `LLAMAR AFIJAR CUOTAS ` ×1 · `LLAMAR PARA ACUERDO` ×1 · `LLAMAR 8 AM` ×1 · `LLAMAR 29` ×1 · `PAGA EL 30 CONDONACION 50` ×1 · `CIOMUNICACION TT` ×1 · `4 MILL ` ×1 · `POSIBLE ACUERDO}` ×1 · `GENERAR INFORMACION ` ×1 · `LLAMAR ES URG ` ×1 · `VER CHAT Y CONFIRMAR ` ×1 · `VERC CHAT ` ×1

<details><summary>Filas de muestra (t_alertas)</summary>

| id | operacion | asesor | fecha | hora | num | alerta |
|---|---|---|---|---|---|---|
| 9390 | 132760802020 | JAIRO.VARGAS | 2024-10-24 | 09:00 AM | 09 | HACER EPS A SUPUESTO HIJO DE LA COD ROSA CASTRO NUMERO: 3106 |
| 9496 | 286103802030 | JAIRO.VARGAS | 2024-11-12 | 08:00 AM | 08 | LLAMAR POSIBLE SENORA TT  |
| 9899 | 5378311802020 | LAURA.VARGAS | 2025-01-27 | 09:00 AM | 09 | LLAMAR COBRAR |
| 9936 | 4642756802020 | FERNANDA.RUGELES | 2025-01-29 | 11:00 AM | 11 | LLAMAR  |
| 10146 | 975060802020 | MARISOL.AREVALO | 2025-03-07 | 09:00 AM | 09 | COBRARLE AL TT  |

</details>

## t_asignacion  ·  54,931 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 54,931 | 0 | — | 1 | 54931 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 54,931 | 0 | — | 1000001 | 83537721802020 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 24 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **asesor** (24 valores): `CEGROUP.RENUENTESDIC2025` ×23,439 · `DANIELA.SALAMANCA` ×3,621 · `PAULA.MEDINA` ×3,604 · `MARIA.PERDOMO` ×3,581 · `SOFIA.LOPEZ` ×3,549 · `ASESORNUEVO1` ×3,536 · `JUAN.RINCON` ×3,478 · `ASESORNUEVO2` ×3,419 · `CEGROUP.RENUENTESFEB2026` ×1,269 · `CARTERA NUEVA JUNIO 2026` ×964 · `CEGROUP.RENUENTESMAY2026` ×883 · `CEGROUP.RENUENTESJUN2026` ×800 · `CEGROUP.RENUESTESABR2026` ×792 · `CEGROUP.RENUENTESMAR2026` ×494 · `CEGROUP.FALLECIDOS` ×398 · `JURIDICO` ×368 · `CEGROUP.RENUENTESENE2026` ×263 · `INMOBILIARIA` ×171 · `CEGROUP.INVESTIGACIONJUN2026` ×131 · `CEGROUP.PAZ_Y_SALVO` ×61 · `0` ×54 · `CARTERA NUEVA MAYO 2026` ×38 · `CEGROUP.INVESTIGACIONMAY2026` ×12 · `MAYRA.RUIZ` ×6

<details><summary>Filas de muestra (t_asignacion)</summary>

| id | operacion | asesor |
|---|---|---|
| 1 | 102238802020 | CEGROUP.RENUESTESABR2026 |
| 2 | 109298802020 | CEGROUP.RENUENTESMAY2026 |
| 3 | 196590802020 | DANIELA.SALAMANCA |
| 4 | 468835802020 | MARIA.PERDOMO |
| 5 | 594928802020 | JUAN.RINCON |

</details>

## t_base  ·  54,931 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `operacion` | bigint(30) | no | ∅ | PRI | 54,931 | 0 | — | 1000001 | 83537721802020 |
| 2 | `cuenta` | int(30) | sí | NULL | — | 51,531 | 0 | — | 218101 | 150033953 |
| 3 | `tcedula` | int(30) | sí | NULL | — | 51,178 | 0 | — | 0 | 2000012941 |
| 4 | `tnombre` | varchar(60) | sí | NULL | — | 51,349 | 0 | 0 | — | — |
| 5 | `ttel1` | varchar(40) | sí | NULL | — | 50,259 | 0 | 0 | — | — |
| 6 | `ttel2` | varchar(40) | sí | NULL | — | 44,597 | 0 | 0 | — | — |
| 7 | `ccedula` | int(30) | sí | NULL | — | 5,938 | 0 | — | 0 | 2147483647 |
| 8 | `cnombre` | varchar(60) | sí | NULL | — | 6,054 | 0 | 0 | — | — |
| 9 | `ctel1` | varchar(40) | sí | NULL | — | 5,768 | 0 | 0 | — | — |
| 10 | `ctel2` | varchar(40) | sí | NULL | — | 4,250 | 0 | 0 | — | — |
| 11 | `gcedula` | int(30) | sí | NULL | — | 6,631 | 0 | — | 0 | 1238341317 |
| 12 | `gnombre` | varchar(60) | sí | NULL | — | 6,821 | 0 | 0 | — | — |
| 13 | `gtel1` | varchar(40) | sí | NULL | — | 6,387 | 0 | 0 | — | — |
| 14 | `gtel2` | varchar(40) | sí | NULL | — | 4,837 | 0 | 0 | — | — |
| 15 | `fvencimiento` | date | sí | NULL | — | 2,834 | 0 | — | 0000-00-00 | 2030-05-06 |
| 16 | `fingreso` | date | sí | NULL | — | 2,891 | 0 | — | 0000-00-00 | 2026-06-17 |
| 17 | `sucursal` | varchar(40) | sí | NULL | — | 397 | 0 | 0 | — | — |
| 18 | `dependencia` | varchar(40) | sí | NULL | — | 281 | 0 | 0 | — | — |
| 19 | `condicion` | varchar(200) | sí | NULL | — | 7 | 0 | 0 | — | — |
| 20 | `banco` | varchar(30) | sí | NULL | — | 98 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **condicion** (7 valores): `ACTIVO` ×54,751 · `ARRENDAMIENTO` ×106 · `VENTA` ×61 · `0` ×7 · `SIN DATOS` ×4 · `INACTIVO` ×1 · `ARCHIVO` ×1

<details><summary>Filas de muestra (t_base)</summary>

| operacion | cuenta | tcedula | tnombre | ttel1 | ttel2 | ccedula | cnombre | ctel1 | ctel2 | gcedula | gnombre | gtel1 | gtel2 | fvencimiento | fingreso | sucursal | dependencia | condicion | banco |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1000001 | 1000001 | 1121831484 | ROBINSON ALBERTO LOPEZ | PENDIENTE | robinlopez27@gmail.com | 1121831484 | ROBINSON ALBERTO LOPEZ | 3135777500 | robinlopez27@gmail.com | 1121831484 | ARL SURA | 4055900 | contactenos@arlsura.com.co | 2024-07-16 | 2024-07-16 | VILLAVICENCIO | 0000-0000 | ARCHIVO | ARL SURA |
| 1000002 | 1000002 | 86082033 | CARLOS EDILSON GARCIA SANCHEZ | 3134411954 | carlosgarciasanchezabogado@gmail.com | 86082033 | PENDIENTE | PENDIENTE | PENDIENTE | 0 | PENDIENTE | PENDIENTE | PENDIENTE | 2024-07-16 | 2024-07-16 | NO APLICA | 0000-0000 | ACTIVO | NO APLICA |
| 1000003 | 1000003 | 9520535 | ACERO MORENO BERNARDO | 3124599739 | bernardoacerom@gmail.com | 9520535 | MARTHA ISABEL CRISTANCHO BARAJAS | 3133470374 | PENDIENTE | 46367932 | BERNARDO ACERO MORENO | 3124599739 | bernardoacerom@gmail.com | 2024-07-16 | 2024-07-16 | MONTERREY | 2022-00038 | ACTIVO | 02 PROMISCUO DEL CIRCUITO DE M |
| 1000004 | 1000004 | 24227181 | LUZ MERY BELTRAN ORDUZ | 3114577019 | luzmerybeltranorduz@gmail.com | 24227181 | LUZ MERY BELTRAN ORDUZ | 3204140103 | luzmerybeltranorduz@gmail.com | 1121821594 | JUAN CARLOS DIAZ BELTRAN | 3123111259 | PENDIENTE | 2024-07-16 | 2024-07-16 | VILLANUEVA | 2019-0752 | ACTIVO | PROMISCUO MUNICIPAL DE VILLANU |
| 1000005 | 1000005 | 97611174 | BARRERA ZAMBRANO GILBERTO HERNAN. | PENDIENTE | PENDIENTE | 97611174 | GILBERTO HERNAN BARRERA ZAMBRANO | PENDIENTE | PENDIENTE | 17336355 | JESUS MARIO PAZ RAMOS | PENDIENTE | jesusmario0967@gmail.com | 2024-07-16 | 2024-07-16 | VILLANUEVA | 2019-00070 | ACTIVO | PROMISCUO MUNICIPAL DE VILLANU |

</details>

## t_campana  ·  54,931 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 54,931 | 0 | — | 1 | 54931 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 54,931 | 0 | — | 1000001 | 83537721802020 |
| 3 | `campana` | varchar(100) | sí | NULL | — | 53 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_campana)</summary>

| id | operacion | campana |
|---|---|---|
| 1 | 102238802020 | CARTERAS ANTIGUAS (75%) |
| 2 | 109298802020 | CARTERAS ANTIGUAS (75%) |
| 3 | 196590802020 | CARTERAS ANTIGUAS (75%) |
| 4 | 468835802020 | CARTERAS ANTIGUAS (75%) |
| 5 | 594928802020 | CARTERAS ANTIGUAS (75%) |

</details>

## t_cartera  ·  54,930 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 54,930 | 0 | — | 1 | 54930 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 54,930 | 0 | — | 1000001 | 83537721802020 |
| 3 | `cartera` | varchar(30) | sí | NULL | — | 292 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_cartera)</summary>

| id | operacion | cartera |
|---|---|---|
| 1 | 102238802020 | FMM Cartera DIC 2016 |
| 2 | 109298802020 | FMM Cartera DIC 2016 |
| 3 | 196590802020 | FMM Cartera DIC 2016 |
| 4 | 468835802020 | FMM Cartera DIC 2016 |
| 5 | 594928802020 | FMM Cartera DIC 2016 |

</details>

## t_decil  ·  54,931 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 54,931 | 0 | — | 1 | 54931 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 54,931 | 0 | — | 1000001 | 83537721802020 |
| 3 | `decil` | varchar(10) | sí | NULL | — | 79 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_decil)</summary>

| id | operacion | decil |
|---|---|---|
| 1 | 102238802020 | 7 |
| 2 | 109298802020 | 6 |
| 3 | 196590802020 | 6 |
| 4 | 468835802020 | 4 |
| 5 | 594928802020 | 7 |

</details>

## t_email  ·  11,728 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 11,728 | 0 | — | 2 | 11747 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 9,762 | 0 | — | 0 | 20877431802040 |
| 3 | `email` | varchar(200) | sí | NULL | — | 10,738 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_email)</summary>

| id | operacion | email |
|---|---|---|
| 2 | 700044802020 | SALOMON_PEREZ75@GMAIL.COM |
| 3 | 700718802020 | RICARDOBIVIANA162@GMAIL.COM |
| 4 | 700976802020 | SANAFRA21091006@GMAIL.COM |
| 5 | 700990802020 | IRMADEVELA@HOTMAIL.COM |
| 6 | 735200802020 | CAROLINA_FUENTESBARRERA@HOTMAIL.COM |

</details>

## t_estados  ·  5 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 5 | 0 | — | 1 | 5 |
| 2 | `estado` | varchar(60) | sí | NULL | — | 5 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (5 valores): `1` ×1 · `2` ×1 · `3` ×1 · `4` ×1 · `5` ×1
- **estado** (5 valores): `ACUERDO` ×1 · `PAZ_Y_SALVO` ×1 · `RENUENTE` ×1 · `INVESTIGACION` ×1 · `ILOCALIZADO` ×1

<details><summary>Filas de muestra (t_estados)</summary>

| id | estado |
|---|---|
| 1 | ACUERDO |
| 2 | ILOCALIZADO |
| 3 | INVESTIGACION |
| 4 | RENUENTE |
| 5 | PAZ_Y_SALVO |

</details>

## t_gestiones  ·  1,838,261 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 1,838,261 | 0 | — | 1 | 1839396 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 124,604 | 0 | — | 0 | 83537721802020 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 188 | 1 | 382 | — | — |
| 4 | `nombre` | varchar(40) | no | ∅ | — | 190 | 0 | 38,206 | — | — |
| 5 | `fecha` | date | sí | NULL | — | 1,890 | 1 | — | 2020-01-02 | 2026-06-20 |
| 6 | `hora` | varchar(20) | sí | NULL | — | 42,247 | 1 | 0 | — | — |
| 7 | `gestion` | varchar(3000) | sí | NULL | — | n/d | — | 20 | — | — |

<details><summary>Filas de muestra (t_gestiones)</summary>

| id | operacion | asesor | nombre | fecha | hora | gestion |
|---|---|---|---|---|---|---|
| 1 | 12312311 | PRUEBA | NOMBRE | 2022-03-09 | HORA | GESTION |
| 2 | 4095184 | YURI.RIVER | YURI RIVERA | 2020-01-31 | 8:23:AM | SE LLAMA A TT AL NUMERO 3103111150 APAGADO, SE DEJA MENSAJE  |
| 3 | 4450495 | SONIA.SOLA | SONIA SOLANO | 2020-01-31 | 8:26:AM | SE LLAMA AL TT DE LA CUENTA CON NUMERO 3508471252 SE ENCUENT |
| 4 | 4272648802020 | ANGELICA.B | ANGELICA BURGOS | 2020-01-31 | 3:45:PM | SE LLAMA AL NUMERO 3184224833 VARIAS VECES NO CONTESTA |
| 5 | 4272648802020 | ANGELICA.B | ANGELICA BURGOS | 2020-01-31 | 7:24:AM | SE LLAMA AL NUMERO 3184224833 VARIAS VECES NO CONTESTA |

</details>

## t_mensaje  ·  0 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | bigint(20) | no | ∅ | PRI | n/d | — | — | — | — |
| 2 | `asesor` | varchar(30) | sí | NULL | — | n/d | — | — | — | — |
| 3 | `mensaje` | varchar(3000) | sí | NULL | — | n/d | — | — | — | — |

## t_pagos  ·  0 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | n/d | — | — | — | — |
| 2 | `operacion` | bigint(20) | sí | NULL | — | n/d | — | — | — | — |
| 3 | `asesor` | varchar(30) | sí | NULL | — | n/d | — | — | — | — |
| 4 | `fecha` | date | sí | NULL | — | n/d | — | — | — | — |
| 5 | `pago` | varchar(20) | sí | NULL | — | n/d | — | — | — | — |

## t_procesos  ·  57,590 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 57,590 | 0 | — | 2 | 58054 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 57,590 | 0 | — | 1000001 | 83537721802020 |
| 3 | `estado` | varchar(60) | sí | NULL | — | 23 | 46 | 0 | — | — |
| 4 | `sub` | varchar(60) | sí | NULL | — | 92 | 46 | 0 | — | — |
| 5 | `fgestion` | date | sí | NULL | — | 650 | 33 | — | 0000-00-00 | 2026-10-27 |
| 6 | `asesor` | varchar(30) | sí | NULL | — | 54 | 33 | 7 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **estado** (24 valores): `RENUENTE` ×29,419 · `INVESTIGACION` ×25,407 · `0` ×1,036 · `ACUERDO` ×758 · `PAZ_Y_SALVO` ×449 · `CIVIL` ×200 · `ARRENDAMIENTO` ×78 · `SIN DATO` ×76 · `VENTA` ×48 · `NULL` ×46 · `PENAL` ×16 · `CASTIGADA` ×14 · `FAMILIA` ×12 · `LABORAL` ×11 · `ADMINISTRATIVO` ×8 · `SIN DATOS` ×4 · `POLICIVO` ×1 · `SUCESION` ×1 · `DOC CARLOS` ×1 · `CIVIL-PENAL` ×1 · `NO APLICA` ×1 · `EJECUTIVO` ×1 · `PENDIENTE` ×1 · `QUERRELLA` ×1

<details><summary>Filas de muestra (t_procesos)</summary>

| id | operacion | estado | sub | fgestion | asesor |
|---|---|---|---|---|---|
| 2 | 102238802020 | RENUENTE | TIMBRA / NO CONTESTA | 2026-05-04 | MARIA.PERDOMO |
| 3 | 109298802020 | RENUENTE | TIMBRA / NO CONTESTA | 2026-05-04 | MAYRA.RUIZ |
| 4 | 118585802020 | PAZ_Y_SALVO | PAZ_Y_SALVO | 2026-03-13 | MARIA.PERDOMO |
| 5 | 196590802020 | ACUERDO | CUOTAS FIJAS | 2026-06-01 | DANIELA.SALAMANCA |
| 6 | 468835802020 | ACUERDO | CUOTAS PARCIALES | 2026-06-01 | MARIA.PERDOMO |

</details>

## t_resumen  ·  493,254 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 493,254 | 0 | — | 13 | 595447 |
| 2 | `fingreso` | date | sí | NULL | — | 4,007 | 0 | — | 0000-00-00 | 2045-09-00 |
| 3 | `cedula` | varchar(20) | sí | NULL | — | 92,073 | 0 | 23 | — | — |
| 4 | `nombre` | varchar(30) | sí | NULL | — | 94,890 | 0 | 24 | — | — |
| 5 | `operacion` | varchar(20) | sí | NULL | — | 90,617 | 0 | 0 | — | — |
| 6 | `tipo` | varchar(20) | sí | NULL | — | 6 | 0 | 0 | — | — |
| 7 | `canal` | varchar(20) | no | ∅ | — | 7 | 0 | 155,099 | — | — |
| 8 | `telefono` | varchar(20) | sí | NULL | — | 126,037 | 0 | 0 | — | — |
| 9 | `contacto` | varchar(5) | sí | NULL | — | 3 | 0 | 0 | — | — |
| 10 | `acuerdo` | varchar(5) | sí | NULL | — | 3 | 0 | 0 | — | — |
| 11 | `ncuotas` | varchar(5) | sí | NULL | — | 289 | 0 | 1 | — | — |
| 12 | `vcredito` | varchar(20) | sí | NULL | — | 20,384 | 0 | 0 | — | — |
| 13 | `vnegociado` | varchar(20) | sí | NULL | — | 10,531 | 0 | 0 | — | — |
| 14 | `condonado` | varchar(20) | sí | NULL | — | 4,894 | 0 | 0 | — | — |
| 15 | `asesor` | varchar(30) | sí | NULL | — | 119 | 0 | 522 | — | — |
| 16 | `fregistro` | date | sí | NULL | — | 833 | 0 | — | 0000-00-00 | 2026-06-03 |

**Distribución de valores (columnas de baja cardinalidad):**

- **tipo** (6 valores): `Titular` ×471,881 · `CODEUDOR_1` ×10,515 · `Codeudor` ×6,495 · `CODEUDOR_2` ×4,313 · `TERCERO` ×49 · `tipo` ×1
- **canal** (7 valores): `TELEFONO CELULAR` ×336,633 · `(vacío)` ×155,099 · `TELEFONO FIJO` ×905 · `CELULAR` ×453 · `WHATSAPP` ×160 · `MSN` ×3 · `CANAL` ×1
- **contacto** (3 valores): `NO` ×396,036 · `SI` ×97,217 · `conta` ×1
- **acuerdo** (3 valores): `NO` ×465,845 · `SI` ×27,408 · `acuer` ×1

<details><summary>Filas de muestra (t_resumen)</summary>

| id | fingreso | cedula | nombre | operacion | tipo | canal | telefono | contacto | acuerdo | ncuotas | vcredito | vnegociado | condonado | asesor | fregistro |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 13 | 2020-09-14 | 1005424076 | LOPEZ ARCIA CARLOS ALBERTO     | 4261981802020 | Titular |  | 3138271718 | SI | SI | 1 | 730000 | 730000 | 730000 | ASESOR30 | 2020-09-22 |
| 14 | 2020-09-14 | 32939392 | HERNANDEZ RODRIGUEZ JHOANA MAR | 3358707 | Titular |  | 3013452154  | SI | SI | 1 | 1892411 | 1350000 | 542411 | ASESOR34 | 2020-09-17 |
| 15 | 2020-09-14 | 32869227 | JARABA NISPERUZA JUDITH ZULEY  | 377210 | Titular |  | 3114159014 | NO | NO | 0 | 0 | 0 | 0 | ASESOR30 | 2020-09-17 |
| 16 | 2020-09-14 | 22437168 | SUAREZ PEREZ ALIX MARIA        | 377839 | Titular |  | 3002589457  | NO | NO | 0  | 0 | 0 | 0 | NATALIA.SANTOS | 2020-12-01 |
| 17 | 2020-09-14 | 3736456 | CABALLERO RUA JESUS GREGORIO   | 378028 | Titular |  | 3003840770  | NO | NO | 0  | 0 | 0 | 0 | NATALIA.SANTOS | 2020-12-01 |

</details>

## t_saldos  ·  54,931 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 54,931 | 0 | — | 1 | 54931 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 54,931 | 0 | — | 1000001 | 83537721802020 |
| 3 | `capital` | varchar(20) | sí | NULL | — | 53,012 | 0 | 0 | — | — |
| 4 | `total` | varchar(20) | sí | NULL | — | 54,272 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_saldos)</summary>

| id | operacion | capital | total |
|---|---|---|---|
| 1 | 102238802020 | 2681441.76 | 2799903.41 |
| 2 | 109298802020 | 48411.67 | 48411.67 |
| 3 | 196590802020 | 10850942.06 | 12704643.59 |
| 4 | 468835802020 | 2150062.27 | 2293107.17 |
| 5 | 594928802020 | 349365.17 | 349365.17 |

</details>

## t_subs  ·  37 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 37 | 0 | — | 1 | 77 |
| 2 | `estado` | varchar(60) | sí | NULL | — | 5 | 0 | 0 | — | — |
| 3 | `sub` | varchar(60) | sí | NULL | — | 37 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (37 valores): `4` ×1 · `46` ×1 · `75` ×1 · `6` ×1 · `49` ×1 · `77` ×1 · `9` ×1 · `50` ×1 · `11` ×1 · `52` ×1 · `13` ×1 · `55` ×1 · `15` ×1 · `56` ×1 · `17` ×1 · `58` ×1 · `19` ×1 · `59` ×1 · `25` ×1 · `61` ×1 · `27` ×1 · `62` ×1 · `32` ×1 · `65` ×1 · `34` ×1 · `68` ×1 · `38` ×1 · `70` ×1 · `1` ×1 · `40` ×1 · `72` ×1 · `2` ×1 · `42` ×1 · `73` ×1 · `3` ×1 · `44` ×1 · `74` ×1
- **estado** (5 valores): `INVESTIGACION` ×19 · `RENUENTE` ×8 · `ACUERDO` ×8 · `PAZ_Y_SALVO` ×1 · `ILOCALIZADO` ×1
- **sub** (37 valores): `CUOTAS PARCIALES` ×1 · `INSOLVENTE` ×1 · `INCUMPLIDO` ×1 · `SEGUNDO BARRIDO` ×1 · `DATOS EXTERNOS CAMPANA 4` ×1 · `DATOS EXTERNOS CAMPANA 10` ×1 · `DATOS EXTERNOS CAMPANA 12` ×1 · `NUMERO CONTESTA / NO RESPONDE` ×1 · `AGENDADO` ×1 · `CONDONACION CAMPANA` ×1 · `MENSAJE EXTERNO` ×1 · `PAZ_Y_SALVO` ×1 · `CONDONACION NORMAL` ×1 · `DATOS EXTERNOS CAMPANA 3` ×1 · `ILOCALIZADO` ×1 · `DATOS EXTERNOS CAMPANA 7` ×1 · `NUMERO FUERA DE SERVCIO` ×1 · `TIMBRA / NO CONTESTA` ×1 · `CUOTAS FIJAS` ×1 · `FALLECIDO` ×1 · `INTENSION DE PAGO` ×1 · `CONDONACION CAPITAL` ×1 · `DATOS EXTERNOS CAMPANA 2` ×1 · `DATOS EXTERNOS CAMPANA 6` ×1 · `DATOS EXTERNOS CAMPANA 8` ×1 · `NUMERO APAGADO` ×1 · `SIN CONTACTO` ×1 · `PAGO TOTAL` ×1 · `SIN INTENSION DE PAGO` ×1 · `RECLAMACION` ×1 · `POR TERCEROS` ×1 · `DATOS EXTERNOS CAMPANA 1` ×1 · `DATOS EXTERNOS CAMPANA 5` ×1 · `DATOS EXTERNOS CAMPANA 9` ×1 · `DATOS EXTERNOS CAMPANA 13` ×1 · `NUMERO EQUIVOCADO` ×1 · `DATOS EXTERNOS CAMPANA 14` ×1

<details><summary>Filas de muestra (t_subs)</summary>

| id | estado | sub |
|---|---|---|
| 1 | ACUERDO | PAGO TOTAL |
| 2 | ACUERDO | CUOTAS FIJAS |
| 3 | ACUERDO | CONDONACION CAMPANA |
| 4 | ACUERDO | CUOTAS PARCIALES |
| 6 | RENUENTE | SIN INTENSION DE PAGO |

</details>

## t_telefonos  ·  119,431 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 119,431 | 0 | — | 1 | 119436 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 63,602 | 0 | — | 5802030 | 83314650802020 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 83 | 0 | 0 | — | — |
| 4 | `telefono` | varchar(20) | sí | NULL | — | 86,188 | 0 | 0 | — | — |
| 5 | `detalle` | varchar(100) | sí | NULL | — | 3,709 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_telefonos)</summary>

| id | operacion | asesor | telefono | detalle |
|---|---|---|---|---|
| 1 | 49802030 | JENNYFER.PISCO | 3103878506 | POSIBLE CONTACTO |
| 2 | 49802030 | PAULA.MEDINA | 3157180039 | INVES CAMP 4 TT |
| 3 | 109802030 | JENNYFER.PISCO | 3045971218 | POSIBLE CONTACTO |
| 4 | 109802030 | PAULA.MEDINA | 3116471841 | INVES CAMP 9 TT |
| 5 | 117802030 | JENNYFER.PISCO | 3158634218 | POSIBLE CONTACTO |

</details>

## t_usuarios  ·  65 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `cedula` | bigint(20) | no | ∅ | PRI | 65 | 0 | — | 0 | 1192775410 |
| 2 | `nombre` | varchar(40) | sí | NULL | — | 65 | 0 | 0 | — | — |
| 3 | `telefono` | bigint(20) | sí | NULL | — | 61 | 0 | — | 0 | 3508434157 |
| 4 | `avatar` | varchar(6) | sí | NULL | — | 12 | 0 | 0 | — | — |
| 5 | `userpass` | varchar(100) | sí | NULL | — | n/d | — | — | — | — |
| 6 | `username` | varchar(30) | no | ∅ | — | 65 | 0 | 0 | — | — |
| 7 | `usertype` | int(1) | sí | NULL | — | 2 | 0 | — | 0 | 1 |
| 8 | `posicion` | varchar(10) | no | '0' | — | 1 | 0 | 0 | — | — |
| 9 | `estado` | varchar(10) | no | 'FALSE' | — | 2 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **avatar** (12 valores): `woman1` ×32 · `man1` ×10 · `woman8` ×5 · `woman2` ×4 · `woman7` ×4 · `woman6` ×2 · `woman5` ×2 · `woman3` ×2 · `man3` ×1 · `man7` ×1 · `man2` ×1 · `man6` ×1
- **usertype** (2 valores): `1` ×62 · `0` ×3
- **posicion** (1 valores): `0` ×65
- **estado** (2 valores): `TRUE` ×64 · `FALSE` ×1

<details><summary>Filas de muestra (t_usuarios)</summary>

| cedula | nombre | telefono | avatar | userpass | username | usertype | posicion | estado |
|---|---|---|---|---|---|---|---|---|
| 0 | GRUPO.INVESTIGACION | 0 | man1 | ***MASKED(len=32)*** | GRUPO.INVESTIGACION | 1 | 0 | TRUE |
| 2 | CEGROUP PAZYSALVO | 0 | woman1 | ***MASKED(len=32)*** | CEGROUP.PAZYSALVO | 1 | 0 | TRUE |
| 3 | CEGROUP FALLECIDOS | 0 | woman1 | ***MASKED(len=32)*** | CEGROUP.FALLECIDOS | 1 | 0 | TRUE |
| 33677272 | NAYIVER GORDILLO | 3112228244 | woman1 | ***MASKED(len=32)*** | NAYIVER.GORDILLO | 1 | 0 | TRUE |
| 39950605 | MARIA PAULINA MEDINA | 3134476288 | woman1 | ***MASKED(len=32)*** | PAULA.MEDINA | 1 | 0 | TRUE |

</details>

