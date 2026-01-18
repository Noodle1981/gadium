# Manual de Usuario: Gestión de Detalle de Horas

## 1. Introducción
El módulo de **Detalle de Horas** le permite registrar, controlar y analizar las horas trabajadas por el personal en los distintos proyectos. Este módulo está diseñado con una interfaz moderna ("Clean & Cards") de color naranja, facilitando la identificación visual de las operaciones.

---

## 2. Dashboard de Horas
Al ingresar al módulo (`/detalle_horas/dashboard`), verá un panel de control con indicadores clave:
*   **KPIs Superiores**: 
    *   **Total Horas**: Suma de horas registradas en el periodo seleccionado.
    *   **Personal Activo**: Cantidad de colaboradores distintos que han reportado horas.
    *   **Proyectos**: Cantidad de obras o servicios activos.
    *   **Promedio**: Promedio de horas por registro.
*   **Filtros**: Puede filtrar la información por **Año** y **Mes** usando los selectores en la parte superior derecha.
*   **Gráficos**:
    *   **Horas por Proyecto**: Lista ordenada de proyectos con mayor carga horaria.
    *   **Mayor Productividad**: Tarjeta destacada con el colaborador que más horas ha registrado en el periodo.

---

## 3. Carga Manual de Horas
Para registrar horas individualmente, diríjase a **Cargar Horas Manualmente** (`/detalle_horas/crear`).

### Pasos para la carga:
1.  **Datos Principales**:
    *   **Fecha**: Seleccione el día.
    *   **Personal**: Comience a escribir el nombre del colaborador. El sistema le **sugerirá nombres existentes** para evitar duplicados (ej: evitar crear "Juan Perez" si ya existe "Juan Pérez").
    *   **Función**: Rol desempeñado.
    *   **Proyecto/Obra**: Identificador del proyecto.
2.  **Métricas**:
    *   **Hs Totales**: Ingrese la cantidad de horas.
    *   **Ponderador**: (Opcional) Factor de ajuste. El sistema calculará automáticamente las "Horas Ponderadas".
3.  **Desglose y Adicionales**:
    *   Puede detallar horas al 50%, 100%, viaje, etc.
    *   Indique si hubo **Vianda** o **Pernoctada**.

> **¡Importante!** El sistema verifica automáticamente si ya existe un registro para ese Personal, Fecha, Proyecto y Cantidad de Horas. Si intenta duplicarlo, recibirá una alerta.

---

## 4. Importación Masiva (Excel)
Para cargar grandes volúmenes de datos, utilice el botón **"Importación Automática Excel"** ubicado en la parte superior de la pantalla de Carga Manual o Historial (`/detalle_horas/importacion`).

### Proceso de Importación:
1.  **Carga**: Suba su archivo Excel (`.xlsx` o `.xls`).
2.  **Análisis**: El sistema validará el formato y buscará posibles duplicados.
3.  **Confirmación**: Revise el resumen (filas válidas vs errores) y confirme la importación.

---

## 5. Historial y Edición
En la sección **Historial de Horas** (`/detalle_horas/historial_importacion`):
*   Verá una tabla con los últimos registros cargados.
*   Utilice el botón **"Editar"** en cada fila para corregir información de un registro específico.
*   Desde el banner superior puede acceder rápidamente tanto a la **Carga Manual** como a la **Importación Automática**.
