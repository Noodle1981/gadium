# Auditoría de Base de Datos - Industrias X
**Fecha**: 2026-01-19  
**Analista**: Sistema de Auditoría Automática  
**Objetivo**: Identificar tablas no utilizadas, verificar trazabilidad relacional y documentar brechas de normalización

---

## 📊 Resumen Ejecutivo

### Métricas Generales
- **Total de Migraciones**: 34
- **Total de Tablas Creadas**: 37
- **Total de Modelos Eloquent**: 23
- **Tablas sin Modelo**: 14 (principalmente infraestructura Laravel/Spatie)
- **Modelos sin Uso Aparente**: 0 (todos tienen rutas/controladores asociados)

### Estado de Normalización
| Módulo | Estado FK | Progreso | Prioridad |
|--------|-----------|----------|-----------|
| **Ventas** | ✅ Completo | 100% | ✓ |
| **Horas** | ✅ Completo | 100% | ✓ |
| **Compras** | ⚠️ Parcial | 50% | Alta |
| **Tableros** | ❌ Pendiente | 0% | Alta |
| **Automatización** | ❌ Pendiente | 0% | Alta |
| **Presupuestos** | ❌ Pendiente | 0% | Media |
| **Satisfacción** | ❌ Pendiente | 0% | Baja |

---

## 🗂️ Inventario Completo de Tablas

### 1. Tablas de Infraestructura Laravel (14)
Estas tablas son parte del framework y **NO requieren modelos Eloquent**.

| Tabla | Propósito | Modelo | Estado |
|-------|-----------|--------|--------|
| `users` | Autenticación | ✅ User | Activo |
| `password_reset_tokens` | Reset de contraseñas | ❌ N/A | Activo |
| `sessions` | Sesiones de usuario | ❌ N/A | Activo |
| `cache` | Sistema de caché | ❌ N/A | Activo |
| `cache_locks` | Locks de caché | ❌ N/A | Activo |
| `jobs` | Cola de trabajos | ❌ N/A | Activo |
| `job_batches` | Lotes de trabajos | ❌ N/A | Activo |
| `failed_jobs` | Trabajos fallidos | ❌ N/A | Activo |
| `personal_access_tokens` | Tokens API (Sanctum) | ❌ N/A | Activo |
| `permissions` | Permisos (Spatie) | ❌ N/A | Activo |
| `roles` | Roles (Spatie) | ❌ N/A | Activo |
| `model_has_permissions` | Pivot (Spatie) | ❌ N/A | Activo |
| `model_has_roles` | Pivot (Spatie) | ❌ N/A | Activo |
| `role_has_permissions` | Pivot (Spatie) | ❌ N/A | Activo |

**Conclusión**: Todas estas tablas están en uso activo por el framework. ✅ **No eliminar**.

---

### 2. Tablas de Negocio (23)

#### A. Módulo Ventas (3 tablas)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `sales` | ✅ Sale | `client_id` → clients | ✅ Normalizado |
| `clients` | ✅ Client | Ninguna (maestra) | ✅ Normalizado |
| `client_aliases` | ✅ ClientAlias | `client_id` → clients | ✅ Normalizado |

**Análisis**:
- ✅ **Normalización completa**: `sales.client_id` apunta a `clients.id`
- ✅ **Sistema de alias**: Previene duplicados por variaciones de nombre
- ✅ **Campo legacy**: `sales.cliente_nombre` se mantiene para auditoría
- 🎯 **Grafana-ready**: Queries relacionales funcionan correctamente

---

#### B. Módulo Horas (4 tablas)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `hour_details` | ✅ HourDetail | `user_id`, `job_function_id`, `guardia_id` | ✅ Normalizado |
| `job_functions` | ✅ JobFunction | Ninguna (maestra) | ✅ Normalizado |
| `guardias` | ✅ Guardia | Ninguna (maestra) | ✅ Normalizado |
| `user_aliases` | ✅ UserAlias | `user_id` → users | ✅ Normalizado |

**Análisis**:
- ✅ **Normalización completa**: FKs para personal, función y guardia
- ✅ **Sistema de alias**: Mapea variaciones de nombres de empleados
- ⚠️ **Campo legacy**: `hour_details.personal` y `hour_details.funcion` aún existen
- ⚠️ **Campo pendiente**: `hour_details.proyecto` (TEXT) → debería ser FK a `projects`

