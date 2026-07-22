# CUMPLIR · DB — Diccionario de datos (por columna)

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 11.8.6-MariaDB-log · BD: `u815310395_data` · charset BD: utf8mb4 / utf8mb4_unicode_ci.
> Introspección original: 2026-06-21T03:48:02-05:00.

Para cada tabla: cada columna con tipo real, nullability, default, clave, **nº de valores distintos**, nulos/vacíos, min/max (numéricos/fechas) y, para columnas de baja cardinalidad (≤50 distintos), **la distribución completa de valores con su frecuencia**. Más abajo, filas de muestra reales.

> Las contraseñas se enmascaran. Donde un cómputo no se pudo completar por tamaño (p. ej. `COUNT(DISTINCT)` sobre texto libre de tablas de millones de filas), aparece `n/d`.

## l_campana  ·  7 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(10) | no | ∅ | PRI | 7 | 0 | — | 1 | 7 |
| 2 | `campana` | varchar(30) | sí | NULL | — | 7 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (7 valores): `1` ×1 · `2` ×1 · `3` ×1 · `4` ×1 · `5` ×1 · `6` ×1 · `7` ×1
- **campana** (7 valores): `CAMPANA 09 (60%)` ×1 · `NUEVAS CARTERAS (20%)` ×1 · `CAMPANA 10 (60%)` ×1 · `CAMPANA 12 (40%)` ×1 · `SIN CAMPANA` ×1 · `NUEVAS CARTERAS (40%)` ×1 · `CAMPANA 11 (20%)` ×1

<details><summary>Filas de muestra (l_campana)</summary>

| id | campana |
|---|---|
| 1 | CAMPANA 09 (60%) |
| 2 | CAMPANA 12 (40%) |
| 3 | CAMPANA 11 (20%) |
| 4 | CAMPANA 10 (60%) |
| 5 | NUEVAS CARTERAS (40%) |

</details>

## l_cartera  ·  62 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(10) | no | ∅ | PRI | 62 | 0 | — | 1 | 62 |
| 2 | `cartera` | varchar(30) | sí | NULL | — | 62 | 0 | 0 | — | — |

<details><summary>Filas de muestra (l_cartera)</summary>

| id | cartera |
|---|---|
| 1 | FMM Ene 2017 |
| 2 | 2 Cartera Mar 2016 |
| 3 | 3 Cartera Jun 2016 |
| 4 | 6 Cartera Mar 2017 |
| 5 | 1 Cartera Dic 2015 |

</details>

## reporte_acuerdos  ·  10 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(2) | no | ∅ | PRI | 10 | 0 | — | 1 | 10 |
| 2 | `asesor` | varchar(30) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 3 | `acu_01` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 4 | `val_01` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 5 | `acu_02` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 6 | `val_02` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 7 | `acu_03` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 8 | `val_03` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 9 | `acu_04` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 10 | `val_04` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 11 | `acu_05` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 12 | `val_05` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 13 | `acu_06` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 14 | `val_06` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 15 | `acu_07` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 16 | `val_07` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 17 | `acu_08` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 18 | `val_08` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 19 | `acu_09` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 20 | `val_09` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 21 | `acu_10` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 22 | `val_10` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 23 | `acu_11` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 24 | `val_11` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 25 | `acu_12` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 26 | `val_12` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 27 | `acu_13` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 28 | `val_13` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 29 | `acu_14` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 30 | `val_14` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 31 | `acu_15` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 32 | `val_15` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 33 | `acu_16` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 34 | `val_16` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 35 | `acu_17` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 36 | `val_17` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 37 | `acu_18` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 38 | `val_18` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 39 | `acu_19` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 40 | `val_19` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 41 | `acu_20` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 42 | `val_20` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 43 | `acu_21` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 44 | `val_21` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 45 | `acu_22` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 46 | `val_22` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 47 | `acu_23` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 48 | `val_23` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 49 | `acu_24` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 50 | `val_24` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 51 | `acu_25` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 52 | `val_25` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 53 | `acu_26` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 54 | `val_26` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 55 | `acu_27` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 56 | `val_27` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 57 | `acu_28` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 58 | `val_28` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 59 | `acu_29` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 60 | `val_29` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 61 | `acu_30` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 62 | `val_30` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 63 | `acu_31` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 64 | `val_31` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 65 | `acu_t` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 66 | `val_t` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (10 valores): `1` ×1 · `2` ×1 · `3` ×1 · `4` ×1 · `5` ×1 · `6` ×1 · `7` ×1 · `8` ×1 · `9` ×1 · `10` ×1
- **asesor** (10 valores): `ANDRES.ALVEAR` ×1 · `DANIEL.TROYANO` ×1 · `JOSE.PILAQUINGA` ×1 · `TRINIDAD.BAOS` ×1 · `YENI.PASTRANA` ×1 · `ANGIE.CUELLAR` ×1 · `DIANA.MARTINEZ` ×1 · `TATIANA.GAVIRIA` ×1 · `VERONICA.BOTINA` ×1 · `YESMIN.HOYOS` ×1
- **acu_01** (1 valores): `-` ×10
- **val_01** (1 valores): `-` ×10
- **acu_02** (1 valores): `-` ×10
- **val_02** (1 valores): `-` ×10
- **acu_03** (1 valores): `-` ×10
- **val_03** (1 valores): `-` ×10
- **acu_04** (1 valores): `-` ×10
- **val_04** (1 valores): `-` ×10
- **acu_05** (1 valores): `-` ×10
- **val_05** (1 valores): `-` ×10
- **acu_06** (1 valores): `-` ×10
- **val_06** (1 valores): `-` ×10
- **acu_07** (1 valores): `-` ×10
- **val_07** (1 valores): `-` ×10
- **acu_08** (1 valores): `-` ×10
- **val_08** (1 valores): `-` ×10
- **acu_09** (1 valores): `-` ×10
- **val_09** (1 valores): `-` ×10
- **acu_10** (1 valores): `-` ×10
- **val_10** (1 valores): `-` ×10
- **acu_11** (1 valores): `-` ×10
- **val_11** (1 valores): `-` ×10
- **acu_12** (1 valores): `-` ×10
- **val_12** (1 valores): `-` ×10
- **acu_13** (1 valores): `-` ×10
- **val_13** (1 valores): `-` ×10
- **acu_14** (1 valores): `-` ×10
- **val_14** (1 valores): `-` ×10
- **acu_15** (1 valores): `-` ×10
- **val_15** (1 valores): `-` ×10
- **acu_16** (1 valores): `-` ×10
- **val_16** (1 valores): `-` ×10
- **acu_17** (1 valores): `-` ×10
- **val_17** (1 valores): `-` ×10
- **acu_18** (1 valores): `-` ×10
- **val_18** (1 valores): `-` ×10
- **acu_19** (1 valores): `-` ×10
- **val_19** (1 valores): `-` ×10
- **acu_20** (1 valores): `-` ×10
- **val_20** (1 valores): `-` ×10
- **acu_21** (1 valores): `-` ×10
- **val_21** (1 valores): `-` ×10
- **acu_22** (1 valores): `-` ×10
- **val_22** (1 valores): `-` ×10
- **acu_23** (1 valores): `-` ×10
- **val_23** (1 valores): `-` ×10
- **acu_24** (1 valores): `-` ×10
- **val_24** (1 valores): `-` ×10
- **acu_25** (1 valores): `-` ×10
- **val_25** (1 valores): `-` ×10
- **acu_26** (1 valores): `-` ×10
- **val_26** (1 valores): `-` ×10
- **acu_27** (1 valores): `-` ×10
- **val_27** (1 valores): `-` ×10
- **acu_28** (1 valores): `-` ×10
- **val_28** (1 valores): `-` ×10
- **acu_29** (1 valores): `-` ×10
- **val_29** (1 valores): `-` ×10
- **acu_30** (1 valores): `-` ×10
- **val_30** (1 valores): `-` ×10
- **acu_31** (1 valores): `-` ×10
- **val_31** (1 valores): `-` ×10
- **acu_t** (1 valores): `-` ×10
- **val_t** (1 valores): `-` ×10

<details><summary>Filas de muestra (reporte_acuerdos)</summary>

| id | asesor | acu_01 | val_01 | acu_02 | val_02 | acu_03 | val_03 | acu_04 | val_04 | acu_05 | val_05 | acu_06 | val_06 | acu_07 | val_07 | acu_08 | val_08 | acu_09 | val_09 | acu_10 | val_10 | acu_11 | val_11 | acu_12 | val_12 | acu_13 | val_13 | acu_14 | val_14 | acu_15 | val_15 | acu_16 | val_16 | acu_17 | val_17 | acu_18 | val_18 | acu_19 | val_19 | acu_20 | val_20 | acu_21 | val_21 | acu_22 | val_22 | acu_23 | val_23 | acu_24 | val_24 | acu_25 | val_25 | acu_26 | val_26 | acu_27 | val_27 | acu_28 | val_28 | acu_29 | val_29 | acu_30 | val_30 | acu_31 | val_31 | acu_t | val_t |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | ANDRES.ALVEAR | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 2 | ANGIE.CUELLAR | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 3 | DANIEL.TROYANO | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 4 | DIANA.MARTINEZ | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 5 | JOSE.PILAQUINGA | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |

