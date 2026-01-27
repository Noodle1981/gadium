# API Pública - Documentación

Esta API permite consultar datos operacionales de la plataforma Gaudium sin necesidad de autenticación.

## URL Base

```
https://tu-dominio.com/api/v1/public
```

## Formato de Respuesta

Todas las respuestas siguen el siguiente formato JSON:

```json
{
    "success": true,
    "data": [...]
}
```

---

## Endpoints

### 1. Ventas

#### Listar Ventas

```
GET /sales
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `cliente` | string | Filtrar por nombre de cliente (búsqueda parcial) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/sales?fecha_desde=2024-01-01&cliente=ACME"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `fecha` | date | Fecha de la venta |
| `client_id` | int | ID del cliente |
| `cliente_nombre` | string | Nombre del cliente |
| `monto` | decimal | Monto de la venta |
| `moneda` | string | Moneda (ej: USD, ARS) |
| `comprobante` | string | Número de comprobante |
| `cod_cli` | string | Código de cliente |
| `n_remito` | string | Número de remito |
| `t_comp` | string | Tipo de comprobante |
| `cond_vta` | string | Condición de venta |
| `porc_desc` | decimal | Porcentaje de descuento |
| `cotiz` | decimal | Cotización |
| `cod_transp` | string | Código de transporte |
| `nom_transp` | string | Nombre del transporte |
| `cod_articu` | string | Código de artículo |
| `descripcio` | string | Descripción |
| `cod_dep` | string | Código de depósito |
| `um` | string | Unidad de medida |
| `cantidad` | decimal | Cantidad |
| `precio` | decimal | Precio |
| `tot_s_imp` | decimal | Total sin impuestos |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

#### Obtener Venta por ID

```
GET /sales/{id}
```

**Ejemplo:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/sales/123"
```

---

### 2. Presupuestos

#### Listar Presupuestos

```
GET /budgets
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `cliente` | string | Filtrar por nombre de cliente (búsqueda parcial) |
| `estado` | string | Filtrar por estado (ej: En proceso, Finalizado, Cancelado) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/budgets?estado=En%20proceso&fecha_desde=2024-01-01"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `fecha` | date | Fecha del presupuesto |
| `client_id` | int | ID del cliente |
| `cliente_nombre` | string | Nombre del cliente |
| `monto` | decimal | Monto total |
| `moneda` | string | Moneda |
| `comprobante` | string | Orden de pedido |
| `centro_costo` | string | Centro de costo |
| `cost_center_id` | int | ID del centro de costo |
| `nombre_proyecto` | string | Nombre del proyecto |
| `project_id` | int | ID del proyecto |
| `fecha_oc` | date | Fecha de orden de compra |
| `fecha_estimada_culminacion` | date | Fecha estimada de culminación |
| `estado_proyecto_dias` | int | Estado del proyecto en días |
| `fecha_culminacion_real` | date | Fecha de culminación real |
| `estado` | string | Estado del proyecto |
| `enviado_facturar` | string | Enviado a facturar |
| `nro_factura` | string | Número de factura |
| `porc_facturacion` | string | Porcentaje de facturación |
| `saldo` | decimal | Saldo pendiente |
| `horas_ponderadas` | decimal | Horas ponderadas |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

#### Obtener Presupuesto por ID

```
GET /budgets/{id}
```

---

### 3. Horas

#### Listar Registros de Horas

```
GET /hours
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `personal` | string | Filtrar por nombre del empleado (búsqueda parcial) |
| `proyecto` | string | Filtrar por nombre del proyecto (búsqueda parcial) |
| `ano` | int | Filtrar por año |
| `mes` | int | Filtrar por mes (1-12) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/hours?ano=2024&mes=6&personal=Juan"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `dia` | string | Día de la semana |
| `fecha` | date | Fecha |
| `ano` | int | Año |
| `mes` | int | Mes |
| `personal` | string | Nombre del empleado |
| `funcion` | string | Función del empleado |
| `proyecto` | string | Nombre del proyecto |
| `project_id` | int | ID del proyecto |
| `horas_ponderadas` | decimal | Horas ponderadas |
| `ponderador` | decimal | Factor de ponderación |
| `hs` | decimal | Horas totales |
| `hs_comun` | decimal | Horas comunes |
| `hs_50` | decimal | Horas al 50% |
| `hs_100` | decimal | Horas al 100% |
| `hs_viaje` | decimal | Horas de viaje |
| `hs_pernoctada` | decimal | Horas de pernoctada |
| `hs_adeudadas` | decimal | Horas adeudadas |
| `vianda` | string | Vianda |
| `observacion` | string | Observaciones |
| `programacion` | string | Programación |
| `user_id` | int | ID del usuario |
| `job_function_id` | int | ID de la función |
| `guardia_id` | int | ID de guardia |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

