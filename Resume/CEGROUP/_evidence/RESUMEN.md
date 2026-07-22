# Evidencia de introspección — CEGROUP

> Generado: 2026-06-21T03:54:28-05:00 · Servidor: 10.11.16-MariaDB-cll-lve · TZ servidor: SYSTEM
> Conexión read-only a `data_cegroup` en `p3plzcpnl489480.prod.phx3.secureserver.net:3306` como `user_cegroup`.

## Tablas (20)

| Tabla | Engine | Filas (aprox IS) | Filas (COUNT) | Datos | Índices | Colación | Última modif. |
|---|---|---:|---:|---:|---:|---|---|
| `l_campana` | InnoDB | 3 | 3 | 16,384 | 0 | utf8mb3_general_ci | — |
| `l_cartera` | InnoDB | 38 | 43 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_acuerdos` | InnoDB | 741 | 10,902 | 98,304 | 0 | utf8mb3_general_ci | — |
| `t_alertas` | InnoDB | 20 | 22 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_asignacion` | InnoDB | 54,953 | 54,931 | 3,686,400 | 0 | utf8mb3_general_ci | — |
| `t_base` | InnoDB | 54,485 | 54,931 | 16,302,080 | 0 | utf8mb3_spanish_ci | — |
| `t_campana` | InnoDB | 54,121 | 54,931 | 3,686,400 | 0 | utf8mb3_general_ci | — |
| `t_cartera` | InnoDB | 54,916 | 54,930 | 3,686,400 | 0 | utf8mb3_general_ci | — |
| `t_decil` | InnoDB | 55,386 | 54,931 | 2,637,824 | 0 | utf8mb3_general_ci | — |
| `t_email` | InnoDB | 3,689 | 11,728 | 262,144 | 0 | latin1_swedish_ci | — |
| `t_estados` | InnoDB | 5 | 5 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_gestiones` | InnoDB | 745,537 | 1,838,261 | 197,853,184 | 0 | utf8mb3_general_ci | — |
| `t_mensaje` | InnoDB | 0 | 0 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_pagos` | InnoDB | 0 | 0 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_procesos` | InnoDB | 51,255 | 57,590 | 4,734,976 | 0 | utf8mb3_general_ci | — |
| `t_resumen` | InnoDB | 154,263 | 493,254 | 24,690,688 | 0 | utf8mb3_general_ci | — |
| `t_saldos` | InnoDB | 54,940 | 54,931 | 3,686,400 | 0 | latin1_swedish_ci | — |
| `t_subs` | InnoDB | 39 | 37 | 16,384 | 0 | utf8mb3_general_ci | — |
| `t_telefonos` | InnoDB | 117,981 | 119,431 | 11,026,432 | 0 | utf8mb3_general_ci | — |
| `t_usuarios` | InnoDB | 58 | 65 | 16,384 | 0 | utf8mb3_general_ci | — |

## Calidad de datos

**Filas basura `operacion=0`:** 
- `t_acuerdos`: 1
- `t_email`: 1
- `t_gestiones`: 34
- `t_resumen`: 6

**Passwords = md5('0'):**
- `t_usuarios.userpass`: 6 de 65

**Fechas 0000-00-00:**
- `t_acuerdos.facuerdo`: 11
- `t_acuerdos.fregistro`: 741
- `t_base.fvencimiento`: 48
- `t_base.fingreso`: 79
- `t_procesos.fgestion`: 1328
- `t_resumen.fingreso`: 7343
- `t_resumen.fregistro`: 1

**Ratio filas/operación-distinta (esperado ~1.0):**
- `t_acuerdos`: 1.248× (10902 filas / 8734 ops)
- `t_alertas`: 1× (22 filas / 22 ops)
- `t_asignacion`: 1× (54931 filas / 54931 ops)
- `t_campana`: 1× (54931 filas / 54931 ops)
- `t_cartera`: 1× (54930 filas / 54930 ops)
- `t_decil`: 1× (54931 filas / 54931 ops)
- `t_email`: 1.201× (11728 filas / 9762 ops)
- `t_gestiones`: 14.753× (1838261 filas / 124604 ops)
- `t_pagos`: × (0 filas / 0 ops)
- `t_procesos`: 1× (57590 filas / 57590 ops)
- `t_resumen`: 5.443× (493254 filas / 90617 ops)
- `t_saldos`: 1× (54931 filas / 54931 ops)
- `t_telefonos`: 1.878× (119431 filas / 63602 ops)

Archivos crudos: `connection.json`, `tables.json`, `columns.json`, `indexes.json`, `column_profiles.json`, `quality.json`, `ddl/*.sql`, `samples/*.json`.
