Siguiendo la lógica siguiente que hemos trabajado

1. Estructura de Datos
Ventas (Sale)
La estructura de ventas está diseñada principalmente para importar datos históricos (ej. desde Tango).

Modelo: App\Models\Sale
Campos Clave:
fecha, monto, moneda, comprobante: Datos básicos de la transacción.
client_id / cliente_nombre: Vinculación con el cliente.
Datos Tango: cod_cli, n_remito, cond_vta, cod_articu (artículo), cantidad, precio.
Integridad: Usa un campo hash generado automáticamente (combinando fecha, cliente, comprobante y monto) para evitar duplicados al importar.
Presupuestos (Budget)
Los presupuestos tienen una estructura más compleja para permitir el seguimiento de proyectos ("semáforo").

Modelo: App\Models\Budget
Campos Clave:
fecha, monto, moneda, comprobante.
Gestión de Proyectos:
centro_costo, nombre_proyecto.
fecha_oc (Orden de Compra), fecha_estimada_culminacion, fecha_culminacion_real.
estado: Controla el estado del proyecto (ej. semáforo en días).
saldo, porc_facturacion, horas_ponderadas.
Integridad: También utiliza un hash para unicidad.
2. Permisos y Seguridad
El sistema utiliza Spatie Permission con Roles y Permisos granulares.

Roles Principales
Super Admin: Tiene acceso total (Permission::all()).
Admin:
Tiene permisos de gestión (view_sales, create_sales, edit_sales).
Puede ver dashboards y gestionar usuarios.
Manager (Gerente):
Tiene permisos similares al Admin para ver información operativa (view_sales, view_production, view_hr).
Permisos Específicos
Para acceder a los módulos de Ventas y Presupuestos, el usuario necesita específicamente el permiso:

view_sales: Permite ver el historial de ventas, presupuestos y acceder al wizard de importación.
3. Acceso en Rutas (web.php)
El acceso está protegido por Middleware en dos niveles:

Nivel de Rol:
Las rutas bajo /admin requieren ser Super Admin, Admin o Manager.
Las rutas bajo /gerente requieren ser Manager.


Crear rutas para los siguientes nuevos módulos:

1. Detalles Horas
2. Compras Materiales
3. Satisfacción Personal
4. Satisfacción Clientes
5. Tableros
6. Proyecto Automatización.


Cada ruta tendrá su sidebar correspondiente, el sidevar es el diseño para todo el sistema, pero no debe tener link, es decir que cuando llege el momento se diseñara el sidebar correspondiente de la ruta, cuando se genere el contenido que tenga la misma. la ideas es que se le debe asignar permisos para poder entrar a la ruta. Generar seeder de usuarios para cada ruta asi probar, por ejemplo detalleshoras@gadium.com