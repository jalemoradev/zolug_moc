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
     La lista vigente está en §3.1.
6. **REGLA PERMANENTE — auditoría en todas las tablas:** **toda** tabla, sin excepción (incluidas las de referencia), lleva `created_at` y `updated_at` (`timestamptz`). Fecha de creación y de última actualización siempre.
7. **Residencias:** la vigente se marca con `is_current`; las demás quedan como histórico.
8. **Placa de vehículo:** única por cliente (dos clientes distintos sí pueden tener la misma placa registrada).
9. ~~**Salud:** una sola fila por cliente (el dato vigente, sin historial).~~ **Reemplazado el 2026-07-19:** `client_health` lleva historial de EPS, con la vigente marcada por `is_current` (§3.9).

---

## 3. Nuevo modelo entidad-relación

> **Estado: APROBADO por el usuario el 2026-07-11.** Alcance: el bloque de clientes (**15 tablas**: `clients` + 7 tablas `client_*` + 7 tablas `types_`). El vínculo con los créditos se define aparte.
> Diagrama relacional interactivo: https://claude.ai/code/artifact/2ef7ed70-ca6b-49a4-81ae-91963ac6115f
> Los valores de las tablas `types_*` se cargan como datos y se ajustan cuando el usuario quiera. **Nunca son un bloqueante.**

### 3.1 Tablas de referencia

Toda tabla de referencia lleva el prefijo **`types_`**. Son **15**:

`types_departments` · `types_cities` · `types_vehicles` · `types_properties` · `types_health_regimes` · `types_health_entities` · `types_health_statuses` · `types_banks` · `types_management_types` · `types_management_roles` · `types_agreement_statuses` · `types_credit_statuses` · `types_credit_sub_statuses` · `types_condonation_statuses` · `types_condonation_sub_statuses`

Siete se definen en sus propios bloques: `types_management_types` y `types_management_roles` en §3E.2, `types_agreement_statuses` en §3G.5, `types_credit_statuses` con `types_credit_sub_statuses` en §3B.2, y `types_condonation_statuses` con `types_condonation_sub_statuses` en §3H.4.

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

Se salen de la estructura estándar: `types_departments` y `types_cities`, porque llevan `name` en vez de `label` y `types_cities` cuelga de su departamento; y `types_credit_sub_statuses`, que cuelga de `types_credit_statuses` (§3B.2).

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

**Regla:** un solo correo marcado como principal por cliente.

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

**Regla:** un solo teléfono marcado como principal por cliente.

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
- Una sola residencia marcada como vigente por cliente.

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

**Con historial.** Un cliente puede haber estado afiliado a varias EPS a lo largo del tiempo; se guardan todas y una se marca como la vigente con `is_current`, igual que en `client_residences` (§3.7).

Cuatro datos, definidos por el usuario: **a qué EPS está registrado**, **si es contributivo o subsidiado**, **si está activo o inactivo**, y **si es la actual**.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `client_id` | uuid FK → `clients` | ON DELETE CASCADE |
| `type_health_entity_id` | uuid FK → `types_health_entities` | La EPS |
| `type_health_regime_id` | uuid FK → `types_health_regimes` | Contributivo / Subsidiado |
| `type_health_status_id` | uuid FK → `types_health_statuses` | Activo / Inactivo |
| `is_current` | boolean, default false | La afiliación vigente |
| `created_at` / `updated_at` | timestamptz | |

**Regla:** una sola afiliación marcada como vigente por cliente.

#### Tablas de referencia de salud

Las tres tienen la estructura estándar `types_`: `id`, `code`, `label`, `active`, `created_at`, `updated_at`.

| Tabla | Qué guarda |
|---|---|
| `types_health_entities` | Las EPS |
| `types_health_regimes` | Contributivo / Subsidiado |
| `types_health_statuses` | Activo / Inactivo |

> Los valores se cargan como datos. No bloquean nada.

---

## 3B. Bloque de créditos

> **Estado: APROBADO por el usuario el 2026-07-11.** Alcance: la tabla `credits`. Lo demás del crédito (saldos, campaña, cartera, decil, procesos, gestiones) se define después.
> Vista gráfica: https://claude.ai/code/artifact/4f5159ed-021a-4624-8b23-e89b8ec8c852

### 3B.1 `credits` — el crédito

Reemplaza a `t_base`. **De 21 columnas a 11.** Las doce columnas de personas del sistema viejo (`tcedula`/`tnombre`/`ttel1`/`ttel2`, `ccedula`/…, `gcedula`/…) desaparecen: ahora son dos campos que apuntan a `clients`.

| Columna | Tipo | Nota |
|---|---|---|
| `operation` | varchar **PK** | **La llave.** Es `credit_number` ⧺ `bank_code`. Ver abajo. |
| `credit_number` | varchar | El número del crédito |
| `bank_code` | varchar FK → `types_banks(code)` | El código del banco |
| `holder_client_id` | uuid FK → `clients` | El titular de la deuda |
| `codebtor_client_ids` | uuid[] | Los codeudores: array de ids de `clients` |
| `due_date` | date | Fecha de vencimiento |
| `entry_date` | date | Fecha de ingreso |
| `branch` | varchar | **La oficina** a la que pertenece el crédito |
| `dependency` | varchar | **La región** a la que pertenece el crédito |
| `created_at` / `updated_at` | timestamptz | |

**Único:** `(credit_number, bank_code)` — implícito, porque `operation` es su concatenación y es la PK.

#### La operación

Es la **concatenación** de crédito y banco, en ese orden. **No es una suma.**

```
credit_number = 342312
bank_code     = 20098
operation     = 34231220098
```

**`operation` no se escribe a mano: sale siempre de concatenar `credit_number` y `bank_code`.** Si cambia cualquiera de los dos, la operación cambia con ellos.

**Dos consecuencias de diseño, forzadas por esa decisión:**

1. **`bank_code` vive dentro de `credits`, no como `type_bank_id`.** La operación se arma con el código del banco, así que el código tiene que estar en la misma fila. Apunta a `types_banks(code)` para garantizar que el banco existe.
2. **`operation` y `credit_number` son texto, no número.** Si un crédito empieza por cero (`0342312`), guardarlo como número se come el cero y la operación sale mal. El sistema viejo usaba `bigint` en `t_base.operacion`: ese riesgo estaba ahí.

