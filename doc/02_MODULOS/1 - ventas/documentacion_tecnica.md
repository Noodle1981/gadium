# Documentación Técnica - Módulo de Ventas

Este documento detalla la arquitectura, modelos de datos y servicios clave del Módulo de Ventas del sistema Gadium.

## 1. Modelo de Datos

### Tabla Principal: `sales`
Almacena el registro histórico de todas las transacciones. Esta tabla es el corazón del módulo y se alimenta principalmente de importaciones masivas.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BigInt | PK |
| `fecha` | Date | Fecha de la transacción |
| `cliente_nombre` | String | Nombre del cliente (Normalizado) |
| `nom_transp` | String | Nombre del transporte (Normalizado) |
| `monto` | Decimal | Monto de la venta |
| `producto` | String | Producto vendido (si aplica) |
| `...` | ... | Otros metadatos |

**Nota sobre Clientes:** Aunque existe una tabla `clients` maestra, la tabla `sales` mantiene el nombre del cliente desnormalizado (`cliente_nombre`) para integridad histórica y rendimiento en reportes masivos. La normalización ocurre PREVIO a la inserción o mediante procesos de limpieza.

### Tabla Maestra: `clients`
Catálogo oficial de clientes únicos. Utilizada por el `ClientNormalizationService` para resolver alias y variaciones.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | BigInt | PK |
| `nombre` | String | Nombre oficial del cliente |
| `alias_json` | JSON | Lista de nombres alternativos conocidos |

---

## 2. Servicios Clave

### ClientNormalizationService
Ubicación: `app/Services/ClientNormalizationService.php`

Este servicio es responsable de asegurar la calidad de los datos de clientes.

**Funciones Principales:**
- `findBestMatch(string $dirtyName)`: Utiliza `levenshtein` y `similar_text` para encontrar el cliente existente más probable dado un nombre sucio.
- `createAlias(int $clientId, string $alias)`: Vincula un nombre variante a un cliente maestro.
- `normalize(string $name)`: Limpia strings (trim, uppercase) para comparaciones.

---

## 3. Componentes Livewire

### Dashboard (`pages.sales.dashboard`)
Un componente optimizado para visualización rápida de KPIs.

**Lógica Simplificada:**
- **Sin Gráficos JS**: Se eliminaron dependencias de Chart.js para evitar problemas de renderizado en Livewire.
- **Filtros**: Año y Mes (selectores simples).
- **Métricas**:
  - `Total Ventas`: Count simple.
  - `Monto Total`: Suma de columna `monto`.
  - `Ticket Promedio`: Monto / Ventas.
  - `Clientes Únicos`: Count distinct de `cliente_nombre`.
- **Top Cliente**: Query simple con `orderByDesc('total_compras')->first()`.
- **Transportes**: Lista calculada en PHP para barras de progreso CSS.

### Resolución de Clientes (`pages.clients.resolution`)
Interfaz para intervención humana en la calidad de datos.

- Permite buscar clientes por nombre.
- Muestra sugerencias de clientes existentes (Match > 80%).
- Acciones: "Vincular como Alias" o "Crear Nuevo Cliente".

---

## 4. Procesos de Importación

El sistema soporta importación masiva mediante Excel (Laravel Excel).

1. **Subida**: Archivo temporal.
2. **Validación**: Verifica estructura de columnas.
3. **Mapeo Inteligente**:
   - Fechas: Convierte formatos Excel numéricos a Date.
   - Nombres: Pasa por un filtro de limpieza básico.
4. **Inserción**: Utiliza `insert` masivos (chunks) para rendimiento.
