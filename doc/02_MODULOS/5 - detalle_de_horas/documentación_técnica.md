# Documentación Técnica: Módulo de Detalle de Horas

## 1. Visión General
El módulo de Detalle de Horas permite la gestión y seguimiento de las horas trabajadas por el personal, facilitando la carga manual, la importación masiva desde Excel y el análisis de productividad mediante un Dashboard.

El diseño sigue la filosofía **"Clean & Cards"**, utilizando tarjetas blancas para la información y banners con gradientes naranjas para la identidad del módulo.

**Identidad Visual:**
*   **Color Principal**: Naranja (`orange-600` a `orange-800`). Usado en Dashboards, Banners, Botones y elementos destacados ("Tema Operativo").

---

## 2. Prevención de Duplicados (Hash de Idempotencia)
Para garantizar la integridad de los registros de tiempo y evitar duplicados (crucial para la liquidación de horas), se utiliza un mecanismo de **Hash Único**.

### Lógica del Hash
El sistema genera una clave única combinando los atributos que definen inequivocamente un registro de hora único.

**Campos que componen el Hash:**
1.  **Fecha**: Día del registro (YYYY-MM-DD).
2.  **Personal**: Nombre del colaborador (Normalizado).
3.  **Proyecto**: Identificador de la obra o proyecto.
4.  **Horas**: Cantidad de horas registradas (con 2 decimales).

**Código de Generación (Modelo `HourDetail`):**
```php
public static function generateHash(string $fecha, string $personal, string $proyecto, float $hs): string
{
    // 1. Normalización
    $fecha = substr($fecha, 0, 10);
    $personal = trim($personal);
    $proyecto = trim($proyecto);
    $hsFormat = number_format($hs, 2, '.', '');
    
    // 2. Concatenación
    $data = $fecha . '|' . $personal . '|' . $proyecto . '|' . $hsFormat;
    
    // 3. Hashing (SHA-256)
    return hash('sha256', $data);
}
```

### Flujo de Verificación
1.  **Validación Previa**: Al intentar guardar (Manual o Importación), se calcula el hash.
2.  **Consulta**: Si `HourDetail::existsByHash($hash)` devuelve `true`, se rechaza el registro.
3.  **Feedback**: Se informa al usuario que "Ya existe un registro idéntico para ese personal, fecha y proyecto".

---

## 3. Arquitectura del Módulo

### Modelo de Datos (`HourDetail`)
La tabla `hour_details` es el núcleo del almacenamiento.
*   **PK**: `id`
*   **Core**: `fecha`, `personal`, `funcion`, `proyecto`.
*   **Métricas**: `hs` (Total), `ponderador`, `horas_ponderadas`.
*   **Desglose**: `hs_comun`, `hs_50`, `hs_100`, `hs_viaje`, `hs_pernoctada`, `vianda`.
*   **Integridad**: `hash` (Unique Index).

### Componentes Livewire (Vistas)
Ubicación: `resources/views/livewire/pages/hours/`

| Componente | Ruta | Descripción |
| :--- | :--- | :--- |
| `dashboard.blade.php` | `/detalle_horas/dashboard` | **Panel de Control**. KPIs de horas totales, personal activo y productividad por proyecto. |
| `manual-create.blade.php` | `/detalle_horas/crear` | **Carga Manual**. Formulario con autocompletado de personal y validación de duplicados. |
| `import-wizard.blade.php` | `/detalle_horas/importacion` | **Asistente de Importación**. Carga masiva mediante Excel con validación previa. |
| `.../historial-horas.blade.php` | `/detalle_horas/historial_importacion` | **Historial**. Listado completo de registros para auditoría y edición. |

---

## 4. Funcionalidades Clave

### Autocompletado de Personal
En la carga manual, el campo "Personal" ofrece sugerencias en tiempo real basadas en registros históricos (`HourDetail::distinct('personal')`). Esto ayuda a estandarizar los nombres (evitando "JUAN PEREZ" vs "Juan Perez").

### Cálculo de Ponderación
El sistema calcula automáticamente las `horas_ponderadas` multiplicando `hs` * `ponderador` en tiempo real, permitiendo ajustes manuales si es necesario.

## 5. Reglas Visuales
*   Los banners de cabecera siempre incluyen el título y una descripción breve.
*   Los botones de acción principal (Guardar, Importar) son siempre Naranjas.
*   Las tarjetas de KPI utilizan iconografía consistente y colores para distinguir categorías (Naranja=Total, Azul=Personas, Púrpura=Proyectos).