#### `types_banks` — los bancos

Estructura estándar de una tabla `types_`: `id`, `code` (único), `label`, `active`, `created_at`, `updated_at`. El `code` es el código del banco que entra en la operación.

#### Nota abierta sobre `codebtor_client_ids`

Va como **array `uuid[]`**, decisión del usuario, **ratificada el 2026-07-19**. Un crédito puede tener uno o varios codeudores y el array no impone límite de cuántos.

Consecuencia, dejada por escrito: **un array no es una llave foránea.** El vínculo con `clients` no queda garantizado por el modelo.

---

### 3B.2 Las 6 tablas que cuelgan del crédito

> **Estado: APROBADAS por el usuario el 2026-07-11 y REDEFINIDAS el 2026-07-19**, cuando el usuario dictó qué es cada una. En esa revisión cambiaron los tipos y las columnas de cinco de las seis.
> Vista gráfica: https://claude.ai/code/artifact/5784efcd-06e2-4ab1-aa2c-443344c11d26 — **desactualizada**: es anterior a la revisión del 2026-07-19.

Son las mismas seis del sistema viejo, con tres cambios: **sin el prefijo `t_`**, **nombres en inglés** (tabla y columnas), y **la operación entra como `credit_number` + `bank_code`**.

#### Las tres columnas comunes a las seis

Idénticas en todas. La operación no se escribe a mano: la genera la base de datos.

| Columna | Tipo | Nota |
|---|---|---|
| `operation` | varchar **PK**, **FK** → `credits(operation)` | `credit_number` ⧺ `bank_code` |
| `credit_number` | varchar | Número del crédito |
| `bank_code` | varchar | Código del banco |

Más `created_at` y `updated_at` en todas, por la regla permanente.

#### Tabla por tabla

| Tabla nueva | Antes | Columnas propias | Tipo |
|---|---|---|---|
| `assignments` | `t_asignacion` | `advisor_user_id` | uuid FK → `users` |
| `campaigns` | `t_campana` | `campaign` | **text** (sin límite de largo) |
| `portfolios` | `t_cartera` | `portfolio` | **text** (sin límite de largo) |
| `deciles` | `t_decil` | `decile` | **int**, del 1 al 10 |
| `balances` | `t_saldos` | `capital` · `total` | **numeric(14,2)**, ambas NOT NULL |
| `processes` | `t_procesos` | `type_credit_status_id` · `type_credit_sub_status_id` · `management_date` · `advisor_user_id` | uuid FK → `types_credit_statuses` · uuid FK → `types_credit_sub_statuses` · date · uuid FK → `users` — **las cuatro nulables** |

**Traducción de columnas:** `asesor` → `advisor_user_id` (ya no es texto: es llave foránea a `users`) · `campana` → `campaign` · `cartera` → `portfolio` · `decil` → `decile` · `estado` → `status` · `sub` → `sub_status` · `fgestion` → `management_date`.

#### Qué es cada tabla

**`assignments` — asignaciones.** Dice **a qué asesor está asignado el crédito**. Un asesor tiene muchos créditos asignados; un crédito tiene un solo asesor — por eso `operation` es la llave primaria.

**`campaigns` — campañas.** Guarda **el nombre de la campaña de descuento** a la que pertenece el crédito. Un crédito **puede no tener campaña**: en ese caso no existe fila en esta tabla.

**`portfolios` — carteras.** Guarda **la cartera específica** a la que pertenece el crédito. Igual que la campaña, un crédito **puede no pertenecer a ninguna**: en ese caso no existe fila.

> **Ni campañas ni carteras tienen catálogo** (decisión del usuario, 2026-07-19). En ambas el nombre es texto y **no lleva límite de largo**: `text`, no `varchar(n)`.

**`deciles` — deciles.** Guarda **la prioridad de cobro del crédito, del 1 al 10**. Un crédito **puede no tener valoración**: en ese caso no existe fila.

> **Es un número, no texto.** Solo admite valores del 1 al 10. En el sistema viejo era `varchar(10)` y por eso `t_decil.decil` de CEGROUP terminó con **79 valores distintos**, incluida la cabecera `DECIL` cargada como dato.

**`balances` — saldos.** Guarda dos valores del crédito: **`capital` es el saldo de capital** y **`total` es el saldo total**. Nada más.

> **Por qué solo esos dos.** Toda la cartera de este negocio es **cartera castigada**: crédito que el banco entrega a la casa de cobranza porque no fue posible cobrarlo de manera regular. Por eso **no se llevan días de mora ni nada de eso** — solo saldo capital y saldo total.

> **Obligatoria para todo crédito.** A diferencia de campaña, cartera y decil, aquí **no existe el caso "sin fila"**: absolutamente todos los créditos tienen saldo capital y saldo total, **así sea en 0** cuando se termina de pagar. Las dos columnas van `NOT NULL`. Que la fila exista para cada crédito **se garantiza al crear o actualizar los datos de forma masiva**, no con una restricción del modelo.

**`processes` — procesos.** Guarda **el último estado**, **el último sub-estado** y **el último asesor que gestionó o realizó un proceso** sobre el crédito. Es la foto del momento, no un histórico.

Y **`management_date`**: la fecha en que se gestionó por última vez.

> **Las cuatro son nulables.** Un crédito recién ingresado **puede no tener estado, ni sub-estado, ni asesor**: todavía no se le ha realizado ningún proceso que permita calificarlo, y por lo mismo ningún asesor lo ha gestionado.

> **Estado y sub-estado van con catálogo** (decisión del usuario, 2026-07-19): `types_credit_statuses` y `types_credit_sub_statuses`, con llave foránea. No son texto libre. En el sistema viejo los catálogos existían (`t_estados`, `t_subs`) pero no se aplicaban como restricción, y por eso `t_procesos.estado` de CEGROUP terminó con **23 valores distintos** mezclando estados de cobranza, tipos de proceso jurídico y basura de un solo carácter (`0`, `D`, `S`, `X`).

#### Cada sub-estado pertenece a un estado

Regla del usuario: **un sub-estado siempre pertenece a un estado**. `FALLECIDO` es de `RENUENTE`; no puede aparecer bajo `ACUERDO`.