#### Obtener Registro de Horas por ID

```
GET /hours/{id}
```

---

### 4. Compras

#### Listar Compras

```
GET /purchases
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `ano` | int | Filtrar por año |
| `empresa` | string | Filtrar por empresa (búsqueda parcial) |
| `cc` | string | Filtrar por código de centro de costo (búsqueda parcial) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/purchases?ano=2024&empresa=Proveedor"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `moneda` | string | Moneda |
| `cc` | string | Centro de costo |
| `ano` | int | Año |
| `empresa` | string | Nombre de la empresa |
| `descripcion` | string | Descripción |
| `materiales_presupuestados` | decimal | Materiales presupuestados |
| `materiales_comprados` | decimal | Materiales comprados |
| `resto_valor` | decimal | Resto en valor |
| `resto_porcentaje` | decimal | Resto en porcentaje |
| `porcentaje_facturacion` | decimal | Porcentaje de facturación |
| `supplier_id` | int | ID del proveedor |
| `cost_center_id` | int | ID del centro de costo |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

#### Obtener Compra por ID

```
GET /purchases/{id}
```

---

### 5. Tableros

#### Listar Tableros

```
GET /boards
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `ano` | int | Filtrar por año |
| `cliente` | string | Filtrar por cliente (búsqueda parcial) |
| `proyecto_numero` | string | Filtrar por número de proyecto (búsqueda parcial) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/boards?ano=2024&cliente=Industrial"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `ano` | int | Año |
| `proyecto_numero` | string | Número de proyecto |
| `cliente` | string | Nombre del cliente |
| `descripcion_proyecto` | string | Descripción del proyecto |
| `project_id` | int | ID del proyecto |
| `client_id` | int | ID del cliente |
| `columnas` | int | Cantidad de columnas |
| `gabinetes` | int | Cantidad de gabinetes |
| `potencia` | int | Potencia |
| `pot_control` | int | Potencia de control |
| `control` | int | Control |
| `intervencion` | int | Intervención |
| `documento_correccion_fallas` | int | Documento de corrección de fallas |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

#### Obtener Tablero por ID

```
GET /boards/{id}
```

---

### 6. Proyectos de Automatización

#### Listar Proyectos de Automatización

```
GET /automation-projects
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `cliente` | string | Filtrar por cliente (búsqueda parcial) |
| `proyecto_id` | string | Filtrar por ID de proyecto (búsqueda parcial) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/automation-projects?cliente=Minera"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `proyecto_id` | string | ID del proyecto de automatización |
| `cliente` | string | Nombre del cliente |
| `proyecto_descripcion` | string | Descripción del proyecto |
| `project_id` | int | ID del proyecto (relación) |
| `client_id` | int | ID del cliente (relación) |
| `fat` | string | FAT (Factory Acceptance Test) |
| `pem` | string | PEM |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

#### Obtener Proyecto de Automatización por ID

```
GET /automation-projects/{id}
```

---

### 7. Satisfacción de Clientes

#### Listar Encuestas de Satisfacción de Clientes

```
GET /client-satisfaction
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `cliente` | string | Filtrar por nombre de cliente (búsqueda parcial) |
| `proyecto` | string | Filtrar por proyecto (búsqueda parcial) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/client-satisfaction?fecha_desde=2024-01-01&cliente=Empresa"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `fecha` | date | Fecha de la encuesta |
| `client_id` | int | ID del cliente |
| `cliente_nombre` | string | Nombre del cliente |
| `proyecto` | string | Nombre del proyecto |
| `pregunta_1` | int | Satisfacción obra/producto (1-5) |
| `pregunta_2` | int | Desempeño técnico (1-5) |
| `pregunta_3` | int | Respuestas a necesidades (1-5) |
| `pregunta_4` | int | Plazo de ejecución (1-5) |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

**Escala de calificación:**
- 1: Muy insatisfecho
- 2: Insatisfecho
- 3: Neutral
- 4: Satisfecho
- 5: Muy satisfecho

#### Obtener Encuesta de Satisfacción de Cliente por ID

```
GET /client-satisfaction/{id}
```

---

### 8. Satisfacción de Personal

#### Listar Encuestas de Satisfacción de Personal

```
GET /staff-satisfaction
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `personal` | string | Filtrar por nombre del empleado (búsqueda parcial) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/staff-satisfaction?fecha_desde=2024-01-01"
```