</details>

## reporte_gestion  ·  10 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(2) | no | ∅ | PRI | 10 | 0 | — | 1 | 10 |
| 2 | `asesor` | varchar(30) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 3 | `ges_01` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 4 | `ope_01` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 5 | `ges_02` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 6 | `ope_02` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 7 | `ges_03` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 8 | `ope_03` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 9 | `ges_04` | varchar(6) | sí | NULL | — | 8 | 0 | 0 | — | — |
| 10 | `ope_04` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 11 | `ges_05` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 12 | `ope_05` | varchar(6) | sí | NULL | — | 8 | 0 | 0 | — | — |
| 13 | `ges_06` | varchar(6) | sí | NULL | — | 8 | 0 | 0 | — | — |
| 14 | `ope_06` | varchar(6) | sí | NULL | — | 7 | 0 | 0 | — | — |
| 15 | `ges_07` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 16 | `ope_07` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 17 | `ges_08` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 18 | `ope_08` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 19 | `ges_09` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 20 | `ope_09` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 21 | `ges_10` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 22 | `ope_10` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 23 | `ges_11` | varchar(6) | sí | NULL | — | 7 | 0 | 0 | — | — |
| 24 | `ope_11` | varchar(6) | sí | NULL | — | 8 | 0 | 0 | — | — |
| 25 | `ges_12` | varchar(6) | sí | NULL | — | 7 | 0 | 0 | — | — |
| 26 | `ope_12` | varchar(6) | sí | NULL | — | 8 | 0 | 0 | — | — |
| 27 | `ges_13` | varchar(6) | sí | NULL | — | 5 | 0 | 0 | — | — |
| 28 | `ope_13` | varchar(6) | sí | NULL | — | 5 | 0 | 0 | — | — |
| 29 | `ges_14` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 30 | `ope_14` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 31 | `ges_15` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 32 | `ope_15` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 33 | `ges_16` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 34 | `ope_16` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 35 | `ges_17` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 36 | `ope_17` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 37 | `ges_18` | varchar(6) | sí | NULL | — | 8 | 0 | 0 | — | — |
| 38 | `ope_18` | varchar(6) | sí | NULL | — | 9 | 0 | 0 | — | — |
| 39 | `ges_19` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 40 | `ope_19` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 41 | `ges_20` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 42 | `ope_20` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 43 | `ges_21` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 44 | `ope_21` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 45 | `ges_22` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 46 | `ope_22` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 47 | `ges_23` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 48 | `ope_23` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 49 | `ges_24` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 50 | `ope_24` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 51 | `ges_25` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 52 | `ope_25` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 53 | `ges_26` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 54 | `ope_26` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 55 | `ges_27` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 56 | `ope_27` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 57 | `ges_28` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 58 | `ope_28` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 59 | `ges_29` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 60 | `ope_29` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 61 | `ges_30` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 62 | `ope_30` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 63 | `ges_31` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 64 | `ope_31` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 65 | `ges_t` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 66 | `ope_t` | varchar(6) | sí | NULL | — | 10 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (10 valores): `1` ×1 · `2` ×1 · `3` ×1 · `4` ×1 · `5` ×1 · `6` ×1 · `7` ×1 · `8` ×1 · `9` ×1 · `10` ×1
- **asesor** (10 valores): `ANDRES.ALVEAR` ×1 · `DANIEL.TROYANO` ×1 · `JOSE.PILAQUINGA` ×1 · `TRINIDAD.BAOS` ×1 · `YENI.PASTRANA` ×1 · `ANGIE.CUELLAR` ×1 · `DIANA.MARTINEZ` ×1 · `TATIANA.GAVIRIA` ×1 · `VERONICA.BOTINA` ×1 · `YESMIN.HOYOS` ×1
- **ges_01** (9 valores): `0` ×2 · `11` ×1 · `34` ×1 · `44` ×1 · `6` ×1 · `18` ×1 · `124` ×1 · `12` ×1 · `22` ×1
- **ope_01** (9 valores): `0` ×2 · `8` ×1 · `27` ×1 · `44` ×1 · `6` ×1 · `18` ×1 · `69` ×1 · `9` ×1 · `11` ×1
- **ges_02** (10 valores): `46` ×1 · `8` ×1 · `41` ×1 · `44` ×1 · `0` ×1 · `30` ×1 · `20` ×1 · `15` ×1 · `26` ×1 · `61` ×1
- **ope_02** (10 valores): `38` ×1 · `8` ×1 · `40` ×1 · `44` ×1 · `0` ×1 · `29` ×1 · `20` ×1 · `11` ×1 · `12` ×1 · `46` ×1
- **ges_03** (9 valores): `0` ×2 · `12` ×1 · `40` ×1 · `43` ×1 · `34` ×1 · `42` ×1 · `123` ×1 · `2` ×1 · `48` ×1
- **ope_03** (9 valores): `0` ×2 · `12` ×1 · `39` ×1 · `43` ×1 · `33` ×1 · `42` ×1 · `44` ×1 · `1` ×1 · `35` ×1
- **ges_04** (8 valores): `41` ×2 · `0` ×2 · `49` ×1 · `11` ×1 · `33` ×1 · `46` ×1 · `31` ×1 · `2` ×1
- **ope_04** (9 valores): `0` ×2 · `10` ×1 · `49` ×1 · `41` ×1 · `33` ×1 · `45` ×1 · `19` ×1 · `1` ×1 · `31` ×1
- **ges_05** (9 valores): `0` ×2 · `7` ×1 · `25` ×1 · `50` ×1 · `46` ×1 · `48` ×1 · `27` ×1 · `3` ×1 · `19` ×1
- **ope_05** (8 valores): `46` ×2 · `0` ×2 · `6` ×1 · `24` ×1 · `40` ×1 · `27` ×1 · `2` ×1 · `15` ×1
- **ges_06** (8 valores): `0` ×2 · `14` ×2 · `18` ×1 · `10` ×1 · `56` ×1 · `3` ×1 · `30` ×1 · `21` ×1
- **ope_06** (7 valores): `14` ×3 · `0` ×2 · `10` ×1 · `37` ×1 · `3` ×1 · `16` ×1 · `13` ×1
- **ges_07** (1 valores): `0` ×10
- **ope_07** (1 valores): `0` ×10
- **ges_08** (1 valores): `0` ×10
- **ope_08** (1 valores): `0` ×10
- **ges_09** (10 valores): `25` ×1 · `20` ×1 · `34` ×1 · `48` ×1 · `0` ×1 · `61` ×1 · `23` ×1 · `70` ×1 · `18` ×1 · `122` ×1
- **ope_09** (9 valores): `20` ×2 · `28` ×1 · `0` ×1 · `48` ×1 · `23` ×1 · `59` ×1 · `36` ×1 · `14` ×1 · `69` ×1
- **ges_10** (10 valores): `29` ×1 · `16` ×1 · `27` ×1 · `44` ×1 · `0` ×1 · `23` ×1 · `35` ×1 · `87` ×1 · `4` ×1 · `51` ×1
- **ope_10** (9 valores): `34` ×2 · `29` ×1 · `14` ×1 · `21` ×1 · `44` ×1 · `0` ×1 · `23` ×1 · `38` ×1 · `4` ×1
- **ges_11** (7 valores): `0` ×3 · `40` ×2 · `24` ×1 · `65` ×1 · `9` ×1 · `18` ×1 · `3` ×1
- **ope_11** (8 valores): `0` ×3 · `35` ×1 · `23` ×1 · `40` ×1 · `24` ×1 · `8` ×1 · `10` ×1 · `3` ×1
- **ges_12** (7 valores): `2` ×2 · `0` ×2 · `31` ×2 · `19` ×1 · `5` ×1 · `29` ×1 · `35` ×1
- **ope_12** (8 valores): `2` ×2 · `0` ×2 · `30` ×1 · `18` ×1 · `31` ×1 · `5` ×1 · `13` ×1 · `27` ×1
- **ges_13** (5 valores): `0` ×6 · `72` ×1 · `19` ×1 · `8` ×1 · `21` ×1
- **ope_13** (5 valores): `0` ×6 · `36` ×1 · `10` ×1 · `8` ×1 · `16` ×1
- **ges_14** (1 valores): `0` ×10
- **ope_14** (1 valores): `0` ×10
- **ges_15** (1 valores): `0` ×10
- **ope_15** (1 valores): `0` ×10
- **ges_16** (9 valores): `42` ×2 · `21` ×1 · `62` ×1 · `43` ×1 · `0` ×1 · `82` ×1 · `74` ×1 · `14` ×1 · `70` ×1
- **ope_16** (10 valores): `17` ×1 · `60` ×1 · `32` ×1 · `43` ×1 · `0` ×1 · `82` ×1 · `41` ×1 · `42` ×1 · `8` ×1 · `45` ×1
- **ges_17** (9 valores): `0` ×2 · `41` ×1 · `32` ×1 · `46` ×1 · `58` ×1 · `60` ×1 · `51` ×1 · `10` ×1 · `128` ×1
- **ope_17** (9 valores): `0` ×2 · `39` ×1 · `29` ×1 · `46` ×1 · `57` ×1 · `55` ×1 · `27` ×1 · `6` ×1 · `77` ×1
- **ges_18** (8 valores): `13` ×2 · `0` ×2 · `12` ×1 · `1` ×1 · `25` ×1 · `30` ×1 · `41` ×1 · `63` ×1
- **ope_18** (9 valores): `0` ×2 · `10` ×1 · `1` ×1 · `25` ×1 · `30` ×1 · `12` ×1 · `41` ×1 · `35` ×1 · `11` ×1
- **ges_19** (1 valores): `-` ×10
- **ope_19** (1 valores): `-` ×10
- **ges_20** (1 valores): `-` ×10
- **ope_20** (1 valores): `-` ×10
- **ges_21** (1 valores): `-` ×10
- **ope_21** (1 valores): `-` ×10
- **ges_22** (1 valores): `-` ×10
- **ope_22** (1 valores): `-` ×10
- **ges_23** (1 valores): `-` ×10
- **ope_23** (1 valores): `-` ×10
- **ges_24** (1 valores): `-` ×10
- **ope_24** (1 valores): `-` ×10
- **ges_25** (1 valores): `-` ×10
- **ope_25** (1 valores): `-` ×10
- **ges_26** (1 valores): `-` ×10
- **ope_26** (1 valores): `-` ×10
- **ges_27** (1 valores): `-` ×10
- **ope_27** (1 valores): `-` ×10
- **ges_28** (1 valores): `-` ×10
- **ope_28** (1 valores): `-` ×10
- **ges_29** (1 valores): `-` ×10
- **ope_29** (1 valores): `-` ×10
- **ges_30** (1 valores): `-` ×10
- **ope_30** (1 valores): `-` ×10
- **ges_31** (1 valores): `-` ×10
- **ope_31** (1 valores): `-` ×10
- **ges_t** (10 valores): `234` ×1 · `175` ×1 · `484` ×1 · `483` ×1 · `0` ×1 · `517` ×1 · `365` ×1 · `783` ×1 · `145` ×1 · `649` ×1
- **ope_t** (10 valores): `204` ×1 · `150` ×1 · `317` ×1 · `482` ×1 · `0` ×1 · `456` ×1 · `328` ×1 · `352` ×1 · `82` ×1 · `338` ×1