**`types_credit_statuses`** — estructura estándar `types_`.

**`types_credit_sub_statuses`** — estructura estándar **más el estado al que pertenece**:

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `type_credit_status_id` | uuid FK → `types_credit_statuses` | El estado dueño de este sub-estado |
| `code` | varchar, único | |
| `label` | varchar | |
| `active` | boolean, default true | |
| `created_at` / `updated_at` | timestamptz | |

**Reglas de la pareja estado + sub-estado en `processes`:**

| estado | sub-estado | ¿Válido? |
|---|---|---|
| `RENUENTE` | `FALLECIDO` (que pertenece a RENUENTE) | Sí |
| `ACUERDO` | `FALLECIDO` (que pertenece a RENUENTE) | **No** — el sub-estado no es de ese estado |
| `ACUERDO` | vacío | Sí — calificado, sin sub-estado |
| vacío | vacío | Sí — crédito recién ingresado |
| vacío | `FALLECIDO` | **No** — no hay sub-estado sin estado |

#### `balances`: de texto a numérico (aprobado con evidencia)

En el sistema viejo `t_saldos.capital` y `t_saldos.total` eran **`varchar(20)`** — texto. El CSV traía coma decimal (`11349632,4`) y se guardaba tal cual, así que cualquier `SUM()` era dudoso. En la muestra de CUMPLIR incluso aparece una fila con `capital = "SALDO CAPITAL"`: la cabecera del CSV metida como dato.

Ahora es **`numeric(14,2)`**:

- Es **decimal exacto**, no punto flotante: `9000000,90` se conserva tal cual. **Nunca `float` para plata.**
- Los datos reales del sistema viejo traen **exactamente 2 decimales** (`2681441.76`, `2226296.21`, `13722518.32`).
- Si algún día llegara un valor con más de 2 decimales, se cambia a `numeric(14,4)`.

---

## 3C. Diagrama del modelo completo

### 3.10 Diagrama

Cada línea es una llave foránea: sale de la tabla hija y apunta a la tabla padre. Las tablas padre aparecen tantas veces como llaves las apunten — `clients`, por ejemplo, es destino de once.

**Ninguna de estas llaves foráneas está construida: no existe base de datos.** Este diagrama es el diseño, no el reflejo de algo implementado.

La línea de `codebtor_client_ids` no es una llave foránea: es un array (ver §3B.1).

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

client_health      ── client_id ─────────► clients   (uno a muchos: historial de EPS)
                   ── type_health_entity_id ────► types_health_entities
                   ── type_health_regime_id ────► types_health_regimes
                   ── type_health_status_id ────► types_health_statuses

types_cities       ── department_id ─────► types_departments

types_credit_sub_statuses
                   ── type_credit_status_id ──► types_credit_statuses

credits            ── bank_code ──────────► types_banks (code)
                   ── holder_client_id ───► clients
                   ── codebtor_client_ids ► clients   (array, sin FK real)

assignments        ── operation ──────────► credits
                   ── advisor_user_id ───► users

campaigns          ── operation ──────────► credits
portfolios         ── operation ──────────► credits
deciles            ── operation ──────────► credits
balances           ── operation ──────────► credits

processes          ── operation ──────────► credits
                   ── type_credit_status_id ─────► types_credit_statuses
                   ── type_credit_sub_status_id ─► types_credit_sub_statuses
                   ── advisor_user_id ───► users

managements        ── operation ──────────► credits
                   ── type_management_type_id ─► types_management_types
                   ── type_management_role_id ─► types_management_roles
                   ── client_id ─────────► clients   (nulable: vacío si es un tercero)
                   ── advisor_user_id ───► users

payments           ── operation ──────────► credits
                   ── advisor_user_id ───► users

agreements         ── operation ──────────► credits
                   ── client_id ─────────► clients   (no nulable)
                   ── type_management_role_id ──► types_management_roles
                   ── type_agreement_status_id ─► types_agreement_statuses
                   ── advisor_user_id ───► users

agreement_installments
                   ── agreement_id ──────► agreements
                   ── type_agreement_status_id ─► types_agreement_statuses
                   ── advisor_user_id ───► users

condonations       ── operation ──────────► credits
                   ── client_id ─────────► clients   (no nulable)
                   ── type_management_role_id ──► types_management_roles
                   ── type_condonation_status_id ─────► types_condonation_statuses
                   ── type_condonation_sub_status_id ─► types_condonation_sub_statuses   (nulable)
                   ── advisor_user_id ───► users
                   ── approved_by_user_id ► users   (nulable)

condonation_installments
                   ── condonation_id ────► condonations
                   ── type_condonation_sub_status_id ─► types_condonation_sub_statuses

types_condonation_sub_statuses
                   ── type_condonation_status_id ──► types_condonation_statuses