**Recomendación Crítica**:
```sql
-- PENDIENTE: Agregar FK para proyectos
ALTER TABLE hour_details 
ADD COLUMN project_id BIGINT UNSIGNED NULL AFTER proyecto,
ADD FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL;
```

---

#### C. Módulo Compras (4 tablas)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `purchase_details` | ✅ PurchaseDetail | `supplier_id` | ⚠️ Parcial |
| `suppliers` | ✅ Supplier | Ninguna (maestra) | ✅ Normalizado |
| `supplier_aliases` | ✅ SupplierAlias | `supplier_id` → suppliers | ✅ Normalizado |
| `cost_centers` | ✅ CostCenter | Ninguna (maestra) | ✅ Normalizado |

**Análisis**:
- ✅ **Proveedores normalizados**: `purchase_details.supplier_id` → `suppliers.id`
- ❌ **Centro de Costo SIN FK**: `purchase_details.cc` (TEXT) → debería ser FK a `cost_centers`
- ⚠️ **Campos legacy**: `purchase_details.empresa` se mantiene

**Recomendación Crítica**:
```sql
-- URGENTE: Agregar FK para centros de costo
ALTER TABLE purchase_details 
ADD COLUMN cost_center_id BIGINT UNSIGNED NULL AFTER cc,
ADD FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE SET NULL;
```

---

#### D. Módulo Tableros (1 tabla)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `board_details` | ✅ BoardDetail | **Ninguna** | ❌ Sin normalizar |

**Análisis**:
- ❌ **Sin FKs**: Todos los campos relacionales son TEXT
- ❌ `board_details.cliente` (TEXT) → debería ser `client_id`
- ❌ `board_details.proyecto_numero` (TEXT) → debería ser `project_id`

**Impacto en Grafana**:
- ⚠️ **Imposible cruzar datos** entre Tableros y Ventas por cliente
- ⚠️ **Imposible cruzar datos** entre Tableros y Horas por proyecto
- ⚠️ **Duplicados potenciales** por variaciones de nombre de cliente

**Recomendación Crítica**:
```sql
-- URGENTE: Normalizar board_details
ALTER TABLE board_details 
ADD COLUMN client_id BIGINT UNSIGNED NULL AFTER cliente,
ADD COLUMN project_id BIGINT UNSIGNED NULL AFTER proyecto_numero,
ADD FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
ADD FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL;
```

---

#### E. Módulo Automatización (1 tabla)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `automation_projects` | ✅ AutomationProject | **Ninguna** | ❌ Sin normalizar |

**Análisis**:
- ❌ **Sin FKs**: Todos los campos relacionales son TEXT
- ❌ `automation_projects.cliente` (TEXT) → debería ser `client_id`
- ❌ `automation_projects.proyecto_id` (TEXT) → debería ser FK real a `projects`

**Impacto en Grafana**:
- ⚠️ **Imposible cruzar datos** entre Automatización y Ventas por cliente
- ⚠️ **Imposible cruzar datos** entre Automatización y Horas por proyecto
- ⚠️ **Duplicados potenciales** por variaciones de nombre

**Recomendación Crítica**:
```sql
-- URGENTE: Normalizar automation_projects
ALTER TABLE automation_projects 
ADD COLUMN client_id BIGINT UNSIGNED NULL AFTER cliente,
ADD COLUMN project_id_fk BIGINT UNSIGNED NULL AFTER proyecto_id,
ADD FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
ADD FOREIGN KEY (project_id_fk) REFERENCES projects(id) ON DELETE SET NULL;

-- Nota: Renombrar proyecto_id (TEXT) a proyecto_codigo para evitar confusión
ALTER TABLE automation_projects CHANGE proyecto_id proyecto_codigo VARCHAR(255);
```

---

#### F. Módulo Presupuestos (1 tabla)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `budgets` | ✅ Budget | **Ninguna** | ❌ Sin normalizar |

**Análisis** (basado en migración `2026_01_12_233134_create_budgets_table.php`):
- ❌ `budgets.cliente_nombre` (TEXT) → debería ser `client_id`
- ❌ `budgets.nombre_proyecto` (TEXT) → debería ser `project_id`
- ❌ `budgets.centro_costo` (TEXT) → debería ser `cost_center_id`

