# Resumen exhaustivo — PROYECTO_CUMPLIR

Documentación al máximo detalle del sistema de gestión de cobranza **CUMPLIR** (`cumplir.net`). Cada afirmación cita evidencia: `archivo:línea` del código en `cumplir/` o una query/dato de introspección guardado en `_evidence/`.

> **Generado:** 2026-06-21, mediante (1) lectura integral del código en `PROYECTO_CUMPLIR/cumplir/` y (2) introspección directa read-only de la base `u815310395_data` (MariaDB 11.8.6 en Hostinger). Reemplaza y amplía la documentación previa del 2026-05-27.

> **Propósito:** una de las dos bases legacy para construir la versión nueva mejorada. CUMPLIR comparte ~80% del esqueleto con CEGROUP, pero añade un módulo de reportería propio (Flight), gestión de estado de teléfonos y procesos de reasignación/ocultamiento. Las diferencias están en `18-comparacion-con-CEGROUP.md`.

## Tabla de contenidos

| # | Documento | Contenido |
|---|---|---|
| 01 | [Visión general](01-vision-general.md) | Qué es, dominio, stack, hosting, tamaño, actividad |
| 02 | [Arquitectura](02-arquitectura.md) | Capas (web/API/cargas/REPORTES Flight), flujos |
| 03 | [Framework web](03-framework-web.md) | Microframework `lib/` (byte-idéntico a CEGROUP) |
| 04 | [Autenticación y sesión](04-autenticacion-sesion.md) | Login HTTPS, sesión, roles, debilidades |
| 05 | [Capa web y vistas](05-capa-web-y-vistas.md) | Vistas campo por campo y diferencias vs CEGROUP |
| 06 | [Capa API y endpoints](06-capa-api-y-endpoints.md) | Endpoints REST (incluye `phone`, sin `g_email`) |
| 07 | [Cargas masivas](07-cargas-masivas.md) | Importadores `DATA/`, REASIGNACION, OCULTAR |
| 08 | [Módulos y procesos](08-modulos-y-procesos.md) | Procesos de negocio paso a paso por módulo |
| 09 | [Módulo REPORTES (Flight)](09-modulo-reportes-flight.md) | Subsistema MVC de reportería con cache pre-computado |
| db/10 | [DB — Esquema](db/10-esquema-completo.md) | DDL completo de las 23 tablas |
| db/11 | [DB — Diccionario de datos](db/11-diccionario-de-datos.md) | Cada columna: tipo, nulos, distintos, distribución, muestras |
| db/12 | [DB — Relaciones](db/12-relaciones.md) | FKs lógicas y cardinalidades medidas |
| db/13 | [DB — Volumetría y calidad](db/13-volumetria-y-calidad.md) | Conteos, basura, duplicados, fechas inválidas |
| 14 | [Seguridad](14-seguridad.md) | Vulnerabilidades consolidadas P0–P3 |
| 15 | [Glosario](15-glosario.md) | Términos del dominio + valores enumerados reales |
| 16 | [Cuestiones abiertas](16-cuestiones-abiertas.md) | Defectos, deuda técnica, preguntas al negocio |
| 17 | [Implicaciones para la nueva versión](17-implicaciones-nueva-version.md) | Qué conservar, corregir, rediseñar |
| 18 | [Comparación con CEGROUP](18-comparacion-con-CEGROUP.md) | Diferencias exhaustivas entre ambos sistemas |

## Datos verificados (introspección 2026-06-21)

