HU-02: Gestor Dinámico de Roles y Permisos (Role Builder UI)
Prioridad: Crítica
Estimación: 13 Puntos de Historia
Dependencias: HU-01
Narrativa de Usuario:
Como Super Administrador,
Quiero una interfaz gráfica donde pueda crear, editar y eliminar roles (ej. "Ventas", "Operario Planta") y asignarles acceso granular a módulos específicos y tableros de control,
Para adaptar los accesos del sistema a la evolución de la estructura organizativa de Gaudium sin necesidad de solicitar cambios de código a los desarrolladores.
Contexto y "So What?":
A diferencia de los roles estáticos (hardcoded), un sistema RBAC (Role-Based Access Control) dinámico permite al cliente definir su propia jerarquía. Esto utiliza el paquete spatie/laravel-permission persistiendo la configuración en base de datos.9
Criterios de Aceptación:
CRUD de Roles:
El administrador debe poder crear un nuevo rol ingresando simplemente un nombre (ej. "Auditor de Calidad").
Validación: No se puede eliminar el rol "Super Admin".
Selector de Módulos (Matriz de Permisos):
Al crear/editar un rol, se debe presentar una lista de módulos del sistema con checkboxes (ej. [x] Módulo Ventas, [ ] Módulo RRHH, [x] Módulo Producción).
Al guardar, el sistema debe asignar los permisos subyacentes correspondientes (ej. view_sales, create_sales) a ese rol en la base de datos.
Asignación de Dashboards:
El formulario de rol debe incluir una sección "Dashboards Permitidos".
El admin puede seleccionar de una lista de Dashboards de Grafana disponibles (pre-configurados en el sistema con sus IDs/URLs).  (primero emplear MOCKS)
Resultado: Un usuario con el rol "Ventas" solo verá en su menú de navegación los links a los dashboards que el Admin haya marcado para ese rol.
Escenario de Prueba (Gherkin):
Dado que existe un usuario "Juan" sin rol.
Cuando el Admin crea el rol "Vendedor Jr" con acceso solo al "Módulo Ventas" y se lo asigna a "Juan".
Entonces al loguearse "Juan", el menú lateral solo debe mostrar "Ventas" y ocultar "Producción" y "RRHH".
Y si "Juan" intenta acceder por URL directa a /produccion, debe recibir un error 403.

ÉPICA 02: Motor de Ingesta y Normalización de Datos (The Import Wizard)
Justificación Estratégica:
La calidad de los datos es el talón de Aquiles de la situación actual. Los archivos Ventas.csv y Presupuestos.csv están plagados de inconsistencias: "TRIELEC S A" vs "TRIELEC S.A.", mezcla de monedas (USD/$AR) y duplicidad potencial.1 Si importamos "basura", los dashboards de Grafana mostrarán "basura". Esta épica construye el filtro sanitario del sistema: un "Import Wizard" inteligente que no solo carga datos, sino que los cura activamente.
HU-03: Asistente de Importación de Ventas con Validación de Esquema
Prioridad: Crítica
Estimación: 13 Puntos de Historia
Narrativa de Usuario:
Como Administrativo Comercial,
Quiero disponer de una interfaz paso a paso para subir los archivos CSV de ventas y presupuestos, que valide la estructura y el contenido antes de procesarlos,
Para prevenir la corrupción de la base de datos histórica con archivos mal formateados y asegurar que solo información válida alimente los KPIs de la empresa.
Contexto y "So What?":
La carga manual en Excel es propensa a errores de desplazamiento de columnas. El sistema debe actuar como un guardián rígido. Según las mejores prácticas de importación CSV 3, la validación temprana es clave para evitar fallos silenciosos.
Criterios de Aceptación:
Validación de Cabeceras y Tipos:
El sistema debe analizar la primera fila del CSV. Si faltan columnas obligatorias (Fecha, Cliente, Monto, Comprobante), debe rechazar el archivo in toto con un mensaje de error específico: "Error: Columna 'Monto' no encontrada en la cabecera".
Validación de tipos de datos: Si la columna 'Fecha' contiene texto no parseable (ej. "Enero 2025" en lugar de "01/01/2025"), el sistema debe identificar la fila exacta del error.
Gestión de Idempotencia (Prevención de Duplicados):
Lógica: Para cada fila, el sistema generará un hash único (SHA-256) basado en la concatenación de campos clave (fecha + cliente_normalizado + nro_comprobante + monto).
Acción: Antes de insertar, consultar si el hash ya existe en la base de datos.
Resultado: Si existe, ignorar el registro (skip) y contarlo como "Duplicado". Si no, insertar.
Reporte Final: Al terminar, mostrar resumen: "Procesados: 1000. Nuevos: 50. Duplicados Omitidos: 950". Esto satisface directamente el KPI de "0 duplicados tras re-subida".1
Performance (Chunking):
Dado que Hostinger puede tener límites de memoria PHP 1, el procesamiento debe realizarse utilizando Laravel Excel con lectura por lotes (Chunks de 1000 filas) y Jobs en colas (Queues), evitando timeouts en archivos grandes.

HU-04: Normalización Inteligente de Clientes (Fuzzy Matching)
Prioridad: Alta
Estimación: 8 Puntos de Historia
Dependencias: HU-03
Narrativa de Usuario:
Como Gerente Comercial,
Quiero que el sistema detecte similitudes en los nombres de clientes durante la importación (ej. "Saint Gobain" vs "Saint-Gobain S.A.") y me sugiera unificarlos,
Para que el análisis de Pareto (Diversificación de Ventas) sea preciso y no diluya el riesgo comercial al tratar al mismo cliente como dos entidades diferentes.
Contexto y "So What?":
El KPI de diversificación 1 exige saber cuánto vende realmente cada cliente. La suciedad en los datos actuales impide este cálculo. La implementación de algoritmos de coincidencia difusa es la solución técnica a este problema de negocio.
Criterios de Aceptación:
Detección de Candidatos (Levenshtein):
Al encontrar un cliente en el CSV que no existe en la tabla maestra clients, el sistema debe calcular la distancia de Levenshtein contra los nombres existentes.
Si la similitud es > 85% (configurable), debe marcarse como "Posible Duplicado".
Resolución Interactiva:
El usuario debe ver una pantalla de "Resolución de Entidades" donde se le pregunte:
"El archivo contiene 'TRIELEC S A'. En su base de datos existe 'TRIELEC S.A.'. ¿Desea vincularlos?"
Opción A: Sí, vincular (usa el ID existente y añade 'TRIELEC S A' como alias).
Opción B: No, crear nuevo cliente.
Aprendizaje (Aliasing):
El sistema debe almacenar los alias confirmados en una tabla client_aliases. En futuras importaciones, 'TRIELEC S A' debe resolverse automáticamente al ID principal sin preguntar al usuario, automatizando progresivamente la limpieza.
