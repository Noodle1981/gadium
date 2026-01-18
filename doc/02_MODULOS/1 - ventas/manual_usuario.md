# Manual de Usuario - Módulo de Ventas

Bienvenido al módulo de Ventas de Gadium. Este documento te guiará en el uso de las principales funcionalidades para la gestión y análisis de ventas.

## 1. Dashboard de Ventas
El Tablero Principal es tu centro de comando. Aquí puedes ver el estado de tu negocio de un vistazo, sin gráficos complicados.

### ¿Cómo usarlo?
1. **Filtros de Tiempo**: En la parte superior derecha, encontrarás dos selectores:
   - **Año**: Selecciona el año fiscal (ej: 2024, 2025).
   - **Mes**: Selecciona un mes específico o "Todo el año" para ver el acumulado.
2. **Interpretación de Tarjetas**:
   - **Total Ventas**: Cantidad de facturas/operaciones realizadas.
   - **Ingresos Totales**: Suma total de dinero facturado.
   - **Ticket Promedio**: El valor promedio de cada venta (Ingresos / Cantidad).
   - **Clientes Únicos**: Cuántas empresas distintas compraron en ese periodo.
3. **Transportes**: Lista de los métodos de envío más utilizados, con una barra que indica su porcentaje del total.
4. **Cliente Estrella**: Muestra destacada del cliente que más compró en el periodo seleccionado.

---

## 2. Importación Masiva (Excel)
Para cargar grandes volúmenes de ventas históricas o mensuales.

**Requisitos del Archivo:**
- Formato: `.xlsx` o `.csv`
- Columnas obligatorias: `Fecha`, `Cliente`, `Monto`, `Transporte`.

**Pasos:**
1. Navega a **Ventas > Importación**.
2. Arrastra tu archivo al área de carga o haz clic para seleccionar.
3. El sistema previsualizará los datos. Verifica que las columnas coincidan.
4. Confirma la importación.
5. El sistema procesará las filas en segundo plano.

---

## 3. Carga Manual
Para registrar una venta individual rápidamente.

1. Ve a **Ventas > Nueva Venta**.
2. Completa el formulario.
   - **Fecha**: Calendario desplegable.
   - **Cliente**: Escribe el nombre. El sistema sugerirá clientes existentes.
   - **Monto**: Ingresa el valor numérico.
   - **Transporte**: Selecciona de la lista.
3. Guarda.

---

## 4. Resolución de Clientes (Calidad de Datos)
Esta herramienta es vital para mantener tu base de datos limpia. Úsala si notas que un cliente aparece duplicado con nombres ligeramente distintos (ej: "Google Inc" y "Google").

**Cuándo usarla:**
- Después de una importación masiva.
- Si notas duplicados en el Dashboard.

**Cómo funciona:**
1. Ve a **Ventas > Resolución de Clientes**.
2. Ingresa el nombre "incorrecto" o variante que encontraste.
3. El sistema buscará similitudes en la base de datos de clientes oficiales.
4. Tendrás dos opciones:
   - **Vincular (Alias)**: "Sí, 'Google' es en realidad 'Google Inc'". El sistema recordará esto para el futuro.
   - **Crear Nuevo**: "No, este es un cliente totalmente nuevo". El sistema lo dara de alta.

---

## 5. Historial de Ventas
Una vista detallada tipo planilla para auditoría.

- Muestra todas las operaciones individuales.
- Permite editar o corregir registros específicos.
- Buscador rápido por cliente o fecha.
