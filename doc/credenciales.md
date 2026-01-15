# Credenciales de Acceso para Pruebas (Entorno de Desarrollo)
# Credenciales del Sistema

## Usuarios de Sistema

### Super Admin (Desarrolladores)
- **Email:** `superadmin@gadium.com`
- **Password:** `password`
- **Rol:** Super Admin
- **Acceso:** Control total del sistema
- **Rutas principales:**
  - Dashboard: `/admin/dashboard`
  - Usuarios: `/admin/users`
  - Roles: `/admin/roles`
  - Perfil: `/admin/profile`
  - Importación: `/admin/importacion`
  - Clientes: `/admin/clientes`
  - Historial Ventas: `/admin/historial-ventas`
  - Historial Presupuestos: `/admin/historial-presupuestos`

### Admin (Administradores)
- **Email:** `admin@gadium.com`
- **Password:** `password`
- **Rol:** Admin
- **Acceso:** Gestión completa excepto Super Admins
- **Rutas principales:**
  - Dashboard: `/admin/dashboard`
  - Usuarios: `/admin/users` (no puede ver/editar Super Admins)
  - Roles: `/admin/roles` (no puede ver rol Super Admin)
  - Perfil: `/admin/profile`
  - Importación: `/admin/importacion`
  - Clientes: `/admin/clientes`
  - Producción: `/admin/produccion`
  - RRHH: `/admin/rrhh`

### Manager (Gerente)
- **Email:** `manager@gadium.com`
- **Password:** `password`
- **Rol:** Manager
- **Acceso:** Gestión de usuarios y roles operativos, reportes
- **Rutas principales:**
  - Dashboard: `/gerente/dashboard`
  - Usuarios: `/gerente/users` (no puede ver/editar Super Admins)
  - Roles: `/gerente/roles` (no puede ver rol Super Admin)
  - Perfil: `/gerente/profile`
  - Historial Ventas: `/gerente/historial-ventas`
  - Historial Presupuestos: `/gerente/historial-presupuestos`

### Vendedor (Usuario de Ventas)
- **Email:** `ventas@gadium.com`
- **Password:** `password`
- **Rol:** Vendedor
- **Acceso:** Solo módulo de ventas
- **Rutas principales:**
  - Dashboard: `/ventas/dashboard`
  - Importación: `/ventas/importacion`
  - Clientes: `/ventas/resolucion-clientes`
  - Historial Ventas: `/ventas/historial-ventas`

### Presupuestador (Usuario de Presupuestos)
- **Email:** `presupuesto@gadium.com`
- **Password:** `password`
- **Rol:** Presupuestador
- **Acceso:** Solo módulo de presupuestos
- **Rutas principales:**
  - Dashboard: `/presupuesto/dashboard`
  - Importación: `/presupuesto/importacion`
  - Historial Importación: `/presupuesto/historial_importacion`

---

## Módulo de Ventas

### Importación de Datos
- **Ruta Admin:** `/admin/importacion`
- **Ruta Manager:** Acceso vía `/admin/importacion` (Manager tiene acceso a rutas admin)
- **Funcionalidad:** Importar ventas y presupuestos desde Excel/CSV

### Resolución de Clientes
- **Ruta Admin:** `/admin/clientes`
- **Ruta Manager:** Acceso vía `/admin/clientes`
- **Funcionalidad:** Resolver duplicados y normalizar nombres de clientes

### Historial de Ventas
- **Ruta Admin:** `/admin/historial-ventas`
- **Ruta Manager:** `/gerente/historial-ventas`
- **Funcionalidad:** Ver últimas 50 ventas registradas (datos de Tango)

### Historial de Presupuestos
- **Ruta Admin:** `/admin/historial-presupuestos`
- **Ruta Manager:** `/gerente/historial-presupuestos`
- **Funcionalidad:** Ver últimos 50 presupuestos registrados

---

## Usuarios de Prueba para Módulos Nuevos

### Detalles de Horas
- **Email:** `detalleshoras@gadium.com`
- **Password:** `password`
- **Rutas:** `/admin/detalles-horas`, `/gerente/detalles-horas`

### Compras de Materiales
- **Email:** `comprasmateriales@gadium.com`
- **Password:** `password`
- **Rutas:** `/admin/compras-materiales`, `/gerente/compras-materiales`

### Satisfacción del Personal
- **Email:** `satisfaccionpersonal@gadium.com`
- **Password:** `password`
- **Rutas:** `/admin/satisfaccion-personal`, `/gerente/satisfaccion-personal`

### Satisfacción de Clientes
- **Email:** `satisfaccionclientes@gadium.com`
- **Password:** `password`
- **Rutas:** `/admin/satisfaccion-clientes`, `/gerente/satisfaccion-clientes`

### Tableros de Control
- **Email:** `tableros@gadium.com`
- **Password:** `password`
- **Rutas:** `/admin/tableros`, `/gerente/tableros`

### Proyecto de Automatización
- **Email:** `automatizacion@gadium.com`
- **Password:** `password`
- **Rutas:** `/admin/proyecto-automatizacion`, `/gerente/proyecto-automatizacion`

---

## Notas Importantes

### Jerarquía de Roles
1. **Super Admin** - Control total (solo desarrolladores)
2. **Manager** - Administra usuarios y roles operativos
3. **Admin** - Gestión operativa
4. **Roles personalizados** - Contador, Operario, Vendedor, etc.

### Protecciones de Seguridad
- ✅ Managers NO pueden ver usuarios Super Admin
- ✅ Managers NO pueden editar usuarios Super Admin
- ✅ Managers NO pueden asignar rol Super Admin
- ✅ Managers NO pueden ver el rol Super Admin en listados

### Arquitectura de Rutas
- `/admin/*` - Para Super Admin, Admin y Manager (acceso compartido)
- `/gerente/*` - Rutas específicas de Manager (dashboard, reportes, gestión)
- Todas las rutas están protegidas por rol y permisos

---

## Para Probar el Sistema

1. **Login:** `http://127.0.0.1:8000/login`
2. **Usar credenciales** según el rol que quieras probar
3. **Navegar** a las rutas correspondientes según el rol

**Ejemplo - Probar como Manager:**
```
Email: manager@gadium.com
Password: password

Rutas disponibles:
- Dashboard: /gerente/dashboard
- Usuarios: /gerente/users
- Roles: /gerente/roles
- Historial Ventas: /gerente/historial-ventas
```
La contraseña por defecto para todos los usuarios en desarrollo es `password`.

## Accesos API (Grafana / BI)



### Endpoints Disponibles
- **Pareto**: `GET /api/v1/metrics/sales-concentration`
- **Eficiencia**: `GET /api/v1/metrics/production-efficiency`