**Campos de respuesta:**

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | int | ID único del registro |
| `personal` | string | Nombre/identificador del empleado |
| `fecha` | date | Fecha de la encuesta |
| `p1_mal` | boolean | Pregunta 1 - Trato del jefe: Mal |
| `p1_normal` | boolean | Pregunta 1 - Trato del jefe: Normal |
| `p1_bien` | boolean | Pregunta 1 - Trato del jefe: Bien |
| `p2_mal` | boolean | Pregunta 2 - Trato de compañeros: Mal |
| `p2_normal` | boolean | Pregunta 2 - Trato de compañeros: Normal |
| `p2_bien` | boolean | Pregunta 2 - Trato de compañeros: Bien |
| `p3_mal` | boolean | Pregunta 3 - Clima laboral: Mal |
| `p3_normal` | boolean | Pregunta 3 - Clima laboral: Normal |
| `p3_bien` | boolean | Pregunta 3 - Clima laboral: Bien |
| `p4_mal` | boolean | Pregunta 4 - Comodidad: Incómodo |
| `p4_normal` | boolean | Pregunta 4 - Comodidad: Normal |
| `p4_bien` | boolean | Pregunta 4 - Comodidad: Cómodo |
| `created_at` | datetime | Fecha de creación |
| `updated_at` | datetime | Fecha de actualización |

**Preguntas de la encuesta:**
1. ¿Cómo es el trato de su jefe?
2. ¿Cómo es el trato de sus compañeros?
3. ¿Cómo percibe el clima laboral?
4. ¿Se siente cómodo en su lugar de trabajo?

#### Obtener Encuesta de Satisfacción de Personal por ID

```
GET /staff-satisfaction/{id}
```

---

## Métricas y Análisis

### 9. Ventas por Cliente

#### Obtener totales de ventas agrupados por cliente

```
GET /metrics/sales-by-client
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/sales-by-client?fecha_desde=2024-01-01"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "clientes": [
            {
                "cliente_nombre": "ACME Corp",
                "total": 150000.50,
                "porcentaje": 35.5
            },
            {
                "cliente_nombre": "Empresa XYZ",
                "total": 100000.00,
                "porcentaje": 23.7
            }
        ],
        "total_general": 422535.00
    }
}
```

---

### 10. Top 20% Clientes (Análisis Pareto)

#### Obtener el porcentaje de ventas de los clientes del top 20%

```
GET /metrics/sales-top-20-clients
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/sales-top-20-clients?fecha_desde=2024-01-01"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "porcentaje_ventas_top_20": 78.5,
        "total_clientes": 50,
        "clientes_top_20": 10,
        "ventas_top_20": 331500.00,
        "ventas_totales": 422535.00,
        "clientes": [
            {"cliente_nombre": "ACME Corp", "total": 150000.50},
            {"cliente_nombre": "Empresa XYZ", "total": 100000.00}
        ]
    }
}
```

---

### 11. Porcentaje de Presupuestos Aprobados

#### Obtener el porcentaje de presupuestos con estado "Aprobado"

```
GET /metrics/budgets-approved-percentage
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/budgets-approved-percentage"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "porcentaje_aprobados": 65.5,
        "total_presupuestos": 200,
        "presupuestos_aprobados": 131
    }
}
```

---

### 12. Desvíos de Plazos en Presupuestos

#### Obtener desvíos porcentuales entre plazo estimado y real

Compara el plazo estimado (fecha_estimada_culminacion - fecha_oc) con el plazo real (fecha_culminacion_real - fecha_oc).

```
GET /metrics/budgets-deadline-deviations
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/budgets-deadline-deviations?fecha_desde=2024-01-01"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "desvios": [
            {
                "id": 1,
                "cliente_nombre": "ACME Corp",
                "nombre_proyecto": "Proyecto Alpha",
                "comprobante": "OC-2024-001",
                "fecha_oc": "2024-01-15",
                "fecha_estimada_culminacion": "2024-03-15",
                "fecha_culminacion_real": "2024-03-30",
                "plazo_estimado_dias": 59,
                "plazo_real_dias": 74,
                "desvio_dias": 15,
                "desvio_porcentaje": 25.42
            }
        ],
        "promedio_desvio_dias": 12.5,
        "promedio_desvio_porcentaje": 18.3,
        "total_presupuestos_analizados": 45
    }
}
```

**Interpretación del desvío:**
- **Valor positivo**: El proyecto se demoró más de lo estimado
- **Valor negativo**: El proyecto terminó antes de lo estimado
- **Valor cero**: El proyecto terminó exactamente en el plazo estimado

---

### 13. Total de Horas Ponderadas de Presupuestos

#### Obtener la suma total de horas_ponderadas de presupuestos

```
GET /metrics/budgets-total-weighted-hours
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/budgets-total-weighted-hours"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "total_horas_ponderadas": 15420.50
    }
}
```

---

### 14. Porcentaje de Horas para Proyectos < 1001

#### Obtener el porcentaje de horas_ponderadas para proyectos con valor numérico menor a 1001

