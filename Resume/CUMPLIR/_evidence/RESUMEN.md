# Evidencia de introspección — CUMPLIR

> Generado: 2026-06-21T03:49:43-05:00 · Servidor: 11.8.6-MariaDB-log · TZ servidor: SYSTEM
> Conexión read-only a `u815310395_data` en `srv450.hstgr.io:3306` como `u815310395_data`.

## Tablas (23)

| Tabla | Engine | Filas (aprox IS) | Filas (COUNT) | Datos | Índices | Colación | Última modif. |
|---|---|---:|---:|---:|---:|---|---|
| `l_campana` | InnoDB | 7 | 7 | 16,384 | 0 | utf8mb3_general_ci | — |
| `l_cartera` | InnoDB | 62 | 62 | 16,384 | 0 | utf8mb3_general_ci | — |
| `reporte_acuerdos` | InnoDB | 10 | 10 | 16,384 | 0 | utf8mb4_unicode_ci | 2026-06-02 09:37:10 |
| `reporte_gestion` | InnoDB | 10 | 10 | 16,384 | 0 | utf8mb4_unicode_ci | 2026-06-18 19:43:23 |
| `reporte_proyeccion` | InnoDB | 10 | 10 | 16,384 | 0 | utf8mb4_unicode_ci | 2026-06-02 09:37:15 |
| `r_acuerdo` | InnoDB | 0 | 0 | 16,384 | 0 | utf8mb4_unicode_ci | — |
| `t_acuerdos` | InnoDB | 11,157 | 11,322 | 2,637,824 | 0 | utf8mb3_general_ci | 2026-06-20 15:08:55 |
| `t_alertas` | InnoDB | 304 | 304 | 49,152 | 0 | utf8mb3_general_ci | 2026-06-20 14:57:47 |
| `t_asignacion` | InnoDB | 57,785 | 58,648 | 3,686,400 | 0 | utf8mb3_general_ci | 2026-06-09 06:50:28 |
| `t_base` | InnoDB | 48,533 | 50,381 | 17,350,656 | 0 | utf8mb3_spanish_ci | 2026-06-09 06:50:39 |
| `t_campana` | InnoDB | 55,114 | 55,154 | 3,686,400 | 0 | utf8mb3_general_ci | 2026-06-11 06:00:11 |
| `t_cartera` | InnoDB | 55,597 | 56,444 | 3,686,400 | 0 | utf8mb3_general_ci | 2026-06-09 06:51:33 |
| `t_decil` | InnoDB | 55,497 | 55,154 | 2,637,824 | 0 | utf8mb3_general_ci | 2026-06-11 05:59:15 |
| `t_estados` | InnoDB | 6 | 6 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_gestiones` | InnoDB | 728,520 | 801,051 | 150,634,496 | 0 | utf8mb3_general_ci | 2026-06-20 18:12:54 |
| `t_mensaje` | InnoDB | 0 | 0 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_pagos` | InnoDB | 0 | 0 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_procesos` | InnoDB | 107,717 | 109,865 | 9,977,856 | 0 | utf8mb3_general_ci | 2026-06-20 18:12:00 |
| `t_resumen` | InnoDB | 473,841 | 488,584 | 77,168,640 | 0 | utf8mb3_general_ci | 2026-06-20 18:12:03 |
| `t_saldos` | InnoDB | 55,276 | 55,154 | 3,686,400 | 0 | utf8mb3_general_ci | 2026-06-11 05:53:13 |
| `t_subs` | InnoDB | 9 | 9 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_telefonos` | InnoDB | 133,906 | 133,934 | 12,075,008 | 0 | utf8mb3_general_ci | 2026-06-20 18:08:27 |
| `t_usuarios` | InnoDB | 13 | 13 | 16,384 | 0 | utf8mb3_general_ci | — |

## Calidad de datos

**Filas basura `operacion=0`:** 
- `t_acuerdos`: 1
- `t_asignacion`: 12
- `t_base`: 1
- `t_campana`: 1
- `t_cartera`: 7
- `t_decil`: 1
- `t_gestiones`: 21
- `t_procesos`: 47
- `t_resumen`: 17
- `t_saldos`: 1
- `t_telefonos`: 10

**Passwords = md5('0'):**
- `t_usuarios.userpass`: 10 de 13

**Fechas 0000-00-00:**
- `t_acuerdos.facuerdo`: 2
- `t_base.fvencimiento`: 44
- `t_base.fingreso`: 44
- `t_procesos.fgestion`: 5838
- `t_resumen.fingreso`: 1088
- `t_resumen.fregistro`: 1

**Ratio filas/operación-distinta (esperado ~1.0):**
- `t_acuerdos`: 1.791× (11322 filas / 6321 ops)
- `t_alertas`: 1.094× (304 filas / 278 ops)
- `t_asignacion`: 1.223× (58648 filas / 47955 ops)
- `t_campana`: 1.217× (55154 filas / 45328 ops)
- `t_cartera`: 1.22× (56444 filas / 46249 ops)
- `t_decil`: 1.217× (55154 filas / 45328 ops)
- `t_gestiones`: 8.293× (801051 filas / 96599 ops)
- `t_pagos`: × (0 filas / 0 ops)
- `t_procesos`: 1.131× (109865 filas / 97109 ops)
- `t_resumen`: 6.062× (488584 filas / 80595 ops)
- `t_saldos`: 1.217× (55154 filas / 45328 ops)
- `t_telefonos`: 2.114× (133934 filas / 63342 ops)

Archivos crudos: `connection.json`, `tables.json`, `columns.json`, `indexes.json`, `column_profiles.json`, `quality.json`, `ddl/*.sql`, `samples/*.json`.
