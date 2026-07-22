# CUMPLIR · DB — Volumetría y calidad de datos

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 11.8.6-MariaDB-log · BD: `u815310395_data` · charset BD: utf8mb4 / utf8mb4_unicode_ci.
> Introspección original: 2026-06-21T03:48:02-05:00.

**Totales:** 1,876,122 filas · 274.1 MB datos · 0 B índices · 23 tablas.

## Volumetría por tabla

| Tabla | Filas (COUNT) | Filas (IS aprox) | Datos | Índices | AUTO_INCREMENT | Últ. modif. |
|---|---:|---:|---:|---:|---:|---|
| `l_campana` | 7 | 7 | 16 KB | 0 B | 8 | — |
| `l_cartera` | 62 | 62 | 16 KB | 0 B | 63 | — |
| `reporte_acuerdos` | 10 | 10 | 16 KB | 0 B | 11 | 2026-06-02 09:37:10 |
| `reporte_gestion` | 10 | 10 | 16 KB | 0 B | 11 | 2026-06-18 19:43:23 |
| `reporte_proyeccion` | 10 | 10 | 16 KB | 0 B | 11 | 2026-06-02 09:37:15 |
| `r_acuerdo` | 0 | 0 | 16 KB | 0 B | 1 | — |
| `t_acuerdos` | 11,322 | 11,157 | 2.5 MB | 0 B | 14,839 | 2026-06-20 15:08:55 |
| `t_alertas` | 304 | 304 | 48 KB | 0 B | 43,120 | 2026-06-20 14:57:47 |
| `t_asignacion` | 58,648 | 57,785 | 3.5 MB | 0 B | 58,649 | 2026-06-09 06:50:28 |
| `t_base` | 50,381 | 48,533 | 16.5 MB | 0 B | — | 2026-06-09 06:50:39 |
| `t_campana` | 55,154 | 55,114 | 3.5 MB | 0 B | 55,155 | 2026-06-11 06:00:11 |
| `t_cartera` | 56,444 | 55,597 | 3.5 MB | 0 B | 56,445 | 2026-06-09 06:51:33 |
| `t_decil` | 55,154 | 55,497 | 2.5 MB | 0 B | 55,155 | 2026-06-11 05:59:15 |
| `t_estados` | 6 | 6 | 16 KB | 0 B | 8 | — |
| `t_gestiones` | 801,051 | 728,520 | 143.7 MB | 0 B | 801,070 | 2026-06-20 18:12:54 |
| `t_mensaje` | 0 | 0 | 16 KB | 0 B | 12 | — |
| `t_pagos` | 0 | 0 | 16 KB | 0 B | 1 | — |
| `t_procesos` | 109,865 | 107,717 | 9.5 MB | 0 B | 109,867 | 2026-06-20 18:12:00 |
| `t_resumen` | 488,584 | 473,841 | 73.6 MB | 0 B | 488,585 | 2026-06-20 18:12:03 |
| `t_saldos` | 55,154 | 55,276 | 3.5 MB | 0 B | 55,155 | 2026-06-11 05:53:13 |
| `t_subs` | 9 | 9 | 16 KB | 0 B | 33 | — |
| `t_telefonos` | 133,934 | 133,906 | 11.5 MB | 0 B | 133,950 | 2026-06-20 18:08:27 |
| `t_usuarios` | 13 | 13 | 16 KB | 0 B | — | — |

## Tablas vacías

- `r_acuerdo`
- `t_mensaje`
- `t_pagos`

## Calidad de datos (medida)

### Filas basura `operacion=0` (cabecera de CSV insertada como dato)


| Tabla | Filas operacion=0 |
|---|---:|
| `t_acuerdos` | 1 |
| `t_asignacion` | 12 |
| `t_base` | 1 |
| `t_campana` | 1 |
| `t_cartera` | 7 |
| `t_decil` | 1 |
| `t_gestiones` | 21 |
| `t_procesos` | 47 |
| `t_resumen` | 17 |
| `t_saldos` | 1 |
| `t_telefonos` | 10 |

### Contraseñas = `md5('0')` (password trivial)

| Columna | md5('0') | Total | % |
|---|---:|---:|---:|
| `t_usuarios.userpass` | 10 | 13 | 76.9% |

### Fechas inválidas `0000-00-00`

| Columna | Filas |
|---|---:|
| `t_acuerdos.facuerdo` | 2 |
| `t_base.fvencimiento` | 44 |
| `t_base.fingreso` | 44 |
| `t_procesos.fgestion` | 5,838 |
| `t_resumen.fingreso` | 1,088 |
| `t_resumen.fregistro` | 1 |

### Duplicación por operación (ver también `12-relaciones.md`)

| Tabla | Filas/operación | Lectura |
|---|---:|---|
| `t_acuerdos` | 1.791× | posibles duplicados por reimport |
| `t_alertas` | 1.094× | posibles duplicados por reimport |
| `t_asignacion` | 1.223× | posibles duplicados por reimport |
| `t_campana` | 1.217× | posibles duplicados por reimport |
| `t_cartera` | 1.22× | posibles duplicados por reimport |
| `t_decil` | 1.217× | posibles duplicados por reimport |
| `t_gestiones` | 8.293× | multi-fila esperada (log) o duplicados |
| `t_procesos` | 1.131× | posibles duplicados por reimport |
| `t_resumen` | 6.062× | multi-fila esperada (log) o duplicados |
| `t_saldos` | 1.217× | posibles duplicados por reimport |
| `t_telefonos` | 2.114× | multi-fila esperada (log) o duplicados |
