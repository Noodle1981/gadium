# Documentación Técnica: Módulos de Importación

> **Última actualización:** 16/01/2026
> **Estado:** Documentación viva

Este documento detalla la arquitectura, archivos involucrados y especificaciones de datos para cada uno de los módulos de importación del sistema. 

---

## Indice

1. [Arquitectura General](#arquitectura-general)
2. [Módulo: Ventas](#módulo-ventas)
3. [Módulo: Presupuestos](#módulo-presupuestos)
4. [Módulo: Horas](#módulo-horas)
5. [Módulo: Compras](#módulo-compras)
6. [Módulo: Tableros](#módulo-tableros)
7. [Módulo: Proyectos Automatización](#módulo-proyectos-automatización)
8. [Mejoras UI/UX Propuestas](#mejoras-uiux-propuestas)

---

## Arquitectura General

Todos los importadores comparten una arquitectura común basada en **Livewire** y un servicio centralizado de procesamiento de Excel.

### Flujo de Importación
1. **Carga (Step 1):** Usuario sube archivo `.xlsx` o `.xls`. Se valida extensión y tamaño.
2. **Análisis (Backend):** `ExcelImportService` lee el archivo, valida headers y tipos de dato.
3. **Resolución (Step 2 - Opcional):** Si hay clientes desconocidos (solo Ventas/Presupuestos), se solicita asignación manual.
4. **Confirmación (Step 3):** Muestra resumen de filas válidas/inválidas.
5. **Procesamiento (Backend):** Se procesa el archivo en chunks de 1000 filas. Se detectan duplicados mediante Hash.
6. **Resultado (Step 4):** Resumen final de importados vs omitidos.

### Archivos Compartidos
- **Servicio:** `app/Services/ExcelImportService.php`
- **Servicio Normalización:** `app/Services/ClientNormalizationService.php`

---

## Módulo: Ventas

Permite importar el historial de ventas (facturación).

- **Ruta:** `/ventas/importacion`
- **Tipo Interno:** `sale`
- **Vista:** `resources/views/livewire/pages/sales/import-wizard.blade.php`
- **Modelo:** `App\Models\Sale`

### Estructura Excel Esperada

| Header (Exacto) | Tipo Dato | Obligatorio | Notas |
|-----------------|-----------|-------------|-------|
| FECHA_EMI | Fecha | ✅ | Formatos: `dd/mm/yyyy`, `yyyy-mm-dd` |
| RAZON_SOCI | Texto | ✅ | Se normaliza para buscar Cliente |
| TOTAL_COMP | Moneda | ✅ | Soporta `1.234,56` (EU) |
| MONEDA | Texto | ❌ | Default: `USD` si es `CTE` |
| T_COMP | Texto | ❌ | Tipo de comprobante (ej: FAC) |
| N_COMP_REM | Texto | ❌ | Número de comprobante |
| ... | ... | ... | (Campos informativos adicionales) |

### Lógica Específica
- **Resolución de Clientes:** ✅ Habilitada. Si "RAZON_SOCI" no coincide con un alias, pide intervención del usuario.
- **Validación:** Rechaza filas sin fecha o sin monto.

---

## Módulo: Presupuestos

Importación de presupuestos emitidos.

- **Ruta:** `/presupuesto/importacion`
- **Tipo Interno:** `budget`
- **Vista:** `resources/views/livewire/pages/budget/import-wizard.blade.php`
- **Modelo:** `App\Models\Budget`

### Estructura Excel Esperada

| Header (Exacto) | Tipo Dato | Obligatorio | Notas |
|-----------------|-----------|-------------|-------|
| Fecha | Fecha | ✅ | |
| Empresa | Texto | ✅ | Equivale a Cliente |
| Monto | Moneda | ✅ | |
| Nº de Presupuesto | Texto | ❌ | Se guarda como comprobante |
| Centro de Costo | Texto | ❌ | |
| Nombre Proyecto | Texto | ❌ | |
| Fecha estimada de culminación | Fecha | ❌ | |

### Lógica Específica
- **Resolución de Clientes:** ✅ Habilitada.
- **Moneda:** Siempre asume `USD`.

---

## Módulo: Horas

Registro de imputación de horas del personal.

- **Ruta:** `/horas/importacion`
- **Tipo Interno:** `hour_detail`
- **Vista:** `resources/views/livewire/pages/hours/import-wizard.blade.php`
- **Modelo:** `App\Models\HourDetail`

### Estructura Excel Esperada

| Header (Exacto) | Tipo Dato | Obligatorio | Notas |
|-----------------|-----------|-------------|-------|
| Dia | Texto | ❌ | |
| Año | Número | ❌ | Determina fecha |
| Mes | Número | ❌ | Determina fecha |
| Personal | Texto | ✅ | Nombre del empleado |
| Funcion | Texto | ❌ | Rol |
| Proyecto | Texto | ✅ | Código o nombre proyecto |
| Hs | Número | ✅ | Cantidad de horas |
| Hs comun | Número | ❌ | Desglose |

### Lógica Específica
- **Fecha:** Se construye usando `Año` y `Mes` si no viene explícita.
- **Validación:** Requiere columna `Hs` numérica.

---

## Módulo: Compras

Detalle de compras y gastos por proyecto.

- **Ruta:** `/compras/importacion`
- **Tipo Interno:** `purchase_detail`
- **Vista:** `resources/views/livewire/pages/purchases/import-wizard.blade.php`
- **Modelo:** `App\Models\PurchaseDetail`

### Estructura Excel Esperada

| Header (Exacto) | Tipo Dato | Obligatorio | Notas |
|-----------------|-----------|-------------|-------|
| CC | Texto | ✅ | Centro de Costo |
| Empresa | Texto | ✅ | Proveedor |
| Año | Número | ❌ | |
| Descripción | Texto | ❌ | |
| Materiales comprados | Moneda | ❌ | Parseo inteligente (USD 1.000) |
| Resto (Valor) | Moneda | ❌ | |

### Lógica Específica
- **Moneda:** Default `USD`.
- **Parseo:** Maneja strings como `USD 1.200,50` eliminando el prefijo.

---

## Módulo: Tableros

Importación de detalles de tableros eléctricos.

- **Ruta:** `/tableros/importacion` (Verificar ruta exacta)
- **Tipo Interno:** `board_detail`
- **Vista:** `resources/views/livewire/pages/boards/import-wizard.blade.php`
- **Modelo:** `App\Models\BoardDetail`

### Estructura Excel Esperada

| Header (Exacto) | Tipo Dato | Obligatorio | Notas |
|-----------------|-----------|-------------|-------|
| Año | Número | ✅ | |
| Proyecto Numero | Texto | ✅ | |
| Cliente | Texto | ✅ | |
| Descripción Proyecto | Texto | ❌ | |
| Columnas | Número | ❌ | Cantidad física |
| Gabinetes | Número | ❌ | Cantidad física |

---

## Módulo: Proyectos Automatización

Gestión de proyectos del área de automatización.

- **Ruta:** `/proyectos_automatizacion/importacion`
- **Tipo Interno:** `automation_project`
- **Vista:** `resources/views/livewire/pages/automation-projects/import-wizard.blade.php`
- **Modelo:** `App\Models\AutomationProject`

### Estructura Excel Esperada

> **Nota:** Estructura simplificada (v2)

| Header (Exacto) | Tipo Dato | Obligatorio | Notas |
|-----------------|-----------|-------------|-------|
| Proyecto ID | Texto | ✅ | ID único del proyecto |
| Cliente | Texto | ✅ | Nombre del cliente |
| Proyecto Descripción | Texto | ❌ | Descripción detallada |
| FAT | Texto | ❌ | SI/NO (Auto mayúsculas) |
| PEM | Texto | ❌ | SI/NO (Auto mayúsculas) |

### Lógica Específica
- **Validación Rigurosa:** Chequea existencia de `Proyecto ID` y `Cliente`.
- **FAT/PEM:** Normaliza automáticamente a mayúsculas y tiene default `NO`.
- **Idempotencia:** Hash basado en `ID + Cliente + Descripción`.

---

## Mejoras UI/UX Propuestas

Ideas para mejorar la experiencia de importación en futuras iteraciones:

1. **Previsualización de Datos (Step 2.5):**
   - Antes de confirmar, mostrar una tabla con las primeras 5 filas tal como se interpretaron.
   - Permitiría ver si las fechas o montos se parsearon bien.

2. **Mapeo de Columnas Dinámico:**
   - Si el Excel tiene headers diferentes (ej: "Client" en vez de "Cliente"), permitir al usuario seleccionar qué columna corresponde a qué dato en la UI.
   - Eliminaría la necesidad de que el Excel sea exacto.

3. **Descarga de Plantillas:**
   - Agregar un botón "Descargar Plantilla de Ejemplo" en el Paso 1 de cada wizard.
   - Evita errores de estructura.

4. **Logs de Error Detallados:**
   - Si una fila falla, permitir descargar un `.txt` o `.csv` solo con las filas fallidas y la razón del error.

5. **Barra de Progreso Real:**
   - Implementar polling o websockets para ver progreso fila a fila en importaciones grandes (>1000 filas).

---

> **Nota:** Para mantener el sistema sano, cualquier nuevo módulo de importación DEBE seguir estrictamente el patrón aquí documentado.
