# CUMPLIR · DB — Relaciones (lógicas, medidas)

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 11.8.6-MariaDB-log · BD: `u815310395_data` · charset BD: utf8mb4 / utf8mb4_unicode_ci.
> Introspección original: 2026-06-21T03:48:02-05:00.

**No hay claves foráneas declaradas** (0 FK) ni índices secundarios. Las relaciones son *por convención*: la columna `operacion` enlaza las tablas satélite con `t_base`. Las cardinalidades de abajo se midieron con `COUNT(*)` vs `COUNT(DISTINCT operacion)`.

`t_base` tiene **50,381 operaciones distintas** (clave del dominio).

## Cardinalidad real respecto a `operacion`

| Tabla | Filas | Operaciones distintas | Filas/operación | Interpretación |
|---|---:|---:|---:|---|
| `t_acuerdos` | 11,322 | 6,321 | 1.791× | leve duplicación / multi-fila |
| `t_alertas` | 304 | 278 | 1.094× | leve duplicación / multi-fila |
| `t_asignacion` | 58,648 | 47,955 | 1.223× | leve duplicación / multi-fila |
| `t_campana` | 55,154 | 45,328 | 1.217× | leve duplicación / multi-fila |
| `t_cartera` | 56,444 | 46,249 | 1.22× | leve duplicación / multi-fila |
| `t_decil` | 55,154 | 45,328 | 1.217× | leve duplicación / multi-fila |
| `t_gestiones` | 801,051 | 96,599 | 8.293× | multi-fila por operación (log/histórico) |
| `t_pagos` | 0 | 0 | — | — |
| `t_procesos` | 109,865 | 97,109 | 1.131× | leve duplicación / multi-fila |
| `t_resumen` | 488,584 | 80,595 | 6.062× | multi-fila por operación (log/histórico) |
| `t_saldos` | 55,154 | 45,328 | 1.217× | leve duplicación / multi-fila |
| `t_telefonos` | 133,934 | 63,342 | 2.114× | multi-fila por operación (log/histórico) |

## Tablas sin `operacion` (catálogos / globales)

- `l_campana` — 7 filas
- `l_cartera` — 62 filas
- `reporte_acuerdos` — 10 filas
- `reporte_gestion` — 10 filas
- `reporte_proyeccion` — 10 filas
- `r_acuerdo` — 0 filas
- `t_estados` — 6 filas
- `t_mensaje` — 0 filas
- `t_subs` — 9 filas
- `t_usuarios` — 13 filas

> Diagrama lógico: `t_base` (centro) ←`operacion`→ satélites 1:1 (`t_asignacion`, `t_campana`, `t_cartera`, `t_decil`, `t_saldos`) y 1:N (`t_gestiones`, `t_resumen`, `t_acuerdos`, `t_telefonos`, `t_alertas`).