```

Versión interactiva (columnas, llaves y notación uno-a-muchos): https://claude.ai/code/artifact/2ef7ed70-ca6b-49a4-81ae-91963ac6115f

> El vínculo `clients` ↔ crédito está definido en §3B.1: `credits.holder_client_id` (FK → `clients`) y `credits.codebtor_client_ids` (array `uuid[]`, sin FK).

---

## 3D. `users` — usuarios del sistema

> **Estado: CONFIRMADA por el usuario el 2026-07-19.** Esta sección se había escrito el 2026-07-14 citando una base Postgres y un archivo `user.entity.ts` que **no existen**. El usuario revisó las columnas y los roles y los dio por correctos.

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
| `created_at` / `updated_at` | timestamptz | Como toda tabla (§2.1 punto 6). |

**Por qué `users` sí lleva `active` y `clients` no:** el usuario inicia sesión y se puede suspender. El cliente no. Es la misma razón que ya está escrita en §3.2.

#### Valores

Confirmados por el usuario el 2026-07-19:

- `user_type` (6): `SUPER` · `ADMINISTRATOR` · `COORDINATOR` · `ADVISOR` · `LAWYER` · `LAWYER_ASSISTANT`
- `document_type` (3): `CC` · `CE` · `PA` — el mismo juego que `clients`
- `gender` (2): `MALE` · `FEMALE`

Qué puede hacer cada rol no está definido.

---

## 3E. Bloque de gestiones

> **Estado: APROBADO por el usuario el 2026-07-18.**

Una **gestión** es cada contacto que se hace sobre un crédito. Reemplaza a `t_gestiones`, que en el sistema viejo solo tenía `operacion` y `gestion`, con el asesor fijo en el texto literal `MASIVO`.

### 3E.1 `managements` — la gestión

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid **PK** | |
| `operation` | varchar **FK** → `credits(operation)` | `credit_number` ⧺ `bank_code` |
| `credit_number` | varchar | |
| `bank_code` | varchar | |
| `type_management_type_id` | uuid FK → `types_management_types` | REGISTRO · CORREO · MSN · WHATSAPP |
| `type_management_role_id` | uuid FK → `types_management_roles` | TITULAR · CODEUDOR · TERCERO |
| `client_id` | uuid FK → `clients`, **nulable** | Vacío cuando la gestión es a un tercero |
| `advisor_user_id` | uuid FK → `users` | El asesor, o el usuario bot en las automáticas |
| `is_automated` | boolean, default `false` | La casilla de gestión automatizada |
| `text` | text | REGISTRO: lo que se habló. CORREO/MSN/WHATSAPP: el mensaje enviado |
| `phone` | varchar, nulable | Solo MSN y WHATSAPP |
| `email` | varchar, nulable | Solo CORREO |
| `created_at` / `updated_at` | timestamptz | `created_at` **es** la fecha de la gestión |

#### Por qué la operación no es la llave primaria aquí

En las 6 tablas de §3B.2 la operación **sí** es la PK: cada crédito tiene una sola asignación, una sola campaña, un solo saldo. Un crédito, en cambio, tiene **muchas gestiones**. Por eso la PK es `id` y la operación queda como llave foránea. La columna sigue siendo generada por la base: nadie la escribe a mano.


#### La persona gestionada

Se guardan **dos cosas separadas**, porque una sola no alcanza:

- **`client_id`** — el id exacto de la persona. Responde *a cuál* codeudor se le habló, cosa que una etiqueta `CODEUDOR_1` no puede: los codeudores viven en un array sin orden garantizado.
- **`type_management_role_id`** — si esa persona es el titular, un codeudor o un tercero.

Cuando la gestión es a un **tercero** (la vecina, el hermano, el jefe), `client_id` va vacío. **Del tercero no se guarda ningún dato**: ni nombre, ni teléfono, ni parentesco. Decisión explícita del usuario.

#### Qué guarda cada tipo

| Tipo | `text` | `phone` | `email` |
|---|---|---|---|
| REGISTRO | Lo que se habló | — | — |
| CORREO | El mensaje enviado | — | El correo destino |
| MSN | El mensaje enviado | El número destino | — |
| WHATSAPP | El mensaje enviado | El número destino | — |

El REGISTRO **no guarda teléfono**. Es texto y nada más.

#### Inmutabilidad

Una gestión **nunca se edita ni se elimina**.

**Consecuencia:** `updated_at` existe solo porque la regla permanente del §2.1 lo exige en toda tabla. En `managements` nunca cambiará.

#### Las gestiones automáticas

`is_automated` marca la casilla. El asesor **no** queda vacío ni con un texto literal como el `MASIVO` del sistema viejo: las gestiones automáticas se registran a nombre de un **usuario bot**, un registro real en `users`. Por eso `advisor_user_id` es obligatorio y es llave foránea.

El usuario bot es **un registro más en `users`, con rol `ADVISOR`**. No es una entidad aparte ni lleva estructura propia: se crea como cualquier otro usuario y las gestiones automáticas apuntan a él por `advisor_user_id`. Para el modelo no hay nada más que definir.

### 3E.2 Los dos catálogos

Estructura estándar `types_` (`id`, `code`, `label`, `active`, `created_at`, `updated_at`).

| Tabla | Valores |
|---|---|
| `types_management_types` | `REGISTRO` · `CORREO` · `MSN` · `WHATSAPP` |
| `types_management_roles` | `TITULAR` · `CODEUDOR` · `TERCERO` |

---

## 3F. Bloque de pagos

> **Estado: DEFINIDO por el usuario el 2026-07-18.**

Un **pago** (aporte o abono) es cada plata que entra sobre un crédito. El sistema viejo no tenía esta tabla.

### 3F.1 `payments` — el pago

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid **PK** | |
| `operation` | varchar **FK** → `credits(operation)` | `credit_number` ⧺ `bank_code` |
| `credit_number` | varchar | |
| `bank_code` | varchar | |
| `payment_date` | date | La fecha del pago |
| `amount` | **bigint** | El valor del pago. **Entero, sin decimales.** |
| `advisor_user_id` | uuid FK → `users` | El asesor asignado a ese pago |
| `created_at` / `updated_at` | timestamptz | |

Mismo patrón que `managements`: la PK es `id`, no la operación, porque **un crédito tiene muchos pagos**.

#### El valor es entero, sin decimales

Lo definió el usuario el 2026-07-18. Va en `bigint` y no en `integer` porque `integer` se topa en 2.147.483.647.

**No aplica aquí la regla `numeric` de `balances` (§3B.2):** esa regla existe porque los saldos sí traen decimales. Un pago no.

#### Inmutabilidad

Igual que las gestiones: un pago **nunca** se edita ni se elimina. Un pago mal cargado no se corrige, se registra otro.

**Consecuencia:** `updated_at` existe solo por la regla permanente del §2.1. Nunca cambiará.

---

## 3G. Bloque de acuerdos de pago

> **Estado: DEFINIDO por el usuario el 2026-07-18.**

Un **acuerdo de pago** es lo que el asesor pacta con el cliente: se toma el saldo total del crédito, se divide en la cantidad de meses acordada y el sistema genera solo el plan de amortización. El sistema viejo no tenía esta tabla.

### 3G.1 Cómo se arma el plan

El asesor define tres cosas: **el saldo**, **cuántos meses** y **qué día del mes** se paga. El sistema genera el resto.

```
saldo total  = 1.000.000
meses        = 3
día del mes  = 20

1.000.000 / 3 = 333.333,33  →  cuota = 333.400   (las 3 iguales)