- **23 tablas**, **1,876,122 filas**, **~274 MB**. **0 claves foráneas**, **0 índices secundarios**.
- Mayor tabla: `t_gestiones` con **801,051 filas** (~151 MB).
- `t_base`: **50,381 operaciones**; satélites 1:1 (`t_asignacion`, `t_campana`, `t_cartera`, `t_decil`, `t_saldos`).
- **13 usuarios** (`t_usuarios`): 3 administradores + 10 asesores.
- Tablas **nuevas vs CEGROUP**: `reporte_gestion`, `reporte_acuerdos`, `reporte_proyeccion` (cache del módulo Flight) y `r_acuerdo` (vacía). **No existe** `t_email`.
- Estado real del cache Flight: `reporte_gestion` **poblada** con datos (update 2026-06-18); `reporte_acuerdos` y `reporte_proyeccion` **inicializadas pero nunca recalculadas** (todas las celdas en `'-'`, update 2026-06-02).
- Sistema **activo**: varias tablas con `update_time` el 2026-06-20 18:12.

## Hallazgos críticos (resumen ejecutivo)

### 🔴 Seguridad (detalle en `14-seguridad.md`)
1. **Sin autenticación** en NINGÚN endpoint REST ni en el módulo Flight (incluido `DELETE /<dom>/all`, que vacía cache anónimamente).
2. **SQL injection universal** (REST + Flight + importadores).
3. **Pasamanos de SQL arbitrario sin auth**: `api/file/sql/user/acue/index.php:12`.
4. **`echo $sql` en producción** (`REPORTES/.../tools/Gestiones.php:11`) — filtra la query al output.
5. **77% de cuentas con contraseña `0`**: 10 de 13 usuarios `md5('0')`, **incluidos 3 admins**.
6. **Bypass de sesión** `session.php?a=on&t=0`; **reset de password por GET**; **md5 sin sal** devuelto al cliente.
7. **OCULTAR y REASIGNACION** mutan datos sin auth ni auditoría.
8. **Credenciales hardcodeadas** y duplicadas en 3 archivos (`api/lib/DB.php`, `DATA/UPDATE/DB.php`, `DATA/OCULTAR/procesar.php`).

### 🟠 Funcionalidad (detalle en `08`, `09`, `16`)
- **Años hardcodeados inconsistentes**: REST `2026`, Flight Gestiones `2026`, Flight Acuerdos/Proyección `2025`, labels de vista `2026` — causa de que dos reportes den 0.
- **Día `31` fijo** en los `BETWEEN` del Flight (fechas inexistentes en meses cortos).
- Registrar gestión **borra todas las alertas** de la operación (heredado de CEGROUP).
- **OCULTAR borra `t_decil`** de la operación → rompe el `INNER JOIN` de `g_operacion`/`b_datafilter`.
- **Exportación a Excel del Flight no implementada**: las vistas `*File.php` y `headExcel.php` son archivos de **0 bytes**.
- `r_acuerdo` vacía sin propósito claro; `reporte_acuerdos`/`reporte_proyeccion` nunca computadas.

### 🟡 Código muerto / inconsistencias
- `g_asesor` enrutado a método inexistente; `asesor.php`/`clientes.php` huérfanos (tablas inexistentes).
- REASIGNACION oculto del menú (su `<title>` aún dice "ASIGNACION").
- `controllers/Reportes.php:20` referencia `acuerdosFile.php` (mayúscula) vs archivo en minúscula → falla en FS case-sensitive.
- Componentes y vistas `*File.php` = stubs de 0 bytes.

## Cómo navegar

- **Dev nuevo:** `01` → `02` → `08`.
- **Si ya conoces CEGROUP:** ve directo a `18-comparacion-con-CEGROUP.md`.
- **Migración a la v2:** `06` + `07` + `09` + `db/10` + `08` + `17`.
- **Corregir defectos:** `14` y `16`.
- **Entender la BD:** `db/11` → `db/12` → `db/13`.

## Evidencia

Toda afirmación cruza **código** (`cumplir/`, citado por línea) y **base de datos** (introspección read-only del 2026-06-21 en `_evidence/`: `connection.json`, `tables.json`, `columns.json`, `column_profiles.json`, `quality.json`, `indexes.json`, `ddl/*.sql`, `samples/*.json`). Método en `_evidence/_run.log`.