**Recomendación**:
```sql
ALTER TABLE budgets 
ADD COLUMN client_id BIGINT UNSIGNED NULL,
ADD COLUMN project_id BIGINT UNSIGNED NULL,
ADD COLUMN cost_center_id BIGINT UNSIGNED NULL,
ADD FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
ADD FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
ADD FOREIGN KEY (cost_center_id) REFERENCES cost_centers(id) ON DELETE SET NULL;
```

---

#### G. Módulo Satisfacción (4 tablas)
| Tabla | Modelo | FKs Implementadas | Estado |
|-------|--------|-------------------|--------|
| `client_satisfaction_responses` | ✅ ClientSatisfactionResponse | ❓ | ⚠️ Revisar |
| `client_satisfaction_analysis` | ✅ ClientSatisfactionAnalysis | ❓ | ⚠️ Revisar |
| `staff_satisfaction_responses` | ✅ StaffSatisfactionResponse | ❓ | ⚠️ Revisar |
| `staff_satisfaction_analysis` | ✅ StaffSatisfactionAnalysis | ❓ | ⚠️ Revisar |

**Nota**: Estas tablas requieren análisis detallado de sus migraciones para verificar FKs.

---

#### H. Tablas Maestras Transversales (2 tablas)
| Tabla | Modelo | Usada Por | Estado |
|-------|--------|-----------|--------|
| `projects` | ✅ Project | ❌ Ningún módulo (aún) | ⚠️ **Huérfana** |
| `cost_centers` | ✅ CostCenter | ❌ Ningún módulo (aún) | ⚠️ **Huérfana** |

**Análisis Crítico**:
- ⚠️ **Tablas creadas pero NO usadas**: Existen las tablas maestras pero ningún módulo apunta a ellas
- ⚠️ **Pérdida de relaciones**: Grafana no puede cruzar datos entre módulos
- 🎯 **Prioridad Alta**: Implementar FKs en todos los módulos hacia estas tablas

---

#### I. Tablas de Agregación (2 tablas)
| Tabla | Modelo | Propósito | Estado |
|-------|--------|-----------|--------|
| `daily_metrics_aggregates` | ✅ DailyMetricsAggregate | Métricas pre-calculadas | ✅ Activo |
| `manufacturing_logs` | ✅ ManufacturingLog | Logs de producción | ✅ Activo |
| `weighting_factors` | ✅ WeightingFactor | Factores de ponderación | ✅ Activo |

---

## 🔍 Hallazgos Críticos

### 1. ❌ Tablas Maestras Huérfanas
**Problema**: Las tablas `projects` y `cost_centers` existen pero **NO tienen FKs apuntando a ellas**.

**Impacto**:
- Grafana no puede cruzar datos entre módulos
- Imposible responder preguntas como:
  - "¿Cuántas horas se trabajaron en el Proyecto X?"
  - "¿Cuánto se gastó en el Centro de Costo Y?"
  - "¿Qué clientes tienen proyectos de automatización?"

**Solución**:
1. Agregar `project_id` FK en: `hour_details`, `board_details`, `automation_projects`, `budgets`
2. Agregar `cost_center_id` FK en: `purchase_details`, `budgets`

---

### 2. ⚠️ Campos TEXT en lugar de FKs

| Tabla | Campo TEXT | Debería ser FK a |
|-------|------------|------------------|
| `hour_details` | `proyecto` | `projects.id` |
| `purchase_details` | `cc` | `cost_centers.id` |
| `board_details` | `cliente` | `clients.id` |
| `board_details` | `proyecto_numero` | `projects.id` |
| `automation_projects` | `cliente` | `clients.id` |
| `automation_projects` | `proyecto_id` | `projects.id` |
| `budgets` | `cliente_nombre` | `clients.id` |
| `budgets` | `nombre_proyecto` | `projects.id` |
| `budgets` | `centro_costo` | `cost_centers.id` |

**Consecuencias**:
- Duplicados por variaciones de nombre ("ACME SA", "Acme S.A.", "ACME")
- Imposible hacer JOINs en Grafana
- Datos inconsistentes entre módulos

---

### 3. ✅ Buenas Prácticas Implementadas

