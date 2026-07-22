# CEGROUP · DB — Esquema completo (DDL)

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 10.11.16-MariaDB-cll-lve · BD: `data_cegroup` · charset BD: latin1 / latin1_swedish_ci.
> Introspección original: 2026-06-21T03:51:46-05:00.

Definición real de las **20 tablas** (`SHOW CREATE TABLE`). Sin claves foráneas declaradas; índices secundarios = (ver `12-relaciones.md` / `13-volumetria-y-calidad.md`).

## Índice de tablas

| Tabla | Engine | Colación | Filas (COUNT) | Comentario |
|---|---|---|---:|---|
| [`l_campana`](#l_campana) | InnoDB | utf8mb3_general_ci | 3 |  |
| [`l_cartera`](#l_cartera) | InnoDB | utf8mb3_general_ci | 43 |  |
| [`t_acuerdos`](#t_acuerdos) | InnoDB | utf8mb3_general_ci | 10,902 |  |
| [`t_alertas`](#t_alertas) | InnoDB | utf8mb3_general_ci | 22 |  |
| [`t_asignacion`](#t_asignacion) | InnoDB | utf8mb3_general_ci | 54,931 |  |
| [`t_base`](#t_base) | InnoDB | utf8mb3_spanish_ci | 54,931 |  |
| [`t_campana`](#t_campana) | InnoDB | utf8mb3_general_ci | 54,931 |  |
| [`t_cartera`](#t_cartera) | InnoDB | utf8mb3_general_ci | 54,930 |  |
| [`t_decil`](#t_decil) | InnoDB | utf8mb3_general_ci | 54,931 |  |
| [`t_email`](#t_email) | InnoDB | latin1_swedish_ci | 11,728 |  |
| [`t_estados`](#t_estados) | InnoDB | utf8mb3_general_ci | 5 |  |
| [`t_gestiones`](#t_gestiones) | InnoDB | utf8mb3_general_ci | 1,838,261 |  |
| [`t_mensaje`](#t_mensaje) | InnoDB | utf8mb3_general_ci | 0 |  |
| [`t_pagos`](#t_pagos) | InnoDB | utf8mb3_general_ci | 0 |  |
| [`t_procesos`](#t_procesos) | InnoDB | utf8mb3_general_ci | 57,590 |  |
| [`t_resumen`](#t_resumen) | InnoDB | utf8mb3_general_ci | 493,254 |  |
| [`t_saldos`](#t_saldos) | InnoDB | latin1_swedish_ci | 54,931 |  |
| [`t_subs`](#t_subs) | InnoDB | utf8mb3_general_ci | 37 |  |
| [`t_telefonos`](#t_telefonos) | InnoDB | utf8mb3_general_ci | 119,431 |  |
| [`t_usuarios`](#t_usuarios) | InnoDB | utf8mb3_general_ci | 65 |  |

## l_campana

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **3** · datos 16 KB · índices 0 B · AUTO_INCREMENT 5,449,502

```sql
CREATE TABLE `l_campana` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `campana` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5449502 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## l_cartera

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **43** · datos 16 KB · índices 0 B · AUTO_INCREMENT 5,449,502

```sql
CREATE TABLE `l_cartera` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cartera` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5449502 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_acuerdos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **10,902** · datos 96 KB · índices 0 B · AUTO_INCREMENT 10,932

```sql
CREATE TABLE `t_acuerdos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `cliente` varchar(30) DEFAULT NULL,
  `nombre` varchar(60) DEFAULT NULL,
  `facuerdo` date DEFAULT NULL,
  `fregistro` date DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `valor` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10932 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_alertas

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **22** · datos 16 KB · índices 0 B · AUTO_INCREMENT 11,418

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
) ENGINE=InnoDB AUTO_INCREMENT=11418 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_asignacion

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **54,931** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 54,932

```sql
CREATE TABLE `t_asignacion` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54932 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_base

- Engine **InnoDB** · colación **utf8mb3_spanish_ci** · filas (COUNT) **54,931** · datos 15.5 MB · índices 0 B

```sql
CREATE TABLE `t_base` (
  `operacion` bigint(30) NOT NULL,
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
  `condicion` varchar(200) DEFAULT NULL,
  `banco` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  PRIMARY KEY (`operacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci
```

## t_campana

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **54,931** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 54,932

```sql
CREATE TABLE `t_campana` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `campana` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54932 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_cartera

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **54,930** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 54,931

```sql
CREATE TABLE `t_cartera` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `cartera` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54931 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_decil

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **54,931** · datos 2.5 MB · índices 0 B · AUTO_INCREMENT 54,932

```sql
CREATE TABLE `t_decil` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `decil` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54932 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_email

- Engine **InnoDB** · colación **latin1_swedish_ci** · filas (COUNT) **11,728** · datos 256 KB · índices 0 B · AUTO_INCREMENT 11,748

```sql
CREATE TABLE `t_email` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11748 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
```

## t_estados

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **5** · datos 16 KB · índices 0 B · AUTO_INCREMENT 6

```sql
CREATE TABLE `t_estados` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `estado` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_gestiones

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **1,838,261** · datos 188.7 MB · índices 0 B · AUTO_INCREMENT 1,839,397

```sql
CREATE TABLE `t_gestiones` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `nombre` varchar(40) NOT NULL,
  `fecha` date DEFAULT NULL,
  `hora` varchar(20) DEFAULT NULL,
  `gestion` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1839397 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_mensaje

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **0** · datos 16 KB · índices 0 B · AUTO_INCREMENT 1

```sql
CREATE TABLE `t_mensaje` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `asesor` varchar(30) DEFAULT NULL,
  `mensaje` varchar(3000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
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

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **57,590** · datos 4.5 MB · índices 0 B · AUTO_INCREMENT 58,055

```sql
CREATE TABLE `t_procesos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `estado` varchar(60) DEFAULT NULL,
  `sub` varchar(60) DEFAULT NULL,
  `fgestion` date DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58055 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_resumen

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **493,254** · datos 23.5 MB · índices 0 B · AUTO_INCREMENT 595,448

```sql
CREATE TABLE `t_resumen` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `fingreso` date DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `nombre` varchar(30) DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=595448 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_saldos

- Engine **InnoDB** · colación **latin1_swedish_ci** · filas (COUNT) **54,931** · datos 3.5 MB · índices 0 B · AUTO_INCREMENT 54,932

```sql
CREATE TABLE `t_saldos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `capital` varchar(20) DEFAULT NULL,
  `total` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54932 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci
```

## t_subs

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **37** · datos 16 KB · índices 0 B · AUTO_INCREMENT 79

```sql
CREATE TABLE `t_subs` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `estado` varchar(60) DEFAULT NULL,
  `sub` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_telefonos

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **119,431** · datos 10.5 MB · índices 0 B · AUTO_INCREMENT 119,437

```sql
CREATE TABLE `t_telefonos` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `operacion` bigint(20) DEFAULT NULL,
  `asesor` varchar(30) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `detalle` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=119437 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
```

## t_usuarios

- Engine **InnoDB** · colación **utf8mb3_general_ci** · filas (COUNT) **65** · datos 16 KB · índices 0 B

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

