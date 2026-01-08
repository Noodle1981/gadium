Arquitectura y Requisitos de Producto (PRD)
Proyecto: Transformación SaaS Industrial (Excel a Laravel TALL Stack)
Versión: 1.0
Autor: Lead Technical PM & Architect
Estado: Draft para Aprobación
1. Stack Tecnológico (Definitivo)
Para garantizar compatibilidad con Hostinger y maximizar la velocidad de desarrollo, esta es la arquitectura innegociable:
Core: Laravel 12 (PHP 8.2+).
Frontend Interactivo: Livewire 3 (Gestión de estado sin complejidad de React/Vue separados).
UI Framework: Tailwind CSS (Diseño utility-first para rapidez).
Base de Datos: MySQL 8.0 (Motor InnoDB).
Infraestructura: Hostinger VPS (Recomendado sobre Shared para soportar Workers de cola y Docker si fuera necesario) o Shared "Cloud Startup" como mínimo.
Visualización: Grafana (Instancia externa o local) conectada vía API REST JSON (Infinity Plugin) para no exponer el puerto 3306 de MySQL directamente a internet.
2. Fase A: Auditoría de Datos e Ingeniería Inversa
Analizando los CSVs proporcionados (Ventas, Satisfacción, Horas, etc.), el modelo de datos actual es plano y redundante.
A.1. Limpieza de "Basura"
Archivo Tipo de gráfico.csv: Descartar completamente. Es una representación visual (layout) en celdas, no son datos. La visualización se definirá en Grafana.
Columna "Criterio de cálculo" en Objetivos.csv: Contiene lógica de negocio en texto natural (ej: "Sumar las cantidades... Comparar con el valor 15..."). Esto NO se puede importar tal cual. Debe traducirse a KpiStrategy clases en el Backend.
A.2. Modelo de Datos Relacional (Normalizado)
Diseño del esquema MySQL para soportar Multi-tenancy y Grafana:
Tablas Maestras (Globales/Tenant):
tenants: (id, name, plan, status)
users: (id, tenant_id, name, email, role_id)
clients: (id, tenant_id, legacy_code, business_name, tax_id) -> Extraído de Ventas/Satisfacción
projects: (id, tenant_id, client_id, code, name, status, start_date) -> Extraído de Presupuestos/Tableros
cost_centers: (id, tenant_id, code, description)
Tablas Transaccionales (Facts):
sales_invoices: (id, client_id, date, total_amount, currency, status) -> Fuente: Ventas.csv
sales_items: (id, invoice_id, article_code, quantity, unit_price) -> Detalle de venta
timesheets: (id, user_id, project_id, date, hours_type, hours_value, cost) -> Fuente: Detalle de Horas.csv
procurements: (id, project_id, budget_amount, actual_spent_amount, currency) -> Fuente: Compra de materiales.csv
manufacturing_logs: (id, project_id, panel_type, quantity, defects_count, date) -> Fuente: Tableros.csv
surveys: (id, type [client/employee], reference_id, date, scores_json, average_score) -> Fuente: Satisfacción.
A.3. Estrategia de Performance (Hostinger Friendly)
Grafana machacará la base de datos con consultas de agregación (SUM, AVG, GROUP BY).
Índices: Obligatorios en columnas tenant_id, created_at (o fechas de transacción), client_id, project_id.
Tablas de Resumen (Materialized Views lógicas): Crear una tabla daily_metrics_aggregates que se llene mediante un Job programado (Laravel Scheduler) cada noche. Grafana leerá de esta tabla, no de las transacciones crudas de hace 5 años.
3. Fase B: Decisión de Arquitectura de API
Decisión: Opción A (API REST integrada en Laravel).
Justificación Técnica:
Los datos analizados (Ventas, Horas, Satisfacción) requieren operaciones aritméticas básicas (Suma, Promedio, Conteo, Regla de tres simple).
Python (FastAPI) sería un "overkill" que consumiría 200-300MB de RAM adicionales en el VPS solo para tener el servicio levantado, sin aportar valor en ML o cálculo vectorial complejo.
Laravel Collections y Eloquent son más que capaces de manejar la lógica de transformación.
Mantenibilidad: Mantener un solo stack (PHP) reduce la carga cognitiva del equipo y facilita el despliegue en Hostinger.
4. Fase C: Sistema de Roles (RBAC) y Seguridad
C.1. Roles (Spatie Laravel Permission)
Super Admin: Acceso a todos los tenants (Soporte).
Tenant Admin: Configura KPIs y usuarios de su empresa.
Manager: Sube Excels y valida datos.
Viewer: Solo ve Dashboards (Usuario de servicio para Grafana).
C.2. Multi-tenancy & Grafana
Para asegurar que un cliente no vea datos de otro en Grafana:
Scope Global: En Laravel, usar GlobalScopes para inyectar automáticamente where('tenant_id', $user->tenant_id).
API Grafana: El endpoint de Grafana requerirá un API Token. Este token estará vinculado a un usuario específico (grafana_user_tenant_X).
Al consultar GET /api/kpi/sales, el sistema autentica al usuario del token e inyecta su tenant_id en la query.
5. Fase D: Gestión de Ingesta y Validación (UX/UI con Livewire)
El módulo "Import Wizard" es el corazón operativo.
Lógica de Control de Duplicidad (Algoritmo de Hash):
Dado que los Excel se suben masivamente y pueden repetirse:
Al leer una fila del Excel, generar un Hash Único (SHA-256) concatenando las columnas clave (Ej: fecha + numero_factura + codigo_articulo).
Comparar este hash contra la columna row_hash en la base de datos.
Si existe: Ignorar (o marcar como "Sin cambios").
No existe: Marcar como "Nuevo".
Hash existe pero datos difieren: Marcar como "Conflicto/Actualización".
Flujo UX (Livewire):
Dropzone: Arrastrar archivo.
Mapping: El sistema intenta adivinar columnas (Fecha -> date, Monto -> amount). Usuario confirma.
Dry Run (Validación): Procesa el archivo en memoria o tabla temporal.
Reporte de Impacto (Modal):
🟢 150 Filas nuevas (Listas para insertar).
🟡 20 Filas duplicadas (Se ignorarán).
🔴 5 Errores (Formato de fecha inválido en fila 4, 8, 20).
Confirmación: Usuario pulsa "Procesar".
6. Fase E: Protocolo Operativo (PRD Detallado)
1. El Problema y la Hipótesis de Valor
Problema: La empresa gestiona decisiones críticas (ventas, satisfacción, producción) basándose en 10 archivos Excel desconectados, propensos a errores humanos y sin historicidad confiable.
Hipótesis: Al centralizar la ingesta en un SaaS con validación estricta y visualización en Grafana, reduciremos el tiempo de generación de reportes de 5 días a tiempo real, y eliminaremos el 95% de los errores de duplicidad.
2. Gestión de Riesgos
Supuesto
Riesgo
Mitigación
El Excel siempre tiene el mismo formato
El usuario cambia columnas y la importación falla
Implementar mapeo dinámico de columnas (Headings matching) en el importador.
Hostinger soporta consultas masivas
Grafana tumba el servidor MySQL
Implementar Cache de 1 hora en endpoints de API y Tablas de Resumen diarias.
Los nombres de clientes son consistentes
"Arcor" y "Arcor SA" crean dos clientes
Normalización difusa (Fuzzy matching) o selector de cliente obligatorio al importar.

