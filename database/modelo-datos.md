# Modelo de datos — CobroZolug

Documento vivo. Aquí se define, paso a paso, el nuevo modelo entidad-relación, contrastándolo contra cómo funcionaba el sistema anterior.

**Reglas de este documento:**
- Solo se escribe lo que el usuario decide. Nada se asume ni se infiere.
- Todo lo que dice "Sistema anterior" está verificado contra `Resume/` (código y evidencia de BD reales).
- Todo lo que dice "Nuevo" solo se llena cuando el usuario lo define. Si no está definido, dice **POR DEFINIR**.
- Cada decisión se registra en la bitácora con su fecha.

---

## 1. Cómo funcionaba antes (verificado)

### 1.1 Mecánica de carga
Todo entraba por **CSV con delimitador `;`** (no Excel). Un menú web tenía una carpeta por dominio; cada una subía el archivo a disco y un script lo recorría fila por fila insertando en su tabla.

Características (todas verificadas en `Resume/*/07-cargas-masivas.md`):
- Un archivo CSV por tabla, con **nombre fijo** en el servidor (`BASE.csv`, `DECIL.csv`, …).
- **INSERT aditivo puro**: sin borrar antes, sin UPSERT → recargar el mismo archivo duplicaba filas.
- **Sin transacción**: un fallo a mitad dejaba carga parcial.
- **No se saltaba la fila de cabecera** → la cabecera entraba como fila basura.
- Sin autenticación, SQL por concatenación.
- Todo se unía por el **número de operación** (identificador del crédito).

### 1.2 Archivos y tablas destino

| Archivo CSV | Columnas | Tabla destino | Modo |
|---|---|---|---|
| BASE | operacion, cuenta, tcedula, tnombre, ttel1, ttel2, ccedula, cnombre, ctel1, ctel2, gcedula, gnombre, gtel1, gtel2, fvencimiento, fingreso, sucursal, dependencia, condicion, banco, referencia¹ | `t_base` | INSERT |
| ASIGNACION | operacion, asesor | `t_asignacion` | INSERT |
| CAMPANA | operacion, campana | `t_campana` | INSERT |
| CARTERA | operacion, cartera | `t_cartera` | INSERT |
| DECIL | operacion, decil | `t_decil` | INSERT |
| SALDOS | operacion, capital, total | `t_saldos` | INSERT |
| PROCESOS | operacion, estado, sub, fgestion, asesor | `t_procesos` | INSERT |
| TELEFONOS² | operacion, asesor, telefono, detalle | `t_telefonos` | INSERT |
| GESTIONES² | operacion, gestion | `t_gestiones` | INSERT (asesor fijo `MASIVO`) |
| MAIL² | operacion, mail | `t_email` | INSERT |
| REASIGNACION³ | operacion, asesor | `t_asignacion` | **UPDATE** del asesor |
| OCULTAR³ | operacion | `t_decil` | **DELETE** (ocultaba el crédito rompiendo el INNER JOIN) |

¹ `referencia` solo existe en CUMPLIR. ² Solo en CEGROUP. ³ Fuera del menú (URL directa).

### 1.3 Volumetría real (CUMPLIR)
`t_base` 50.381 · `t_asignacion` 58.648 · `t_procesos` 109.865 · `t_telefonos` 133.934 · `t_cartera` 56.444 · `t_campana` / `t_decil` / `t_saldos` 55.154. Los desajustes son consecuencia de las recargas duplicadas.

---

## 2. Cómo va a funcionar ahora

### 2.1 Decisiones tomadas (2026-07-11)