<details><summary>Filas de muestra (reporte_gestion)</summary>

| id | asesor | ges_01 | ope_01 | ges_02 | ope_02 | ges_03 | ope_03 | ges_04 | ope_04 | ges_05 | ope_05 | ges_06 | ope_06 | ges_07 | ope_07 | ges_08 | ope_08 | ges_09 | ope_09 | ges_10 | ope_10 | ges_11 | ope_11 | ges_12 | ope_12 | ges_13 | ope_13 | ges_14 | ope_14 | ges_15 | ope_15 | ges_16 | ope_16 | ges_17 | ope_17 | ges_18 | ope_18 | ges_19 | ope_19 | ges_20 | ope_20 | ges_21 | ope_21 | ges_22 | ope_22 | ges_23 | ope_23 | ges_24 | ope_24 | ges_25 | ope_25 | ges_26 | ope_26 | ges_27 | ope_27 | ges_28 | ope_28 | ges_29 | ope_29 | ges_30 | ope_30 | ges_31 | ope_31 | ges_t | ope_t |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | ANDRES.ALVEAR | 0 | 0 | 8 | 8 | 12 | 12 | 11 | 10 | 25 | 24 | 18 | 14 | 0 | 0 | 0 | 0 | 20 | 20 | 16 | 14 | 0 | 0 | 2 | 2 | 0 | 0 | 0 | 0 | 0 | 0 | 62 | 60 | 0 | 0 | 1 | 1 | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | 175 | 150 |
| 2 | ANGIE.CUELLAR | 44 | 44 | 44 | 44 | 43 | 43 | 41 | 41 | 46 | 46 | 14 | 14 | 0 | 0 | 0 | 0 | 48 | 48 | 44 | 44 | 40 | 40 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 43 | 43 | 46 | 46 | 30 | 30 | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | 483 | 482 |
| 3 | DANIEL.TROYANO | 18 | 18 | 30 | 29 | 42 | 42 | 46 | 45 | 27 | 27 | 56 | 37 | 0 | 0 | 0 | 0 | 61 | 59 | 23 | 23 | 0 | 0 | 31 | 31 | 0 | 0 | 0 | 0 | 0 | 0 | 82 | 82 | 60 | 55 | 41 | 41 | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | 517 | 456 |
| 4 | DIANA.MARTINEZ | 124 | 69 | 15 | 11 | 123 | 44 | 31 | 19 | 0 | 0 | 3 | 3 | 0 | 0 | 0 | 0 | 70 | 36 | 87 | 38 | 65 | 24 | 5 | 5 | 72 | 36 | 0 | 0 | 0 | 0 | 74 | 42 | 51 | 27 | 63 | 35 | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | 783 | 352 |
| 5 | JOSE.PILAQUINGA | 12 | 9 | 26 | 12 | 2 | 1 | 2 | 1 | 3 | 2 | 30 | 16 | 0 | 0 | 0 | 0 | 18 | 14 | 4 | 4 | 9 | 8 | 2 | 2 | 0 | 0 | 0 | 0 | 0 | 0 | 14 | 8 | 10 | 6 | 13 | 11 | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | 145 | 82 |

</details>

## reporte_proyeccion  ·  10 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(2) | no | ∅ | PRI | 10 | 0 | — | 1 | 10 |
| 2 | `asesor` | varchar(30) | sí | NULL | — | 10 | 0 | 0 | — | — |
| 3 | `pro_01` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 4 | `val_01` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 5 | `pro_02` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 6 | `val_02` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 7 | `pro_03` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 8 | `val_03` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 9 | `pro_04` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 10 | `val_04` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 11 | `pro_05` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 12 | `val_05` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 13 | `pro_06` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 14 | `val_06` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 15 | `pro_07` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 16 | `val_07` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 17 | `pro_08` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 18 | `val_08` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 19 | `pro_09` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 20 | `val_09` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 21 | `pro_10` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 22 | `val_10` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 23 | `pro_11` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 24 | `val_11` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 25 | `pro_12` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 26 | `val_12` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 27 | `pro_13` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 28 | `val_13` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 29 | `pro_14` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 30 | `val_14` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 31 | `pro_15` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 32 | `val_15` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 33 | `pro_16` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 34 | `val_16` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 35 | `pro_17` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 36 | `val_17` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 37 | `pro_18` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 38 | `val_18` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 39 | `pro_19` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 40 | `val_19` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 41 | `pro_20` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 42 | `val_20` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 43 | `pro_21` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 44 | `val_21` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 45 | `pro_22` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 46 | `val_22` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 47 | `pro_23` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 48 | `val_23` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 49 | `pro_24` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 50 | `val_24` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 51 | `pro_25` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 52 | `val_25` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 53 | `pro_26` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 54 | `val_26` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 55 | `pro_27` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 56 | `val_27` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 57 | `pro_28` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 58 | `val_28` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 59 | `pro_29` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 60 | `val_29` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 61 | `pro_30` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 62 | `val_30` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 63 | `pro_31` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 64 | `val_31` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 65 | `pro_t` | varchar(6) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 66 | `val_t` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (10 valores): `1` ×1 · `2` ×1 · `3` ×1 · `4` ×1 · `5` ×1 · `6` ×1 · `7` ×1 · `8` ×1 · `9` ×1 · `10` ×1
- **asesor** (10 valores): `ANDRES.ALVEAR` ×1 · `DANIEL.TROYANO` ×1 · `JOSE.PILAQUINGA` ×1 · `TRINIDAD.BAOS` ×1 · `YENI.PASTRANA` ×1 · `ANGIE.CUELLAR` ×1 · `DIANA.MARTINEZ` ×1 · `TATIANA.GAVIRIA` ×1 · `VERONICA.BOTINA` ×1 · `YESMIN.HOYOS` ×1
- **pro_01** (1 valores): `-` ×10
- **val_01** (1 valores): `-` ×10
- **pro_02** (1 valores): `-` ×10
- **val_02** (1 valores): `-` ×10
- **pro_03** (1 valores): `-` ×10
- **val_03** (1 valores): `-` ×10
- **pro_04** (1 valores): `-` ×10
- **val_04** (1 valores): `-` ×10
- **pro_05** (1 valores): `-` ×10
- **val_05** (1 valores): `-` ×10
- **pro_06** (1 valores): `-` ×10
- **val_06** (1 valores): `-` ×10
- **pro_07** (1 valores): `-` ×10
- **val_07** (1 valores): `-` ×10
- **pro_08** (1 valores): `-` ×10
- **val_08** (1 valores): `-` ×10
- **pro_09** (1 valores): `-` ×10
- **val_09** (1 valores): `-` ×10
- **pro_10** (1 valores): `-` ×10
- **val_10** (1 valores): `-` ×10
- **pro_11** (1 valores): `-` ×10
- **val_11** (1 valores): `-` ×10
- **pro_12** (1 valores): `-` ×10
- **val_12** (1 valores): `-` ×10
- **pro_13** (1 valores): `-` ×10
- **val_13** (1 valores): `-` ×10
- **pro_14** (1 valores): `-` ×10
- **val_14** (1 valores): `-` ×10
- **pro_15** (1 valores): `-` ×10
- **val_15** (1 valores): `-` ×10
- **pro_16** (1 valores): `-` ×10
- **val_16** (1 valores): `-` ×10
- **pro_17** (1 valores): `-` ×10
- **val_17** (1 valores): `-` ×10
- **pro_18** (1 valores): `-` ×10
- **val_18** (1 valores): `-` ×10
- **pro_19** (1 valores): `-` ×10
- **val_19** (1 valores): `-` ×10
- **pro_20** (1 valores): `-` ×10
- **val_20** (1 valores): `-` ×10
- **pro_21** (1 valores): `-` ×10
- **val_21** (1 valores): `-` ×10
- **pro_22** (1 valores): `-` ×10
- **val_22** (1 valores): `-` ×10
- **pro_23** (1 valores): `-` ×10
- **val_23** (1 valores): `-` ×10
- **pro_24** (1 valores): `-` ×10
- **val_24** (1 valores): `-` ×10
- **pro_25** (1 valores): `-` ×10
- **val_25** (1 valores): `-` ×10
- **pro_26** (1 valores): `-` ×10
- **val_26** (1 valores): `-` ×10
- **pro_27** (1 valores): `-` ×10
- **val_27** (1 valores): `-` ×10
- **pro_28** (1 valores): `-` ×10
- **val_28** (1 valores): `-` ×10
- **pro_29** (1 valores): `-` ×10
- **val_29** (1 valores): `-` ×10
- **pro_30** (1 valores): `-` ×10
- **val_30** (1 valores): `-` ×10
- **pro_31** (1 valores): `-` ×10
- **val_31** (1 valores): `-` ×10
- **pro_t** (1 valores): `-` ×10
- **val_t** (1 valores): `-` ×10

