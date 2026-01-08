ÉPICA 01: Gestión de Accesos y Gobierno de Datos (RBAC Dinámico)
Justificación Estratégica:
Al tratarse de una solución a medida para un único cliente (Gaudium), la prioridad arquitectónica cambia del aislamiento entre inquilinos (Multi-tenancy) a la flexibilidad administrativa interna. El sistema debe empoderar a la organización para autogestionar su estructura de permisos sin depender del equipo de desarrollo para cada cambio organizacional. El "Super Administrador" se convierte en el arquitecto de la seguridad interna, definiendo qué ve y qué hace cada empleado mediante una interfaz gráfica de gestión de roles dinámica.
HU-01: Infraestructura de Autenticación y Seguridad Base
Prioridad: Crítica (Bloqueante)
Estimación: 5 Puntos de Historia
Asignación: Backend Architect
Narrativa de Usuario:
Como Gerente de Sistemas de Gaudium,
Quiero un sistema de autenticación robusto y centralizado,
Para asegurar que solo el personal autorizado de la empresa pueda acceder a la plataforma, protegiendo la información sensible de ventas y producción contra accesos externos no autorizados.
Contexto y "So What?":
Aunque el sistema es interno, la exposición a internet requiere estándares de seguridad modernos. Se debe implementar Laravel Auth (Fortify o Breeze) con protecciones contra fuerza bruta.
Criterios de Aceptación:
Login Seguro: Implementación de autenticación con validación de credenciales encriptadas (Bcrypt/Argon2).
Bloqueo de Registro Público: La ruta de registro (/register) debe estar deshabilitada públicamente. Solo los administradores existentes pueden crear nuevos usuarios desde dentro del panel.
Gestión de Sesiones: Logout automático tras  1 día  de inactividad para prevenir accesos en terminales compartidas en planta.


HU - 01.1:  Gestión de Usuarios (CRUD) y Ciclo de Vida de Credenciales

Prioridad: Alta
Estimación: 8 Puntos de Historia
Dependencias: HU-01

Narrativa de Usuario:

Como Super Administrador,
Quiero registrar nuevos empleados en la plataforma ingresando únicamente sus datos mínimos (Nombre, Email, Rol) y que ellos gestionen su propia contraseña,
Para agilizar el proceso de alta de personal operativo (Alta Fricción vs Baja Fricción) y reducir la carga de soporte técnico delegando la seguridad de la contraseña al usuario final.

Contexto y "¿So What?":
En un entorno industrial, solicitar demasiados datos al alta genera resistencia. El administrador debe poder dar de alta a un operario en 30 segundos. A su vez, por seguridad, nadie (ni el admin) debe conocer la contraseña del usuario; esta debe ser privada y recuperable autónomamente.

Criterios de Aceptación:
Alta Rápida de Usuarios (Solo Admin):
El formulario de creación debe exigir estrictamente: Nombre, Email y Rol (Dropdown poblado por HU-02).
Al crear el usuario, el sistema debe generar una contraseña temporal aleatoria o enviar un enlace de "Configurar contraseña" al email ingresado (usando Laravel Notification/Mail).
Validación: El email debe ser único en la base de datos.
Listado y Gestión:
El Admin debe visualizar una tabla con todos los usuarios, su rol actual y estado (Activo/Inactivo).
Debe existir la opción de "Revocar Acceso" (Soft Delete) inmediata para empleados desvinculados.
Autogestión de Contraseñas (Usuario Final):
Flujo de Olvido: En la pantalla de login, debe existir un enlace "¿Olvidaste tu contraseña?". Al ingresar el email, el sistema enviará un token de un solo uso con expiración (60 min).
Cambio Voluntario: Dentro del perfil de usuario, debe existir la opción de cambiar contraseña, solicitando obligatoriamente la "Contraseña Actual" y la "Nueva Contraseña" (mínimo 8 caracteres).
Escenario Gherkin (Recuperación):
Dado que el usuario "Sergio" olvidó su clave.
Cuando solicita el restablecimiento y hace clic en el enlace del correo.
Entonces el sistema le permite definir una nueva credencial y revoca cualquier sesión activa previa en otros dispositivos por seguridad.