1. **Regla de nombres:** tablas y columnas **en inglés**, `snake_case` en la base de datos, `camelCase` en el código TypeScript.
2. **El cliente se separa del crédito.** En el sistema anterior `t_base` traía dentro del crédito los datos del titular, del codeudor y del segundo codeudor (columnas `tcedula/tnombre`, `ccedula/cnombre`, `gcedula/gnombre`). Ahora hay una tabla **`clients`** independiente: cada persona existe una sola vez, sin importar si es titular de un crédito o codeudora de otro. El vínculo con los créditos se define después.
3. **Correos y teléfonos son tablas propias**, con marca de "en uso" y de "principal".
4. **Se añade información que el sistema anterior no tenía:** vehículos, propiedades, histórico residencial y trabajos del cliente.
5. **Listas de opciones = tablas de referencia** (no enums): tipos de vehículo, tipos de propiedad, departamentos, ciudades, y todo lo que venga después.
   - **REGLA PERMANENTE — prefijo `types_`:** **toda** tabla de referencia o de tipos, sin excepción, se nombra con el prefijo `types_`. Así se reconoce de un vistazo qué es catálogo y qué es dato del negocio.
     `types_vehicles` · `types_properties` · `types_departments` · `types_cities` · `types_health_regimes` · `types_health_entities` · `types_health_affiliates` · `types_health_statuses` · `types_banks`
6. **REGLA PERMANENTE — auditoría en todas las tablas:** **toda** tabla, sin excepción (incluidas las de referencia), lleva `created_at` y `updated_at` (`timestamptz`). Fecha de creación y de última actualización siempre.
7. **Residencias:** la vigente se marca con `is_current`; las demás quedan como histórico.
8. **Placa de vehículo:** única por cliente (dos clientes distintos sí pueden tener la misma placa registrada).
9. **Salud:** una sola fila por cliente (el dato vigente, sin historial).

---

## 3. Nuevo modelo entidad-relación

> **Estado: APROBADO por el usuario el 2026-07-11.** Alcance: el bloque de clientes (16 tablas). El vínculo con los créditos se define aparte.
> Diagrama relacional interactivo: https://claude.ai/code/artifact/2ef7ed70-ca6b-49a4-81ae-91963ac6115f
> Los valores de las tablas `types_*` se cargan como datos y se ajustan cuando el usuario quiera. **Nunca son un bloqueante.**

### 3.1 Tablas de referencia

Toda tabla de referencia lleva el prefijo **`types_`**. Son **9**:

`types_departments` · `types_cities` · `types_vehicles` · `types_properties` · `types_health_regimes` · `types_health_entities` · `types_health_affiliates` · `types_health_statuses` · `types_banks`

#### Estructura estándar de una tabla `types_`

Salvo que se indique lo contrario, **todas** tienen exactamente estas columnas:

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `code` | varchar, único | identificador estable, no cambia |
| `label` | varchar | el texto que ve el usuario |
| `active` | boolean, default true | |
| `created_at` | timestamptz | |
| `updated_at` | timestamptz | |

Las dos únicas que se salen de la estructura estándar son `types_departments` y `types_cities`, porque llevan `name` en vez de `label` y `types_cities` cuelga de su departamento.

---

**`types_departments`** — departamentos

> **Por qué no `states`:** en inglés "state" significa tanto departamento como *estado/status*. Como después vienen las tablas de estados de crédito y de acuerdo, la palabra "status" queda reservada para eso.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `code` | varchar, único | código DANE |
| `name` | varchar | |
| `active` | boolean, default true | |
| `created_at` / `updated_at` | timestamptz | |

**`types_cities`** — ciudades / municipios
| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `department_id` | uuid FK → `types_departments` | |
| `code` | varchar, único | código DANE |
| `name` | varchar | |
| `active` | boolean, default true | |
| `created_at` / `updated_at` | timestamptz | |

**`types_vehicles`** — tipos de vehículo
| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `code` | varchar, único | |
| `label` | varchar | texto que ve el usuario |
| `active` | boolean, default true | |
| `created_at` / `updated_at` | timestamptz | |

**`types_properties`** — tipos de propiedad. Misma estructura que `types_vehicles` (incluida la auditoría).

> Los valores de estas tablas se cargan por datos, no por migración. **No bloquean el modelo.**

#### Valores de `types_vehicles` (investigados — se cargan como datos, se ajustan cuando se quiera)

