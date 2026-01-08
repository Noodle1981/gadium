# Contexto del Proyecto Gadium

## Información General
- **Proyecto**: Transformación SaaS Industrial - Sistema de Gestión Empresarial
- **Cliente**: Gaudium (Empresa Industrial)
- **Versión Actual**: 1.0 (Fase de Desarrollo)
- **Estado**: En Planificación - Preparando Sprint de Instalación

## Stack Tecnológico

### Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **Base de Datos**: SQLite (Desarrollo) → MySQL 8.0 (Producción)
- **Autenticación**: Laravel Fortify/Breeze
- **Permisos**: Spatie Laravel Permission (RBAC Dinámico)

### Frontend
- **Framework Interactivo**: Livewire 3
- **UI Framework**: Tailwind CSS
- **Enfoque**: TALL Stack (Tailwind, Alpine.js, Livewire, Laravel)

### Infraestructura
- **Desarrollo**: Local con SQLite
- **Producción**: Hostinger VPS o Cloud Startup
- **Visualización**: Grafana (Conexión vía API REST JSON)

## Objetivo del Proyecto

Transformar la gestión empresarial de Gaudium desde archivos Excel desconectados hacia un sistema SaaS centralizado que:

1. **Elimine duplicidad de datos** mediante validación con hash SHA-256
2. **Reduzca tiempo de reportes** de 5 días a tiempo real
3. **Normalice datos** con fuzzy matching para clientes
4. **Automatice cálculos** de KPIs y alertas de calidad
5. **Centralice visualización** en dashboards de Grafana

## Arquitectura del Sistema

### Modelo de Datos Relacional

#### Tablas Maestras
- `users` - Usuarios del sistema con RBAC
- `roles` - Roles dinámicos configurables
- `permissions` - Permisos granulares
- `clients` - Clientes normalizados
- `client_aliases` - Aliases para fuzzy matching
- `projects` - Proyectos vinculados a clientes
- `cost_centers` - Centros de costo

#### Tablas Transaccionales
- `sales_invoices` - Facturas de venta
- `sales_items` - Detalle de artículos vendidos
- `timesheets` - Registro de horas trabajadas
- `procurements` - Compras de materiales
- `manufacturing_logs` - Producción de tableros
- `surveys` - Encuestas de satisfacción

#### Tablas de Soporte
- `daily_metrics_aggregates` - Métricas pre-calculadas para Grafana
- `import_logs` - Historial de importaciones
- `quality_alerts` - Alertas de calidad automáticas

### Sistema de Roles (RBAC)

1. **Super Admin**: Acceso total, gestión de roles y permisos
2. **Tenant Admin**: Configuración de KPIs y usuarios
3. **Manager**: Carga de archivos y validación de datos
4. **Viewer**: Solo visualización de dashboards

## Épicas del Proyecto

### ÉPICA 01: Gestión de Accesos y Gobierno de Datos
- **HU-01**: Infraestructura de autenticación segura
- **HU-01.1**: CRUD de usuarios con autogestión de contraseñas
- **HU-02**: Gestor dinámico de roles y permisos (Role Builder UI)

### ÉPICA 02: Motor de Ingesta y Normalización de Datos
- **HU-03**: Asistente de importación con validación de esquema
- **HU-04**: Normalización inteligente de clientes (Fuzzy Matching)

### ÉPICA 03: Motor de Digitalización de Producción y Calidad
- **HU-05**: Registro de producción vinculado a proyectos
- **HU-06**: Motor de cálculo de defectos y alertas críticas

### ÉPICA 04: Gestión de Capital Humano
- **HU-07**: Gestión temporal de factores de ponderación
- **HU-08**: Procesamiento automático de horas y eficiencia

### ÉPICA 05: Motor de Inteligencia de Negocios
- **HU-09**: Implementación del algoritmo de Pareto (Diversificación)

### ÉPICA 06: Integración con Grafana
- Endpoints API REST para métricas
- Autenticación con tokens
- Tablas de resumen optimizadas

## Estructura de Documentación

### Ubicación de Documentación de Épicas

