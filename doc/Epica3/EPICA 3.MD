ÉPICA 03: Motor de Digitalización de Producción y Calidad
Justificación Estratégica:
El análisis de Tableros.csv es alarmante: proyectos con 100% de tasa de error (defectos en cada unidad producida).1 La gestión actual es reactiva. Esta épica transforma el registro de producción en un sistema de alerta temprana. No se trata solo de contar tableros, sino de detener la hemorragia de eficiencia.
HU-05: Registro de Producción con Vinculación de Proyectos
Prioridad: Alta
Estimación: 5 Puntos de Historia
Narrativa de Usuario:
Como Jefe de Taller,
Quiero registrar la producción diaria (unidades y correcciones) vinculándola inequívocamente a un Proyecto activo,
Para alimentar los indicadores de avance de obra y calidad en tiempo real, reemplazando las planillas aisladas.
Contexto y "So What?":
La integridad referencial es clave. Un tablero no puede existir en el vacío; debe pertenecer a un proyecto para analizar la rentabilidad.
Criterios de Aceptación:
Validación Referencial:
El sistema debe impedir la carga de registros si el ID de Proyecto (ej. "3400") no existe previamente en la base de datos. Debe ofrecer la opción de crear el proyecto "on-the-fly" con datos mínimos o rechazar la fila.
Captura de Datos de Calidad:
Campos obligatorios: unidades_producidas (integer > 0) y documentos_correccion (integer >= 0).
Validación lógica: documentos_correccion no puede ser mayor que unidades_producidas * N (donde N es un factor de tolerancia, típicamente 1, salvo reprocesos múltiples).

HU-06: Motor de Cálculo de Defectos y Alertas Críticas
Prioridad: Crítica
Estimación: 8 Puntos de Historia
Dependencias: HU-05
Narrativa de Usuario:
Como Gerente de Calidad,
Quiero que el sistema recalcule la Tasa de Error acumulada de cada proyecto tras cada carga de datos y me envíe una alerta inmediata si supera el 20%,
Para intervenir en la línea de producción antes de finalizar el proyecto, evitando costos de retrabajo masivos y protegiendo la reputación de Gaudium ante clientes como Calidra.
Contexto y "So What?":
El caso de estudio muestra proyectos con 100% de error. Esto es inaceptable. El sistema pasa de pasivo a proactivo. La latencia de 5 días para enterarse de un problema de calidad se reduce a segundos.
Criterios de Aceptación:
Algoritmo de Tasa de Error:
Tasa (%) = (Sumatoria de Correcciones / Sumatoria de Unidades Producidas) * 100.
El cálculo debe ser acumulativo por project_id.
Disparador de Alertas (Event-Driven):
Dado que se actualiza el registro de producción del Proyecto 3400.
Cuando la nueva Tasa de Error calculada es 40% (superando el umbral del 20%).
Entonces el sistema debe:
Cambiar el estado de calidad del proyecto a "CRÍTICO".
Generar una notificación interna en el dashboard para los usuarios con permiso de "Ver Alertas de Calidad".
Enviar un correo electrónico automático con el detalle: "Alerta de Calidad: Proyecto 3400 (Manga Retráctil) excede tolerancia. Tasa actual: 40%".
