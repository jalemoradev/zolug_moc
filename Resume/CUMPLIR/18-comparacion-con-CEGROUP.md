# CUMPLIR · Comparación exhaustiva con CEGROUP

Diferencias verificadas entre los dos sistemas legacy. CUMPLIR nació como una copia de CEGROUP (esqueleto ~80% idéntico) y divergió en endpoints, módulos y datos. Todo lo de abajo está confirmado leyendo ambos códigos o comparando con `diff`, y cruzando con la evidencia de BD de cada uno.

> Útil para decidir, en la v2, qué unificar: ambos son el **mismo producto** desplegado para dos operaciones distintas, con drift acumulado.

## 1. Resumen ejecutivo

| Aspecto | CEGROUP | CUMPLIR |
|---|---|---|
| Hosting | GoDaddy | Hostinger |
| Base de datos | `data_cegroup` | `u815310395_data` |
| Servidor | MariaDB 10.11.16 | MariaDB 11.8.6 |
| Charset BD | latin1 (mezcla con utf8mb3) | utf8mb4 (mezcla con utf8mb3) |
| Tablas | 20 | 23 |
| Filas totales | 2,860,926 | 1,876,122 |
| `t_gestiones` | 1,838,261 | 801,051 |
| Usuarios | 65 (3 admin + 62 asesores) | 13 (3 admin + 10 asesores) |
| Contraseñas `md5('0')` | 6 de 65 (9%) | 10 de 13 (77%) |
| Módulo REPORTES (Flight) | ❌ no existe | ✅ existe |
| Endpoint `phone` / estado de teléfono | ❌ | ✅ |
| Endpoint/feature Email (`g_email`, `t_email`) | ✅ | ❌ eliminado |
| Proceso REASIGNACION | ❌ | ✅ |
| Proceso OCULTAR | ❌ | ✅ |
| Año hardcoded en filtros | 2023 | 2026 (REST/vistas) + 2025 (Flight) |

## 2. Lo que es idéntico (verificado con diff)

- **Microframework `lib/`** completo (`Router.php`, `App.php`, `Action.php`, `Response.php`, `Css.php`, `init.php`): byte-idéntico.
- `public/index.php`, `public/.htaccess`, `app/php/session.php`: byte-idénticos.
- **Capa web base**: `MainController.php`, `app/http/routes.php`, `parts/{header,menu,footer}.php`: byte-idénticos → mismas 13 rutas web y mismo menú por rol.
- **Esqueleto del API** (`api/lib/`): mismo framework REST.
- **Defectos heredados**: bypass de sesión, SQLi universal, sin auth, password en GET, md5 sin sal, `g_gestiones` borra alertas, pasamanos `file/sql/.../?sql=`, upload sin validación, `g_asesor` roto, `asesor.php`/`clientes.php` huérfanos.

> Conclusión: no son dos productos, es **el mismo producto clonado**. La deuda de seguridad es común a ambos.

## 3. Lo que CUMPLIR añade

- **Módulo REPORTES (Flight MVC)** — subsistema separado en `REPORTES/`, con rutas, controladores, tools y 3 tablas de cache `reporte_*`. Detalle en `09-modulo-reportes-flight.md`. No existe en CEGROUP.
- **Gestión de estado de teléfono** — columna `t_telefonos.status`, endpoint `phone.php` (`UPDATE t_telefonos SET status`), y toggle activar/desactivar en `gestion.js`. La columna SÍ se usa: `ACTIVO` en 21,772 filas. CEGROUP no tiene nada de esto.
- **Asignación de asesor activa** en `gestion`: en CEGROUP el bloque está comentado; en CUMPLIR está activo (con comparación `.trim()`), y la vista muestra `banco + referencia`.
- **REASIGNACION** (`DATA/UPDATE/REASIGNACION`): `UPDATE t_asignacion SET asesor`. Oculto del menú.
- **OCULTAR** (`DATA/OCULTAR`): `DELETE FROM t_decil WHERE operacion` — saca la operación de la cola rompiendo el `INNER JOIN t_decil` de `g_operacion`. Sin UI, sin auth, sin auditoría.
- **`t_base.referencia`**: columna extra seleccionada en `g_operacion` (en CEGROUP no). Nota: los datos muestran que `referencia` duplica `banco` (drift de columnas en el import de BASE).

## 4. Lo que CUMPLIR elimina

- **Email**: en `gestion.php` se quitó la subvista `info_view_email`, la tabla `tbl_emails`, el form de inserción y las llamadas JS; el endpoint `g_email.php` **no existe** y la tabla `t_email` **no existe**. En CEGROUP, `t_email` tiene 11,728 filas.

## 5. Diferencias de datos y catálogos

- **Duplicación `t_procesos`**: CEGROUP ratio 1.0× (limpio); CUMPLIR 1.131× (109,865 / 97,109). (Nota: la documentación previa decía 2.18× para CUMPLIR; la introspección actual lo desmiente — los datos cambiaron.)
- **Cargas duplicadas**: CUMPLIR muestra `t_asignacion` 58,648 y `t_procesos` 109,865 frente a `t_base` 50,381 → reimports sin TRUNCATE. Mismo patrón frágil que CEGROUP.
- **Fechas `0000-00-00`**: ambos las tienen; en CUMPLIR `t_procesos.fgestion` 5,838, `t_resumen.fingreso` 1,088.
- **Catálogos**: en ambos `t_estados`/`t_subs` no cubren los valores reales usados; en CUMPLIR `t_estados` lista ACUERDO/ILOCALIZADO/JURIDICO/RENUENTE/PAZ_Y_SALVO.
- **Importador BASE**: el CSV de PROCESOS trae 7 columnas pero `procesar.php` lee 5; SALDOS usa coma decimal — riesgos comunes a ambos pero con variantes por proyecto.

## 6. Divergencia de configuración temporal (riesgo)

El "año actual" está hardcodeado y **diverge no solo entre proyectos sino dentro de CUMPLIR**:
- CEGROUP: `2023` (obsoleto — sus filtros mensuales ya no devuelven nada en 2026).
- CUMPLIR REST y vistas: `2026`.
- CUMPLIR Flight: Gestiones `2026`, Acuerdos/Proyección `2025` → por eso `reporte_acuerdos` y `reporte_proyeccion` nunca computaron datos de 2026.

## 7. Implicación para la v2

Como son el mismo producto clonado, la v2 debería ser **una sola base de código multi-tenant/multi-operación**, no dos copias. Conviene tomar:
- de **CUMPLIR**: la idea de estado de teléfono, reasignación y un módulo de reportería (rediseñado, no el Flight actual);
- de **CEGROUP**: el feature de Email si el negocio lo sigue usando (confirmar);
- y de **ninguno**: la duplicación de esqueleto, los años hardcodeados, el cache Flight a medias y la ausencia de seguridad.

Pregunta abierta para el negocio: ¿CEGROUP y CUMPLIR son dos clientes/operaciones de la misma agencia que deben converger, o productos que seguirán separados? La respuesta define si la v2 es multi-tenant. Ver `16-cuestiones-abiertas.md`.