**Directorio principal**: `d:\Gadium\doc\`

- **Épicas en desarrollo**: `doc/Epica{N}/`
  - Cada épica tiene su propia carpeta mientras está en desarrollo
  - Contiene: EPICA N.MD, bitacora_epica_N.md, auditoria_epica_N.md
  
- **Épicas finalizadas**: `doc/Epicas_finalizadas/`
  - Una vez completada y mergeada una épica, su documentación se mueve aquí
  - Mantiene el historial completo del proyecto

### Estructura por Épica

Cada carpeta de épica contiene:
1. `EPICA N.MD` - Documentación completa de la épica
2. `bitacora_epica_N.md` - Cronología, tiempos y problemas encontrados
3. `auditoria_epica_N.md` - Checklist de verificación pre-merge

## Reglas de Trabajo

### Gestión de Épicas
1. Una sesión de trabajo se centra en **una épica**
2. Cada épica crea un **feature branch**: `feature/epica-{nombre}`
3. Nunca trabajar sobre rama de épica distinta
4. Usar **SQLite** en desarrollo, MySQL en producción
5. Respetar arquitectura propuesta
6. Armar **sprints** por épica
7. **Cronometrar** épicas (fecha/hora inicio y fin)
8. Mantener **bitácora** de la épica (demoras, errores, mejoras)
9. **Documentar en `doc/Epica{N}/`** durante desarrollo
10. **Mover a `doc/Epicas_finalizadas/`** después del merge

### Testing y Validación
1. Probar implementación de la épica
2. Completar **Seeders** de datos de prueba
3. Concatenar seeders de épicas anteriores
4. Ejecutar **Unit Testing** y **Feature Testing**
5. Documentar y borrar tests no utilizados
6. Crear **auditoria_{nombre_epica}.md** antes de merge
7. Arreglar issues de auditoría
8. Subir a rama de épica y esperar aprobación para merge

### Documentación
1. Mantener documentación de épica actualizada
2. Mantener documentación de arquitectura actualizada
3. Mantener documentación de base de datos actualizada
4. Mantener documentación de seguridad actualizada
5. Mantener documentación de testing actualizada
6. Mantener README.md actualizado

## Estrategias Técnicas Clave

### Prevención de Duplicados
- **Hash SHA-256** de campos clave (fecha + cliente + comprobante + monto)
- Verificación antes de inserción
- Reporte de duplicados omitidos

### Normalización de Clientes
- **Algoritmo Levenshtein** para similitud > 85%
- Resolución interactiva de duplicados
- Sistema de **aliases** con aprendizaje automático

### Performance (Hostinger Friendly)
- **Índices** en: tenant_id, created_at, client_id, project_id
- **Chunking** de 1000 filas en importaciones
- **Jobs en colas** para evitar timeouts
- **Tablas de resumen** actualizadas por Laravel Scheduler

### Alertas de Calidad
- Cálculo automático de **Tasa de Error** por proyecto
- Umbral crítico: **20%**
- Notificaciones en dashboard + email
- Estado de proyecto: CRÍTICO cuando excede umbral

## Riesgos Identificados

| Riesgo | Mitigación |
|--------|------------|
| Cambio de formato Excel | Mapeo dinámico de columnas |
| Grafana tumba MySQL | Cache 1h + Tablas resumen |
| Nombres inconsistentes | Fuzzy matching + Aliases |
| Lógica textual en KPIs | Hardcodear en clases PHP |
| Timeout en importaciones | Chunking + Queue Jobs |
| Fechas Excel variadas | Validación estricta previa |

## Métricas de Éxito

- ⏱️ Tiempo de carga: **< 5 segundos** para 2000 filas
- 🔒 Integridad: **0 duplicados** tras re-subir archivo 3 veces
- 📊 Reportes: De **5 días** a **tiempo real**
- ✅ Reducción de errores: **95%** en duplicidad

## Estado Actual

- ✅ Repositorio Git inicializado
- ✅ Conectado a GitHub: `https://github.com/Noodle1981/gadium.git`
- ✅ Documentación de arquitectura completa
- ✅ 6 Épicas definidas con Historias de Usuario
- 🔄 **Siguiente paso**: Sprint de Instalación (Épica 0)

## Referencias

- **Arquitectura**: `d:\Gadium\arquitectura.md`
- **Épicas**: `d:\Gadium\Epica{1-6}\EPICA {1-6}.MD`
- **Reglas**: `d:\Gadium\.agent\reglas_de_trabajo.md`
- **Repositorio**: https://github.com/Noodle1981/gadium.git
