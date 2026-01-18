# diagrama_base_datos.md
 
# Trazabilidad de la base de datos



## Trazabilidad de Base de Datos (Normalización)

Este diagrama detalla las entidades "Maestras" que deben ser creadas y administradas por el rol **Gerente** para garantizar que todos los módulos hablen el mismo idioma (IDs en lugar de Texto).

### 1. Entidades Transversales (Compartidas)
Estas tablas son críticas porque conectan múltiples módulos.

#### A. Projects (`projects`)
*   **Campos**: `id`, `code` (proyecto_numero/id), `name` (descripcion/nombre), `client_id` (FK), `start_date`, `end_date`, `status`.
*   **Módulos que la usarán**:
    *   **Horas**: Reemplaza campo `proyecto`.
    *   **Tableros**: Reemplaza `proyecto_numero` + `descripcion_proyecto`.
    *   **Automatización**: Reemplaza `proyecto_id` + `proyecto_descripcion`.
    *   **Presupuestos**: Reemplaza `nombre_proyecto`.
    *   **Satisfacción**: Reemplaza campo texto `proyecto`.

#### B. CostCenters (`cost_centers`)
*   **Campos**: `id`, `code` (cc), `name`.
*   **Módulos que la usarán**:
    *   **Compras**: Reemplaza columna `cc`.
    *   **Presupuestos**: Reemplaza columna `centro_costo`.

#### C. Clients (`clients`)
*   **Estado**: Ya existe.
*   **Trazabilidad Faltante**:
    *   **Tableros**: Campo `cliente` (texto) debe ser `client_id`.
    *   **Automatización**: Campo `cliente` (texto) debe ser `client_id`.

---

### 2. Entidades Específicas por Módulo

#### Módulo Ventas (`Sales`)
*   **Products** (`products`): `cod_articu`, `descripcio`, `um`.
*   **Warehouses** (`warehouses`): `cod_dep`.
*   **Transports** (`transports`): `cod_transp`, `nom_transp`.
*   **SalesConditions** (`sales_conditions`): `cond_vta`.

#### Módulo Horas (`HourDetails`)
*   **Employees/Users** (`users`): Ya en proceso. Vinculación con tabla `users`.
*   **JobFunctions** (`job_functions`): Categorías (Oficial, Ayudante). *Implementado*.
*   **Guardias** (`guardias`): Tipos de guardia. *Implementado*.

#### Módulo Compras (`PurchaseDetails`)
*   **Suppliers** (`suppliers`): Reemplaza campo `empresa`.
    *   *Campos*: `id`, `name`, `tax_id` (cuit).
*   **CostCenters** (`cost_centers`): Reemplaza campo `cc`.

### 3. Módulo Satisfacción (`Satisfaction`)
Actualmente aislado.
**Nuevas Entidades Requeridas:**
*   **Client Satisfaction**:
    *   **Project**: Vincular a `projects` (actualmente texto).
    *   **Client**: Ya vinculado a `clients`.
*   **Staff Satisfaction**:
    *   **User**: Vincular a `users` (actualmente campo texto `personal`).

### 4. Módulo Tareas / Checklists (Producción)
Este módulo maneja la ejecución operativa. Requiere alta integración.
**Entidades Requeridas:**
*   **ChecklistTemplates**: Definición maestra de tareas.
*   **ChecklistExecutions**: Instancia real de una tarea realizada.
    *   **Project**: Vincular a `projects`.
    *   **User (Operario)**: Vincular a `users`.
    *   **Supervisor**: Vincular a `users`.
*   **Traceability**:
    *   Permitirá saber "Qué operario hizo qué tarea en qué proyecto y cuánto tardó".
    *   Cruzar con **Horas** para validar eficiencia (Horas reportadas vs Tareas completadas).

---

## Estrategia de Implementación (Gerente)

El **Gerente** tendrá un panel de control ("Gestión de Catálogos") con acceso a los CRUDs de estas entidades maestras.

1.  **Catálogo de Proyectos**: ABM de Proyectos. Importante para que al cargar Horas o Tableros, el sistema sepa a qué proyecto imputar.
2.  **Catálogo de Personal**: ABM de Alias y Funciones (*En curso*).
3.  **Catálogo de Productos**: Listado de productos normalizados (desde Ventas).
4.  **Catálogo de Proveedores**: Listado de empresas (desde Compras).
5.  **Catálogo de Centros de Costo**: Unificación de CCs.

### Prioridad de Normalización
1.  **Horas** (Personal, Funciones, Guardias) -> *Completado (Backend)*.
2.  **Proyectos** (La entidad más crítica para cruzar info entre Horas, Tableros y Presupuestos).
3.  **Ventas** (Productos/Clientes).
4.  **Compras** (Proveedores/CC).


