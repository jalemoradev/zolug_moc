# CEGROUP · DB — Relaciones (lógicas, medidas)

> Generado desde evidencia de introspección (`_evidence/`) el 2026-06-21T08:57:05+00:00.
> Servidor: 10.11.16-MariaDB-cll-lve · BD: `data_cegroup` · charset BD: latin1 / latin1_swedish_ci.
> Introspección original: 2026-06-21T03:51:46-05:00.

**No hay claves foráneas declaradas** (0 FK) ni índices secundarios. Las relaciones son *por convención*: la columna `operacion` enlaza las tablas satélite con `t_base`. Las cardinalidades de abajo se midieron con `COUNT(*)` vs `COUNT(DISTINCT operacion)`.

`t_base` tiene **54,931 operaciones distintas** (clave del dominio).

## Cardinalidad real respecto a `operacion`

| Tabla | Filas | Operaciones distintas | Filas/operación | Interpretación |
|---|---:|---:|---:|---|
| `t_acuerdos` | 10,902 | 8,734 | 1.248× | leve duplicación / multi-fila |
| `t_alertas` | 22 | 22 | 1× | 1:1 (satélite) |
| `t_asignacion` | 54,931 | 54,931 | 1× | 1:1 (satélite) |
| `t_campana` | 54,931 | 54,931 | 1× | 1:1 (satélite) |
| `t_cartera` | 54,930 | 54,930 | 1× | 1:1 (satélite) |
| `t_decil` | 54,931 | 54,931 | 1× | 1:1 (satélite) |
| `t_email` | 11,728 | 9,762 | 1.201× | leve duplicación / multi-fila |
| `t_gestiones` | 1,838,261 | 124,604 | 14.753× | multi-fila por operación (log/histórico) |
| `t_pagos` | 0 | 0 | — | — |
| `t_procesos` | 57,590 | 57,590 | 1× | 1:1 (satélite) |
| `t_resumen` | 493,254 | 90,617 | 5.443× | multi-fila por operación (log/histórico) |
| `t_saldos` | 54,931 | 54,931 | 1× | 1:1 (satélite) |
| `t_telefonos` | 119,431 | 63,602 | 1.878× | leve duplicación / multi-fila |

## Tablas sin `operacion` (catálogos / globales)

- `l_campana` — 3 filas
- `l_cartera` — 43 filas
- `t_estados` — 5 filas
- `t_mensaje` — 0 filas
- `t_subs` — 37 filas
- `t_usuarios` — 65 filas

> Diagrama lógico: `t_base` (centro) ←`operacion`→ satélites 1:1 (`t_asignacion`, `t_campana`, `t_cartera`, `t_decil`, `t_saldos`) y 1:N (`t_gestiones`, `t_resumen`, `t_acuerdos`, `t_telefonos`, `t_alertas`).
