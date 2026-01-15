# Protección Super Admin - Documentación de Seguridad

## Fecha: 2026-01-15
## Rama: `feature/tarea-refactor-gerente`

---

## Problema Identificado

**Vulnerabilidad crítica:** Managers podían ver, editar y asignar el rol Super Admin, lo cual representa un riesgo de seguridad grave.

**Jerarquía incorrecta:**
- Super Admin y Manager tenían casi los mismos permisos
- No había protección para el rol Super Admin
- Managers podían revocar permisos a Super Admins

---

## Solución Implementada

### Jerarquía de Roles Establecida

1. **Super Admin** - Desarrolladores del sistema - Control total
2. **Manager** - Administradores del sistema - Gestión de usuarios y roles operativos
3. **Roles Operativos** - Contador, Operario, Vendedor, etc. - Acceso limitado

### Protecciones Implementadas

#### UserController

**index()** - Listado de usuarios
```php
// Managers no pueden ver Super Admins
if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
    $query->whereDoesntHave('roles', function($q) {
        $q->where('name', 'Super Admin');
    });
}
```

**create()** - Crear usuario
```php
// Managers no pueden asignar rol Super Admin
if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
    $roles->where('name', '!=', 'Super Admin');
}
```

**store()** - Guardar usuario
```php
// Validación: Managers no pueden asignar rol Super Admin
if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
    if ($validated['role'] === 'Super Admin') {
        return redirect()->with('error', 'No tiene permisos para asignar el rol Super Admin.');
    }
}
```

**edit()** - Editar usuario
```php
// Managers no pueden editar Super Admins
if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
    if ($user->hasRole('Super Admin')) {
        return redirect()->with('error', 'No tiene permisos para editar un Super Admin.');
    }
}

// Filtrar rol Super Admin de la lista
$roles->where('name', '!=', 'Super Admin');
```

**update()** - Actualizar usuario
```php
// Doble validación:
// 1. No puede editar usuarios Super Admin
// 2. No puede asignar rol Super Admin
if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
    if ($user->hasRole('Super Admin')) {
        return redirect()->with('error', 'No tiene permisos para editar un Super Admin.');
    }
    
    if ($request->input('role') === 'Super Admin') {
        return redirect()->with('error', 'No tiene permisos para asignar el rol Super Admin.');
    }
}
```

#### RoleController

**index()** - Listado de roles
```php
// Managers no pueden ver el rol Super Admin
if (auth()->user()->hasRole('Manager') && !auth()->user()->hasRole('Super Admin')) {
    $roles->where('name', '!=', 'Super Admin');
}
```

---

## Protecciones Existentes (Ya implementadas)

- **destroy()** en UserController: No se puede eliminar Super Admin
- **edit(), update(), destroy(), updatePermissions()** en RoleController: No se puede modificar el rol Super Admin

---

## Testing de Seguridad

### Escenarios a Verificar

1. **Manager intenta ver usuarios**
   - ✅ No debe ver usuarios con rol Super Admin
   
2. **Manager intenta crear usuario**
   - ✅ No debe ver opción de rol Super Admin
   
3. **Manager intenta asignar Super Admin vía POST**
   - ✅ Debe recibir error de permisos
   
4. **Manager intenta editar Super Admin**
   - ✅ Debe ser redirigido con error
   
5. **Manager intenta cambiar rol a Super Admin**
   - ✅ Debe recibir error de permisos
   
6. **Manager intenta ver roles**
   - ✅ No debe ver el rol Super Admin

---

## Impacto

- ✅ **Seguridad mejorada:** Super Admin completamente protegido
- ✅ **Jerarquía clara:** Super Admin > Manager > Operativos
- ✅ **Sin cambios visuales:** Managers simplemente no ven Super Admins
- ✅ **Backward compatible:** Super Admins mantienen acceso total

---

## Recomendaciones Futuras

1. **Auditoría:** Registrar intentos de acceso no autorizado
2. **Testing automatizado:** Crear Feature Tests para estas protecciones
3. **Documentación:** Actualizar manual de usuario con jerarquía de roles