<details><summary>Filas de muestra (reporte_proyeccion)</summary>

| id | asesor | pro_01 | val_01 | pro_02 | val_02 | pro_03 | val_03 | pro_04 | val_04 | pro_05 | val_05 | pro_06 | val_06 | pro_07 | val_07 | pro_08 | val_08 | pro_09 | val_09 | pro_10 | val_10 | pro_11 | val_11 | pro_12 | val_12 | pro_13 | val_13 | pro_14 | val_14 | pro_15 | val_15 | pro_16 | val_16 | pro_17 | val_17 | pro_18 | val_18 | pro_19 | val_19 | pro_20 | val_20 | pro_21 | val_21 | pro_22 | val_22 | pro_23 | val_23 | pro_24 | val_24 | pro_25 | val_25 | pro_26 | val_26 | pro_27 | val_27 | pro_28 | val_28 | pro_29 | val_29 | pro_30 | val_30 | pro_31 | val_31 | pro_t | val_t |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | ANDRES.ALVEAR | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 2 | ANGIE.CUELLAR | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 3 | DANIEL.TROYANO | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 4 | DIANA.MARTINEZ | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |
| 5 | JOSE.PILAQUINGA | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - | - |

</details>

## r_acuerdo  ·  0 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(10) | no | ∅ | PRI | n/d | — | — | — | — |
| 2 | `asesor` | varchar(40) | sí | NULL | — | n/d | — | — | — | — |
| 3 | `ope` | varchar(40) | sí | NULL | — | n/d | — | — | — | — |
| 4 | `acu` | varchar(10) | sí | NULL | — | n/d | — | — | — | — |
| 5 | `valor` | varchar(20) | sí | NULL | — | n/d | — | — | — | — |
| 6 | `fecha` | date | no | ∅ | — | n/d | — | — | — | — |

## t_acuerdos  ·  11,322 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 11,322 | 0 | — | 541 | 14838 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 6,321 | 0 | — | 0 | 82365423 |
| 3 | `cliente` | varchar(30) | sí | NULL | — | 4 | 0 | 0 | — | — |
| 4 | `nombre` | varchar(60) | sí | NULL | — | 6,312 | 0 | 0 | — | — |
| 5 | `facuerdo` | date | sí | NULL | — | 1,216 | 0 | — | 0000-00-00 | 2204-02-26 |
| 6 | `fregistro` | date | sí | NULL | — | 1,115 | 0 | — | 2022-05-01 | 2026-06-20 |
| 7 | `asesor` | varchar(30) | sí | NULL | — | 34 | 0 | 0 | — | — |
| 8 | `estado` | varchar(20) | sí | NULL | — | 1 | 0 | 0 | — | — |
| 9 | `valor` | varchar(20) | sí | NULL | — | 1,049 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **cliente** (4 valores): `TITULAR` ×10,949 · `CODEUDOR_2` ×186 · `CODEUDOR_1` ×173 · `TERCERO` ×14
- **asesor** (34 valores): `DIANA.MARTINEZ` ×2,101 · `TRINIDAD.BAOS` ×1,444 · `ANGIE.CUELLAR` ×1,271 · `YENI.PASTRANA` ×1,037 · `ALEJA.MEN` ×733 · `MARCELA.BURBANO` ×611 · `YEISON.PENCUE` ×512 · `DANIEL.TROYANO` ×477 · `ANDRES.ALVEAR` ×428 · `YESMIN.HOYOS` ×371 · `HELIANA.BUITRON` ×291 · `JOSE.PILAQUINGA` ×281 · `DANIELA.ARIAS` ×260 · `VERONICA.BOTINA` ×203 · `JESUS.CERON` ×191 · `LUISA.LASERNA` ×161 · `DIEGO.PAPAMIJA` ×156 · `LISETH.GOMEZ` ×154 · `LI.GOMEZ` ×108 · `ANGELA.GALINDEZ` ×104 · `TATIANA.GAVIRIA` ×91 · `AIDA.SARRIA` ×84 · `LUISA.RIVERA` ×66 · `CARO.TORO` ×58 · `VANESSA.ZUNIGA` ×32 · `NAYIVE.LOPEZ` ×28 · `ANGIE.HURTADO` ×22 · `UBINEY.CERON` ×13 · `UBINEY.ORDONEZ` ×11 · `LINA.MORENO` ×7 · `NULL` ×6 · `SEBASTIAN.ORTIZ` ×5 · `ISA.CRUZ` ×3 · `CUENTA.CUMPLIR` ×2
- **estado** (1 valores): `activo` ×11,322

<details><summary>Filas de muestra (t_acuerdos)</summary>

| id | operacion | cliente | nombre | facuerdo | fregistro | asesor | estado | valor |
|---|---|---|---|---|---|---|---|---|
| 541 | 4475817 | TITULAR | SIN DATO | 2022-11-26 | 2022-05-01 | LUISA.RIVERA | activo | 160000 |
| 542 | 4475817 | TITULAR | SIN DATO | 2022-12-23 | 2022-05-01 | LUISA.RIVERA | activo | 160000 |
| 2396 | 5238254 | TITULAR | PARRADO QUINTERO LUZ ELENA                                   | 2022-09-30 | 2022-06-06 | MARCELA.BURBANO | activo | 4000000 |
| 2777 | 3995285 | TITULAR | CHACON GALARZA CLAUDIA MARCELA                               | 2022-10-31 | 2022-07-18 | MARCELA.BURBANO | activo | 303000 |
| 2985 | 63156 | TITULAR | QUINONES PEDROZA FREDY                                       | 2022-09-25 | 2022-08-05 | TRINIDAD.BAOS | activo | 400000 |

</details>

