# Matriz de Vistas y Funcionalidades por Rol (Gadium v2.5)
> **Auditoría Realizada:** 2026-01-13
> **Estado:** Verificado con Código Fuente (`web.php`, `Sidebar`, `Seeders`)

## 0. Credenciales de Acceso (Test)
| Rol | Email | Contraseña |
| :--- | :--- | :--- |
| **Super Admin** | `admin@gadium.com` | `password` |
| **Admin** | `administrador@gadium.com` | `password` |
| **Manager** | `gerente@gadium.com` | `password` |

---

## 1. Roles de Administración (Super Admin / Admin)
**Finalidad:** Gestión global del sistema, seguridad y configuración.
**Acceso Adicional:** Tienen acceso a todas las herramientas de *Manager*.

| Vista | Ruta | Ubicación Sidebar | Funcionalidad Clave |
|-------|------|-------------------|---------------------|
| **Control Maestro** | `/admin/dashboard` | Principal > Dashboard | KPI de usuarios, roles y salud del sistema. |
| **Gestión de Usuarios** | `/admin/users` | Configuración > Usuarios | CRUD de usuarios (Solo Admin/Super). |
| **Matriz de Roles** | `/admin/roles` | Configuración > Roles | (*Solo Super Admin*) Definición de roles y permisos. |
| **Factores HR** | `/admin/rrhh` | Configuración > Factores | Configuración de multiplicadores para horas. |
| **Importación Sales** | `/admin/importacion` | Operaciones > Importación | Asistente de carga de facturas. |
| **Resolución Clientes** | `/admin/clientes` | Operaciones > Resolución Clientes | Unificación de nombres. |
| **Bitácora Producción** | `/admin/produccion` | Operaciones > Producción | Registro de horas y producción (Compartido con Manager). |

---

## 2. Rol de Gerencia (Manager)
**Finalidad:** Supervisión operativa de ventas y manufactura.

| Vista | Ruta | Ubicación Sidebar | Funcionalidad Clave |
|-------|------|-------------------|---------------------|
| **Panel de Gestión** | `/manager/dashboard` | Principal > Dashboard | KPIs operativos y eficiencia vs meta. |
| **Importación Sales** | `/gerente/importacion` | Operaciones > Importación | Carga de datos de ventas. |
| **Resolución Clientes** | `/gerente/clientes` | Operaciones > Resolución Clientes | Unificación de nombres de clientes. |
| **Bitácora Producción** | `/gerente/produccion` | Operaciones > Producción | Registro manual de horas hombre. |
| **Factores HR** | `/gerente/rrhh` | Operaciones > Factores | Visualización de factores (si tiene permiso). |

---

## 3. Rol de Inteligencia (Viewer)
> **NOTA:** Este rol ha sido eliminado del sistema en la versión 2.5 para simplificar la gestión de accesos. Sus funcionalidades se absorberán en paneles futuros de Manager.

---

## 4. Hallazgos de Auditoría (Resultados v2.5)
1.  **Segregación de Rutas:** Se implementó una segregación estricta `/admin/*` y `/gerente/*` para evitar rutas genéricas ambiguas.
2.  **Eliminación de Viewer:** Se eliminó el rol, usuario, vistas y rutas asociadas a Viewer.
3.  **Sidebar Dinámico:** El sidebar ahora resuelve dinámicamente las rutas (ej. "Importación" lleva a la ruta correspondiente según el rol logueado).
