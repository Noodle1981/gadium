# Documentación Técnica: Módulo de Presupuestos

## 1. Visión General
El módulo de Presupuestos permite la gestión integral de las estimaciones comerciales, ofreciendo herramientas para la carga manual, importación masiva desde Excel, y visualización analítica mediante un Dashboard.

El diseño sigue la filosofía **"Clean & Cards"**, utilizando tarjetas blancas con sombras suaves para los datos y banners con gradientes para los encabezados.

**Identidad Visual:**
*   **Color Principal**: Verde (`green-600` a `green-800`). Usado en Dashboards, Sidebar y elementos de identidad.
*   **Color de Acción**: Naranja (`orange-600`). Usado en botones (Guardar, Importar) y banners de acción para mantener consistencia con el flujo comercial (Ventas).

---

## 2. Prevención de Duplicados (La "Bandera")
Para evitar la duplicidad de datos y mantener la integridad del sistema (especialmente importante dado que el sistema no permite borrar registros fácilmente por reglas de auditoría), se implementa un mecanismo de **Idempotencia basado en Hash**.

### Lógica de la Bandera (Hash Único)
Cada vez que se intenta crear o importar un presupuesto, el sistema genera una "huella digital" única (Hash) combinando los campos críticos que definen una transacción única.

**Campos que componen el Hash:**
1.  **Fecha**: Fecha de emisión del presupuesto (YYYY-MM-DD).
2.  **Cliente Normalizado**: Nombre del cliente limpio de espacios y mayúsculas.
3.  **Comprobante**: Número de Orden de Pedido o identificador del presupuesto.
4.  **Monto**: Valor total de la operación (con 2 decimales).

**Código de Generación (Modelo `Budget`):**
```php
public static function generateHash(string $fecha, string $clienteNombre, string $comprobante, float $monto): string
{
    // 1. Normalización
    $fecha = substr($fecha, 0, 10);
    $clienteNormalizado = Client::normalizeClientName($clienteNombre);
    $montoFormat = number_format($monto, 2, '.', '');
    
    // 2. Concatenación
    $data = $fecha . '|' . $clienteNormalizado . '|' . $comprobante . '|' . $montoFormat;
    
    // 3. Hashing (SHA-256)
    return hash('sha256', $data);
}
```

### Flujo de Verificación
1.  **Antes de Guardar**: El sistema calcula el hash de los datos entrantes.
2.  **Consulta DB**: Busca en la columna `hash` de la tabla `budgets`.
3.  **Bandera Roja**: Si el hash existe, se detiene el proceso y se lanza un error de validación: *"Este presupuesto ya existe en el sistema"*.

---

## 3. Arquitectura del Módulo

### Modelos de Datos (`Budget`)
La tabla `budgets` almacena la información principal.
*   **PK**: `id`
*   **FK**: `client_id` (Relación con `clients` para normalización).
*   **Core**: `fecha`, `monto`, `moneda` (Default USD), `comprobante`.
*   **Tracking**: `estado` (En proceso, Finalizado), `enviado_facturar` (Flag).
*   **Seguridad**: `hash` (Unique Index).

### Componentes Livewire (Vistas)
Ubicación: `resources/views/livewire/pages/budget/`

| Componente | Ruta | Descripción |
| :--- | :--- | :--- |
| `dashboard.blade.php` | `/presupuesto/dashboard` | **Panel Analítico**. KPIs de totales, promedios y desglose por estado. |
| `manual-create.blade.php` | `/presupuesto/crear` | **Formulario de Carga**. Incluye normalización de clientes y botón de "Importación Automática". |
| `import-wizard.blade.php` | `/presupuesto/importacion` | **Asistente de Importación**. Proceso de 3 pasos (Subir -> Validar -> Confirmar). |
| `.../historial-presupuesto.blade.php` | `/presupuesto/historial-importacion` | **Listado Histórico**. Tabla con filtros y acceso a detalles. |

---

## 4. Normalización de Clientes
El formulario de creación manual utiliza el servicio `ClientNormalizationService`.
*   **Autocompletado**: Al escribir 2 caracteres, busca clientes existentes (`LIKE %...%`).
*   **Selección Inteligente**: Si el usuario selecciona un cliente de la lista, se usa su ID.
*   **Creación Implícita**: Si el usuario escribe un nombre nuevo, el sistema lo crea automáticamente en el *background* al guardar el presupuesto, asegurando que no se creen duplicados como "Cliente A" y "cliente a".

## 5. Reglas de Negocio Implementadas
1.  **Moneda Base**: El sistema opera principalmente en **USD**.
2.  **Estados**: Los presupuestos nacen con estado "Pendiente" o se definen manualmente.
3.  **Navegación**:
    *   La entrada principal para datos es **Crear Presupuesto** (Manual).
    *   Desde allí se puede saltar a **Importación Automática**.
    *   El **Historial** es solo de consulta y auditoría.
