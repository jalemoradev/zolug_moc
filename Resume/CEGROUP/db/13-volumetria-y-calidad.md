# CEGROUP · DB — Volumetría y calidad de datos

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 10.11.16-MariaDB-cll-lve · BD: `data_cegroup` · charset BD: latin1 / latin1_swedish_ci.
> Introspección original: 2026-06-21T03:51:46-05:00.

**Totales:** 2,860,926 filas · 259.9 MB datos · 0 B índices · 20 tablas.

## Volumetría por tabla

| Tabla | Filas (COUNT) | Filas (IS aprox) | Datos | Índices | AUTO_INCREMENT | Últ. modif. |
|---|---:|---:|---:|---:|---:|---|
| `l_campana` | 3 | 3 | 16 KB | 0 B | 5,449,502 | — |
| `l_cartera` | 43 | 38 | 16 KB | 0 B | 5,449,502 | — |
| `t_acuerdos` | 10,902 | 741 | 96 KB | 0 B | 10,932 | — |
| `t_alertas` | 22 | 20 | 16 KB | 0 B | 11,418 | — |
| `t_asignacion` | 54,931 | 54,953 | 3.5 MB | 0 B | 54,932 | — |
| `t_base` | 54,931 | 54,485 | 15.5 MB | 0 B | — | — |
| `t_campana` | 54,931 | 54,121 | 3.5 MB | 0 B | 54,932 | — |
| `t_cartera` | 54,930 | 54,916 | 3.5 MB | 0 B | 54,931 | — |
| `t_decil` | 54,931 | 55,386 | 2.5 MB | 0 B | 54,932 | — |
| `t_email` | 11,728 | 3,689 | 256 KB | 0 B | 11,748 | — |
| `t_estados` | 5 | 5 | 16 KB | 0 B | 6 | — |
| `t_gestiones` | 1,838,261 | 745,537 | 188.7 MB | 0 B | 1,839,397 | — |
| `t_mensaje` | 0 | 0 | 16 KB | 0 B | 1 | — |
| `t_pagos` | 0 | 0 | 16 KB | 0 B | 1 | — |
| `t_procesos` | 57,590 | 51,255 | 4.5 MB | 0 B | 58,055 | — |
| `t_resumen` | 493,254 | 154,263 | 23.5 MB | 0 B | 595,448 | — |
| `t_saldos` | 54,931 | 54,940 | 3.5 MB | 0 B | 54,932 | — |
| `t_subs` | 37 | 39 | 16 KB | 0 B | 79 | — |
| `t_telefonos` | 119,431 | 117,981 | 10.5 MB | 0 B | 119,437 | — |
| `t_usuarios` | 65 | 58 | 16 KB | 0 B | — | — |

## Tablas vacías

- `t_mensaje`
- `t_pagos`

## Calidad de datos (medida)

### Filas basura `operacion=0` (cabecera de CSV insertada como dato)


| Tabla | Filas operacion=0 |
|---|---:|
| `t_acuerdos` | 1 |
| `t_email` | 1 |
| `t_gestiones` | 34 |
| `t_resumen` | 6 |

### Contraseñas = `md5('0')` (password trivial)

| Columna | md5('0') | Total | % |
|---|---:|---:|---:|
| `t_usuarios.userpass` | 6 | 65 | 9.2% |

### Fechas inválidas `0000-00-00`

| Columna | Filas |
|---|---:|
| `t_acuerdos.facuerdo` | 11 |
| `t_acuerdos.fregistro` | 741 |
| `t_base.fvencimiento` | 48 |
| `t_base.fingreso` | 79 |
| `t_procesos.fgestion` | 1,328 |
| `t_resumen.fingreso` | 7,343 |
| `t_resumen.fregistro` | 1 |

### Duplicación por operación (ver también `12-relaciones.md`)

| Tabla | Filas/operación | Lectura |
|---|---:|---|
| `t_acuerdos` | 1.248× | posibles duplicados por reimport |
| `t_email` | 1.201× | posibles duplicados por reimport |
| `t_gestiones` | 14.753× | multi-fila esperada (log) o duplicados |
| `t_resumen` | 5.443× | multi-fila esperada (log) o duplicados |
| `t_telefonos` | 1.878× | posibles duplicados por reimport |
