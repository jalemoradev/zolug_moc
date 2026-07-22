# Resumen exhaustivo — PROYECTO_CEGROUP

Documentación al máximo detalle del sistema de gestión de cobranza **CEGROUP**. Cada afirmación cita evidencia: `archivo:línea` del código en `cegroup/` o una query/dato de introspección guardado en `_evidence/`.

> **Generado:** 2026-06-21, mediante (1) lectura integral del código en `PROYECTO_CEGROUP/cegroup/` y (2) introspección directa read-only de la base `data_cegroup` (MariaDB 10.11.16 en GoDaddy). Reemplaza y amplía la documentación previa del 2026-05-27.

> **Propósito:** este sistema es una de las dos bases legacy a partir de las cuales se construirá la versión nueva mejorada. Por eso se documenta hasta el detalle mínimo: tablas, columnas, módulos, procesos, defectos y riesgos.

## Tabla de contenidos

| # | Documento | Contenido |
|---|---|---|
| 01 | [Visión general](01-vision-general.md) | Qué es, dominio, stack, hosting, tamaño, actividad |
| 02 | [Arquitectura](02-arquitectura.md) | Capas, layout en disco, flujos de request web/API |
| 03 | [Framework web](03-framework-web.md) | Microframework `lib/` (Router, App, Action, Response, Css) |
| 04 | [Autenticación y sesión](04-autenticacion-sesion.md) | Login, `$_SESSION`, localStorage, roles, debilidades |
| 05 | [Capa web y vistas](05-capa-web-y-vistas.md) | Controlador, rutas, menú por rol, las 17 vistas campo por campo |
| 06 | [Capa API y endpoints](06-capa-api-y-endpoints.md) | Framework REST + catálogo de los ~24 endpoints |
| 07 | [Cargas masivas](07-cargas-masivas.md) | Importadores CSV, formatos, tablas destino, riesgos |
| 08 | [Módulos y procesos](08-modulos-y-procesos.md) | Procesos de negocio paso a paso por módulo |
| db/10 | [DB — Esquema](db/10-esquema-completo.md) | DDL completo de las 20 tablas |
| db/11 | [DB — Diccionario de datos](db/11-diccionario-de-datos.md) | Cada columna: tipo, nulos, distintos, distribución, muestras |
| db/12 | [DB — Relaciones](db/12-relaciones.md) | FKs lógicas y cardinalidades medidas |
| db/13 | [DB — Volumetría y calidad](db/13-volumetria-y-calidad.md) | Conteos, basura, duplicados, fechas inválidas |
| 14 | [Seguridad](14-seguridad.md) | Vulnerabilidades consolidadas P0–P3 |
| 15 | [Glosario](15-glosario.md) | Términos del dominio + valores enumerados reales |
| 16 | [Cuestiones abiertas](16-cuestiones-abiertas.md) | Defectos, deuda técnica, preguntas al negocio |
| 17 | [Implicaciones para la nueva versión](17-implicaciones-nueva-version.md) | Qué conservar, corregir, rediseñar |

## Datos verificados (introspección 2026-06-21)

- **20 tablas**, **2,860,926 filas**, **~260 MB**. **0 claves foráneas**, **0 índices secundarios** (solo PRIMARY).
- Mayor tabla: `t_gestiones` con **1,838,261 filas** (~189 MB) — log de gestiones, 14.75 filas por operación.
- Catálogo de operaciones: `t_base` con **54,931 operaciones** distintas; satélites (`t_asignacion`, `t_campana`, `t_cartera`, `t_decil`, `t_saldos`) en 1:1 real.
- **65 usuarios** (`t_usuarios`): 3 administradores + 62 asesores (`usertype` solo toma valores 0 y 1; no hay jerarquía de niveles).
- Tablas **vacías**: `t_pagos` y `t_mensaje` (funcionalidad nunca conectada en este código).
- Sistema **activo**: columnas de fecha registran movimiento hasta 2026-06-20.

## Hallazgos críticos (resumen ejecutivo)

### 🔴 Seguridad (detalle en `14-seguridad.md`)
1. **Sin autenticación en NINGÚN endpoint del API** ni en los subsistemas de carga.
2. **SQL injection universal** — los 24 endpoints concatenan entrada en el SQL.
3. **Pasamanos de SQL arbitrario sin auth**: `api/file/sql/user/acue/index.php:12` ejecuta `$_GET['sql']` literal.
4. **Bypass de sesión** vía `app/php/session.php` con `?a=on&t=0` (rol lo decide el cliente).
5. **Reset de contraseña por GET sin verificar identidad** (`d_usuario_cambio.php`).
6. **Contraseña trivial `md5('0')`** en 6 de 65 usuarios; origen: `admin_asesor.php:165` crea cada asesor con clave `0`.
7. **Contraseña viaja por URL GET**; hash **md5 sin sal** y devuelto al cliente.
8. **Upload sin validación** (`UPDATE/*/upload.php`) — riesgo de RCE.
9. **Credenciales de producción hardcodeadas** en `api/lib/DB.php:5`.

### 🟠 Funcionalidad (detalle en `08` y `16`)
- Registrar una gestión (`g_gestiones.php:110` POST) ejecuta **`DELETE FROM t_alertas`** de toda la operación, sin aviso.
- **Año `2023` hardcodeado** en filtros mensuales (`b_acuerdos`, `b_resumen`) → rotos fuera de 2023.
- El bloqueo "solo el asesor asignado edita" está **comentado** (`gestion.js`) → cualquier asesor edita cualquier operación.
- `t_acuerdos.estado` es siempre `'activo'` (10,902/10,902) — sin ciclo de vida.
- `t_procesos.estado` está **sobrecargado**: 23 valores que mezclan estado de cobranza, tipo de proceso jurídico, condición y basura.

### 🟡 Código muerto / inconsistencias
- Ruta API `g_asesor` apunta a un método inexistente → error fatal si se invoca.
- `rest/asesor.php` (tabla `asesor` inexistente) y `rest/clientes.php` (tabla `clientes` inexistente): huérfanos.
- `lib/Css.php` nunca se instancia → el CSS por vista no se carga.
- Vistas vacías (`index`, `pagos`, `comunicacion`) y 9 componentes stub.

## Cómo navegar

- **Dev nuevo:** `01` → `02` → `08`.
- **Migración a la v2:** `06` + `07` + `db/10` + `08` + `17`.
- **Corregir defectos:** `14` y `16`.
- **Entender la BD:** `db/11` (diccionario) → `db/12` (relaciones) → `db/13` (volumetría/calidad).

## Evidencia

Toda afirmación cruza dos fuentes: **código** (archivos en `cegroup/`, citados por línea) y **base de datos** (introspección read-only del 2026-06-21 guardada en `_evidence/`: `connection.json`, `tables.json`, `columns.json`, `column_profiles.json`, `quality.json`, `indexes.json`, `ddl/*.sql`, `samples/*.json`). El método de introspección está en `_evidence/_run.log`. La única casilla no calculada fue `COUNT(DISTINCT)` sobre la columna de texto libre `t_gestiones.gestion` (excede los límites del servidor en 1.8M filas).
