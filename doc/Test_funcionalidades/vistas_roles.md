# Matriz de Vistas y Funcionalidades por Rol (Gadium v2.4)
> **Auditoría Realizada:** 2026-01-11
> **Estado:** Verificado con Código Fuente (`web.php`, `Sidebar`, `Seeders`)

## 0. Credenciales de Acceso (Test)
| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| **Super Admin** | `admin@gadium.com` | `password` |
| **Admin** | `administrador@gadium.com` | `password` |
| **Manager** | `gerente@gadium.com` | `password` |
| **Viewer** | `viewer@gadium.com` | `password` |

---

## 1. Roles de Administración (Super Admin / Admin)
**Finalidad:** Gestión global del sistema, seguridad y configuración.
**Acceso Adicional:** Tienen acceso a todas las herramientas de *Manager*.

| Vista | Ruta | Ubicación Sidebar | Funcionalidad Clave |
|-------|------|-------------------|---------------------|
| **Control Maestro** | `/admin/dashboard` | Principal > Dashboard | KPI de usuarios, roles y salud del sistema. |
| **Gestión de Usuarios** | `/admin/users` | Configuración > Usuarios | CRUD de usuarios (Solo Admin/Super). |
| **Matriz de Roles** | `/admin/roles` | Configuración > Roles | (*Solo Super Admin*) Definición de roles y permisos. |
| **Factores HR** | `/admin/hr/factors` | Configuración > Factores | Configuración de multiplicadores para horas. |
| **Importación Sales** | `/admin/sales/import` | Operaciones > Importación | Asistente de carga de facturas. |
| **Bitácora Producción** | `/admin/manufacturing/production-log` | Operaciones > Producción | Registro de horas y producción (Compartido con Manager). |

---

## 2. Rol de Gerencia (Manager)
**Finalidad:** Supervisión operativa de ventas y manufactura.

| Vista | Ruta | Ubicación Sidebar | Funcionalidad Clave |
|-------|------|-------------------|---------------------|
| **Panel de Gestión** | `/manager/dashboard` | Principal > Dashboard | KPIs operativos y eficiencia vs meta. |
| **Importación Sales** | `/manager/sales/import` | Operaciones > Importación | Carga de datos de ventas. |
| **Bitácora Producción** | `/manager/manufacturing/production-log` | Operaciones > Producción | Registro manual de horas hombre. |
| **Resolución Clientes** | `/manager/clients/resolve` | **NO EN SIDEBAR** (Acceso Directo) | Unificación de nombres de clientes. |

---

## 3. Rol de Inteligencia (Viewer)
**Finalidad:** Gestión de tableros de lectura (Grafana).

| Vista | Ruta | Ubicación Sidebar | Funcionalidad Clave |
|-------|------|-------------------|---------------------|
| **Portal Inteligencia** | `/viewer/dashboard` | Inteligencia > Grafana | Enlaces a tableros externos. |

---

## 4. Hallazgos de Auditoría (Discrepancias Detectadas)
1.  **Enlace Faltante:** La vista "Resolución de Clientes" (`clients/resolve`) existe y funciona, pero **no tiene enlace en el Sidebar** para ningún rol. Debe accederse manualmente por URL.
2.  **Visibilidad de Enlace Grafana:** El enlace a "Grafana" (`/viewer/dashboard`) aparece en el Sidebar de **todos los usuarios**, pero la ruta está protegida solo para el rol `Viewer`. Un Admin que haga clic recibirá un error 403.
3.  **Permisos de Admin:** El Admin tiene acceso completo a las rutas de Operaciones (Importación/Producción), lo cual es correcto según `web.php` y el Sidebar.

## Guía de Testing Crítica
1.  **Prueba de Intrusión:** Intentar entrar a `/admin/users` con un usuario `Viewer`. (Debe retornar 403 Forbidden).
2.  **Prueba de Sidebar:** Verificar que el Manager NO vea la sección "Configuración".
3.  **Prueba de Enlace Roto:** Verificar comportamiento de Admin al hacer click en "Grafana" (Esperado: 403 / Sugerido: Ocultar enlace u otorgar permiso).