Fuentes: Ley 769 de 2002 art. 2 (Código Nacional de Tránsito) y datos abiertos oficiales de `datos.gov.co` con el parque automotor registrado en RUNT (Risaralda: 453.925 vehículos, 18 clases; otro municipal: 177.969 vehículos, 13 clases). Ambos coinciden en los nombres de clase.

| `code` | `label` | Peso en el parque real |
|---|---|---|
| `MOTOCICLETA` | Motocicleta | 55,5% |
| `AUTOMOVIL` | Automóvil | 24,3% |
| `CAMIONETA` | Camioneta | 11,3% |
| `CAMPERO` | Campero | 5,2% |
| `CAMION` | Camión | 1,4% |
| `MOTOCARRO` | Motocarro | 0,8% |
| `VOLQUETA` | Volqueta | 0,4% |
| `MICROBUS` | Microbús | 0,4% |
| `BUS` | Bus | 0,3% |
| `TRACTOCAMION` | Tractocamión | 0,2% |
| `BUSETA` | Buseta | 0,2% |
| `CUATRIMOTO` | Cuatrimoto | 0,1% |
| `CICLOMOTOR` | Ciclomotor | 0,1% |
| `MOTOTRICICLO` | Mototriciclo | — |
| `OTRO` | Otro | — |

Las cuatro primeras concentran el 96,3% del parque.

**Idioma de los códigos:** la regla del inglés aplica a **tablas y columnas**, no a los datos. `CAMPERO`, `VOLQUETA` y `TRACTOCAMION` son términos legales colombianos sin equivalente en inglés; el `code` conserva el nombre oficial de la clase.

#### Valores de `types_properties`
Se definen más adelante. Base mencionada por el usuario: casa, apartamento, lote. **Se cargan como datos: no bloquean nada.**


---

### 3.2 `clients` — cliente (titular o codeudor, indistinto)

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `document_type` | varchar | `CC` / `CE` / `PA` (mismo juego que `users`) |
| `document_number` | varchar | |
| `full_name` | varchar | nombres y apellidos en un solo campo |
| `created_at` / `updated_at` | timestamptz | |

**Único:** `(document_type, document_number)`.

> **Sin campo `active`.** El cliente no inicia sesión ni se suspende: no hay razón de negocio para desactivarlo. La columna existió por error (copiada de `users`, donde sí tiene sentido) y **el usuario la mandó quitar el 2026-07-11**. No volver a agregarla.

---

### 3.3 `client_emails`

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `email` | varchar | |
| `description` | varchar, nullable | |
| `in_use` | boolean, default true | |
| `is_primary` | boolean, default false | |
| `created_at` / `updated_at` | timestamptz | |

**Índice único parcial:** un solo `is_primary = true` por cliente.

---

### 3.4 `client_phones`

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `phone_number` | varchar(10) | 10 dígitos |
| `description` | varchar, nullable | |
| `in_use` | boolean, default true | |
| `is_primary` | boolean, default false | |
| `created_at` / `updated_at` | timestamptz | |

**Índice único parcial:** un solo `is_primary = true` por cliente.

---

### 3.5 `client_vehicles`

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `type_vehicle_id` | uuid FK → `types_vehicles` | |
| `brand` | varchar | marca |
| `model` | varchar | modelo |
| `plate` | varchar | placa |
| `description` | text, nullable | |
| `created_at` / `updated_at` | timestamptz | |

**Único:** `(client_id, plate)`.

---

### 3.6 `client_properties`

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `type_property_id` | uuid FK → `types_properties` | |
| `department_id` | uuid FK → `types_departments` | departamento |
| `city_id` | uuid FK → `types_cities` | ciudad |
| `neighborhood` | varchar | barrio o vereda |
| `address` | text | dirección o indicaciones |
| `description` | text, nullable | |
| `created_at` / `updated_at` | timestamptz | |

---

### 3.7 `client_residences` — histórico residencial

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `is_abroad` | boolean, default false | fuera del país |
| `department_id` | uuid FK → `types_departments`, **nullable** | null si `is_abroad` |
| `city_id` | uuid FK → `types_cities`, **nullable** | null si `is_abroad` |
| `neighborhood` | varchar, **nullable** | null si `is_abroad` |
| `address` | text, **nullable** | null si `is_abroad` |
| `description` | text, nullable | |
| `is_current` | boolean, default false | residencia vigente |
| `created_at` / `updated_at` | timestamptz | |

