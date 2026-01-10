# ÉPICA 06: Integración con Grafana y Experiencia Unificada

## Contexto y Alcance
Esta épica consolida la infraestructura de inteligencia de negocios iniciada en la Épica 5 y unifica la experiencia de usuario. Se basa en los requisitos de `contex.md` ("Integración con Grafana") y la necesidad de cohesión del producto.

**Estado Inicial (Post-Épica 5):**
- API REST para métricas: **Implementado** (parcialmente).
- Autenticación: **Implementado** (Sanctum/Tokens).
- Tablas de Resumen: **Pendiente**.
- Integración Visual: **Pendiente**.

---

## HU-11: Integración Visual de tableros Grafana
**Prioridad**: Alta
**Estimación**: 5 Puntos
**Narrativa**:
Como **Gerente General**, quiero acceder a los tableros de Grafana directamente desde el menú principal de la aplicación, para consultar KPIs sin gestionar accesos externos.

**Criterios de Aceptación**:
- [ ] Sección "Inteligencia" o "Dashboards" en el menú lateral.
- [ ] Embedding o enlace directo seguro a los dashboards (Ventas, Producción).
- [ ]Mecanismo de autenticación transparente (SSO simplificado o Token passing si aplica, o simplemente enlace seguro).

---

## HU-12: Sidebar Global Dinámico (Navegación por Roles)
**Prioridad**: Alta
**Estimación**: 8 Puntos
**Narrativa**:
Como **Usuario**, quiero un menú lateral que muestre solo las opciones de mi rol, para simplificar la navegación y evitar errores de acceso.

**Criterios de Aceptación**:
- [ ] Componente `Sidebar` implementado con Tailwind.
- [ ] Filtro de ítems basado en Permisos/Roles (Spatie).
    - **Admin**: Configuración, Usuarios, Importación.
    - **Manager**: Producción, Reportes Operativos.
    - **Viewer**: Dashboards Grafana.
- [ ] Responsive y colapsable.

---

## HU-13: Unificación de Módulos (Wiring)
**Prioridad**: Media
**Estimación**: 5 Puntos
**Narrativa**:
Como **Usuario**, quiero poder navegar a todos los módulos funcionales (Ventas, Producción, RRHH) desde el sistema central, asegurando que nada quede aislado.

**Criterios de Aceptación**:
- [ ] Revisión y conexión de rutas de Épicas 1-5.
- [ ] Limpieza de enlaces de debug o temporales.
- [ ] Estandarización de headers y layouts en todas las vistas conectadas.

---

## HU-14: Optimización de Datos (Tablas de Resumen)
**Prioridad**: Media
**Estimación**: 5 Puntos
**Narrativa**:
Como **Sistema**, quiero mantener tablas de resumen (`daily_metrics_aggregates`, etc.) actualizadas automáticamente, para que las consultas de la API de Grafana sean rápidas y no saturen la base de datos transaccional.

**Criterios de Aceptación**:
- [ ] Existencia de tablas de resumen en la BD.
- [ ] Jobs o Comandos (Scheduler) para poblar/actualizar estas tablas periódicamente (ej. cada hora o noche).
- [ ] Endpoints de API (MetricsController) consumiendo de estas tablas cuando sea posible.

---

## Definición de Hecho (DoD)
- Todas las HUs aprobadas.
- Tests (Feature) para nuevas rutas y el Sidebar.
- Auditoría completada.
- Documentación técnica actualizada.