## t_alertas  ·  304 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 304 | 0 | — | 6262 | 43119 |
| 2 | `operacion` | bigint(20) | sí | NULL | — | 278 | 0 | — | 70461 | 7975627 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 21 | 0 | 0 | — | — |
| 4 | `fecha` | date | sí | NULL | — | 205 | 0 | — | 2022-10-04 | 2026-10-22 |
| 5 | `hora` | varchar(20) | no | ∅ | — | 9 | 0 | 0 | — | — |
| 6 | `num` | varchar(5) | no | ∅ | — | 9 | 0 | 0 | — | — |
| 7 | `alerta` | varchar(1000) | sí | NULL | — | 161 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **asesor** (21 valores): `MARCELA.BURBANO` ×124 · `ANGELA.GALINDEZ` ×33 · `TATIANA.GAVIRIA` ×32 · `ANGIE.CUELLAR` ×24 · `ANDRES.ALVEAR` ×23 · `DANIEL.TROYANO` ×17 · `LISETH.GOMEZ` ×17 · `DANIELA.ARIAS` ×10 · `VERONICA.BOTINA` ×4 · `HELIANA.BUITRON` ×4 · `NULL` ×3 · `LUISA.LASERNA` ×3 · `VANESSA.ZUNIGA` ×2 · `YENI.PASTRANA` ×1 · `TRINIDAD.BAOS` ×1 · `UBINEY.CERON` ×1 · `DIANA.MARTINEZ` ×1 · `UBINEY.ORDONEZ` ×1 · `AIDA.SARRIA` ×1 · `ANGIE.HURTADO` ×1 · `YESMIN.HOYOS` ×1
- **hora** (9 valores): `08:00 AM` ×258 · `10:00 AM` ×18 · `09:00 AM` ×11 · `01:00 PM` ×4 · `12:00 MM` ×4 · `02:00 PM` ×4 · `03:00 PM` ×2 · `11:00 AM` ×2 · `04:00 PM` ×1
- **num** (9 valores): `08` ×258 · `10` ×18 · `09` ×11 · `13` ×4 · `14` ×4 · `12` ×4 · `11` ×2 · `15` ×2 · `16` ×1

<details><summary>Filas de muestra (t_alertas)</summary>

| id | operacion | asesor | fecha | hora | num | alerta |
|---|---|---|---|---|---|---|
| 6262 | 2193363 | ANGELA.GALINDEZ | 2022-11-08 | 08:00 AM | 08 | LLAMADA PENDIENTE  |
| 6276 | 2662601 | ANGELA.GALINDEZ | 2022-11-07 | 08:00 AM | 08 | LLAMADA PENDIENTE |
| 6359 | 3139241 | ANGELA.GALINDEZ | 2022-10-04 | 08:00 AM | 08 | LLAMADA PENDIENTE  |
| 6564 | 2602828 | ANGELA.GALINDEZ | 2022-11-10 | 08:00 AM | 08 | LLAMADA PENDIENTE  |
| 7574 | 5137417 | ANGELA.GALINDEZ | 2022-10-06 | 08:00 AM | 08 | LLAMADA PENDIENTE  |

</details>

## t_asignacion  ·  58,648 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 58,648 | 0 | — | 1 | 58648 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 47,955 | 0 | — | 0 | 84333897 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 14 | 0 | 1 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **asesor** (14 valores): `ANGIE.CUELLAR` ×7,929 · `DIANA.MARTINEZ` ×7,335 · `VERONICA.BOTINA` ×6,201 · `DANIEL.TROYANO` ×6,170 · `TRINIDAD.BAOS` ×6,134 · `YESMIN.HOYOS` ×6,084 · `JOSE.PILAQUINGA` ×5,923 · `ANDRES.ALVEAR` ×5,513 · `TATIANA.GAVIRIA` ×5,071 · `YEISON.PENCUE` ×1,803 · `UBINEY.CERON` ×473 · `ASESOR ` ×10 · `ASIGNACION` ×1 · `(vacío)` ×1

<details><summary>Filas de muestra (t_asignacion)</summary>

| id | operacion | asesor |
|---|---|---|
| 1 | 0 | ASESOR  |
| 2 | 2696924 | ANDRES.ALVEAR |
| 3 | 2696924 | ANDRES.ALVEAR |
| 4 | 2719691 | ANDRES.ALVEAR |
| 5 | 2719691 | ANDRES.ALVEAR |

</details>

## t_base  ·  50,381 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `operacion` | bigint(40) | no | ∅ | PRI | 50,381 | 0 | — | 0 | 84333897 |
| 2 | `cuenta` | int(30) | sí | NULL | — | 47,191 | 0 | — | 0 | 2147483647 |
| 3 | `tcedula` | int(30) | sí | NULL | — | 47,239 | 0 | — | 0 | 2147483647 |
| 4 | `tnombre` | varchar(60) | sí | NULL | — | 47,227 | 0 | 1 | — | — |
| 5 | `ttel1` | varchar(40) | sí | NULL | — | 45,543 | 0 | 210 | — | — |
| 6 | `ttel2` | varchar(40) | sí | NULL | — | 39,808 | 0 | 128 | — | — |
| 7 | `ccedula` | int(30) | sí | NULL | — | 5,279 | 0 | — | 0 | 2000005388 |
| 8 | `cnombre` | varchar(60) | sí | NULL | — | 5,281 | 0 | 0 | — | — |
| 9 | `ctel1` | varchar(40) | sí | NULL | — | 4,985 | 0 | 137 | — | — |
| 10 | `ctel2` | varchar(40) | sí | NULL | — | 3,904 | 0 | 585 | — | — |
| 11 | `gcedula` | int(30) | sí | NULL | — | 5,989 | 0 | — | 0 | 2000016776 |
| 12 | `gnombre` | varchar(60) | sí | NULL | — | 5,991 | 0 | 0 | — | — |
| 13 | `gtel1` | varchar(40) | sí | NULL | — | 5,685 | 0 | 238 | — | — |
| 14 | `gtel2` | varchar(40) | sí | NULL | — | 4,660 | 0 | 860 | — | — |
| 15 | `fvencimiento` | date | sí | NULL | — | 161 | 0 | — | 0000-00-00 | 2029-03-18 |
| 16 | `fingreso` | date | sí | NULL | — | 133 | 0 | — | 0000-00-00 | 2026-05-19 |
| 17 | `sucursal` | varchar(40) | sí | NULL | — | 290 | 0 | 0 | — | — |
| 18 | `dependencia` | varchar(40) | sí | NULL | — | 4 | 0 | 0 | — | — |
| 19 | `condicion` | varchar(20) | sí | NULL | — | 4 | 0 | 0 | — | — |
| 20 | `banco` | varchar(60) | no | ∅ | — | 5 | 0 | 0 | — | — |
| 21 | `referencia` | varchar(100) | no | ∅ | — | 6 | 0 | 507 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **dependencia** (4 valores): `SIN DATO` ×49,626 · `ACTIVO` ×507 · `0` ×247 · `DEPENDENCIA` ×1
- **condicion** (4 valores): `ACTIVO` ×49,796 · `802020` ×507 · `0` ×77 · `CONDICION` ×1
- **banco** (5 valores): `802020` ×49,471 · `802030` ×834 · `802040` ×71 · `802060` ×4 · `BANCO` ×1
- **referencia** (6 valores): `802020` ×48,964 · `802030` ×834 · `(vacío)` ×507 · `802040` ×71 · `802060` ×4 · `REF` ×1

<details><summary>Filas de muestra (t_base)</summary>

| operacion | cuenta | tcedula | tnombre | ttel1 | ttel2 | ccedula | cnombre | ctel1 | ctel2 | gcedula | gnombre | gtel1 | gtel2 | fvencimiento | fingreso | sucursal | dependencia | condicion | banco | referencia |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 0 | 0 | 0 | NOM | TEL1 | TEL2 | 0 | NOM | TEL1 | TEL2 | 0 | NOM | TEL1 | TEL2 | 0000-00-00 | 0000-00-00 | SUCURSAL | DEPENDENCIA | CONDICION | BANCO | REF |
| 2 | 150022945 | 800009758 | LIMITADA FERRETERIA LA PORTADA                               |  /                                       |  /                                       | 31624146 | PARRA CORTES MARIA DORA                                      |                                          |                                          | 0 | 0 | 0 | 0 | 2025-04-11 | 2025-04-11 | AVENIDA ORIENTAL               | SIN DATO | ACTIVO | 802030 | 802030 |
| 7 | 150005779 | 13640736 | MUNOZ CONTRERAS LUIS GUILLERMO                               | 3155202915/4202755/4200019/              | 4202755/4200019/                         | 16715257 | BOCANEGRA  JAMES ORTIZ                                       |                                          |                                          | 0 | 0 | 0 | 0 | 2025-04-11 | 2025-04-11 | AVENIDA ORIENTAL               | SIN DATO | ACTIVO | 802030 | 802030 |
| 11 | 150022763 | 98290993 | PEREZ  JAIRO ORDONEZ                                         | 3216463705/2647744/2647744/              | 2647744/2647744/                         | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 2025-04-11 | 2025-04-11 | AVENIDA ORIENTAL               | SIN DATO | ACTIVO | 802030 | 802030 |
| 12 | 150022971 | 900295488 | FENIX LA BODEGA MAYORISTA SAS                                | 6804026/                                 | 6804026/                                 | 52443877 | PORTELA MENDOZA DIANA CECILIA                                |                                          |                                          | 0 | 0 | 0 | 0 | 2025-04-11 | 2025-04-11 | AVENIDA ORIENTAL               | SIN DATO | ACTIVO | 802030 | 802030 |