**Reglas:**
- Si `is_abroad = false` → `department_id`, `city_id`, `neighborhood` y `address` son obligatorios.
- Si `is_abroad = true` → esos campos van en null; solo se guarda la descripción.
- Índice único parcial: una sola residencia con `is_current = true` por cliente.

---

### 3.8 `client_jobs` — trabajos del cliente

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `department_id` | uuid FK → `types_departments` | departamento |
| `city_id` | uuid FK → `types_cities` | ciudad |
| `neighborhood` | varchar | barrio o vereda |
| `address` | text | dirección o indicaciones |
| `employer_name` | varchar | nombre de la empresa o del empleador |
| `employer_phone` | varchar | teléfono de la empresa o del empleador |
| `salary` | numeric(14,2) | salario |
| `is_active` | boolean, default true | ¿el cliente sigue activo en ese trabajo? |
| `created_at` / `updated_at` | timestamptz | |

Un cliente puede tener varios trabajos. `is_active` distingue el vigente del histórico.

---

### 3.9 `client_health` — afiliación a salud

**Una sola fila por cliente: el dato vigente.** Sin historial (decisión del usuario). Estructura basada en la **BDUA** (Base de Datos Única de Afiliados, Ministerio de Salud — datos.gov.co, 23,1 millones de afiliados del régimen contributivo). Los campos son los que usa el Estado, no una invención.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients`, **único** | ON DELETE CASCADE. Uno a uno con el cliente. |
| `type_health_regime_id` | uuid FK → `types_health_regimes` | Contributivo / Subsidiado / … |
| `type_health_entity_id` | uuid FK → `types_health_entities` | La EPS |
| `type_health_affiliate_id` | uuid FK → `types_health_affiliates` | Cotizante / Beneficiario / … |
| `type_health_status_id` | uuid FK → `types_health_statuses` | Activo / Protección laboral / … |
| `sisben_level` | varchar, nullable | Solo aplica al régimen subsidiado |
| `created_at` / `updated_at` | timestamptz | |

#### Tablas de referencia de salud

Las cuatro tienen la misma estructura: `id`, `code`, `label`, `active`, `created_at`, `updated_at`.

**`types_health_regimes`** — valores reales de la BDUA: `CONTRIBUTIVO`, `SUBSIDIADO`. El SGSSS además contempla `ESPECIAL` y `EXCEPCION` (magisterio, fuerzas militares, Ecopetrol).

**`types_health_affiliates`** — `COTIZANTE` (14,4 M), `BENEFICIARIO` (8,6 M), `ADICIONAL` (68 mil), `CABEZA_DE_FAMILIA` (solo en subsidiado).

**`types_health_statuses`** — `ACTIVO`, `PROTECCION_LABORAL`, `RETIRADO`.

**`types_health_entities`** — las EPS, con su código oficial: `EPS010` Sura, `EPS005` Sanitas, `EPS037` Nueva EPS, `EPS002` Salud Total… Son **32 en el contributivo y 29 en el subsidiado**.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `code` | varchar, único | Código oficial (`EPS010`) |
| `label` | varchar | Nombre de la EPS |
| `active` | boolean, default true | |
| `created_at` / `updated_at` | timestamptz | |

> Los valores se cargan como datos. No bloquean nada.

---

## 3B. Bloque de créditos

> **Estado: APROBADO por el usuario el 2026-07-11.** Alcance: la tabla `credits`. Lo demás del crédito (saldos, campaña, cartera, decil, procesos, gestiones) se define después.
> Vista gráfica: https://claude.ai/code/artifact/4f5159ed-021a-4624-8b23-e89b8ec8c852

### 3B.1 `credits` — el crédito

Reemplaza a `t_base`. **De 21 columnas a 11.** Las doce columnas de personas del sistema viejo (`tcedula`/`tnombre`/`ttel1`/`ttel2`, `ccedula`/…, `gcedula`/…) desaparecen: ahora son dos campos que apuntan a `clients`.

| Columna | Tipo | Nota |
|---|---|---|
| `operation` | varchar **PK** | **La llave.** Columna generada: `credit_number` ⧺ `bank_code`. Ver abajo. |
| `credit_number` | varchar | El número del crédito |
| `bank_code` | varchar FK → `types_banks(code)` | El código del banco |
| `holder_client_id` | uuid FK → `clients` | El titular de la deuda |
| `codebtor_client_ids` | uuid[] | Los codeudores: array de ids de `clients` |
| `due_date` | date | Fecha de vencimiento |
| `entry_date` | date | Fecha de ingreso |
| `branch` | varchar | Sucursal |
| `dependency` | varchar | Dependencia |
| `created_at` / `updated_at` | timestamptz | |

**Único:** `(credit_number, bank_code)` — implícito, porque `operation` es su concatenación y es la PK.

#### La operación

Es la **concatenación** de crédito y banco, en ese orden. **No es una suma.**

```
credit_number = 342312
bank_code     = 20098
operation     = 34231220098
```

**La calcula la base de datos, no la aplicación:**

```sql
operation varchar GENERATED ALWAYS AS (credit_number || bank_code) STORED PRIMARY KEY
```

Nadie puede escribirla a mano ni equivocarse al concatenar. Si cambia el crédito o el banco, se recalcula sola.

**Dos consecuencias de diseño, forzadas por esa decisión:**

1. **`bank_code` vive dentro de `credits`, no como `type_bank_id`.** Postgres solo puede generar una columna a partir de columnas de su propia fila — no puede ir a buscar el código a otra tabla. Por eso el código se guarda aquí, y además apunta a `types_banks(code)` para garantizar que el banco existe.
2. **`operation` y `credit_number` son texto, no número.** Si un crédito empieza por cero (`0342312`), guardarlo como número se come el cero y la operación sale mal. El sistema viejo usaba `bigint` en `t_base.operacion`: ese riesgo estaba ahí.

#### `types_banks` — los bancos

Estructura estándar de una tabla `types_`: `id`, `code` (único), `label`, `active`, `created_at`, `updated_at`. El `code` es el código del banco que entra en la operación.

#### Nota abierta sobre `codebtor_client_ids`

Va como **array**, según lo pidió el usuario. Consecuencia real, dejada por escrito para que nadie se sorprenda después: **Postgres no valida un `uuid[]` contra `clients`.** Si un id se borra o alguien mete uno inventado, ahí se queda sin que nadie avise — es el mismo tipo de agujero que tenía el sistema viejo, donde `operacion` tampoco era llave foránea y por eso borrar un crédito dejaba huérfanos en 13 tablas. La alternativa (tabla puente `credit_codebtors`) sí lo garantiza y permite responder "¿en qué créditos aparece esta persona?" de forma directa. **El usuario decidirá si quiere cambiarlo.**

---

### 3B.2 Las 6 tablas que cuelgan del crédito

> **Estado: APROBADAS por el usuario el 2026-07-11.**
> Vista gráfica: https://claude.ai/code/artifact/5784efcd-06e2-4ab1-aa2c-443344c11d26

Son las mismas seis del sistema viejo, con tres cambios: **sin el prefijo `t_`**, **nombres en inglés** (tabla y columnas), y **la operación entra como `credit_number` + `bank_code`**.

#### Las tres columnas comunes a las seis

Idénticas en todas. La operación no se escribe a mano: la genera la base de datos.

| Columna | Tipo | Nota |
|---|---|---|
| `operation` | varchar **PK**, **FK** → `credits(operation)` | **Generada:** `GENERATED ALWAYS AS (credit_number \|\| bank_code) STORED` |
| `credit_number` | varchar | Número del crédito |
| `bank_code` | varchar | Código del banco |

Más `created_at` y `updated_at` en todas, por la regla permanente.

#### Tabla por tabla

| Tabla nueva | Antes | Columnas propias | Tipo |
|---|---|---|---|
| `assignments` | `t_asignacion` | `advisor` | varchar(30) |
| `campaigns` | `t_campana` | `campaign` | varchar(30) |
| `portfolios` | `t_cartera` | `portfolio` | varchar(30) |
| `deciles` | `t_decil` | `decile` | varchar(10) |
| `balances` | `t_saldos` | `capital` · `total` | **numeric(14,2)** |
| `processes` | `t_procesos` | `status` · `sub_status` · `management_date` · `advisor` | varchar(60) · varchar(60) · date · varchar(30) |

**Traducción de columnas:** `asesor` → `advisor` · `campana` → `campaign` · `cartera` → `portfolio` · `decil` → `decile` · `estado` → `status` · `sub` → `sub_status` · `fgestion` → `management_date`.

#### `balances`: de texto a numérico (aprobado con evidencia)

En el sistema viejo `t_saldos.capital` y `t_saldos.total` eran **`varchar(20)`** — texto. El CSV traía coma decimal (`11349632,4`) y se guardaba tal cual, así que cualquier `SUM()` era dudoso. En la muestra de CUMPLIR incluso aparece una fila con `capital = "SALDO CAPITAL"`: la cabecera del CSV metida como dato.

Ahora es **`numeric(14,2)`**. Verificado en Postgres, no supuesto:

- **`numeric` es decimal exacto, no punto flotante.** `9000000,90` se guarda como `9000000.90`, sin redondeo ni pérdida del cero final.
- **El punto flotante sí falla:** `0.1 + 0.2` da `0.3` con `numeric`, pero `0.30000000000000004` con `float`. Por eso **nunca** se usa `float` para plata.
- Los datos reales del sistema viejo traen **exactamente 2 decimales** (`2681441.76`, `2226296.21`, `13722518.32`). `numeric(14,2)` los preserva todos.
- Si algún día llega un valor con más de 2 decimales, se redondearía. No ocurre en los datos actuales; si llegara a pasar, se cambia a `numeric(14,4)`.
- **Efecto colateral bueno:** la fila basura de la cabecera ya no puede entrar. La base la rechaza.

---

## 3C. Diagrama del bloque de clientes

### 3.10 Diagrama

Cada tabla aparece **una sola vez**. Cada línea es una llave foránea: sale de la tabla hija y apunta a la tabla padre.

**22 llaves foráneas construidas** en Postgres (verificado en `pg_constraint` el 2026-07-14), más las **6 de `operation`**, que están aprobadas en §3B.2 pero **no construidas**. La línea de `codebtor_client_ids` no es una llave foránea: es un array, y Postgres no lo valida contra `clients` (ver la nota abierta en §3B.1).

```
client_emails      ── client_id ─────────► clients
client_phones      ── client_id ─────────► clients

