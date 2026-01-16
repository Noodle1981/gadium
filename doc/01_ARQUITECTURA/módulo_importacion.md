# Módulo de Importación - Arquitectura y Análisis

## Resumen Ejecutivo

Este documento analiza la arquitectura del sistema de importación de Excel utilizado en todos los módulos del sistema Gadium. El objetivo es documentar los patrones comunes y encontrar las diferencias que causan el fallo en el módulo de **Proyectos de Automatización**.

---

## Módulos Analizados

| Módulo | Ruta | Tipo | Estado |
|--------|------|------|--------|
| Ventas | `/ventas/importacion` | `sale` | ✅ Funcionando |
| Presupuesto | `/presupuesto/importacion` | `budget` | ✅ Funcionando |
| Horas | `/horas/importacion` | `hour_detail` | ✅ Funcionando |
| Compras | `/compras/importacion` | `purchase_detail` | ✅ Funcionando |
| **Proyectos Automatización** | `/proyectos_automatizacion/importacion` | `automation_project` | ❌ **FALLA** |

---

## Arquitectura Común

### 1. Componente Livewire (import-wizard.blade.php)

Todos los módulos siguen el mismo patrón de 4 pasos:

```
Paso 1: Carga de Archivo
    ↓
Paso 2: Resolución de Clientes (solo ventas/presupuesto)
    ↓
Paso 3: Confirmación
    ↓
Paso 4: Resultado
```

#### Propiedades Comunes

```php
public $step = 1;
public $file;
public $storedFilePath;  // #[Locked]
public $type;            // Tipo de importación
public $totalRows = 0;
public $validRows = 0;
public $validationErrors = [];
public $processing = false;
public $analyzing = false;
public $importedCount = 0;
public $skippedCount = 0;
```

#### Métodos Comunes

1. **`updatedFile()`** - Se ejecuta cuando se selecciona un archivo
2. **`analyzeFile()`** - Valida y analiza el archivo
3. **`startImport()`** - Ejecuta la importación
4. **`resetWizard()`** - Reinicia el wizard

---

### 2. Flujo de Importación

#### Paso 1: Análisis del Archivo

```php
public function analyzeFile()
{
    // 1. Validar archivo existe
    if (!$this->file) {
        throw new \Exception('No se ha seleccionado ningún archivo.');
    }
    
    // 2. Generar nombre único
    $filename = uniqid('import_') . '.' . $extension;
    
    // 3. Guardar archivo
    $path = $this->file->storeAs('imports', $filename);
    
    // 4. Construir ruta completa
    $this->storedFilePath = storage_path('app/private') . DIRECTORY_SEPARATOR . 
                           str_replace('/', DIRECTORY_SEPARATOR, $path);
    
    // 5. Verificar que existe
    if (!file_exists($this->storedFilePath)) {
        throw new \Exception("El archivo no se guardó correctamente.");
    }
    
    // 6. Analizar con ExcelImportService
    $analysis = $service->validateAndAnalyze($this->storedFilePath, $this->type);
    
    // 7. Guardar resultados
    $this->totalRows = $analysis['total_rows'] ?? 0;
    $this->validRows = $analysis['valid_rows'] ?? 0;
    $this->validationErrors = $analysis['errors'] ?? [];
    
    // 8. Limpiar file property
    $this->file = null;
    
    // 9. Decidir siguiente paso
    if (!empty($this->validationErrors)) {
        // Quedarse en paso 1
        return;
    }
    
    if (!empty($this->unknownClients)) {
        $this->step = 2; // Ir a resolución
    } else {
        $this->step = 3; // Ir a confirmación
    }
}
```

#### Paso 2: Importación