</details>

## t_campana  ·  55,154 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 55,154 | 0 | — | 1 | 55154 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 45,328 | 0 | — | 0 | 84333897 |
| 3 | `campana` | varchar(30) | sí | NULL | — | 9 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **campana** (9 valores): `SEGMENTO 5 a 7 aNos (60%)` ×17,777 · `SIN CAMPANA` ×11,820 · `SEGMENTO  3 a 4 aNos (40%)` ×9,870 · `SEGMENTO 1 a 2 aNos (20%)` ×9,075 · `CAMPANA 15 CADUCIDAD 2026 (60%` ×5,294 · `CARTERAS ANTIGUAS (75%)` ×761 · `CAMPANA 16 CADUCIDAD 2027 (40%` ×555 · `CAMPANA` ×1 · `0` ×1

<details><summary>Filas de muestra (t_campana)</summary>

| id | operacion | campana |
|---|---|---|
| 1 | 0 | CAMPANA |
| 2 | 102499 | CARTERAS ANTIGUAS (75%) |
| 3 | 102499 | CARTERAS ANTIGUAS (75%) |
| 4 | 102499 | CARTERAS ANTIGUAS (75%) |
| 5 | 431060 | CARTERAS ANTIGUAS (75%) |

</details>

## t_cartera  ·  56,444 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 56,444 | 0 | — | 1 | 56444 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 46,249 | 0 | — | 0 | 84333897 |
| 3 | `cartera` | varchar(30) | sí | NULL | — | 104 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_cartera)</summary>

| id | operacion | cartera |
|---|---|---|
| 1 | 0 | CARTERA  |
| 2 | 102499 | FMM Cartera DIC 2016 |
| 3 | 102499 | FMM Cartera DIC 2016 |
| 4 | 102499 | FMM Cartera DIC 2016 |
| 5 | 431060 | FMM Cartera DIC 2016 |

</details>

## t_decil  ·  55,154 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 55,154 | 0 | — | 1 | 55154 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 45,328 | 0 | — | 0 | 84333897 |
| 3 | `decil` | varchar(10) | sí | NULL | — | 12 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **decil** (12 valores): `2` ×7,357 · `3` ×6,646 · `10` ×6,330 · `5` ×5,927 · `9` ×5,229 · `7` ×4,943 · `1` ×4,918 · `6` ×4,751 · `4` ×4,661 · `8` ×3,919 · `SIN DECIL` ×472 · `DECIL` ×1

<details><summary>Filas de muestra (t_decil)</summary>

| id | operacion | decil |
|---|---|---|
| 1 | 0 | DECIL |
| 2 | 102499 | 6 |
| 3 | 102499 | 6 |
| 4 | 102499 | 6 |
| 5 | 431060 | 8 |

</details>

## t_estados  ·  6 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 6 | 0 | — | 1 | 7 |
| 2 | `estado` | varchar(60) | sí | NULL | — | 6 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (6 valores): `1` ×1 · `2` ×1 · `3` ×1 · `4` ×1 · `5` ×1 · `7` ×1
- **estado** (6 valores): `ACUERDO` ×1 · `PAZ_Y_SALVO` ×1 · `RENUENTE` ×1 · `JURIDICO` ×1 · `ILOCALIZADO` ×1 · `LOCALIZADO` ×1

<details><summary>Filas de muestra (t_estados)</summary>

| id | estado |
|---|---|
| 1 | ACUERDO |
| 2 | ILOCALIZADO |
| 3 | JURIDICO |
| 4 | RENUENTE |
| 5 | PAZ_Y_SALVO |

</details>

## t_gestiones  ·  801,051 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 801,051 | 0 | — | 3 | 801069 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 96,599 | 0 | — | 0 | 84333897 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 63 | 0 | 338 | — | — |
| 4 | `nombre` | varchar(40) | no | ∅ | — | 72 | 0 | 338 | — | — |
| 5 | `fecha` | date | sí | NULL | — | 1,698 | 0 | — | 2020-09-10 | 2026-06-20 |
| 6 | `hora` | varchar(20) | sí | NULL | — | 41,422 | 0 | 0 | — | — |
| 7 | `gestion` | varchar(3000) | sí | NULL | — | 671,896 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_gestiones)</summary>

| id | operacion | asesor | nombre | fecha | hora | gestion |
|---|---|---|---|---|---|---|
| 3 | 1038175 | TRINIDAD.BAOS | MARIA TRINIDAD BAOS | 2020-10-08 | 12:23 p.?m. | 07/10/20 S.C.V |
| 4 | 1038428 | TRINIDAD.BAOS | MARIA TRINIDAD BAOS | 2020-10-08 | 12:26 p.?m. | 07/10/20 SE CONTACTA A EL CODEUDOR QUIEN EXPONE QUE EL TITUL |
| 5 | 1038483 | TRINIDAD.BAOS | MARIA TRINIDAD BAOS | 2020-10-08 | 12:29 p.?m. | 07/10/20 N.I  |
| 6 | 1038659 | TRINIDAD.BAOS | MARIA TRINIDAD BAOS | 2020-10-08 | 12:35 p.?m. | 07/10/20 S.C.V |
| 7 | 1038912 | TRINIDAD.BAOS | MARIA TRINIDAD BAOS | 2020-10-08 | 12:36 p.?m. | 07/10/20 S.C.V |

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

## t_procesos  ·  109,865 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 109,865 | 0 | — | 2 | 109866 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 97,109 | 0 | — | 0 | 84333897 |
| 3 | `estado` | varchar(60) | sí | NULL | — | 14 | 0 | 0 | — | — |
| 4 | `sub` | varchar(60) | sí | NULL | — | 39 | 0 | 0 | — | — |
| 5 | `fgestion` | date | sí | NULL | — | 1,583 | 0 | — | 0000-00-00 | 2026-06-20 |
| 6 | `asesor` | varchar(30) | sí | NULL | — | 40 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **estado** (14 valores): `ILOCALIZADO` ×64,530 · `LOCALIZADO` ×17,207 · `RENUENTE` ×8,514 · `0` ×5,591 · `PAZ_Y_SALVO` ×4,971 · `INVESTIGACION` ×4,767 · `ACUERDO` ×1,981 · `CASTIGADA` ×1,959 · `SIN DATO` ×322 · `PAZ Y SALVO` ×19 · `D` ×1 · `S` ×1 · `JURIDICO` ×1 · `X` ×1
- **sub** (39 valores): `ILOCALIZADO` ×64,525 · `LOCALIZADO` ×17,207 · `0` ×5,591 · `PAZ_Y_SALVO` ×4,971 · `PRIMER BARRIDO` ×2,690 · `RENUENTE` ×2,578 · `NO CONTESTA` ×2,518 · `SIN DATO` ×2,281 · `FALLECIDO` ×1,544 · `INSOLVENTE` ×1,459 · `ACUERDO DE PAGO` ×1,373 · `INVESTIGACION` ×846 · `INVESTIGADO` ×666 · `SEGUNDO BARRIDO` ×554 · `ACUERDO INCUMPLIDO` ×338 · `SIN INTENSION DE PAGO` ×327 · `ACUERDO` ×115 · `INCUMPLIDO` ×58 · `RECLAMACION` ×44 · `INTENSION DE PAGO` ×41 · `CONDONACION CAPITAL` ×33 · `CONDONACION CAMPANA` ×23 · `PAGO TOTAL` ×21 · `PAZ Y SALVO` ×19 · `CUOTAS PARCIALES` ×9 · `FUERA DEL PAIS` ×5 · `CONDONACION` ×5 · `DATOS EXTERNOS SUPER GIROS` ×4 · `VEHICULOS/BIENES` ×4 · `DATOS EXTERNOS EFECTY` ×3 · `CUOTAS FIJAS` ×3 · `APAGADO` ×2 · `CONDONACION NORMAL` ×2 · `POR TERCEROS` ×1 · `S` ×1 · `NUMERO EQUIVOCADO` ×1 · `X` ×1 · `JURIDICO` ×1 · `D` ×1
- **asesor** (40 valores): `TRINIDAD.BAOS` ×12,755 · `DIANA.MARTINEZ` ×10,861 · `VERONICA.BOTINA` ×9,540 · `ANGIE.CUELLAR` ×9,319 · `JOSE.PILAQUINGA` ×7,884 · `YENI.PASTRANA` ×6,318 · `YESMIN.HOYOS` ×5,490 · `MARCELA.BURBANO` ×4,891 · `TATIANA.GAVIRIA` ×4,602 · `DANIEL.TROYANO` ×4,035 · `ALEJA.MEN` ×3,863 · `ANDRES.ALVEAR` ×3,855 · `0` ×3,764 · `CARO.TORO` ×2,765 · `HELIANA.BUITRON` ×2,709 · `DANIELA.ARIAS` ×2,288 · `ISA.CRUZ` ×2,155 · `LI.GOMEZ` ×2,043 · `ANGELA.GALINDEZ` ×2,021 · `LUISA.RIVERA` ×1,970 · `YEISON.PENCUE` ×1,517 · `DIEGO.PAPAMIJA` ×1,020 · `JESUS.CERON` ×789 · `LUISA.LASERNA` ×746 · `LISETH.GOMEZ` ×712 · `AIDA.SARRIA` ×376 · `SEBASTIAN.ORTIZ` ×334 · `LINA.MORENO` ×297 · `ANGIE.HURTADO` ×292 · `UBINEY.CERON` ×277 · `VANESSA.ZUNIGA` ×232 · `NULL` ×42 · `VERONICA.GUERRA` ×38 · `NAYIVE.LOPEZ` ×27 · `JURIDICOS.CUMPLIR` ×19 · `CUENTA.CUMPLIR` ×8 · `UBINEY.ORDONEZ` ×8 · `S` ×1 · `D` ×1 · `X` ×1

