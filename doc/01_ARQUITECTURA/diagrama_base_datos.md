# diagrama_base_datos.md
 
# Trazabilidad de la base de datos


## Trazabilidad de Base de Datos (Normalización)

Análisis por módulo de las entidades necesarias para garantizar integridad referencial y explotación de datos en Grafana.

### 1. Módulo Ventas (`Sales`)
Actualmente normalizado: `Client` (cliente_nombre).
**Nuevas Entidades Requeridas:**
- **Product** (`products`): `cod_articu`, `descripcio`, `um`, `precio_referencia`.
  - *Objetivo*: Análisis de ventas por producto, evolución de precios.
- **Transport** (`transports`): `cod_transp`, `nom_transp`.
  - *Objetivo*: Análisis de costos logísticos y eficiencia de distribución.
- **Warehouse** (`warehouses`): `cod_dep`.
  - *Objetivo*: Stock y rotación por depósito.
- **SalesCondition** (`sales_conditions`): `cond_vta`.
  - *Objetivo*: Análisis de flujo de caja (Contado vs Crédito).

### 2. Módulo Horas (`HourDetails`)
Actualmente texto plano: `personal`, `funcion`, `proyecto`.
**Nuevas Entidades Requeridas:**
- **User / Employee** (`users`): Vinculación con usuarios de sistema.
  - *Requisito*: Tabla `user_aliases` para mapear nombres de Excel ("Juan C.") a usuarios únicos.
- **JobFunction** (`job_functions`): `funcion` (e.g., Oficial, Ayudante).
  - *Objetivo*: Tarifarios y análisis de horas por seniority/rol.
- **Guardia** (`guardias`): Tipificación de regímenes de guardia.
- **Project** (`projects`): `proyecto`. (Compartido con otros módulos).

### 3. Módulo Compras (`PurchaseDetails`)
Actualmente texto plano: `empresa`, `cc`.
**Nuevas Entidades Requeridas:**
- **Supplier** (`suppliers`): `empresa`. Normilazación de proveedores.
- **CostCenter** (`cost_centers`): `cc`. Análisis de gastos por unidad de negocio.

### 4. Módulo Tableros (`BoardDetails`)
Actualmente texto plano: `cliente`, `proyecto_numero`.
**Nuevas Entidades Requeridas:**
- **Client**: Debe vincularse a la tabla maestra `clients` existente.
- **Project**: Unificación con tabla de proyectos maestra.

### 5. Módulo Proyectos (`AutomationProjects`)
Actualmente texto plano: `cliente`, `proyecto_id`.
**Nuevas Entidades Requeridas:**
- **Client**: Debe vincularse a la tabla maestra `clients` existente.
- **Project**: Unificación con tabla de proyectos maestra.

### 6. Módulo Satisfacción
- **Client Satisfaction**: Ya vinculado a `clients`. `Proyectos` es texto libre.
- **Staff Satisfaction**: `Personal` es texto libre -> Debe vincularse a `users`.

---

## Estrategia de Implementación (Gerente)
El rol **Manager (Gerente)** será responsable del mantenimiento de estos catálogos (ABM) para asegurar la calidad del dato antes de la visualización en tableros.