```php
public function startImport()
{
    $this->processing = true;
    
    // 1. Verificar archivo existe
    if (!$this->storedFilePath || !file_exists($this->storedFilePath)) {
        $this->addError('file', 'Archivo no encontrado.');
        return;
    }
    
    // 2. Leer filas del Excel
    $rows = $service->readExcelRows($this->storedFilePath);
    
    // 3. Procesar en chunks de 1000
    $chunk = [];
    foreach ($rows as $row) {
        $chunk[] = $row;
        
        if (count($chunk) >= 1000) {
            $stats = $service->importChunk($chunk, $this->type);
            $this->importedCount += $stats['inserted'];
            $this->skippedCount += $stats['skipped'];
            $chunk = [];
        }
    }
    
    // 4. Procesar resto
    if (!empty($chunk)) {
        $stats = $service->importChunk($chunk, $this->type);
        $this->importedCount += $stats['inserted'];
        $this->skippedCount += $stats['skipped'];
    }
    
    // 5. Limpiar archivo
    @unlink($this->storedFilePath);
    
    // 6. Ir a paso 4
    $this->step = 4;
    $this->processing = false;
}
```

---

## ExcelImportService - Servicio Central

### Métodos Principales

#### 1. `validateAndAnalyze(string $filePath, string $type): array`

Valida el archivo y retorna:
```php
[
    'total_rows' => int,
    'valid_rows' => int,
    'errors' => array,
    'unknown_clients' => array  // Solo para sale/budget
]
```

#### 2. `readExcelRows(string $filePath): array`

Lee el Excel y retorna array de filas con headers como keys.

**IMPORTANTE:** Maneja headers duplicados agregando sufijo `_2`, `_3`, etc.

```php
// Si hay dos columnas "Proyecto":
// Primera: "Proyecto"
// Segunda: "Proyecto_2"
```

#### 3. `importChunk(array $rows, string $type): array`

Importa un chunk de filas y retorna:
```php
[
    'inserted' => int,
    'skipped' => int
]
```

---

## Comparación: Módulos Funcionando vs Proyectos Automatización

### ✅ Módulos que Funcionan (Ventas, Presupuesto, Horas, Compras)

**Características comunes:**

1. **Ruta de Storage:** `storage_path('app/private')`
2. **Stepper:** Pasos 1, 2 (opcional), 3, 4
3. **Botón en Paso 3:** 
   ```blade
   <button wire:click="startImport" 
           wire:loading.attr="disabled">
       <span wire:loading.remove>Iniciar Importación</span>
       <span wire:loading>Procesando...</span>
   </button>
   ```
4. **Manejo de errores:** Try-catch en ambos métodos
5. **Limpieza:** `$this->file = null` después de guardar

### ❌ Proyectos Automatización - Problemas Identificados

#### Problema 1: Headers Duplicados

**Excel tiene:**
- Columna 1: "Proyecto" (ID)
- Columna 2: "Proyecto" (Descripción)

**Solución implementada:**
- `readExcelRows()` renombra a "Proyecto" y "Proyecto_2"
- `importChunk()` usa estos nombres

#### Problema 2: Código Inicial Incorrecto

**Código original (NO FUNCIONA):**
```php
$headers = array_keys($row);
$proyectoKeys = array_keys(array_filter($headers, 
    function($h) { return $h === 'Proyecto'; }));
```

**Problema:** `array_keys($row)` solo devuelve keys únicas. Si hay dos "Proyecto", PHP solo mantiene la última.

**Código corregido:**
```php
$proyectoId = trim($row['Proyecto'] ?? '');
$proyectoDescripcion = trim($row['Proyecto_2'] ?? $proyectoId);
```

#### Problema 3: Método `validateHeaders` No Existe

**Línea problemática en `validateAndAnalyze()`:**
```php
$this->validateHeaders($headers, $type);  // ❌ Método no existe
```

**Solución:**
```php
// TODO: validateHeaders method doesn't exist, commenting out for now
// $this->validateHeaders($headers, $type);
```

---

## Estructura de Datos por Módulo

### Ventas (`sale`)

**Tabla:** `sales`

**Campos:**
- `cliente_id` (FK)
- `fecha`
- `monto`
- `comprobante`
- `moneda`
- `hash`