<details><summary>Filas de muestra (t_procesos)</summary>

| id | operacion | estado | sub | fgestion | asesor |
|---|---|---|---|---|---|
| 2 | 1722 | RENUENTE | INSOLVENTE | 2023-02-22 | ALEJA.MEN |
| 3 | 2233 | ACUERDO | ACUERDO DE PAGO | 2022-12-12 | JOSE.PILAQUINGA |
| 4 | 3119 | RENUENTE | RECLAMACION | 2022-06-07 | DIANA.MARTINEZ |
| 5 | 3347 | PAZ_Y_SALVO | PAZ_Y_SALVO | 2022-10-20 | JOSE.PILAQUINGA |
| 6 | 3434 | ILOCALIZADO | ILOCALIZADO | 2023-01-17 | DANIELA.ARIAS |

</details>

## t_resumen  ·  488,584 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 488,584 | 0 | — | 1 | 488584 |
| 2 | `fingreso` | date | sí | NULL | — | 4,463 | 0 | — | 0000-00-00 | 2029-06-10 |
| 3 | `cedula` | varchar(20) | sí | NULL | — | 86,128 | 0 | 0 | — | — |
| 4 | `nombre` | varchar(30) | sí | NULL | — | 86,465 | 0 | 1 | — | — |
| 5 | `operacion` | varchar(40) | sí | NULL | — | 80,595 | 0 | 0 | — | — |
| 6 | `tipo` | varchar(20) | sí | NULL | — | 6 | 0 | 0 | — | — |
| 7 | `canal` | varchar(20) | no | ∅ | — | 6 | 0 | 0 | — | — |
| 8 | `telefono` | varchar(20) | sí | NULL | — | 150,650 | 0 | 0 | — | — |
| 9 | `contacto` | varchar(5) | sí | NULL | — | 3 | 0 | 0 | — | — |
| 10 | `acuerdo` | varchar(5) | sí | NULL | — | 3 | 0 | 0 | — | — |
| 11 | `ncuotas` | varchar(5) | sí | NULL | — | 107 | 0 | 0 | — | — |
| 12 | `vcredito` | varchar(20) | sí | NULL | — | 2,477 | 0 | 0 | — | — |
| 13 | `vnegociado` | varchar(20) | sí | NULL | — | 1,251 | 0 | 0 | — | — |
| 14 | `condonado` | varchar(20) | sí | NULL | — | 1,523 | 0 | 0 | — | — |
| 15 | `asesor` | varchar(30) | sí | NULL | — | 39 | 0 | 36 | — | — |
| 16 | `fregistro` | date | sí | NULL | — | 1,322 | 0 | — | 0000-00-00 | 2026-06-20 |

**Distribución de valores (columnas de baja cardinalidad):**

- **tipo** (6 valores): `Titular` ×452,469 · `CODEUDOR_1` ×16,624 · `CODEUDOR_2` ×15,686 · `Codeudor` ×3,775 · `TERCERO` ×29 · `tipo` ×1
- **canal** (6 valores): `TELEFONO CELULAR` ×393,748 · `Telefono` ×68,681 · `WHATSAPP` ×25,945 · `TELEFONO FIJO` ×121 · `MSN` ×88 · `canal` ×1
- **contacto** (3 valores): `NO` ×341,137 · `SI` ×147,446 · `conta` ×1
- **acuerdo** (3 valores): `NO` ×416,257 · `SI` ×72,326 · `acuer` ×1
- **asesor** (39 valores): `DIANA.MARTINEZ` ×74,174 · `TRINIDAD.BAOS` ×64,979 · `VERONICA.BOTINA` ×52,103 · `YENI.PASTRANA` ×46,035 · `MARCELA.BURBANO` ×41,959 · `ALEJA.MEN` ×27,554 · `DANIELA.ARIAS` ×25,874 · `ANGIE.CUELLAR` ×23,709 · `CARO.TORO` ×22,085 · `HELIANA.BUITRON` ×19,632 · `LUISA.RIVERA` ×15,837 · `LI.GOMEZ` ×11,213 · `YEISON.PENCUE` ×11,120 · `ANGELA.GALINDEZ` ×10,116 · `LUISA.LASERNA` ×7,666 · `ISA.CRUZ` ×6,464 · `DIEGO.PAPAMIJA` ×5,658 · `YESMIN.HOYOS` ×4,139 · `LISETH.GOMEZ` ×3,445 · `ANDRES.ALVEAR` ×2,744 · `AIDA.SARRIA` ×2,041 · `MARIA.SALGAR` ×1,937 · `JOSE.PILAQUINGA` ×1,591 · `DANIEL.TROYANO` ×1,436 · `SEBASTIAN.ORTIZ` ×1,078 · `TATI.BENA` ×972 · `ANGIE.HURTADO` ×557 · `LINA.MORENO` ×478 · `CUENTA.CUMPLIR` ×432 · `YURI.RUIZ` ×334 · `UBINEY.CERON` ×330 · `NAYIVE.LOPEZ` ×283 · `JURIDICOS.CUMPLIR` ×190 · `NULL` ×136 · `UBINEY.ORDONEZ` ×110 · `VERONICA.GUERRA` ×72 · `JESUS.CERON` ×64 · `(vacío)` ×36 · `agente` ×1

<details><summary>Filas de muestra (t_resumen)</summary>

| id | fingreso | cedula | nombre | operacion | tipo | canal | telefono | contacto | acuerdo | ncuotas | vcredito | vnegociado | condonado | asesor | fregistro |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | 0000-00-00 | cedula | nombre | operacion | tipo | canal | telefono | conta | acuer | ncuot | vcredito | vnegociado | condonado | agente | 0000-00-00 |
| 2 | 2020-09-14 | 72256719 | BROCHERO RIVALDO DEIBYS ENRIQU | 979505 | Codeudor | Telefono | 3003702816 | NO | NO | 0 | 0 | 0 | 0 | YURI.RUIZ | 2022-01-03 |
| 3 | 2020-09-14 | 8782318 | MONSALVO VILLARREAL ERIC MANUE | 979515 | Titular | Telefono | 3107172933 | NO | NO | 0 | 0 | 0 | 0 | YURI.RUIZ | 2022-01-03 |
| 4 | 2020-09-14 | 8497538 | PERTUZ CABALLERO OCTAVIO RAFAE | 979548 | Titular | Telefono | 3017432406 | NO | NO | 0 | 0 | 0 | 0 | YURI.RUIZ | 2022-01-03 |
| 5 | 2020-09-14 | 32721614 | MEJIA VARELA NANCY ESTHER      | 979549 | Titular | Telefono | 3015052476 | NO | NO | 0 | 0 | 0 | 0 | YURI.RUIZ | 2022-01-03 |

</details>

## t_saldos  ·  55,154 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 55,154 | 0 | — | 1 | 55154 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 45,328 | 0 | — | 0 | 84333897 |
| 3 | `capital` | varchar(20) | sí | NULL | — | 44,223 | 0 | 0 | — | — |
| 4 | `total` | varchar(20) | sí | NULL | — | 45,326 | 0 | 0 | — | — |

<details><summary>Filas de muestra (t_saldos)</summary>

| id | operacion | capital | total |
|---|---|---|---|
| 1 | 0 | SALDO CAPITAL | SALDO TOTAL |
| 2 | 102499 | 2226296,21 | 2226296,21 |
| 3 | 102499 | 2226296,21 | 2226296,21 |
| 4 | 102499 | 2226296,21 | 2226296,21 |
| 5 | 431060 | 963557,99 | 963557,99 |

</details>

