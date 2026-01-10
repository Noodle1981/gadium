# Matriz de Vistas y Funcionalidades por Rol (Gadium v2.4)

## 0. Credenciales de Acceso (Test)
Para realizar las pruebas de QA, utiliza las siguientes cuentas pre-configuradas:

| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| **Super Admin** | `admin@gaudium.com` | `password` |
| **Admin** | `administrador@gaudium.com` | `password` |
| **Manager** | `gerente@gaudium.com` | `password` |
| **Viewer** | `viewer@gaudium.com` | `password` |

---

## 1. Roles de Administración (Super Admin / Admin)
**Finalidad:** Gestión global del sistema, seguridad y configuración de parámetros base.

| Vista | Ruta | Funcionalidad Clave | Procedimiento |
|-------|------|---------------------|---------------|
| **Control Maestro** | `/admin/dashboard` | KPI de usuarios, roles y salud del sistema. | Verificar conteo real de usuarios. |
| **Gestión de Usuarios** | `/admin/users` | CRUD de usuarios y asignación de roles. | Crear un nuevo usuario y validar envío de correo. |
| **Matriz de Roles** | `/admin/roles` | (*Solo Super Admin*) Definición de roles y permisos Spatie. | Modificar permisos y verificar afectación inmediata. |
| **Factores HR** | `/admin/hr/factors` | Configuración de multiplicadores para horas ponderadas. | Cambiar un factor y validar recalcular producción. |
| **Importación Sales** | `/admin/sales/import` | Asistente de carga de facturas (CSV/Excel). | Subir archivo y validar cálculo de Hash SHA-256. |

---

## 2. Rol de Gerencia (Manager)
**Finalidad:** Supervisión operativa de ventas, clientes y eficiencia de manufactura.

| Vista | Ruta | Funcionalidad Clave | Procedimiento |
|-------|------|---------------------|---------------|
| **Panel de Gestión** | `/manager/dashboard` | KPIs de ventas mensuales y eficiencia vs meta. | Comparar horas ponderadas vs meta establecida. |
| **Importación Sales** | `/manager/sales/import` | Carga de datos de ventas de ERP externo. | Cargar lote de ventas y verificar normalización. |
| **Resolución de Clientes** | `/manager/clients/resolve` | Unificación de nombres de clientes (Limpieza de datos). | Resolver un cliente con "Nombre Sucio" al "Nombre Maestro". |
| **Bitácora Producción** | `/manager/manufacturing/production-log` | Registro manual de horas hombre y producción diaria. | Ingresar jornada y validar impacto en KPI de eficiencia. |

---

## 3. Rol de Inteligencia (Viewer)
**Finalidad:** Análisis de datos, visualización de tendencias y exportación para BI.

| Vista | Ruta | Funcionalidad Clave | Procedimiento |
|-------|------|---------------------|---------------|
| **Portal de Inteligencia** | `/viewer/dashboard` | Torre de control con enlaces a Grafana. | Verificar visibilidad de token API Sanctum. |
| **Documentación API** | (Vía API URL) | Endpoints de Pareto y Eficiencia. | Probar endpoint con Postman usando el token mostrado. |
| **Tableros Externos** | (External Links) | Dashboards integrados de Grafana. | Validar que el botón redirija correctamente a Grafana. |

---

## 4. Elementos Globales (Todos los Roles)
- **Sidebar Dinámico:** Debe ocultar/mostrar elementos según la tabla anterior.
- **Perfil de Usuario:** `/profile` (Edición de datos básicos).
- **Cierre de Sesión:** Funcional desde el sidebar en cualquier vista.

## Guía de Testing Crítica
1. **Prueba de Intrusión:** Intentar entrar a `/admin/users` con un usuario `Viewer`. (Debe retornar 403 Forbidden).
2. **Pruebas de Navegación:** Validar que al hacer clic en el logo, el sistema redirija al dashboard correcto según el rol activo.
3. **Prueba de Datos Agregados:** Con el rol `Viewer`, validar que las métricas mostradas coinciden con los datos cargados por el `Manager`.
