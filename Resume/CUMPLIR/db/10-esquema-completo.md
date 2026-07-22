# CUMPLIR · DB — Esquema completo (DDL)

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 11.8.6-MariaDB-log · BD: `u815310395_data` · charset BD: utf8mb4 / utf8mb4_unicode_ci.
> Introspección original: 2026-06-21T03:48:02-05:00.

Definición real de las **23 tablas** (`SHOW CREATE TABLE`). Sin claves foráneas declaradas; índices secundarios = (ver `12-relaciones.md` / `13-volumetria-y-calidad.md`).

## Índice de tablas

| Tabla | Engine | Colación | Filas (COUNT) | Comentario |
|---|---|---|---:|---|
| [`l_campana`](#l_campana) | InnoDB | utf8mb3_general_ci | 7 |  |
| [`l_cartera`](#l_cartera) | InnoDB | utf8mb3_general_ci | 62 |  |
| [`reporte_acuerdos`](#reporte_acuerdos) | InnoDB | utf8mb4_unicode_ci | 10 |  |
| [`reporte_gestion`](#reporte_gestion) | InnoDB | utf8mb4_unicode_ci | 10 |  |
| [`reporte_proyeccion`](#reporte_proyeccion) | InnoDB | utf8mb4_unicode_ci | 10 |  |
| [`r_acuerdo`](#r_acuerdo) | InnoDB | utf8mb4_unicode_ci | 0 |  |
| [`t_acuerdos`](#t_acuerdos) | InnoDB | utf8mb3_general_ci | 11,322 |  |
| [`t_alertas`](#t_alertas) | InnoDB | utf8mb3_general_ci | 304 |  |
| [`t_asignacion`](#t_asignacion) | InnoDB | utf8mb3_general_ci | 58,648 |  |
| [`t_base`](#t_base) | InnoDB | utf8mb3_spanish_ci | 50,381 |  |
| [`t_campana`](#t_campana) | InnoDB | utf8mb3_general_ci | 55,154 |  |
| [`t_cartera`](#t_cartera) | InnoDB | utf8mb3_general_ci | 56,444 |  |
| [`t_decil`](#t_decil) | InnoDB | utf8mb3_general_ci | 55,154 |  |
| [`t_estados`](#t_estados) | InnoDB | utf8mb3_general_ci | 6 |  |
| [`t_gestiones`](#t_gestiones) | InnoDB | utf8mb3_general_ci | 801,051 |  |
| [`t_mensaje`](#t_mensaje) | InnoDB | utf8mb3_general_ci | 0 |  |
| [`t_pagos`](#t_pagos) | InnoDB | utf8mb3_general_ci | 0 |  |
| [`t_procesos`](#t_procesos) | InnoDB | utf8mb3_general_ci | 109,865 |  |
| [`t_resumen`](#t_resumen) | InnoDB | utf8mb3_general_ci | 488,584 |  |
| [`t_saldos`](#t_saldos) | InnoDB | utf8mb3_general_ci | 55,154 |  |
| [`t_subs`](#t_subs) | InnoDB | utf8mb3_general_ci | 9 |  |
| [`t_telefonos`](#t_telefonos) | InnoDB | utf8mb3_general_ci | 133,934 |  |
| [`t_usuarios`](#t_usuarios) | InnoDB | utf8mb3_general_ci | 13 |  |

## l_campana

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **7** · datos 16 KB · índices 0 B · AUTO_INCREMENT 8

```sql
CREATE TABLE `l_campana` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `campana` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## l_cartera

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **62** · datos 16 KB · índices 0 B · AUTO_INCREMENT 63

```sql
CREATE TABLE `l_cartera` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cartera` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## reporte_acuerdos

- Engine **InnoDB** · colación **utf8mb4_unicode_ci** · filas (COUNT) **10** · datos 16 KB · índices 0 B · AUTO_INCREMENT 11 · últ. modif. 2026-06-02 09:37:10

```sql
CREATE TABLE `reporte_acuerdos` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(30) DEFAULT NULL,
  `acu_01` varchar(6) DEFAULT NULL,
  `val_01` varchar(20) DEFAULT NULL,
  `acu_02` varchar(6) DEFAULT NULL,
  `val_02` varchar(20) DEFAULT NULL,
  `acu_03` varchar(6) DEFAULT NULL,
  `val_03` varchar(20) DEFAULT NULL,
  `acu_04` varchar(6) DEFAULT NULL,
  `val_04` varchar(20) DEFAULT NULL,
  `acu_05` varchar(6) DEFAULT NULL,
  `val_05` varchar(20) DEFAULT NULL,
  `acu_06` varchar(6) DEFAULT NULL,
  `val_06` varchar(20) DEFAULT NULL,
  `acu_07` varchar(6) DEFAULT NULL,
  `val_07` varchar(20) DEFAULT NULL,
  `acu_08` varchar(6) DEFAULT NULL,
  `val_08` varchar(20) DEFAULT NULL,
  `acu_09` varchar(6) DEFAULT NULL,
  `val_09` varchar(20) DEFAULT NULL,
  `acu_10` varchar(6) DEFAULT NULL,
  `val_10` varchar(20) DEFAULT NULL,
  `acu_11` varchar(6) DEFAULT NULL,
  `val_11` varchar(20) DEFAULT NULL,
  `acu_12` varchar(6) DEFAULT NULL,
  `val_12` varchar(20) DEFAULT NULL,
  `acu_13` varchar(6) DEFAULT NULL,
  `val_13` varchar(20) DEFAULT NULL,
  `acu_14` varchar(6) DEFAULT NULL,
  `val_14` varchar(20) DEFAULT NULL,
  `acu_15` varchar(6) DEFAULT NULL,
  `val_15` varchar(20) DEFAULT NULL,
  `acu_16` varchar(6) DEFAULT NULL,
  `val_16` varchar(20) DEFAULT NULL,
  `acu_17` varchar(6) DEFAULT NULL,
  `val_17` varchar(20) DEFAULT NULL,
  `acu_18` varchar(6) DEFAULT NULL,
  `val_18` varchar(20) DEFAULT NULL,
  `acu_19` varchar(6) DEFAULT NULL,
  `val_19` varchar(20) DEFAULT NULL,
  `acu_20` varchar(6) DEFAULT NULL,
  `val_20` varchar(20) DEFAULT NULL,
  `acu_21` varchar(6) DEFAULT NULL,
  `val_21` varchar(20) DEFAULT NULL,
  `acu_22` varchar(6) DEFAULT NULL,
  `val_22` varchar(20) DEFAULT NULL,
  `acu_23` varchar(6) DEFAULT NULL,
  `val_23` varchar(20) DEFAULT NULL,
  `acu_24` varchar(6) DEFAULT NULL,
  `val_24` varchar(20) DEFAULT NULL,
  `acu_25` varchar(6) DEFAULT NULL,
  `val_25` varchar(20) DEFAULT NULL,
  `acu_26` varchar(6) DEFAULT NULL,
  `val_26` varchar(20) DEFAULT NULL,
  `acu_27` varchar(6) DEFAULT NULL,
  `val_27` varchar(20) DEFAULT NULL,
  `acu_28` varchar(6) DEFAULT NULL,
  `val_28` varchar(20) DEFAULT NULL,
  `acu_29` varchar(6) DEFAULT NULL,
  `val_29` varchar(20) DEFAULT NULL,
  `acu_30` varchar(6) DEFAULT NULL,
  `val_30` varchar(20) DEFAULT NULL,
  `acu_31` varchar(6) DEFAULT NULL,
  `val_31` varchar(20) DEFAULT NULL,
  `acu_t` varchar(6) DEFAULT NULL,
  `val_t` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## reporte_gestion

- Engine **InnoDB** · colación **utf8mb4_unicode_ci** · filas (COUNT) **10** · datos 16 KB · índices 0 B · AUTO_INCREMENT 11 · últ. modif. 2026-06-18 19:43:23

```sql
CREATE TABLE `reporte_gestion` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(30) DEFAULT NULL,
  `ges_01` varchar(6) DEFAULT NULL,
  `ope_01` varchar(6) DEFAULT NULL,
  `ges_02` varchar(6) DEFAULT NULL,
  `ope_02` varchar(6) DEFAULT NULL,
  `ges_03` varchar(6) DEFAULT NULL,
  `ope_03` varchar(6) DEFAULT NULL,
  `ges_04` varchar(6) DEFAULT NULL,
  `ope_04` varchar(6) DEFAULT NULL,
  `ges_05` varchar(6) DEFAULT NULL,
  `ope_05` varchar(6) DEFAULT NULL,
  `ges_06` varchar(6) DEFAULT NULL,
  `ope_06` varchar(6) DEFAULT NULL,
  `ges_07` varchar(6) DEFAULT NULL,
  `ope_07` varchar(6) DEFAULT NULL,
  `ges_08` varchar(6) DEFAULT NULL,
  `ope_08` varchar(6) DEFAULT NULL,
  `ges_09` varchar(6) DEFAULT NULL,
  `ope_09` varchar(6) DEFAULT NULL,
  `ges_10` varchar(6) DEFAULT NULL,
  `ope_10` varchar(6) DEFAULT NULL,
  `ges_11` varchar(6) DEFAULT NULL,
  `ope_11` varchar(6) DEFAULT NULL,
  `ges_12` varchar(6) DEFAULT NULL,
  `ope_12` varchar(6) DEFAULT NULL,
  `ges_13` varchar(6) DEFAULT NULL,
  `ope_13` varchar(6) DEFAULT NULL,
  `ges_14` varchar(6) DEFAULT NULL,
  `ope_14` varchar(6) DEFAULT NULL,
  `ges_15` varchar(6) DEFAULT NULL,
  `ope_15` varchar(6) DEFAULT NULL,
  `ges_16` varchar(6) DEFAULT NULL,
  `ope_16` varchar(6) DEFAULT NULL,
  `ges_17` varchar(6) DEFAULT NULL,
  `ope_17` varchar(6) DEFAULT NULL,
  `ges_18` varchar(6) DEFAULT NULL,
  `ope_18` varchar(6) DEFAULT NULL,
  `ges_19` varchar(6) DEFAULT NULL,
  `ope_19` varchar(6) DEFAULT NULL,
  `ges_20` varchar(6) DEFAULT NULL,
  `ope_20` varchar(6) DEFAULT NULL,
  `ges_21` varchar(6) DEFAULT NULL,
  `ope_21` varchar(6) DEFAULT NULL,
  `ges_22` varchar(6) DEFAULT NULL,
  `ope_22` varchar(6) DEFAULT NULL,
  `ges_23` varchar(6) DEFAULT NULL,
  `ope_23` varchar(6) DEFAULT NULL,
  `ges_24` varchar(6) DEFAULT NULL,
  `ope_24` varchar(6) DEFAULT NULL,
  `ges_25` varchar(6) DEFAULT NULL,
  `ope_25` varchar(6) DEFAULT NULL,
  `ges_26` varchar(6) DEFAULT NULL,
  `ope_26` varchar(6) DEFAULT NULL,
  `ges_27` varchar(6) DEFAULT NULL,
  `ope_27` varchar(6) DEFAULT NULL,
  `ges_28` varchar(6) DEFAULT NULL,
  `ope_28` varchar(6) DEFAULT NULL,
  `ges_29` varchar(6) DEFAULT NULL,
  `ope_29` varchar(6) DEFAULT NULL,
  `ges_30` varchar(6) DEFAULT NULL,
  `ope_30` varchar(6) DEFAULT NULL,
  `ges_31` varchar(6) DEFAULT NULL,
  `ope_31` varchar(6) DEFAULT NULL,
  `ges_t` varchar(6) DEFAULT NULL,
  `ope_t` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## reporte_proyeccion

- Engine **InnoDB** · colación **utf8mb4_unicode_ci** · filas (COUNT) **10** · datos 16 KB · índices 0 B · AUTO_INCREMENT 11 · últ. modif. 2026-06-02 09:37:15

```sql
CREATE TABLE `reporte_proyeccion` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(30) DEFAULT NULL,
  `pro_01` varchar(6) DEFAULT NULL,
  `val_01` varchar(20) DEFAULT NULL,
  `pro_02` varchar(6) DEFAULT NULL,
  `val_02` varchar(20) DEFAULT NULL,
  `pro_03` varchar(6) DEFAULT NULL,
  `val_03` varchar(20) DEFAULT NULL,
  `pro_04` varchar(6) DEFAULT NULL,
  `val_04` varchar(20) DEFAULT NULL,
  `pro_05` varchar(6) DEFAULT NULL,
  `val_05` varchar(20) DEFAULT NULL,
  `pro_06` varchar(6) DEFAULT NULL,
  `val_06` varchar(20) DEFAULT NULL,
  `pro_07` varchar(6) DEFAULT NULL,
  `val_07` varchar(20) DEFAULT NULL,
  `pro_08` varchar(6) DEFAULT NULL,
  `val_08` varchar(20) DEFAULT NULL,
  `pro_09` varchar(6) DEFAULT NULL,
  `val_09` varchar(20) DEFAULT NULL,
  `pro_10` varchar(6) DEFAULT NULL,
  `val_10` varchar(20) DEFAULT NULL,
  `pro_11` varchar(6) DEFAULT NULL,
  `val_11` varchar(20) DEFAULT NULL,
  `pro_12` varchar(6) DEFAULT NULL,
  `val_12` varchar(20) DEFAULT NULL,
  `pro_13` varchar(6) DEFAULT NULL,
  `val_13` varchar(20) DEFAULT NULL,
  `pro_14` varchar(6) DEFAULT NULL,
  `val_14` varchar(20) DEFAULT NULL,
  `pro_15` varchar(6) DEFAULT NULL,
  `val_15` varchar(20) DEFAULT NULL,
  `pro_16` varchar(6) DEFAULT NULL,
  `val_16` varchar(20) DEFAULT NULL,
  `pro_17` varchar(6) DEFAULT NULL,
  `val_17` varchar(20) DEFAULT NULL,
  `pro_18` varchar(6) DEFAULT NULL,
  `val_18` varchar(20) DEFAULT NULL,
  `pro_19` varchar(6) DEFAULT NULL,
  `val_19` varchar(20) DEFAULT NULL,
  `pro_20` varchar(6) DEFAULT NULL,
  `val_20` varchar(20) DEFAULT NULL,
  `pro_21` varchar(6) DEFAULT NULL,
  `val_21` varchar(20) DEFAULT NULL,
  `pro_22` varchar(6) DEFAULT NULL,
  `val_22` varchar(20) DEFAULT NULL,
  `pro_23` varchar(6) DEFAULT NULL,
  `val_23` varchar(20) DEFAULT NULL,
  `pro_24` varchar(6) DEFAULT NULL,
  `val_24` varchar(20) DEFAULT NULL,
  `pro_25` varchar(6) DEFAULT NULL,
  `val_25` varchar(20) DEFAULT NULL,
  `pro_26` varchar(6) DEFAULT NULL,
  `val_26` varchar(20) DEFAULT NULL,
  `pro_27` varchar(6) DEFAULT NULL,
  `val_27` varchar(20) DEFAULT NULL,
  `pro_28` varchar(6) DEFAULT NULL,
  `val_28` varchar(20) DEFAULT NULL,
  `pro_29` varchar(6) DEFAULT NULL,
  `val_29` varchar(20) DEFAULT NULL,
  `pro_30` varchar(6) DEFAULT NULL,
  `val_30` varchar(20) DEFAULT NULL,
  `pro_31` varchar(6) DEFAULT NULL,
  `val_31` varchar(20) DEFAULT NULL,
  `pro_t` varchar(6) DEFAULT NULL,
  `val_t` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## r_acuerdo

- Engine **InnoDB** · colación **utf8mb4_unicode_ci** · filas (COUNT) **0** · datos 16 KB · índices 0 B · AUTO_INCREMENT 1

```sql
CREATE TABLE `r_acuerdo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(40) DEFAULT NULL,
  `ope` varchar(40) DEFAULT NULL,
  `acu` varchar(10) DEFAULT NULL,
  `valor` varchar(20) DEFAULT NULL,
  `fecha` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## t_acuerdos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **11,322** · datos 2.5 MB · índices 0 B · AUTO_INCREMENT 14,839 · últ. modif. 2026-06-20 15:08:55

```sql
CREATE TABLE `t_acuerdos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `cliente` varchar(30) DEFAULT NULL,
  `nombre` varchar(60) DEFAULT NULL,
  `facuerdo` date DEFAULT NULL,
  `fregistro` date DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `valor` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14839 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_alertas

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **304** · datos 48 KB · índices 0 B · AUTO_INCREMENT 43,120 · últ. modif. 2026-06-20 14:57:47

```sql
CREATE TABLE `t_alertas` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` varchar(20) NOT NULL,
  `num` varchar(5) NOT NULL,
  `alerta` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43120 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_asignacion

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **58,648** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 58,649 · últ. modif. 2026-06-09 06:50:28

```sql
CREATE TABLE `t_asignacion` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58649 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_base

- Engine **InnoDB** · colación **utf8mb3_spanish_ci** · filas (COUNT) **50,381** · datos 16.5 MB · índices 0 B · últ. modif. 2026-06-09 06:50:39

```sql
CREATE TABLE `t_base` (
  `operacion` bigint(40) NOT NULL,
  `cuenta` int(30) DEFAULT NULL,
  `tcedula` int(30) DEFAULT NULL,
  `tnombre` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ttel1` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ttel2` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ccedula` int(30) DEFAULT NULL,
  `cnombre` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ctel1` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ctel2` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gcedula` int(30) DEFAULT NULL,
  `gnombre` varchar(60) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gtel1` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `gtel2` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `fvencimiento` date DEFAULT NULL,
  `fingreso` date DEFAULT NULL,
  `sucursal` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `dependencia` varchar(40) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `condicion` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `banco` varchar(60) NOT NULL,
  `referencia` varchar(100) NOT NULL,
  PRIMARY KEY (`operacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci
```

## t_campana

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **55,154** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 55,155 · últ. modif. 2026-06-11 06:00:11

```sql
CREATE TABLE `t_campana` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `campana` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55155 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_cartera

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **56,444** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 56,445 · últ. modif. 2026-06-09 06:51:33

```sql
CREATE TABLE `t_cartera` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `cartera` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56445 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_decil

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **55,154** · datos 2.5 MB · índices 0 B · AUTO_INCREMENT 55,155 · últ. modif. 2026-06-11 05:59:15

```sql
CREATE TABLE `t_decil` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `decil` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55155 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_estados

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **6** · datos 16 KB · índices 0 B · AUTO_INCREMENT 8

```sql
CREATE TABLE `t_estados` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `estado` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_gestiones

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **801,051** · datos 143.7 MB · índices 0 B · AUTO_INCREMENT 801,070 · últ. modif. 2026-06-20 18:12:54

```sql
CREATE TABLE `t_gestiones` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `nombre` varchar(40) NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora` varchar(20) DEFAULT NULL,
  `gestion` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=801070 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_mensaje

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **0** · datos 16 KB · índices 0 B · AUTO_INCREMENT 12

```sql
CREATE TABLE `t_mensaje` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(30) DEFAULT NULL,
  `mensaje` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_pagos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **0** · datos 16 KB · índices 0 B · AUTO_INCREMENT 1

```sql
CREATE TABLE `t_pagos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `pago` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_procesos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **109,865** · datos 9.5 MB · índices 0 B · AUTO_INCREMENT 109,867 · últ. modif. 2026-06-20 18:12:00

```sql
CREATE TABLE `t_procesos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `estado` varchar(60) DEFAULT NULL,
  `sub` varchar(60) DEFAULT NULL,
  `fgestion` date DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=109867 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_resumen

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **488,584** · datos 73.6 MB · índices 0 B · AUTO_INCREMENT 488,585 · últ. modif. 2026-06-20 18:12:03

```sql
CREATE TABLE `t_resumen` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `fingreso` date DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(30) DEFAULT NULL,
  `operacion` varchar(40) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `canal` varchar(20) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `contacto` varchar(5) DEFAULT NULL,
  `acuerdo` varchar(5) DEFAULT NULL,
  `ncuotas` varchar(5) DEFAULT NULL,
  `vcredito` varchar(20) DEFAULT NULL,
  `vnegociado` varchar(20) DEFAULT NULL,
  `condonado` varchar(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `fregistro` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=488585 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_saldos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **55,154** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 55,155 · últ. modif. 2026-06-11 05:53:13

```sql
CREATE TABLE `t_saldos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `capital` varchar(20) DEFAULT NULL,
  `total` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55155 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_subs

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **9** · datos 16 KB · índices 0 B · AUTO_INCREMENT 33

```sql
CREATE TABLE `t_subs` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `estado` varchar(60) DEFAULT NULL,
  `sub` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_telefonos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **133,934** · datos 11.5 MB · índices 0 B · AUTO_INCREMENT 133,950 · últ. modif. 2026-06-20 18:08:27

```sql
CREATE TABLE `t_telefonos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(40) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `detalle` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133950 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_usuarios

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **13** · datos 16 KB · índices 0 B

```sql
CREATE TABLE `t_usuarios` (
  `cedula` bigint(20) NOT NULL,
  `nombre` varchar(40) DEFAULT NULL,
  `telefono` bigint(20) DEFAULT NULL,
  `avatar` varchar(6) DEFAULT NULL,
  `userpass` varchar(100) DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `usertype` int(1) DEFAULT NULL,
  `posicion` varchar(10) NOT NULL DEFAULT '0',
  `estado` varchar(10) NOT NULL DEFAULT 'FALSE',
  PRIMARY KEY (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

