# CUMPLIR · Implicaciones para la nueva versión

Qué del sistema actual debe **conservarse**, **corregirse** o **rediseñarse** en la v2. Se apoya en los hallazgos verificados de `01`–`16`, el módulo Flight (`09`), la comparación (`18`) y la evidencia en `_evidence/`. No se asume modelo de negocio; lo no confirmado va como **pregunta abierta**.

> Documento de insumo, no de decisión. Las decisiones de negocio las toma el usuario. Lee también `CEGROUP/17` — gran parte aplica a ambos por ser el mismo producto clonado.

## 1. Qué conservar

El dominio de cobranza es el mismo de CEGROUP y es aprovechable (operación central, satélites, log de gestiones, flujo del asesor). Además, CUMPLIR aporta tres ideas que la v2 debería mantener **conceptualmente**:

- **Estado de teléfono** (`t_telefonos.status`): marcar números como activos/inactivos es útil para la operación y ya se usa (`ACTIVO` en 21,772 filas). En la v2 debe ser un catálogo cerrado con valores definidos (hoy solo se ve `ACTIVO` vs vacío).
- **Reasignación de operaciones** entre asesores (REASIGNACION): es una necesidad operativa real. Debe rehacerse como acción auditada y autenticada.
- **Reportería con métricas por asesor y por día** (módulo Flight): el negocio quiere tableros de gestiones/acuerdos/proyección. La intención es buena; la implementación actual no (ver §3).

## 2. Qué corregir sí o sí

Hereda todos los defectos críticos de CEGROUP (ver `CEGROUP/17` §2: auth, SQLi, sesión, password, upload, credenciales, borrado de alertas como efecto colateral, año hardcoded, edición sin control). Además, propios de CUMPLIR:

| Tema | Defecto actual (evidencia) | Corrección en v2 |
|---|---|---|
| Password trivial | **10 de 13** cuentas con `md5('0')`, incluidos 3 admins (`14-seguridad.md`) | Hash fuerte + cambio inicial forzado; el riesgo aquí es peor que en CEGROUP |
| Fuga de SQL | `echo $sql` en `REPORTES/.../tools/Gestiones.php:11` | Eliminar; nunca emitir SQL al output |
| Año hardcoded inconsistente | REST `2026`, Flight Gestiones `2026`, Flight Acuerdos/Proyección `2025`, vistas `2026` | Parametrizar el período en un solo lugar (config); es la causa de reportes en 0 |
| Día 31 fijo | `BETWEEN` con día `31` en el Flight | Calcular el último día real del mes |
| OCULTAR | Borra `t_decil` sin auth ni auditoría; rompe `INNER JOIN` de `g_operacion` | Soft-delete con bandera + auditoría, no DELETE físico |
| REASIGNACION | `UPDATE t_asignacion` sin auth, oculto del menú, `<title>` equivocado | Acción autenticada, auditada y visible |
| Excel del Flight | `view/*File.php` y `headExcel.php` son archivos de 0 bytes (exportación inexistente) | Implementar de verdad o eliminar las rutas `/file` |
| Cache de reportes | `reporte_acuerdos`/`reporte_proyeccion` nunca recalculadas; sin cron ni triggers | Recálculo programado o cálculo on-demand consistente |

## 3. Qué rediseñar

Aplica todo lo de `CEGROUP/17` §3 (FKs, índices, idempotencia de cargas, catálogos normalizados, tipos correctos, encoding `utf8mb4`, separación de capas). Específico de CUMPLIR:

- **Módulo de reportería**: el patrón actual (tablas wide `reporte_*` con columnas `ges_01..ges_31`, una por día, todo `varchar`, vacío = `'-'`) es un antipatrón. La v2 debería calcular métricas con consultas agregadas sobre datos normalizados e indexados, o un modelo dimensional simple — no 66 columnas de texto por tabla sincronizadas a mano.
- **`r_acuerdo`**: tabla vacía sin propósito claro. Confirmar con el negocio si era un rediseño abandonado de `t_acuerdos` antes de arrastrarla.
- **`t_base.referencia`**: hoy duplica `banco` por drift de columnas en el import. Definir su semántica real o eliminarla.
- **Unificación con CEGROUP**: ver §5.

## 4. Preguntas abiertas que el negocio debe responder

Además de las de `CEGROUP/17` §4 (pagos, ciclo de acuerdos, decil/sub/estados, jurídico, asignación, PII):

1. ¿Por qué se **eliminó Email** en CUMPLIR y se mantiene en CEGROUP? ¿La v2 lo necesita?
2. ¿Qué valores válidos tiene **`t_telefonos.status`** además de `ACTIVO`? ¿Cuál es la regla?
3. ¿Para qué sirve **`r_acuerdo`** (vacía)?
4. ¿Quién y con qué frecuencia debe **recalcular los reportes**? ¿Quién usa el módulo Flight hoy?
5. ¿Bajo qué criterio se **oculta** una operación (OCULTAR)? ¿Debe ser reversible/auditable?

## 5. La decisión estructural: ¿una base o dos?

CEGROUP y CUMPLIR son **el mismo producto clonado** para dos operaciones (ver `18-comparacion-con-CEGROUP.md`). Mantener dos copias multiplica la deuda: cada defecto vive por duplicado y las divergencias (años, Email, phone) son drift, no diseño.

**Recomendación para evaluar con el negocio:** construir la v2 como **una sola base de código multi-operación (multi-tenant)**, tomando lo mejor de cada uno (estado de teléfono y reasignación de CUMPLIR; Email de CEGROUP si sigue vigente; reportería rediseñada), y descartando lo común malo (sin seguridad, esqueleto duplicado, cache a medias, hardcodes). La pregunta que habilita esto: **¿son dos clientes de la misma agencia que deben converger, o seguirán separados?**

## 6. Resumen para el diseño de la v2

CUMPLIR = CEGROUP + reportería Flight + estado de teléfono + reasignación/ocultamiento − Email, pero con **peor higiene de contraseñas** (77% triviales) y **más hardcodes temporales**.

La v2 conserva el dominio y las tres ideas nuevas. Corrige la seguridad, tanto la heredada como la propia. Rediseña la capa de datos y, sobre todo, **la reportería**. Y antes que nada decide con el negocio si unifica ambos sistemas. Antes de migrar, hay que limpiar la basura que ya está cuantificada en `db/13` y `16-cuestiones-abiertas.md`.
