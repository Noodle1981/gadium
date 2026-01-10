## Reglas de Trabajo

1. Trabajar con Epicas
    1.1 Una sesion de trabajo se centra en una epica
    1.2 Cada epica creará un feature branch llamado feature/epica nombre de la epica, nunca trabajar sobre una rama de una epica distinta a la que se está trabajando
    1.3 Se analiza la epica y se implementa lo que se pide, solo se cambia del pedido, la estructura de la base de datos que haya por la de msqlite, para rapida implementacion de la epica. hasta que se lleve a producción
    1.4 Se respeta la arquitectura propuesta
    1.5 Se arman sprint de acuerdo a la epica
    1.6 Las Epicas deben estar cronometradas, es decir registrar fechas y hora de inicio y fin de la epica
    1.7 La ia debe mantener comunicaciones para promt largos y consultas largas cuando le asignen leer nuevos_requerimientos.md y banco_de_preguntas.md
    1.8 armar una bitácora de la epica, para ver en que se demora la epica, errores encontrados y como mejorar eso.
2. Probar el sistema
    2.1 Probar la implementación del la epica
    2.2 completar Seeders de datos de prueba
    2.3 Los Seeder debe ir contatenado con otros de acuerdo al orden de las epicas, aprovechando la funcionalidad de los seeders de laravel del DataSeeder.php y activar la funcionalidad de "cargando seeder de epica 1, detalles del seeder de epica 1". y eso permitirá concatenar los seeders de las epicas anteriores.
    2.4 Ejecutar Testing del estilo Unit Testing y Feature Testing, para verificar que todo funciona correctamente.
    2.5 si no se usa más los archivos de testing, se debe borrar y documentar resultados y como se hizo
    2.6 Antes de subir del trabajo a la rama de la epica. armar una auditoria_nombre_de_la_epica.md, explicar como está, cual es el estado, y si hay que mejorar algo, explicar que mejorar.
    2.7 Arreglar y actualizar los arregloes de auditoria_nombre_de_la_epica.md
    2.8 subir trabajo a la rama de la epica. y esperar instrucciones de mi parte para merge.

2. Respetar y mantener Documentaciones
    2.1 Mantener la documentación de la epica actualizada
    2.2 Mantener la documentación de la arquitectura actualizada
    2.3 Mantener la documentación de la base de datos actualizada
    2.4 Mantener la documentación de la seguridad actualizada
    2.5 Mantener la documentación de la los testings actualizados
    2.6 Mantener la documentación del readme.md actualizada

3. Respertar la arquitectura planeada para el proyecto, por ejemplo crear vistas con componentes livewire, el diseño debe ser responsive, y debe ser similar a los demas componentes. y la visual de visual.md, mantenter la estructura de rutas /rol/vista, para tener separado las vistas por rol. por ejemplo /admin/clientes y /user/clientes

4. Estándares de Livewire y Volt
    4.1 Un componente Volt de página debe tener un único elemento raíz (usualmente un `<div>` o `<nav>`).
    4.2 Los componentes de página deben definir el layout explícitamente usando el atributo `#[Layout('layouts.app')]` o similar para asegurar el renderizado correcto.
    4.3 Usar siempre `wire:navigate` en los enlaces de navegación interna para mantener la experiencia de Single Page Application (SPA).
    4.4 Evitar el uso de `dump()` o `dd()` fuera de los bloques `@php` en las vistas Blade de Livewire, ya que pueden romper la estructura DOM esperada por el motor.

5. Arquitectura de Seguridad y Redirecciones
    5.1 Centralización: Prohibido realizar redirecciones manuales o lógica de acceso compleja dentro de `routes/web.php`.
    5.2 Middleware de Redirección: Toda lógica de deriva basada en roles tras la autenticación debe residir en middlewares dedicados (ej: `RoleRedirect.php`).
    5.3 Robustez: Los middlewares de seguridad deben contemplar fallbacks para usuarios sin roles asignados para evitar excepciones en entornos de testing.
    5.4 Nombres de Rutas: Usar siempre rutas nombradas (`route('admin.dashboard')`) en lugar de rutas estáticas para facilitar el mantenimiento y la consistencia en los tests.

6. Testing de Seguridad y Rutas
    6.1 Cada nueva ruta protegida debe contar con un test de Feature que verifique:
        a) Acceso autorizado para el rol correspondiente (200 OK/Redirect esperado).
        b) Acceso denegado (403 Forbidden) para roles no autorizados.
    6.2 Mantener actualizado el seeder de permisos (`PermissionSeeder`) y roles (`RoleSeeder`) para que los tests siempre cuenten con el estado de seguridad real.


en /doc estan todas las carpetas de las epicas, y en cada una de ellas se encuentra la documentación de la epica, la bitacora de la epica, la auditoria de la epica y la epica en si.