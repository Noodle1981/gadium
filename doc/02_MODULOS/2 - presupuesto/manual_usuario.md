# Manual de Usuario: Módulo de Presupuestos

## Introducción
El módulo de Presupuestos le permite gestionar todas las estimaciones comerciales de su empresa. Desde aquí podrá cargar nuevos presupuestos, importar listados masivos desde Excel y analizar el rendimiento comercial mediante un tablero de control.

---

## 1. Panel de Presupuestos (Dashboard)
Al ingresar al módulo, verá el **Panel de Presupuestos** (`/presupuesto/dashboard`).

**¿Qué información muestra?**
*   **Tarjetas de KPIs (Indicadores Clave)**:
    *   **Total Presupuestos**: Cantidad de estimaciones emitidas en el periodo.
    *   **Monto Presupuestado**: Suma total de los valores (en miles de dólares 'K').
    *   **Monto Promedio**: Valor medio de cada presupuesto.
    *   **Clientes Potenciales**: Cantidad de clientes únicos cotizados.
*   **Gráfico de Estados**: Barra visual que muestra cuántos presupuestos están "En Proceso", "Finalizados", etc.
*   **Cliente Destacado**: Una tarjeta especial que resalta al cliente con mayor volumen de cotización.

**Filtros**:
En la esquina superior derecha puede filtrar toda la información por **Año** y **Mes**.

---

## 2. Crear un Presupuesto (Carga Manual)
Para registrar un presupuesto individualmente:
1.  Vaya al menú lateral y seleccione **"Crear Presupuesto"**.
2.  **Búsqueda de Cliente**: Escriba el nombre del cliente. El sistema le sugerirá clientes existentes para evitar duplicados. Si es nuevo, simplemente escriba el nombre completo.
3.  **Datos Obligatorios**: Complete Fecha, Orden de Pedido (Comprobante) y Monto.
4.  **Guardar**: Presione el botón naranja **"Guardar Presupuesto"**.

> **Nota**: Si intenta cargar un presupuesto con la misma Fecha, Cliente, Comprobante y Monto que uno existente, el sistema le avisará que es un **Duplicado** y no permitirá guardarlo.

---

## 3. Importación Masiva (Excel)
Si tiene muchos presupuestos para cargar, use la importación automática.

**Acceso**:
*   Desde la pantalla de "Crear Presupuesto", haga clic en el botón superior **"IMPORTACIÓN AUTOMÁTICA EXCEL"**.

**Pasos del Asistente:**
1.  **Carga**: Arrastre su archivo Excel (`.xlsx` o `.csv`) al recuadro.
2.  **Validación**: El sistema revisará el archivo.
    *   Si hay errores (ej. fechas mal formadas), se los mostrará para corregir.
    *   Si hay **Duplicados**, el sistema los detectará (Bandera Roja) y los omitirá automáticamente para no ensuciar la base de datos.
3.  **Confirmación**: Verá un resumen de cuántos registros se crearán. Haga clic en **"Importar Automáticamente"**.

---

## 4. Historial de Importaciones
Para ver qué se ha cargado anteriormente:
1.  Vaya al menú lateral -> **Historial Importación**.
2.  Verá una lista cronológica de todos los presupuestos.
3.  Use el botón **"Crear Presupuesto"** en el encabezado si necesita agregar uno nuevo manualmente.

---

## Preguntas Frecuentes

**¿Por qué no veo el botón de Importar en el Historial?**
Para simplificar el uso, el botón de Importación Masiva se ha movido a la pantalla de **Crear Presupuesto**, ya que es una forma alternativa de "crear" datos.

**¿Puedo editar un presupuesto?**
Actualmente el sistema está diseñado para la carga y análisis. Si necesita corregir un presupuesto cargado erróneamente, contacte al administrador para realizar un ajuste o cargar una nota rectificativa.