## t_subs  ·  9 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 9 | 0 | — | 1 | 31 |
| 2 | `estado` | varchar(60) | sí | NULL | — | 6 | 0 | 0 | — | — |
| 3 | `sub` | varchar(60) | sí | NULL | — | 9 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **id** (9 valores): `1` ×1 · `8` ×1 · `9` ×1 · `19` ×1 · `23` ×1 · `25` ×1 · `27` ×1 · `30` ×1 · `31` ×1
- **estado** (6 valores): `RENUENTE` ×3 · `ACUERDO` ×2 · `JURIDICO` ×1 · `PAZ_Y_SALVO` ×1 · `ILOCALIZADO` ×1 · `LOCALIZADO` ×1
- **sub** (9 valores): `ACUERDO DE PAGO` ×1 · `NO CONTESTA` ×1 · `LOCALIZADO` ×1 · `FALLECIDO` ×1 · `JURIDICO` ×1 · `ILOCALIZADO` ×1 · `PAZ_Y_SALVO` ×1 · `ACUERDO INCUMPLIDO` ×1 · `INSOLVENTE` ×1

<details><summary>Filas de muestra (t_subs)</summary>

| id | estado | sub |
|---|---|---|
| 1 | ACUERDO | ACUERDO DE PAGO |
| 8 | ACUERDO | ACUERDO INCUMPLIDO |
| 9 | ILOCALIZADO | ILOCALIZADO |
| 19 | RENUENTE | FALLECIDO |
| 23 | RENUENTE | NO CONTESTA |

</details>

## t_telefonos  ·  133,934 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `id` | int(20) | no | ∅ | PRI | 133,934 | 0 | — | 3 | 133949 |
| 2 | `operacion` | bigint(40) | sí | NULL | — | 63,342 | 0 | — | 0 | 83497124 |
| 3 | `asesor` | varchar(30) | sí | NULL | — | 35 | 0 | 0 | — | — |
| 4 | `telefono` | varchar(20) | sí | NULL | — | 100,321 | 0 | 0 | — | — |
| 5 | `detalle` | varchar(100) | sí | NULL | — | 9,231 | 0 | 1 | — | — |
| 6 | `status` | varchar(20) | no | ∅ | — | 2 | 0 | 112,162 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **asesor** (35 valores): `0` ×29,339 · `TRINIDAD.BAOS` ×20,133 · `DIANA.MARTINEZ` ×12,725 · `YENI.PASTRANA` ×12,666 · `JOSE.PILAQUINGA` ×8,548 · `ALEJA.MEN` ×6,563 · `VERONICA.BOTINA` ×6,198 · `HELIANA.BUITRON` ×6,017 · `ANGIE.CUELLAR` ×5,809 · `YEISON.PENCUE` ×3,097 · `DIEGO.PAPAMIJA` ×2,362 · `MARCELA.BURBANO` ×2,334 · `DANIELA.ARIAS` ×2,279 · `ANDRES.ALVEAR` ×2,109 · `ANGELA.GALINDEZ` ×1,910 · `DANIEL.TROYANO` ×1,893 · `CARO.TORO` ×1,707 · `JESUS.CERON` ×1,550 · `TATIANA.GAVIRIA` ×1,523 · `YESMIN.HOYOS` ×1,162 · `SEBASTIAN.ORTIZ` ×1,129 · `LUISA.LASERNA` ×1,051 · `CUENTA.CUMPLIR` ×461 · `LI.GOMEZ` ×303 · `AIDA.SARRIA` ×300 · `NAYIVE.LOPEZ` ×251 · `JURIDICOS.CUMPLIR` ×184 · `ANGIE.HURTADO` ×113 · `UBINEY.CERON` ×57 · `VANESSA.ZUNIGA` ×56 · `LISETH.GOMEZ` ×43 · `NULL` ×33 · `LUISA.RIVERA` ×14 · `LINA.MORENO` ×10 · `UBINEY.ORDONEZ` ×5
- **status** (2 valores): `(vacío)` ×112,162 · `ACTIVO` ×21,772

<details><summary>Filas de muestra (t_telefonos)</summary>

| id | operacion | asesor | telefono | detalle | status |
|---|---|---|---|---|---|
| 3 | 362884 | 0 | 32446856 | ES TIA |  |
| 4 | 753441 | 0 | 3214569874 | SUPER GIROS  |  |
| 5 | 756234 | 0 | 3225438980 | SUPER GIROS |  |
| 6 | 767443 | 0 | 3046062866 | SUPER GIROS  |  |
| 7 | 767542 | 0 | 3152700968 | SUPER GIROS  |  |

</details>

## t_usuarios  ·  13 filas

| # | Columna | Tipo | Null | Default | Key | Distintos | Nulos | Vacíos | Min | Max |
|---:|---|---|:--:|---|:--:|---:|---:|---:|---|---|
| 1 | `cedula` | bigint(20) | no | ∅ | PRI | 13 | 0 | — | 0 | 1124866386 |
| 2 | `nombre` | varchar(40) | sí | NULL | — | 13 | 0 | 0 | — | — |
| 3 | `telefono` | bigint(20) | sí | NULL | — | 4 | 0 | — | 0 | 3224567890 |
| 4 | `avatar` | varchar(6) | sí | NULL | — | 6 | 0 | 0 | — | — |
| 5 | `userpass` | varchar(100) | sí | NULL | — | n/d | — | — | — | — |
| 6 | `username` | varchar(30) | no | ∅ | — | 13 | 0 | 0 | — | — |
| 7 | `usertype` | int(1) | sí | NULL | — | 2 | 0 | — | 0 | 1 |
| 8 | `posicion` | varchar(10) | no | '0' | — | 1 | 0 | 0 | — | — |
| 9 | `estado` | varchar(10) | no | 'FALSE' | — | 1 | 0 | 0 | — | — |

**Distribución de valores (columnas de baja cardinalidad):**

- **cedula** (13 valores): `1061761004` ×1 · `0` ×1 · `1084251604` ×1 · `1` ×1 · `1124866386` ×1 · `123456` ×1 · `225544` ×1 · `12121212` ×1 · `123456232` ×1 · `123456741` ×1 · `123456789` ×1 · `1004440845` ×1 · `1061754898` ×1
- **nombre** (13 valores): `JURIDICOS CUMPLIR` ×1 · `0` ×1 · `DANIEL TROYANO` ×1 · `DIANA MARTINEZ` ×1 · ` ANDRES ALVEAR` ×1 · `YENIFFER PASTRANA DAZA` ×1 · `TATIANA GAVIRIA` ×1 · `YESMIN HOYOS` ×1 · `CUENTA CUMPLIR` ×1 · `VERONICA BOTINA` ×1 · `JOSE ADOLFO PILAQUIN` ×1 · `MARIA TRINIDAD BAOS` ×1 · `ANGIE LORENA CUELLAR DELGADO` ×1
- **telefono** (4 valores): `0` ×10 · `22211133` ×1 · `3224567890` ×1 · `3216121192` ×1
- **avatar** (6 valores): `woman1` ×5 · `woman8` ×3 · `women1` ×2 · `man4` ×1 · `woman6` ×1 · `man1` ×1
- **username** (13 valores): `JURIDICOS.CUMPLIR` ×1 · `VERONICA.BOTINA` ×1 · `YENI.PASTRANA` ×1 · `CUENTA.CUMPLIR` ×1 · `ANDRES.ALVEAR` ×1 · `YESMIN.HOYOS` ×1 · `DIANA.MARTINEZ` ×1 · `ANGIE.CUELLAR` ×1 · `DANIEL.TROYANO` ×1 · `TRINIDAD.BAOS` ×1 · `UBINEY.CERON` ×1 · `JOSE.PILAQUINGA` ×1 · `TATIANA.GAVIRIA` ×1
- **usertype** (2 valores): `1` ×10 · `0` ×3
- **posicion** (1 valores): `0` ×13
- **estado** (1 valores): `TRUE` ×13

<details><summary>Filas de muestra (t_usuarios)</summary>

| cedula | nombre | telefono | avatar | userpass | username | usertype | posicion | estado |
|---|---|---|---|---|---|---|---|---|
| 0 | JURIDICOS CUMPLIR | 0 | woman1 | ***MASKED(len=32)*** | JURIDICOS.CUMPLIR | 0 | 0 | TRUE |
| 1 | YESMIN HOYOS | 0 | woman1 | ***MASKED(len=32)*** | YESMIN.HOYOS | 1 | 0 | TRUE |
| 123456 | 0 | 0 | woman1 | ***MASKED(len=32)*** | UBINEY.CERON | 0 | 0 | TRUE |
| 225544 | CUENTA CUMPLIR | 0 | women1 | ***MASKED(len=32)*** | CUENTA.CUMPLIR | 0 | 0 | TRUE |
| 12121212 | DANIEL TROYANO | 0 | man4 | ***MASKED(len=32)*** | DANIEL.TROYANO | 1 | 0 | TRUE |

</details>