client_vehicles    ── client_id ─────────► clients
                   ── type_vehicle_id ───► types_vehicles

client_properties  ── client_id ─────────► clients
                   ── type_property_id ──► types_properties
                   ── department_id ─────► types_departments
                   ── city_id ───────────► types_cities

client_residences  ── client_id ─────────► clients
                   ── department_id ─────► types_departments
                   ── city_id ───────────► types_cities

client_jobs        ── client_id ─────────► clients
                   ── department_id ─────► types_departments
                   ── city_id ───────────► types_cities

client_health      ── client_id ─────────► clients   (uno a uno)
                   ── type_health_regime_id ────► types_health_regimes
                   ── type_health_entity_id ────► types_health_entities
                   ── type_health_affiliate_id ─► types_health_affiliates
                   ── type_health_status_id ────► types_health_statuses

types_cities       ── department_id ─────► types_departments

credits            ── bank_code ──────────► types_banks (code)
                   ── holder_client_id ───► clients
                   ── codebtor_client_ids ► clients   (array, sin FK real)

assignments        ── operation ──────────► credits   (aprobada, NO construida)
campaigns          ── operation ──────────► credits   (aprobada, NO construida)
portfolios         ── operation ──────────► credits   (aprobada, NO construida)
deciles            ── operation ──────────► credits   (aprobada, NO construida)
balances           ── operation ──────────► credits   (aprobada, NO construida)
processes          ── operation ──────────► credits   (aprobada, NO construida)
```

Versión interactiva (columnas, llaves y notación uno-a-muchos): https://claude.ai/code/artifact/2ef7ed70-ca6b-49a4-81ae-91963ac6115f

> **Las 6 llaves foráneas hacia `credits` están aprobadas pero no existen en Postgres.** Las seis tablas hijas comparten la columna generada `operation`, pero la base no verifica que el crédito exista: nada impide una fila hija huérfana. Pendiente de decisión en `questions.md`.

> El vínculo `clients` ↔ crédito **ya está definido y construido**: `credits.holder_client_id` (FK real → `clients`) y `credits.codebtor_client_ids` (array `uuid[]`, sin FK). Ver §3B.1.

---

## 3D. `users` — usuarios del sistema

> **Esta tabla existe en la base desde antes de este documento** (módulos `api/src/modules/users` y `api/src/modules/auth`). Nunca se definió aquí. Se registra el 2026-07-14 leyendo Postgres y `user.entity.ts`. **No es una decisión de modelo: es el estado actual, escrito tal cual.**

Es el usuario que **inicia sesión**. No es el cliente ni el deudor — esos son `clients`.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `username` | varchar, **único** | |
| `password` | varchar | |
| `full_name` | varchar | |
| `email` | varchar, **único** | |
| `document_type` | varchar, nullable | `CC` / `CE` / `PA` |
| `document_number` | varchar, **único**, nullable | |
| `phone` | varchar, **único**, nullable | |
| `gender` | varchar, nullable | |
| `user_type` | varchar | El rol. Valores abajo. |
| `active` | boolean, default true | |
| `must_change_password` | boolean, default false | |
| `password_reset_otp` | varchar, nullable | |
| `password_reset_otp_expires_at` | timestamptz, nullable | |
| `created_at` / `updated_at` | **timestamp sin zona horaria** | Ver la desviación, abajo. |

**Por qué `users` sí lleva `active` y `clients` no:** el usuario inicia sesión y se puede suspender. El cliente no. Es la misma razón que ya está escrita en §3.2.

#### Valores que declara el código

Son los literales de `api/src/modules/users/user.entity.ts`. **Qué hace cada rol no está definido en ninguna parte** — está preguntado en `questions.md`.

- `user_type` (6): `SUPER` · `ADMINISTRATOR` · `COORDINATOR` · `ADVISOR` · `LAWYER` · `LAWYER_ASSISTANT`
- `document_type` (3): `CC` · `CE` · `PA` — el mismo juego que `clients`
- `gender` (2): `MALE` · `FEMALE`

#### Desviación de la regla permanente

La regla del §2.1 punto 6 exige `created_at` y `updated_at` como **`timestamptz`** en **toda** tabla, sin excepción. En `users` son **`timestamp` sin zona horaria**. Es la única tabla de las 25 que la incumple.

Se deja escrito como hecho verificado. **Corregirlo cambia la base y requiere autorización del usuario.**

---

## 4. Bitácora de decisiones

| Fecha | Decisión | Quién |
|---|---|---|
| 2026-07-11 | Se crea este documento como fuente única del modelo de datos. | Usuario |
| 2026-07-11 | Tablas y columnas en inglés (regla permanente). | Usuario |
| 2026-07-11 | `clients` separado del crédito; una persona existe una sola vez. | Usuario |
| 2026-07-11 | Correos, teléfonos, vehículos, propiedades y residencias como tablas propias del cliente. | Usuario |
| 2026-07-11 | Se agrega **`client_jobs`** (trabajos): ubicación, empleador, teléfono del empleador, salario y si sigue activo en ese trabajo. | Usuario |
| 2026-07-11 | Se agrega **`client_health`** (afiliación a salud), estructurada según la BDUA oficial. **Solo el dato vigente, sin historial** (uno a uno con el cliente). Las EPS van en `types_health_entities`. | Usuario |
| 2026-07-11 | Listas de opciones (tipos de vehículo/propiedad, departamentos, ciudades) como **tablas de referencia**, no enums. | Usuario |
| 2026-07-11 | Residencia vigente marcada con `is_current`. | Usuario |
| 2026-07-11 | Placa de vehículo única **por cliente**. | Usuario |
| 2026-07-11 | **REGLA PERMANENTE:** **toda** tabla de referencia o de tipos, sin excepción, lleva el prefijo `types_`. Incluye `types_departments` y `types_cities`. | Usuario |
| 2026-07-11 | **REGLA PERMANENTE:** **toda** tabla, sin excepción (también las de referencia), lleva `created_at` y `updated_at`. | Usuario |
| 2026-07-11 | **`credits` APROBADA.** Reemplaza a `t_base`: de 21 columnas a 11. Las personas salen del crédito y apuntan a `clients`. | Usuario |
| 2026-07-11 | **La operación es la PK de `credits`** y es la **concatenación** `credit_number` ⧺ `bank_code` (en ese orden, no una suma). La calcula la base como columna generada. Por eso `bank_code` vive dentro de `credits` y todo es texto, no número. | Usuario |
| 2026-07-11 | Los codeudores van como **array** `uuid[]` (decisión del usuario). Queda por escrito que Postgres no valida arrays contra `clients`. | Usuario |
| 2026-07-11 | **APROBADAS las 6 tablas del crédito:** `assignments`, `campaigns`, `portfolios`, `deciles`, `balances`, `processes`. Sin prefijo `t_`, nombres en inglés, y las tres columnas de la operación (`operation` generada + `credit_number` + `bank_code`) en todas. | Usuario |
| 2026-07-11 | **`balances.capital` y `balances.total` pasan de `varchar` a `numeric(14,2)`.** Verificado en Postgres: `numeric` es decimal exacto, conserva `9000000,90` sin redondear. **Nunca usar `float` para plata** (`0.1+0.2 = 0.30000000000000004`). | Usuario |
| 2026-07-11 | **MODELO DE CLIENTES APROBADO** (16 tablas). | Usuario |
| 2026-07-11 | Los valores de las tablas `types_*` son datos, no estructura: se ajustan después y **jamás bloquean** el avance. | Usuario |
| 2026-07-11 | **`clients` NO lleva campo `active`.** Se agregó por error copiando el patrón de `users`; el usuario nunca lo pidió y no hay razón de negocio. Eliminado de la entidad, del seed y de la base. | Usuario |
| 2026-07-11 | `states` → **`types_departments`** (y `state_id` → `department_id`). "State" en inglés significa también "estado/status" y chocaría con los futuros estados de crédito y de acuerdo. | Usuario |
| 2026-07-14 | Se documenta **`users`** (§3D). Existía en la base y en el código, pero este documento nunca la había definido: eran 24 tablas descritas de las 25 que hay. **No es una decisión nueva: es el estado actual, leído de Postgres y de `user.entity.ts`.** | Revisión contra Postgres |
| 2026-07-14 | Se registra que **`users` incumple la regla permanente de auditoría**: sus `created_at`/`updated_at` son `timestamp` sin zona horaria, no `timestamptz`. Única tabla de 25. **No se corrige: cambiar la base requiere autorización.** | Revisión contra Postgres |
| 2026-07-14 | §3.10 decía "Son 20 relaciones". El número era falso: Postgres tiene **22 llaves foráneas construidas**, más las 6 de `operation` aprobadas y no construidas. Corregido. | Revisión contra Postgres |
| 2026-07-14 | §3.10 decía que el vínculo `clients` ↔ crédito estaba "POR DEFINIR", contradiciendo a §3B.1, que lo define y que la base **sí** tiene construido (`holder_client_id`). Se elimina la contradicción. | Revisión contra Postgres |
| 2026-07-14 | Se marca en el diagrama que **las 6 llaves foráneas `operation` → `credits` están aprobadas pero NO construidas**. Nada impide hoy una fila hija huérfana. Pendiente en `questions.md`. | Revisión contra Postgres |