#### Sistema de Alias (Normalización de Nombres)
- ✅ `client_aliases`: Mapea variaciones de nombres de clientes
- ✅ `user_aliases`: Mapea variaciones de nombres de empleados
- ✅ `supplier_aliases`: Mapea variaciones de nombres de proveedores

**Beneficio**: Previene duplicados y permite normalización automática durante importación.

#### Campos `hash` para Idempotencia
- ✅ Todas las tablas transaccionales tienen campo `hash` único
- ✅ Previene duplicados en importaciones repetidas
- ✅ Permite re-importar archivos sin crear registros duplicados

---

## 📋 Plan de Acción Recomendado

### Fase 1: Normalización de Proyectos (Prioridad Alta)
**Objetivo**: Permitir cruzar datos entre Horas, Tableros, Automatización y Presupuestos.

1. **Crear migración para `hour_details`**:
   ```bash
   php artisan make:migration add_project_id_to_hour_details_table
   ```

2. **Crear migración para `board_details`**:
   ```bash
   php artisan make:migration add_foreign_keys_to_board_details_table
   ```

3. **Crear migración para `automation_projects`**:
   ```bash
   php artisan make:migration add_foreign_keys_to_automation_projects_table
   ```

4. **Crear migración para `budgets`**:
   ```bash
   php artisan make:migration add_foreign_keys_to_budgets_table
   ```

5. **Poblar FKs con datos existentes**:
   - Crear servicio de normalización (similar a `ClientNormalizationService`)
   - Mapear proyectos existentes (TEXT) a `projects.id`
   - Actualizar registros históricos

---

### Fase 2: Normalización de Centros de Costo (Prioridad Alta)
**Objetivo**: Unificar centros de costo entre Compras y Presupuestos.

1. **Crear migración para `purchase_details`**:
   ```bash
   php artisan make:migration add_cost_center_id_to_purchase_details_table
   ```

2. **Actualizar migración de `budgets`** (agregar `cost_center_id`)

3. **Poblar FKs con datos existentes**

---

### Fase 3: Normalización de Clientes (Prioridad Media)
**Objetivo**: Unificar clientes entre Tableros y Automatización.

1. **Crear migración para `board_details`** (agregar `client_id`)
2. **Crear migración para `automation_projects`** (agregar `client_id`)
3. **Poblar FKs usando `client_aliases` existente**

---

### Fase 4: Verificación de Satisfacción (Prioridad Baja)
**Objetivo**: Auditar tablas de satisfacción y verificar FKs.

1. Analizar migraciones de `client_satisfaction_*`
2. Analizar migraciones de `staff_satisfaction_*`
3. Verificar que tengan FKs a `clients`, `users`, `projects`

---

## 🎯 Métricas de Éxito

Una vez completada la normalización, deberías poder ejecutar queries como:

```sql
-- Horas trabajadas por proyecto
SELECT p.name, SUM(h.horas_ponderadas) as total_horas
FROM hour_details h
JOIN projects p ON h.project_id = p.id
GROUP BY p.name;

-- Gastos por centro de costo
SELECT cc.name, SUM(pd.materiales_comprados) as total_gastado
FROM purchase_details pd
JOIN cost_centers cc ON pd.cost_center_id = cc.id
GROUP BY cc.name;

-- Clientes con proyectos de automatización
SELECT c.name, COUNT(ap.id) as total_proyectos
FROM automation_projects ap
JOIN clients c ON ap.client_id = c.id
GROUP BY c.name;
```

---

## 📊 Conclusión

**Estado Actual**: El sistema tiene una base sólida con:
- ✅ Infraestructura de alias para prevenir duplicados
- ✅ Sistema de hash para idempotencia
- ✅ Normalización completa en Ventas y Horas (parcial)

**Brecha Principal**: Falta de FKs en módulos transversales (Tableros, Automatización, Presupuestos) hacia tablas maestras (`projects`, `cost_centers`, `clients`).

**Impacto**: Grafana no puede generar reportes cruzados entre módulos, perdiendo el valor de un sistema relacional.

**Recomendación**: Ejecutar Fase 1 y Fase 2 del Plan de Acción **de inmediato** para recuperar la trazabilidad relacional.

---

**Auditoría completada el**: 2026-01-19  
**Próxima revisión recomendada**: Después de implementar Fase 1 y Fase 2