3. Especificaciones Funcionales (Historias de Usuario)
HU-01: Ingesta de Ventas (Crítica)
Como: Administrador Financiero.
Quiero: Subir el Ventas.csv mensual.
Para: Que se actualicen los ingresos sin duplicar facturas pasadas.
Criterios de Aceptación:
Detectar formato de fecha DD/MM/YYYY y convertir a MySQL YYYY-MM-DD.
Identificar facturas ya cargadas (por N_COMP) y omitirlas.
Alerta si un COD_CLI no existe en la base de maestros.
HU-02: Dashboard de Satisfacción (Visualización)
Como: Gerente de Calidad.
Quiero: Ver la evolución del NPS (Net Promoter Score) en Grafana.
Para: Reaccionar rápido a caídas en servicio.
Componentes: API Endpoint que agrupe surveys por mes y calcule promedio de average_score.
4. Anexos Técnicos: Métricas de Éxito
Tiempo de Carga: < 5 segundos para archivos de 2000 filas.
Integridad de Datos: 0 registros duplicados tras re-subir el mismo archivo 3 veces.
7. Fase F: Conexión Grafana
API Specification:
Base URL: https://api.tudominio.com/api/v1/metrics
Auth: Header Authorization: Bearer {token}
Endpoints:
GET /sales-performance
Params: from (date), to (date), group_by (day/month).
Response:
[
  {"time": "2023-01-01", "sales": 15000, "budget": 12000},
  {"time": "2023-02-01", "sales": 18000, "budget": 12000}
]


GET /project-efficiency
Logic: Cruce de tabla timesheets (Horas reales) vs projects (Horas estimadas).
Configuración en Grafana:
Utilizar plugin Infinity o JSON API. Configurar el JSON Path para mapear time al eje X y sales al eje Y.
8. Análisis de Riesgos y Verdad Técnica (El Desafío)
Aquí es donde dejo de ser amable y soy realista con tu Excel:
La Lógica Textual es un Problema Grave:
En Objetivos.csv, tienes celdas como: "Analizar 'Tableros'. Sumar las cantidades... Si el porcentaje es <= 20% el KPI es verde".
Riesgo: Ningún software lee lenguaje natural y lo ejecuta mágicamente.
Solución: Tienes que "Hardcodear" (programar explícitamente) estas reglas en clases PHP (App\Kpis\CalidadTablerosKpi.php) o crear un motor de reglas muy complejo (que no recomiendo para V1). No prometas que el usuario podrá "editar la fórmula escribiendo texto" en el SaaS.
Datos Sucios en "Clientes":
En Satisfacción, el cliente es "Saint Gobain". En Ventas puede ser "SAINT GOBAIN ARGENTINA".
Riesgo: Grafana mostrará dos líneas separadas.
Verdad Técnica: Necesitas una etapa de "Limpieza de Maestros" antes de salir a producción. El sistema debe obligar a vincular strings nuevos a IDs existentes.
Limitaciones de Hostinger Shared:
Si usas Grafana Cloud o Desktop consultando a tu API en Hostinger Shared, la latencia será alta (handshake SSL + boot de Laravel + Query + JSON response).
Advertencia: Si la consulta tarda > 30s (común en agragaciones grandes sin optimizar), Grafana dará timeout.
Mitigación: Usa tablas resumen (daily_aggregates) sí o sí. No hagas SUM() sobre la tabla de ventas_detalle (que puede tener millones de filas) en tiempo real.
Fechas Excel:
Excel guarda fechas como enteros o strings variados (10/1/2025 vs 2025-01-10). PHP Carbon es inteligente, pero fallará si el usuario mezcla formatos en la misma columna. La validación previa es obligatoria.
