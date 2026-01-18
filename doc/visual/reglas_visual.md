# Reglas Visuales y de Diseño - Sistema Gadium

Este documento define los estándares visuales y de UX para garantizar la consistencia en todos los módulos del sistema.

## 1. Estructura General de Página

El sistema utiliza un diseño basado en **contenedores centrados pero expandibles**.

- **Contenedor Principal**: Por defecto, el contenido debe estar centrado con un ancho máximo de `max-w-7xl`.
- **Excepción**: Tablas densas o vistas de mapas pueden requerir ancho completo (`max-w-full`), pero siempre deben ofrecer una vista predeterminada alineada.

```html
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Contenido -->
    </div>
</div>
```

---

## 2. Encabezados de Módulo (Headers)

Cada vista principal debe tener un "Hero Header" distintivo que se integra con el navbar sticky.

### Estándar Visual
- **Tipo**: Tarjeta con gradiente de borde a borde del contenedor.
- **Gradiente Ventas**: `bg-gradient-to-r from-orange-600 to-orange-800`
- **Márgenes Negativos**: `-mx-6 sm:-mx-8` (Crucial para que el gradiente toque los bordes del navbar).
- **Tipografía**: Título `text-2xl font-bold text-white`, Subtítulo `text-orange-100 text-sm`.
- **Iconografía**: Icono SVG decorativo a la derecha, `w-12 h-12 text-orange-300 opacity-50`.

### Código Base (Componente Blade / Volt)
```html
<x-slot name="header">
    <div class="bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8">
        <div class="px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Título de la Página</h1>
                    <p class="text-orange-100 text-sm">Descripción corta de la funcionalidad</p>
                </div>
                <div class="hidden md:block">
                    <!-- SVG Icon here -->
                </div>
            </div>
        </div>
    </div>
</x-slot>
```

---

## 3. Tablas de Datos Densas

Para tablas con muchas columnas (ej: Historial de Ventas, Reportes), se aplica el patrón **"Clean First, Expand Later"**.

### Reglas de UX
1. **Vista por Defecto (Clean)**: 
   - Mostrar solo 5-7 columnas críticas.
   - Contenedor alineado a `max-w-7xl`.
2. **Vista Expandida (Full Detail)**:
   - Botón de acción para alternar vista.
   - Contenedor se expande a `max-w-full`.
   - Se revelan columnas secundarias.

### Implementación (Alpine.js)
```html
<div class="py-12" x-data="{ expanded: false }">
    <div class="mx-auto sm:px-6 lg:px-8 transition-all duration-300" 
         :class="expanded ? 'max-w-full' : 'max-w-7xl'">
        
        <!-- Botón Toggle -->
        <button @click="expanded = !expanded">
            <span x-show="!expanded">>>> Expandir Vista</span>
            <span x-show="expanded">Contraer Vista</span>
        </button>

        <!-- Tabla -->
        <table>
            <thead>
                <tr>
                    <th>Columna Principal</th>
                    <th x-show="expanded">Columna Secundaria (Detalle)</th>
                </tr>
            </thead>
            <!-- ... -->
        </table>
    </div>
</div>
```

---

## 4. Paleta de Colores por Módulo

Para mantener identidad visual pero diferenciar contextos:

- **Ventas**: Orange (`from-orange-600 to-orange-800`)
- **Compras**: (Por definir, ej: Teal/Emerald)
- **Producción**: (Por definir, ej: Blue/Indigo)
- **RRHH**: (Por definir, ej: Rose/Pink)

---

## 5. Componentes UI

### Botones de Acción Principal
- `bg-{primary}-600 hover:bg-{primary}-700 text-white`
- Donde `{primary}` es el color del módulo (ej: `orange` para Ventas).
- Uppercase, tracking-widest, text-xs, font-semibold.

### Botones Secundarios / Toggles
- `bg-{fym}-100 text-{fym}-800 border border-{fym}-200`
- Donde `{fym}` es el color del módulo (ej: orange).

---

## 6. Dashboard: Filosofía "Clean & Cards"

Para los dashboards operativos del sistema, se prioriza la **legibilidad instantánea** sobre la visualización de datos complejos.

**Principios:**
- **No Gráficos Complejos (No JS Charts/Livewire issues)**: Evitar Chart.js para evitar conflictos de renderizado y tiempos de carga. Usar barras de progreso CSS simples si es necesario.
- **KPI Cards**: Tarjetas grandes, claras y coniconografía sutil.
- **Filtros Simplificados**: Filtrar por **Mes y Año** estándar, evitando lógicas complejas de trimestres (Q1-Q4) a menos que sea estrictamente necesario.
- **Listas > Pies**: Preferir listas ordenadas con barras de porcentaje visual (como en Transportes) en lugar de gráficos de torta (pie charts).
- **Tarjetas Destacadas**: Usar diseños especiales (gradientes, emblemas) para métricas de orgullo (ej: Cliente Estrella).

**Layout Recomendado:**
- Encabezado con Selectores (Año/Mes).
- 4 Tarjetas KPI en Grid superior.
- 2 Paneles de detalle inferior (Listas, Rankings).

---

## 7. Navbar y Branding

El sistema mantiene una identidad corporativa fuerte pero adaptable.

### Logo
- **Escritorio**: Logo completo (`img/logo.webp`), altura `h-12`.
- **Móvil**: SÓLO Logo (`img/logo.webp`), altura `h-8`. **Nunca usar texto plano "Gadium" como reemplazo**. El logo es la única identidad.

### Menú Móvil (Hamburguesa)
- **Ancho del Drawer**: Debe ser contenido, máx `w-64` (256px). No debe ocupar toda la pantalla.
- **Backdrop**: Obligatorio oscurecer el fondo (`bg-black/50 backdrop-blur-sm`).
- **Navegación**: Replicar la estructura del sidebar de escritorio.

---

## 8. Responsividad

Todos los componentes deben ser `mobile-first` o adaptarse elegantemente.
- **Tablas**: Scroll horizontal en móvil o tarjetas apiladas.
- **Grids**: `grid-cols-1` en móvil, `grid-cols-2` o `4` en md/lg.
- **Textos Críticos**: Usar `truncate` y `title="..."` (tooltip nativo) para números grandes o nombres largos en tarjetas pequeñas.
