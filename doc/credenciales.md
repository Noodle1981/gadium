# Credenciales de Acceso para Pruebas (Entorno de Desarrollo)
# Credenciales del Sistema

## Usuarios de Sistema

### Super Admin (Desarrolladores)
- **Email:** `superadmin@gaudium.com`
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
- **Email:** `admin@gaudium.com`
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
- **Email:** `manager@gaudium.com`
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
- **Email:** `ventas@gaudium.com`
- **Password:** `password`
- **Rol:** Vendedor
- **Acceso:** Solo módulo de ventas
- **Rutas principales:**
  - Dashboard: `/ventas/dashboard`
  - Importación: `/ventas/importacion`
  - Clientes: `/ventas/resolucion-clientes`
  - Historial Ventas: `/ventas/historial-ventas`

### Presupuestador (Usuario de Presupuestos)
- **Email:** `presupuesto@gaudium.com`
- **Password:** `password`
- **Rol:** Presupuestador
- **Acceso:** Solo módulo de presupuestos
- **Rutas principales:**
  - Dashboard: `/presupuesto/dashboard`
  - Importación: `/presupuesto/importacion`
  - Historial Importación: `/presupuesto/historial_importacion`

---

## Módulos Nuevos (Gestores)

### Detalles de Horas
- **Email:** `horas@gaudium.com`
- **Password:** `password`
- **Rol:** Gestor de Horas
- **Ruta:** `/detalle_horas/dashboard`

### Compras de Materiales
- **Email:** `compras@gaudium.com`
- **Password:** `password`
- **Rol:** Gestor de Compras
- **Ruta:** `/compras/dashboard`

### Satisfacción del Personal
- **Email:** `satisfaccion_personal@gaudium.com`
- **Password:** `password`
- **Rol:** Gestor de Satisfacción Personal
- **Ruta:** `/satisfaccion_personal/dashboard`

### Satisfacción de Clientes
- **Email:** `satisfaccion_clientes@gaudium.com`
- **Password:** `password`
- **Rol:** Gestor de Satisfacción Clientes
- **Ruta:** `/satisfaccion_clientes/dashboard`

### Tableros de Control
- **Email:** `tableros@gaudium.com`
- **Password:** `password`
- **Rol:** Gestor de Tableros
- **Ruta:** `/tableros/dashboard`

### Proyecto de Automatización
- **Email:** `proyectos@gaudium.com`
- **Password:** `password`
- **Rol:** Gestor de Proyectos
- **Ruta:** `/proyectos_automatizacion/dashboard`

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
Email: manager@gaudium.com
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
✅ Rebranding completado a Gaudium

He actualizado todos los emails de @gadium.com a @gaudium.com en:

UserSeeder.php
ModuleTestUsersSeeder.php
doc/credenciales.md
Y ejecuté ambos seeders. Ahora puedes usar:

Usuarios principales:

ventas@gaudium.com
presupuesto@gaudium.com
admin@gaudium.com
manager@gaudium.com
superadmin@gaudium.com
Módulos:

horas@gaudium.com
compras@gaudium.com
satisfaccion_personal@gaudium.com
satisfaccion_clientes@gaudium.com
tableros@gaudium.com
proyectos@gaudium.com

---

## 🛠️ Comando Universal de Restauración / Generación

Si necesitas regenerar todas las credenciales y permisos desde cero (reset de fábrica), ejecuta el siguiente comando en la terminal:

```bash
php artisan migrate:fresh --seed
```

Este comando ejecuta automáticamente el nuevo `UniversalCredentialsSeeder` (porque ya lo configuramos en el `DatabaseSeeder` principal), realizando todo el proceso:

1. Borra la DB `(migrate:fresh)`
2. Ejecuta `UniversalCredentialsSeeder` `(--seed)` que:
    - Crea Permisos
    - Crea Roles
    - Crea Usuarios

 



