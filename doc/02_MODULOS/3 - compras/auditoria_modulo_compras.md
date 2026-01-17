# Auditoría - Módulo Compras

**Módulo:** Gestión de Compras de Materiales
**Versión:** 1.0.0
**Estado:** Finalizado

## 1. Estructura de Base de Datos
- **Tabla:** `purchase_details`
- **Campos Clave:** `moneda`, `cc`, `ano`, `empresa`, `descripcion`, `materiales_presupuestados`, `materiales_comprados`, `resto_valor`, `resto_porcentaje`, `hash`.
- **Integridad:** Uso de hash SHA256 para unicidad de registros.

## 2. Servicios
- **ExcelImportService:** 
  - Validado para tipo `purchase_detail`.
  - Soporta carga de archivos .xlsx.
  - Valida tipos de datos y columnas requerdas.

## 3. Seguridad y Accesos
- **Roles:**
  - `Admin` / `Manager`: Control total (View/Create/Edit).
  - `Gestor de Compras`: Control acceso a módulo propio.
- **Permisos:**
  - `view_purchases`, `create_purchases`, `edit_purchases` implementados y asignados.

## 4. Interfaz de Usuario
- **Dashboard:** Métricas claras de ejecución presupuestaria.
- **Importador:** Wizard paso a paso con validación previa.
- **Manual:** Formularios reactivos con cálculos automáticos.
- **Historial:** Tabla responsiva con filtros implícitos (latest 50).

## 5. Pruebas Pendientes (Usuario)
- Cargar archivo `compras.xlsx` proporcionado para validar parsing de formatos específicos de moneda.
- Verificar acceso con usuario `compras@gadium.com`.

## Conclusión
El módulo cumple con los requisitos funcionales de replicar la experiencia de usuario del módulo de Horas pero adaptado a la lógica financiera de Compras.