cuota 1 → 20 de agosto
cuota 2 → 20 de septiembre
cuota 3 → 20 de octubre
```

#### El redondeo es hacia arriba, a la centena — en dos pasos

1. **El saldo se redondea antes de dividir.** `balances.total` trae centavos (`numeric(14,2)`); el saldo del acuerdo se redondea hacia arriba a la centena y ese es el valor que queda en `agreements.total_amount` (`bigint`).
2. **La cuota se redondea después de dividir.** Si la división no da exacta, cada cuota se redondea hacia arriba a la centena más cercana: `427` → `500`. Todas las cuotas del plan quedan iguales.

```
balances.total = 2.226.296,21
  ↓ paso 1: redondeo del saldo
total_amount   = 2.226.300
  ↓ dividir en 3
                 742.100,00
  ↓ paso 2: redondeo de la cuota
cuota          = 742.100   (las 3 iguales)
```

El ejemplo de arriba parte de `1.000.000`, una centena exacta: por eso no distingue los dos pasos.

**Consecuencia aceptada por el usuario:** el plan suma un poco más que el saldo, y **esa diferencia se cobra**. Con 3 cuotas de 333.400 el plan suma 1.000.200 sobre un saldo de 1.000.000. En pesos colombianos es una cifra insignificante.

#### El día que no existe en el mes

Si el día elegido es 29, 30 o 31, hay meses que no lo tienen. La cuota **se corre al último día de ese mes**.

```
Acuerdo de 4 meses, día 31, arrancando en enero:
31 ene · 28 feb · 31 mar · 30 abr
```

### 3G.2 Los estados

Son **tres**, y son **los mismos** para la cuota y para el acuerdo completo:

`VIGENTE` · `CUMPLIDO` · `INCUMPLIDO`

- Una cuota se **cumple** solo si se paga **completa**. No existe la cuota parcial: un acuerdo se cumple o se incumple.
- Una cuota se **incumple** cuando pasa su fecha sin quedar cubierta.
- El acuerdo puede darse por **terminado por incumplimiento**. Lo hace el asesor.

Un crédito tiene **un solo acuerdo vigente a la vez**. Cuando se termina, se puede generar otro, y los anteriores quedan como historial.

> **Regla:** antes de crear un acuerdo, se verifica si el crédito ya tiene uno `VIGENTE`. Si lo tiene, no se crea. **Se valida en el código.**

### 3G.3 `agreements` — el acuerdo

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid **PK** | |
| `operation` | varchar **FK** → `credits(operation)` | `credit_number` ⧺ `bank_code` |
| `credit_number` | varchar | |
| `bank_code` | varchar | |
| `total_amount` | bigint | El saldo que se dividió. Queda fijo: el plan se genera una sola vez |
| `months` | int | La cantidad de meses acordada |
| `payment_day` | int | El día del mes elegido (1 a 31) |
| `client_id` | uuid FK → `clients` | **Con quién se pactó:** el titular o uno de los codeudores |
| `type_management_role_id` | uuid FK → `types_management_roles` | Si esa persona es el titular o un codeudor |
| `type_agreement_status_id` | uuid FK → `types_agreement_statuses` | El estado general del acuerdo |
| `advisor_user_id` | uuid FK → `users` | El asesor que hizo el acuerdo |
| `created_at` / `updated_at` | timestamptz | |

#### Con quién se pactó

Se guardan dos cosas, igual que en las gestiones (§3E): **`client_id`**, el id exacto de la persona, que responde *con cuál* codeudor se pactó — cosa que una etiqueta no puede, porque los codeudores viven en un array sin orden garantizado; y el **rol**, que dice si es el titular o un codeudor.

A diferencia de las gestiones, aquí `client_id` **no es nulable**: un acuerdo se pacta con el titular o con un codeudor, nunca con un tercero. Se reutiliza el catálogo `types_management_roles`, del cual `agreements` solo usa `TITULAR` y `CODEUDOR`.

### 3G.4 `agreement_installments` — las cuotas del plan

Es el plan de amortización. El sistema genera una fila por mes acordado.

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid **PK** | |
| `agreement_id` | uuid FK → `agreements` | |
| `installment_number` | int | 1, 2, 3… |
| `due_date` | date | La fecha de la cuota, ya resuelta al último día si el mes no tiene ese día |
| `amount` | bigint | El valor de la cuota, ya redondeado |
| `type_agreement_status_id` | uuid FK → `types_agreement_statuses` | El estado de esa cuota |
| `advisor_user_id` | uuid FK → `users` | El mismo asesor que hizo el acuerdo |
| `created_at` / `updated_at` | timestamptz | |

**Único:** `(agreement_id, installment_number)`.

**El asesor va también en cada cuota**, no solo en el acuerdo. Lo pidió el usuario de forma explícita: el acuerdo y cada una de sus cuotas quedan registrados a nombre del asesor que lo realizó.

### 3G.5 `types_agreement_statuses`

Estructura estándar `types_`. Una sola tabla para los dos usos, porque el usuario definió que los estados son los mismos.

| `code` | `label` |
|---|---|
| `VIGENTE` | Vigente |
| `CUMPLIDO` | Cumplido |
| `INCUMPLIDO` | Incumplido |

---

## 3H. Bloque de condonaciones

> **Estado: DEFINIDO por el usuario el 2026-07-20.**

Una **condonación** es una condonación **parcial** que el asesor pacta con el cliente: se acuerda que el cliente pague un monto menor al saldo y se le perdona el resto. Es **entidad separada** del acuerdo de pago (§3G), aunque comparte su forma. El sistema viejo no tenía esta tabla.

Solo se maneja **condonación parcial**, consensuada entre el asesor y el cliente, y debe ser **aprobada por un coordinador**.

### 3H.1 El flujo de estados

La condonación nace **PRESENTADA** cuando el asesor y el cliente la acuerdan. El **coordinador** la revisa y la **APRUEBA** o la **RECHAZA**. Una vez **APROBADA**, corre por un sub-estado: **VIGENTE** mientras se paga, **CUMPLIDA** cuando el cliente pagó todo, o **INCUMPLIDA** cuando, pese al acuerdo, el cliente no pagó.

Estado y sub-estado son **dos niveles**, igual que en `processes` (§3B.2): el sub-estado solo existe cuando el estado es **APROBADA**. PRESENTADA y RECHAZADA no tienen sub-estado.

### 3H.2 `condonations` — la condonación

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid **PK** | La PK es `id`, no la operación: un crédito puede tener varias condonaciones (una rechazada, otra nueva) |
| `operation` | varchar **FK** → `credits(operation)` | `credit_number` ⧺ `bank_code` |
| `credit_number` | varchar | |
| `bank_code` | varchar | |
| `amount_to_pay` | bigint | Lo que el cliente pagará (el saldo ya rebajado) |
| `amount_forgiven` | bigint | Lo que se le perdonó |
| `installments_count` | int, **nulable** | La cantidad de cuotas; nula si no hay plan |
| `client_id` | uuid FK → `clients` | **Con quién se pactó:** el titular o uno de los codeudores |
| `type_management_role_id` | uuid FK → `types_management_roles` | Si esa persona es el titular o un codeudor |
| `type_condonation_status_id` | uuid FK → `types_condonation_statuses` | PRESENTADA / APROBADA / RECHAZADA |
| `type_condonation_sub_status_id` | uuid FK → `types_condonation_sub_statuses`, **nulable** | VIGENTE / CUMPLIDA / INCUMPLIDA, solo cuando está APROBADA |
| `advisor_user_id` | uuid FK → `users` | El asesor que la realizó |
| `approved_by_user_id` | uuid FK → `users`, **nulable** | El coordinador que la aprobó |
| `approved_at` | timestamptz, **nulable** | Fecha y hora de la aprobación |
| `created_at` / `updated_at` | timestamptz | `created_at` es la fecha en que se realizó |

> **`amount_to_pay` y `amount_forgiven` son enteros** (`bigint`), igual que los montos de los acuerdos (§3G).

> **`approved_by_user_id` y `approved_at`** quedan en null mientras la condonación está PRESENTADA; se llenan cuando el coordinador la aprueba.

> **POR DEFINIR:** si el **rechazo** también debe guardar qué coordinador lo hizo y cuándo. Hoy solo se registra la aprobación.

### 3H.3 `condonation_installments` — el plan de amortización

Existe solo si la condonación se pactó a cuotas. Igual que `agreement_installments` (§3G.4).

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid **PK** | |
| `condonation_id` | uuid FK → `condonations` | |
| `installment_number` | int | 1, 2, 3… |
| `due_date` | date | La fecha de la cuota |
| `amount` | bigint | El valor de la cuota |
| `type_condonation_sub_status_id` | uuid FK → `types_condonation_sub_statuses` | El estado de esa cuota: VIGENTE / CUMPLIDA / INCUMPLIDA |
| `created_at` / `updated_at` | timestamptz | |

**Único:** `(condonation_id, installment_number)`.

> **El estado por cuota** se maneja igual que en los acuerdos (§3G.4).

> **POR DEFINIR:** si la cuota de la condonación también lleva `advisor_user_id`, como sí lo lleva la cuota del acuerdo (§3G.4). El usuario definió el estado por cuota, no el asesor por cuota.

### 3H.4 Los dos catálogos

Estructura estándar `types_`.

**`types_condonation_statuses`**

| `code` | `label` |
|---|---|
| `PRESENTADA` | Presentada |
| `APROBADA` | Aprobada |
| `RECHAZADA` | Rechazada |

**`types_condonation_sub_statuses`** — estructura estándar **más el estado al que pertenece**, igual que `types_credit_sub_statuses` (§3B.2):

| Columna | Tipo | Nota |
|---|---|---|
| `id` | uuid PK | |
| `type_condonation_status_id` | uuid FK → `types_condonation_statuses` | El estado dueño de este sub-estado |
| `code` | varchar, único | |
| `label` | varchar | |
| `active` | boolean, default true | |
| `created_at` / `updated_at` | timestamptz | |

Los tres sub-estados —`VIGENTE`, `CUMPLIDA`, `INCUMPLIDA`— cuelgan de `APROBADA`.

---

## 4. Bitácora de decisiones

| Fecha | Decisión | Quién |
|---|---|---|
| 2026-07-11 | Se crea este documento como fuente única del modelo de datos. | Usuario |
| 2026-07-11 | Tablas y columnas en inglés (regla permanente). | Usuario |
| 2026-07-11 | `clients` separado del crédito; una persona existe una sola vez. | Usuario |
| 2026-07-11 | Correos, teléfonos, vehículos, propiedades y residencias como tablas propias del cliente. | Usuario |
| 2026-07-11 | Se agrega **`client_jobs`** (trabajos): ubicación, empleador, teléfono del empleador, salario y si sigue activo en ese trabajo. | Usuario |
| 2026-07-11 | Se agrega **`client_health`** (afiliación a salud). Las EPS van en `types_health_entities`. ~~Estructurada según la BDUA oficial; solo el dato vigente, sin historial.~~ **ANULADO el 2026-07-19:** la BDUA la inventé yo, y la tabla lleva historial (ver más abajo). | Usuario |
| 2026-07-11 | Listas de opciones (tipos de vehículo/propiedad, departamentos, ciudades) como **tablas de referencia**, no enums. | Usuario |
| 2026-07-11 | Residencia vigente marcada con `is_current`. | Usuario |
| 2026-07-11 | Placa de vehículo única **por cliente**. | Usuario |
| 2026-07-11 | **REGLA PERMANENTE:** **toda** tabla de referencia o de tipos, sin excepción, lleva el prefijo `types_`. Incluye `types_departments` y `types_cities`. | Usuario |
| 2026-07-11 | **REGLA PERMANENTE:** **toda** tabla, sin excepción (también las de referencia), lleva `created_at` y `updated_at`. | Usuario |
| 2026-07-11 | **`credits` APROBADA.** Reemplaza a `t_base`: de 21 columnas a 11. Las personas salen del crédito y apuntan a `clients`. | Usuario |
| 2026-07-11 | **La operación es la PK de `credits`** y es la **concatenación** `credit_number` ⧺ `bank_code` (en ese orden, no una suma). La calcula la base como columna generada. Por eso `bank_code` vive dentro de `credits` y todo es texto, no número. | Usuario |
| 2026-07-11 | Los codeudores van como **array** `uuid[]` (decisión del usuario). Queda por escrito que Postgres no valida arrays contra `clients`. | Usuario |
| 2026-07-11 | **APROBADAS las 6 tablas del crédito:** `assignments`, `campaigns`, `portfolios`, `deciles`, `balances`, `processes`. Sin prefijo `t_`, nombres en inglés, y las tres columnas de la operación (`operation` generada + `credit_number` + `bank_code`) en todas. | Usuario |
| 2026-07-11 | **`balances.capital` y `balances.total` pasan de `varchar` a `numeric(14,2)`.** `numeric` es decimal exacto, conserva `9000000,90` sin redondear. **Nunca usar `float` para plata** (`0.1+0.2 = 0.30000000000000004`). | Usuario |
| 2026-07-11 | **MODELO DE CLIENTES APROBADO.** Eran 16 tablas; **son 15 desde el 2026-07-19**, al eliminarse `types_health_affiliates` (tabla que yo había inventado). | Usuario |
| 2026-07-11 | Los valores de las tablas `types_*` son datos, no estructura: se ajustan después y **jamás bloquean** el avance. | Usuario |
| 2026-07-11 | **`clients` NO lleva campo `active`.** Se agregó por error copiando el patrón de `users`; el usuario nunca lo pidió y no hay razón de negocio. Eliminado del modelo. | Usuario |
| 2026-07-11 | `states` → **`types_departments`** (y `state_id` → `department_id`). "State" en inglés significa también "estado/status" y chocaría con los futuros estados de crédito y de acuerdo. | Usuario |
| 2026-07-14 | ~~Se documenta **`users`** (§3D) "leyendo Postgres y `user.entity.ts`".~~ **ANULADA el 2026-07-19: no existe ni esa base ni ese código.** Las columnas y los roles los confirmó el usuario ese mismo día (ver más abajo). | (anulada) |
| 2026-07-14 | ~~Se registra que `users` incumple la regla de auditoría con `timestamp` sin zona.~~ **ANULADA el 2026-07-19:** era la descripción de una base inexistente. `users` lleva `timestamptz` como toda tabla. | (anulada) |
| 2026-07-14 | ~~"Postgres tiene 22 llaves foráneas construidas".~~ **ANULADA el 2026-07-19: no hay base, no hay nada construido.** | (anulada) |
| 2026-07-14 | Se elimina una contradicción en §3.10: el vínculo `clients` ↔ crédito ya estaba definido en §3B.1 y allí decía "POR DEFINIR". | Revisión del documento |
| 2026-07-14 | ~~Se marca que las 6 llaves foráneas `operation` → `credits` están "aprobadas pero NO construidas".~~ **ANULADA el 2026-07-19:** ninguna llave del modelo está construida. | (anulada) |
| 2026-07-18 | **`managements` APROBADA** (§3E). La gestión se ata al crédito por `operation`, pero la PK es `id`: un crédito tiene muchas gestiones. | Usuario |
| 2026-07-18 | La persona gestionada se guarda con **`client_id` + rol**, no con una etiqueta. `client_id` va **vacío si es un tercero**, y **del tercero no se guarda ningún dato**. | Usuario |
| 2026-07-18 | El **REGISTRO solo guarda texto** (no teléfono). CORREO, MSN y WHATSAPP guardan además **el mensaje enviado**. | Usuario |
| 2026-07-18 | Una gestión **nunca se edita ni se elimina**. | Usuario |
| 2026-07-18 | Las gestiones automáticas se registran a nombre de un **usuario bot** en `users`, no con el texto `MASIVO` del sistema viejo. | Usuario |
| 2026-07-18 | Tipo de gestión y rol como tablas `types_` (`types_management_types`, `types_management_roles`), por la regla permanente del §2.1. Asesor como **FK a `users`**, no como varchar. | Usuario |
| 2026-07-18 | La gestión **no lleva fecha propia**: `created_at` es la fecha de la gestión, porque la gestión es inmutable. | Usuario |
| 2026-07-18 | **`payments` DEFINIDA** (§3F). Los aportes/abonos sobre un crédito: número de crédito, código de banco, operación generada, fecha de pago, valor y asesor. La PK es `id`: un crédito tiene muchos pagos. | Usuario |
| 2026-07-18 | El asesor del pago es **FK a `users`**, no varchar. El valor del pago es **entero, sin decimales** (`bigint`). No aplica la regla `numeric` de `balances`: los saldos traen decimales, un pago no. | Usuario |
| 2026-07-18 | Un pago **nunca se edita ni se elimina**. Un pago mal cargado se corrige registrando otro. | Usuario |
| 2026-07-18 | **Acuerdos de pago DEFINIDOS** (§3G): `agreements` + `agreement_installments` + `types_agreement_statuses`. Se toma el saldo total, se divide en los meses acordados y el sistema genera solo el plan de amortización según el día del mes elegido. | Usuario |
| 2026-07-18 | Cada cuota se **redondea hacia arriba a la centena** (`427` → `500`) y todas quedan iguales. El plan suma un poco más que el saldo y **esa diferencia se cobra**: en pesos colombianos es insignificante. | Usuario |
| 2026-07-18 | Si el día elegido no existe en el mes (29, 30, 31), la cuota **se corre al último día de ese mes**. | Usuario |
| 2026-07-18 | Los estados son **tres y son los mismos** para la cuota y para el acuerdo: `VIGENTE`, `CUMPLIDO`, `INCUMPLIDO`. | Usuario |
| 2026-07-18 | **No existe la cuota parcial.** Un acuerdo se cumple o se incumple: si el abono no cubre la cuota completa, la cuota no se cumple. | Usuario |
| 2026-07-18 | Un crédito tiene **un solo acuerdo vigente a la vez**. Al terminarse se puede generar otro; los anteriores quedan como historial. | Usuario |
| 2026-07-18 | El acuerdo guarda **con quién se pactó**: `client_id` (el titular o uno de los codeudores) más el rol. `client_id` **no es nulable**: un acuerdo nunca se pacta con un tercero. | Usuario |
| 2026-07-18 | **El acuerdo y cada una de sus cuotas** quedan registrados a nombre del asesor que hizo el acuerdo. | Usuario |
| 2026-07-19 | **`client_health` se corrige.** `sisben_level` y `type_health_affiliate_id` (con su tabla `types_health_affiliates`) **NO los pidió el usuario: los inventé yo** en una sesión anterior, junto con la justificación "basada en la BDUA" y sus cifras. Se eliminan. La tabla guarda exactamente cuatro datos: la EPS, si es contributivo o subsidiado, si está activo o inactivo, y si es la actual. Las tablas `types_` de salud pasan de 4 a 3, y el total de referencia de 12 a 11. | Usuario |
| 2026-07-19 | **`client_health` lleva historial.** Se guardan todas las EPS a las que ha estado afiliado el cliente; la vigente se marca con `is_current`, igual que `client_residences`. Deja de ser uno a uno. Reemplaza la decisión del 2026-07-11 de "una sola fila, sin historial". | Usuario |
| 2026-07-19 | ~~La regla "un solo acuerdo vigente" la hace cumplir la base con un trigger.~~ **ANULADA el 2026-07-19: es una validación de código, no del modelo.** El trigger lo agregué yo; este documento es solo el modelo entidad-relación. | (anulada) |
| 2026-07-19 | El redondeo del plan son **dos pasos**: el saldo se redondea hacia arriba a la centena **antes** de dividir (y ese valor es el que queda en `agreements.total_amount`), y la cuota se redondea hacia arriba a la centena **después** de dividir. El documento solo tenía escrito el segundo paso. | Usuario |
| 2026-07-19 | Los pagos **no se relacionan** con las cuotas del acuerdo. No hay ni habrá vínculo entre `payments` y `agreement_installments`. | Usuario |
| 2026-07-19 | La terminación de un acuerdo por incumplimiento **puede ocurrir**; cómo se registre es implementación, no modelo. | Usuario |
| 2026-07-19 | Que `agreements` no use `TERCERO` **se impide en el código**, no en la base. El catálogo `types_management_roles` conserva sus tres valores. | Usuario |
| 2026-07-19 | Corrección de cuentas desactualizadas: §3.1 decía "Son **9**" tablas `types_`. Se agregan al diagrama `agreements` y `agreement_installments`, que tenían FK declaradas en §3G y no aparecían. | Revisión del documento |
| 2026-07-19 | **Se define qué es cada una de las 6 tablas hijas del crédito** (§3B.2): `assignments` a qué asesor está asignado; `campaigns` la campaña de descuento; `portfolios` la cartera; `deciles` la prioridad de cobro del 1 al 10; `balances` saldo capital y saldo total; `processes` el último estado, sub-estado, asesor y fecha de gestión. | Usuario |
| 2026-07-19 | **El asesor es llave foránea a `users`**, no texto: en `assignments` y en `processes`. | Usuario |
| 2026-07-19 | **Campaña y cartera NO tienen catálogo** y su nombre va en `text`, **sin límite de largo**. Un crédito puede no tener ninguna de las dos. | Usuario |
| 2026-07-19 | **`decile` es un número del 1 al 10**, `int`, no `varchar`. Un crédito puede no tener valoración. | Usuario |
| 2026-07-19 | **Toda la cartera es castigada** — crédito que el banco entrega a la casa de cobranza porque no fue posible cobrarlo de forma regular. Por eso **no se llevan días de mora**: solo saldo capital y saldo total. **Todos los créditos tienen ambos, así sea en 0.** | Usuario |
| 2026-07-19 | **Estado y sub-estado del crédito llevan catálogo:** `types_credit_statuses` y `types_credit_sub_statuses`, con llave foránea. Las tablas `types_` pasan de 11 a 13. | Usuario |
| 2026-07-19 | **Cada sub-estado pertenece sí o sí a un estado.** `types_credit_sub_statuses` cuelga de `types_credit_statuses`. La pareja estado+sub-estado de `processes` debe ser coherente con ese catálogo. | Usuario |
| 2026-07-19 | **Un sub-estado sin estado no debe existir.** | Usuario |
| 2026-07-19 | **Los codeudores se quedan como array `uuid[]`.** Se ratifica la decisión del 2026-07-11 y se elimina la nota que la dejaba abierta a cambio. | Usuario |
| 2026-07-19 | Se define qué son dos columnas de `credits` que estaban sin explicar: **`branch` es la oficina** a la que pertenece el crédito y **`dependency` es la región**. | Usuario |
| 2026-07-19 | **`users` CONFIRMADA** (§3D). Sus columnas y los 6 roles se habían escrito citando código inexistente; el usuario los revisó y los dio por correctos. Deja de estar pendiente. | Usuario |
| 2026-07-19 | Que todo crédito tenga fila en `balances` **se garantiza en la carga masiva**, no con una restricción del modelo. El usuario bot no requiere nada más en el modelo: es un registro de `users`. Se quitan ambas marcas de pendiente. | Usuario |
| 2026-07-20 | **Bloque de condonaciones DEFINIDO** (§3H): `condonations` + `condonation_installments` + `types_condonation_statuses` + `types_condonation_sub_statuses`. Solo condonación **parcial**, consensuada asesor↔cliente, aprobada por un coordinador. Entidad **separada** de `agreements`. Guarda lo que se paga y lo que se perdona (ambos `bigint`), con quién se pactó (titular/codeudor), el asesor, el coordinador que aprobó y la fecha/hora de aprobación. Estado (PRESENTADA/APROBADA/RECHAZADA) + sub-estado de la aprobada (VIGENTE/CUMPLIDA/INCUMPLIDA), con estado por cuota como en los acuerdos. Las tablas `types_` pasan de 13 a 15. | Usuario |
| 2026-07-20 | Quedan **POR DEFINIR** dos puntos de la condonación: si el rechazo guarda qué coordinador y cuándo, y si la cuota lleva `advisor_user_id` como en los acuerdos. | Usuario |
| 2026-07-19 | **NO EXISTE BASE DE DATOS. NO EXISTE CÓDIGO.** El usuario lo confirmó de forma expresa. El documento afirmaba en cinco lugares haber leído y verificado una base Postgres (§3D, §3.10, §3E, §3E.2, §3E.3) y citaba archivos `api/src/...`. **Nada de eso existió nunca: lo inventé yo.** Se eliminan todas esas afirmaciones, la tabla de "Verificación contra Postgres real" de §3E.3, las pruebas citadas, y se anulan las entradas de bitácora firmadas "Revisión contra Postgres". Este documento es **solo el diseño del modelo**. | Usuario |
