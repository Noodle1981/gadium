# Credenciales de Prueba - Módulos Nuevos

## Usuarios de Prueba Creados

Todos los usuarios tienen la contraseña: **`password`**

### Módulo: Detalles Horas
- **Email:** `detalleshoras@gadium.com`
- **Contraseña:** `password`
- **Rol:** Admin
- **Permisos:** view_hours, create_hours, edit_hours
- **Rutas de acceso:**
  - `/admin/detalles-horas`
  - `/gerente/detalles-horas` (si tiene rol Manager)

---

### Módulo: Compras Materiales
- **Email:** `comprasmateriales@gadium.com`
- **Contraseña:** `password`
- **Rol:** Admin
- **Permisos:** view_purchases, create_purchases, edit_purchases
- **Rutas de acceso:**
  - `/admin/compras-materiales`
  - `/gerente/compras-materiales` (si tiene rol Manager)

---

### Módulo: Satisfacción Personal
- **Email:** `satisfaccionpersonal@gadium.com`
- **Contraseña:** `password`
- **Rol:** Admin
- **Permisos:** view_staff_satisfaction, create_staff_satisfaction, edit_staff_satisfaction
- **Rutas de acceso:**
  - `/admin/satisfaccion-personal`
  - `/gerente/satisfaccion-personal` (si tiene rol Manager)

---

### Módulo: Satisfacción Clientes
- **Email:** `satisfaccionclientes@gadium.com`
- **Contraseña:** `password`
- **Rol:** Admin
- **Permisos:** view_client_satisfaction, create_client_satisfaction, edit_client_satisfaction
- **Rutas de acceso:**
  - `/admin/satisfaccion-clientes`
  - `/gerente/satisfaccion-clientes` (si tiene rol Manager)

---

### Módulo: Tableros
- **Email:** `tableros@gadium.com`
- **Contraseña:** `password`
- **Rol:** Admin
- **Permisos:** view_boards, create_boards, edit_boards
- **Rutas de acceso:**
  - `/admin/tableros`
  - `/gerente/tableros` (si tiene rol Manager)

---

### Módulo: Proyecto Automatización
- **Email:** `automatizacion@gadium.com`
- **Contraseña:** `password`
- **Rol:** Admin
- **Permisos:** view_automation, create_automation, edit_automation
- **Rutas de acceso:**
  - `/admin/proyecto-automatizacion`
  - `/gerente/proyecto-automatizacion` (si tiene rol Manager)

---

## Cómo Probar

1. Iniciar sesión con cualquiera de los usuarios de prueba
2. Serás redirigido al dashboard de Admin
3. Acceder manualmente a la ruta del módulo correspondiente
4. Verificar que se muestra la vista placeholder sin errores 403

## Notas

- Todos los usuarios tienen rol **Admin** con permisos CRUD específicos para su módulo
- Los permisos `delete_*` NO están asignados a estos usuarios de prueba (solo Super Admin los tiene)
- Cada usuario puede acceder a su módulo específico y realizar operaciones de visualización, creación y edición
