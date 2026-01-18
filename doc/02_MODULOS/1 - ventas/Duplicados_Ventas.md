# Lógica de Detección de Duplicados - Módulo Ventas

## Campo de Unicidad

El sistema utiliza un campo **`hash`** (SHA-256) para garantizar la idempotencia en las importaciones de ventas.

### Composición del Hash

El hash se genera combinando los siguientes campos:

```
fecha + cliente_nombre_normalizado + comprobante + monto
```

**Implementación** (`Sale.php`):
```php
public static function generateHash(string $fecha, string $clienteNombre, string $comprobante, float $monto): string
{
    $normalized = Client::normalizeClientName($clienteNombre);
    $data = $fecha . '|' . $normalized . '|' . $comprobante . '|' . number_format($monto, 2, '.', '');
    
    return hash('sha256', $data);
}
```

### Campos Clave

1. **`fecha`**: Fecha de la venta (formato: YYYY-MM-DD)
2. **`cliente_nombre`**: Nombre del cliente (normalizado para evitar variaciones de mayúsculas/espacios)
3. **`comprobante`**: Número de comprobante (ej: "FAC-001234")
4. **`monto`**: Monto total de la venta (con 2 decimales)

### Validación en Base de Datos

- **Constraint**: `$table->string('hash')->unique();` en la migración
- **Verificación**: `Sale::existsByHash($hash)` antes de insertar

## ¿Es `N_COMP` suficiente para unicidad?

**NO**. El campo `comprobante` (equivalente a `N_COMP` en Tango) **NO es único por sí solo** porque:

1. **Diferentes clientes** pueden tener el mismo número de comprobante
2. **Diferentes fechas** pueden reutilizar números de comprobante
3. **Diferentes montos** pueden existir para el mismo comprobante (correcciones, notas de crédito)

Por eso el sistema usa una **combinación de 4 campos** para garantizar unicidad real.

## Ejemplo Práctico

### Venta Original
```
Fecha: 2026-01-15
Cliente: "ACME Corp"
Comprobante: "FAC-001234"
Monto: 15000.00
Hash: a3f5b2c1d4e6f7a8b9c0d1e2f3a4b5c6...
```

### Intento de Duplicado (será rechazado)
```
Fecha: 2026-01-15
Cliente: "ACME CORP" (normalizado a "acme corp")
Comprobante: "FAC-001234"
Monto: 15000.00
Hash: a3f5b2c1d4e6f7a8b9c0d1e2f3a4b5c6... (mismo hash)
✗ RECHAZADO: Hash duplicado
```

### Venta Diferente (será aceptada)
```
Fecha: 2026-01-15
Cliente: "ACME Corp"
Comprobante: "FAC-001234"
Monto: 15500.00 (monto diferente)
Hash: b4g6c3d5e7f8a9b0c1d2e3f4a5b6c7d8... (hash diferente)
✓ ACEPTADO: Hash único
```

## Recomendaciones

- **NO** confiar solo en `N_COMP` para evitar duplicados
- **SÍ** usar el sistema de hash existente que combina múltiples campos
- Si necesitas validación adicional, considera agregar índices compuestos en la base de datos