```
GET /metrics/hours-projects-under-1001
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `ano` | int | Filtrar por año |
| `mes` | int | Filtrar por mes (1-12) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/hours-projects-under-1001?ano=2024"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "porcentaje_proyectos_menor_1001": 42.5,
        "horas_proyectos_menor_1001": 6500.25,
        "horas_totales": 15294.70
    }
}
```

---

### 15. Horas del Proyecto 606

#### Obtener la suma de horas_ponderadas para el proyecto 606

```
GET /metrics/hours-project-606
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |
| `ano` | int | Filtrar por año |
| `mes` | int | Filtrar por mes (1-12) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/hours-project-606?ano=2024&mes=6"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "horas_ponderadas_proyecto_606": 1250.75
    }
}
```

---

### 16. Presupuestos por Estado

#### Obtener cantidad de presupuestos agrupados por estado

```
GET /metrics/budgets-by-status
```

**Parámetros de filtro:**

| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `fecha_desde` | date | Filtrar desde fecha (formato: Y-m-d) |
| `fecha_hasta` | date | Filtrar hasta fecha (formato: Y-m-d) |

**Ejemplo de petición:**

```bash
curl -X GET "https://tu-dominio.com/api/v1/public/metrics/budgets-by-status?fecha_desde=2024-01-01"
```

**Ejemplo de respuesta:**

```json
{
    "success": true,
    "data": {
        "estados": [
            {
                "estado": "Aprobado",
                "cantidad": 85,
                "porcentaje": 42.5
            },
            {
                "estado": "Pendiente",
                "cantidad": 60,
                "porcentaje": 30.0
            },
            {
                "estado": "Rechazado",
                "cantidad": 35,
                "porcentaje": 17.5
            },
            {
                "estado": "Sin estado",
                "cantidad": 20,
                "porcentaje": 10.0
            }
        ],
        "total": 200
    }
}
```

**Campos de respuesta:**

| Campo | Descripción |
|-------|-------------|
| `estados` | Lista de estados con su cantidad y porcentaje |
| `estado` | Nombre del estado (o "Sin estado" si es nulo) |
| `cantidad` | Cantidad de presupuestos en ese estado |
| `porcentaje` | Porcentaje que representa del total |
| `total` | Total de presupuestos analizados |

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Solicitud exitosa |
| 404 | Registro no encontrado |
| 500 | Error interno del servidor |

## Ejemplo de Respuesta de Error

```json
{
    "success": false,
    "message": "Registro no encontrado"
}
```

---

## Ejemplos de Integración

### Python

```python
import requests

# Listar ventas del mes actual
response = requests.get(
    'https://tu-dominio.com/api/v1/public/sales',
    params={
        'fecha_desde': '2024-01-01',
        'fecha_hasta': '2024-01-31'
    }
)

data = response.json()
for sale in data['data']:
    print(f"Venta #{sale['id']}: {sale['cliente_nombre']} - ${sale['monto']}")
```

### JavaScript (Node.js / Browser)

```javascript
// Usando fetch
const response = await fetch(
    'https://tu-dominio.com/api/v1/public/budgets?estado=En%20proceso'
);
const data = await response.json();

data.data.forEach(budget => {
    console.log(`Presupuesto #${budget.id}: ${budget.cliente_nombre} - $${budget.monto}`);
});
```

### PHP

```php
<?php
$url = 'https://tu-dominio.com/api/v1/public/hours?ano=2024&mes=6';
$response = file_get_contents($url);
$data = json_decode($response, true);

foreach ($data['data'] as $hour) {
    echo "Empleado: {$hour['personal']} - Horas: {$hour['hs']}\n";
}
```

### cURL

```bash
# Obtener todas las ventas de un cliente específico
curl -X GET "https://tu-dominio.com/api/v1/public/sales?cliente=ACME"

# Obtener presupuestos finalizados
curl -X GET "https://tu-dominio.com/api/v1/public/budgets?estado=Finalizado"

# Obtener horas de un empleado en un mes específico
curl -X GET "https://tu-dominio.com/api/v1/public/hours?personal=Juan&ano=2024&mes=6"
```

---

## Notas Importantes

1. **Sin autenticación:** Esta API no requiere autenticación. Se recomienda implementar restricciones de IP o rate limiting en producción.

2. **Sin paginación:** Todos los endpoints devuelven todos los registros que coinciden con los filtros. Use los filtros de fecha para limitar la cantidad de datos.

3. **Búsqueda parcial:** Los filtros de texto (cliente, personal, proyecto, etc.) realizan búsqueda parcial (LIKE), por lo que no es necesario escribir el nombre completo.

4. **Formato de fechas:** Todas las fechas deben enviarse en formato `Y-m-d` (ej: 2024-01-15).

5. **Relaciones incluidas:** Los endpoints incluyen automáticamente las relaciones relevantes (cliente, proyecto, centro de costo, etc.) en la respuesta.