**Headers Excel:**
- Empresa
- Fecha
- Monto
- Orden de Pedido
- Moneda

### Presupuesto (`budget`)

**Tabla:** `budgets`

**Campos:** (Mismos que ventas)

**Headers Excel:** (Mismos que ventas)

### Horas (`hour_detail`)

**Tabla:** `hour_details`

**Campos:**
- `proyecto`
- `cliente`
- `horas`
- `fecha`
- `hash`

**Headers Excel:**
- Proyecto
- Cliente
- Horas
- Fecha

### Compras (`purchase_detail`)

**Tabla:** `purchase_details`

**Campos:**
- `proyecto_numero`
- `cliente`
- `descripcion_proyecto`
- `materiales_comprados`
- `resto_valor`
- `resto_porcentaje`
- `porcentaje_facturacion`
- `hash`

**Headers Excel:**
- Proyecto Numero
- Cliente
- Descripción Proyecto
- Materiales comprados
- Resto (Valor)
- Resto (%)
- % de facturación

### Proyectos Automatización (`automation_project`)

**Tabla:** `automation_projects`

**Campos:**
- `proyecto_id`
- `cliente`
- `proyecto_descripcion`
- `fat` (SI/NO)
- `pem` (SI/NO)
- `hash`

**Headers Excel:**
- **Proyecto** (ID) → Renombrado a "Proyecto"
- **Proyecto** (Descripción) → Renombrado a "Proyecto_2"
- Cliente
- FAT
- PEM

---

## Sistema de Hash para Idempotencia

Todos los módulos usan un hash SHA256 para detectar duplicados:

```php
public static function generateHash(array $data): string
{
    return hash('sha256', json_encode($data));
}

// Scope para buscar por hash
public function scopeByHash($query, string $hash)
{
    return $query->where('hash', $hash);
}
```

**Antes de insertar:**
```php
if (Model::byHash($hash)->exists()) {
    $skipped++;
    continue;
}
```

---

## Recomendaciones

### Para Proyectos Automatización

1. ✅ **HECHO:** Modificar `readExcelRows()` para manejar headers duplicados
2. ✅ **HECHO:** Simplificar lógica de importación usando "Proyecto" y "Proyecto_2"
3. ✅ **HECHO:** Comentar llamada a `validateHeaders()` inexistente
4. ⏳ **PENDIENTE:** Probar importación completa
5. ⏳ **PENDIENTE:** Verificar que datos se guardan correctamente en BD

### Para Futuros Módulos

1. **Usar plantilla estándar** de import-wizard de ventas/presupuesto
2. **Evitar headers duplicados** en Excel siempre que sea posible
3. **Implementar `validateHeaders()`** si se necesita validación específica
4. **Mantener consistencia** en nombres de archivos temporales
5. **Usar mismo path de storage:** `storage_path('app/private')`

---

## Checklist de Implementación

Para crear un nuevo módulo de importación:

- [ ] Crear migración con campo `hash`
- [ ] Crear modelo con `generateHash()` y `scopeByHash()`
- [ ] Agregar tipo al `ExcelImportService::importChunk()`
- [ ] Crear `import-wizard.blade.php` basado en plantilla
- [ ] Definir headers esperados del Excel
- [ ] Implementar lógica de extracción de datos
- [ ] Probar con archivo Excel real
- [ ] Verificar detección de duplicados
- [ ] Documentar estructura de Excel esperada

---

## Conclusión

El sistema de importación es robusto y consistente en todos los módulos. El problema con Proyectos de Automatización se debe principalmente a:

1. **Headers duplicados en Excel** que PHP no puede manejar nativamente con `array_combine()`
2. **Código complejo** intentando detectar columnas duplicadas de forma incorrecta
3. **Método faltante** `validateHeaders()` que causaba errores silenciosos

**Solución:** Implementar renombrado automático de headers duplicados en `readExcelRows()` y simplificar la lógica de importación.
