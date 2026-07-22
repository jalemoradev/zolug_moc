# CEGROUP · Implicaciones para la nueva versión

Qué del sistema actual debe **conservarse**, **corregirse** o **rediseñarse** al construir la v2. Cada punto se apoya en los hallazgos verificados de los documentos `01`–`16` y la evidencia en `_evidence/`. No se asume modelo de negocio: lo que no está confirmado se marca como **pregunta abierta** (ver también `16-cuestiones-abiertas.md`).

> Este documento es insumo de diseño, no una decisión. Las decisiones de negocio las toma el usuario.

## 1. Qué conservar (el dominio funciona)

El **modelo de dominio de cobranza** es sólido y vale la pena preservarlo conceptualmente:

- **Operación como entidad central** (`t_base.operacion`): cada crédito/cuenta en cobro, con titular, codeudor y garante. Es la clave natural alrededor de la cual giran todas las tablas satélite.
- **Separación operación ↔ satélites**: asignación (asesor), campaña, cartera, decil, saldos, teléfonos, gestiones, acuerdos, alertas. Es una buena descomposición; solo le faltan claves foráneas y catálogos normalizados.
- **El log de gestiones** (`t_gestiones`): historial append-only de contactos/acciones por operación. Es el corazón operativo y debe mantenerse como bitácora inmutable.
- **El flujo de trabajo del asesor**: buscar operación → ver 360° (datos, teléfonos, gestiones, saldos) → registrar gestión → pactar acuerdo → programar alerta. Es coherente y debe replicarse, corrigiendo los efectos colaterales.
- **Cargas masivas por dominio** (BASE, ASIGNACION, CAMPANA, etc.): el negocio vive de importar carteras periódicamente. La capacidad debe conservarse, pero con validación, idempotencia y transacciones (ver §3).

## 2. Qué corregir sí o sí (defectos críticos)

Estos son bugs/riesgos confirmados que la v2 **no debe heredar**:

| Tema | Defecto actual (evidencia) | Corrección en v2 |
|---|---|---|
| Auth API | Cero autenticación en endpoints y cargas (`14-seguridad.md`) | Autenticación obligatoria (JWT/sesión server-side) en cada endpoint |
| SQL injection | Universal, por concatenación de entrada | ORM/consultas parametrizadas siempre |
| Sesión | Bypass `session.php?a=on&t=0`; rol decidido por el cliente | Rol y sesión emitidos y validados solo en el servidor |
| Reset de password | `d_usuario_cambio` cambia clave por GET sin identidad | Flujo autenticado, verificación de identidad, método no-GET |
| Password trivial | `md5('0')` en 6/65; hash md5 sin sal devuelto al cliente | Hash fuerte (bcrypt/argon2), nunca devolver hash, forzar cambio inicial |
| SQL arbitrario | `file/sql/user/acue/?sql=` ejecuta SQL del cliente | Eliminar por completo |
| Upload | Sin validación de tipo/tamaño, conserva nombre → RCE | Validación estricta, almacenamiento fuera del webroot, nombres generados |
| Efecto colateral | Registrar gestión **borra todas las alertas** de la operación | Nunca borrar como efecto lateral; separar acciones explícitas |
| Fechas hardcoded | Año `2023` fijo en filtros mensuales | Parametrizar período por configuración/UI |
| Edición sin control | Bloqueo de "solo asesor asignado" comentado | Autorización real por propiedad de la operación |
| Credenciales | Hardcodeadas en `api/lib/DB.php` | Variables de entorno / secretos, nunca en código |

## 3. Qué rediseñar (arquitectura y datos)

- **Integridad referencial:** la BD tiene **0 FKs y 0 índices secundarios**. La v2 debe declarar claves foráneas, índices por `operacion` y por las columnas de filtro, y eliminar la posibilidad de huérfanos (hoy `elimina.php` los crea).
- **Idempotencia de cargas:** los importadores no hacen TRUNCATE/UPSERT ni usan transacciones → recargar duplica (evidencia: ratios >1 en varias tablas y filas basura `operacion=0` por cabeceras de CSV insertadas). La v2 necesita import transaccional, con clave de deduplicación y validación de cabecera.
- **Catálogos normalizados:** `t_procesos.estado` mezcla 23 conceptos distintos; `t_decil` tiene 79 valores (no 1–10); `t_subs` no cubre los valores reales de `t_procesos.sub`. Rediseñar como catálogos con FK y dominios cerrados.
- **Tipos correctos:** importar saldos con coma decimal como texto rompe agregaciones; fechas `0000-00-00` abundan. Usar tipos numéricos y de fecha reales con validación en ingreso.
- **Encoding unificado:** mezcla de `latin1` y `utf8mb3` entre tablas. La v2 debe ser `utf8mb4` en todo.
- **Separación de capas real:** hoy el front decide el rol y arma SQL; la lógica de negocio vive mezclada en vistas y endpoints. La v2 debe tener una capa de servicios con reglas de negocio y autorización centralizadas.
- **Tablas/feature muertos:** decidir con el negocio el destino de `t_pagos` y `t_mensaje` (vacías), el módulo Clientes (huérfano) y Comunicación (vista vacía).

## 4. Preguntas abiertas que el negocio debe responder antes de migrar

Estas NO se pueden deducir del código (ver `16-cuestiones-abiertas.md` para la lista completa):

1. ¿De dónde salen los **pagos/recaudos**? `t_pagos` está vacía y no hay importador ni endpoint de escritura: ¿se gestionan en otro sistema?
2. ¿Cuál es el **ciclo de vida real de un acuerdo**? Hoy todos quedan `'activo'`; ¿qué estados existen (cumplido, incumplido, anulado)?
3. ¿Qué significan exactamente **decil**, **sub** y los estados de `t_procesos`? Los datos muestran valores fuera de catálogo.
4. ¿El **módulo jurídico** (valores CIVIL/PENAL/FAMILIA en `t_procesos.estado`) es un flujo activo? No tiene UI propia visible.
5. ¿Qué reglas rigen la **asignación y reasignación** de operaciones a asesores?
6. ¿Qué datos son **PII sensible** y qué retención/seguridad exige el negocio? (hoy todo viaja sin protección).

## 5. Resumen para el diseño de la v2

CEGROUP funciona y está en uso, y su modelo de dominio se puede aprovechar. El problema es lo demás: no tiene seguridad, la integridad de datos es débil y los importadores son frágiles.

Para la v2 esto se traduce en cuatro frentes. Conservar el modelo operación-céntrico y el flujo del asesor. Reconstruir la capa de datos con claves foráneas, índices, tipos correctos y catálogos normalizados. Rehacer las cargas como procesos transaccionales e idempotentes. Y construir autenticación y autorización reales desde el primer día. Antes de migrar nada, hay que resolver con el negocio las preguntas de §4 y limpiar la basura que `db/13` ya tiene cuantificada.
