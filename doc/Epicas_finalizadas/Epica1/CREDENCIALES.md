# Credenciales de Usuarios de Prueba - ÉPICA 01

## Usuarios Creados

### Super Administrador
- **Email**: `admin@gaudium.com`
- **Contraseña**: `password`
- **Rol**: Super Admin
- **Permisos**: Todos los permisos del sistema

### Administrador
- **Email**: `administrador@gaudium.com`
- **Contraseña**: `password`
- **Rol**: Admin
- **Permisos**: 
  - Gestión de usuarios (CRUD)
  - Visualización de roles
  - Ventas (view, create, edit)
  - Producción (view, create, edit)
  - RRHH (view, create, edit)
  - Dashboards (view)

### Gerente
- **Email**: `gerente@gaudium.com`
- **Contraseña**: `password`
- **Rol**: Manager
- **Permisos**:
  - Usuarios (view)
  - Ventas (view, create, edit)
  - Producción (view, create, edit)
  - RRHH (view)
  - Dashboards (view)

### Visualizador
- **Email**: `viewer@gaudium.com`
- **Contraseña**: `password`
- **Rol**: Viewer
- **Permisos**:
  - Ventas (view)
  - Producción (view)
  - RRHH (view)
  - Dashboards (view)

## Estructura de Permisos

### Módulos Implementados

1. **Usuarios** (Users)
   - `view_users`
   - `create_users`
   - `edit_users`
   - `delete_users`

2. **Roles** (Roles)
   - `view_roles`
   - `create_roles`
   - `edit_roles`
   - `delete_roles`

3. **Ventas** (Sales)
   - `view_sales`
   - `create_sales`
   - `edit_sales`
   - `delete_sales`

4. **Producción** (Production)
   - `view_production`
   - `create_production`
   - `edit_production`
   - `delete_production`

5. **RRHH** (HR)
   - `view_hr`
   - `create_hr`
   - `edit_hr`
   - `delete_hr`

6. **Dashboards**
   - `view_dashboards`
   - `manage_dashboards`

## Cómo Probar

### Login
1. Acceder a `http://localhost:8000/login`
2. Usar cualquiera de los emails y contraseña `password`
3. Verificar redirección a dashboard

### Verificar Permisos
```php
// En cualquier controlador o vista
auth()->user()->can('view_users'); // true/false
auth()->user()->hasRole('Super Admin'); // true/false
auth()->user()->getRoleNames(); // Collection de roles
```

### Proteger Rutas
```php
// En routes/web.php
Route::middleware(['auth', 'role:Super Admin'])->group(function () {
    // Rutas solo para Super Admin
});

Route::middleware(['auth', 'permission:view_users'])->group(function () {
    // Rutas para usuarios con permiso view_users
});
```

## Notas Importantes

- ⚠️ **Cambiar contraseñas en producción**
- ✅ Soft deletes habilitado en usuarios
- ✅ Registro público deshabilitado
- ✅ Timeout de sesión: 1 día (1440 minutos)
- ✅ Colores corporativos aplicados (#E8491B)
- ✅ Dark mode disponible

---

**Última actualización**: 2026-01-08 20:24:00
