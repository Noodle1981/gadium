ÉPICA 04: Gestión de Capital Humano (Horas Ponderadas)
Justificación Estratégica:
Gaudium opera con una lógica compleja de "Horas Ponderadas" (Factores con precisión decimal como 1.94390399).1 La lógica es el núcleo de la facturación y los pagos. Automatizar esto libera al Coordinador (Sergio) de cálculos manuales propensos a error y garantiza que la empresa venda las 3394 horas objetivo mensuales.
HU-07: Gestión Temporal de Factores de Ponderación
Prioridad: Alta
Estimación: 5 Puntos de Historia
Narrativa de Usuario:
Como Gerente de RRHH,
Quiero administrar una tabla maestra de factores de ponderación definidos por Rol y Rango de Fechas,
Para asegurar que el sistema aplique el multiplicador correcto (ej. 1.9439) vigente en el momento en que se trabajó la hora, manteniendo la integridad histórica de los datos incluso si los factores cambian en el futuro.
Criterios de Aceptación:
Precisión Decimal:
El campo valor_factor en la base de datos debe ser DECIMAL(10, 8) o superior para evitar errores de redondeo acumulativos que afecten la facturación.
Validación de Rangos:
El sistema debe impedir la creación de factores con rangos de fechas superpuestos para un mismo rol.
Debe existir siempre un factor por defecto (1.0) si no se especifica uno.
HU-08: Procesamiento Automático de Horas y Eficiencia
Prioridad: Media
Estimación: 8 Puntos de Historia
Dependencias: HU-07
Narrativa de Usuario:
Como Coordinador de Proyectos (Sergio),
Quiero cargar mis horas reloj simples y que el sistema derive automáticamente las horas ponderadas basándose en mi rol,
Para eliminar la carga cognitiva de realizar cálculos manuales diarios y obtener feedback inmediato sobre mi contribución al objetivo mensual de horas vendidas.
Criterios de Aceptación:
Lógica de Transformación:
Al importar una línea del CSV de horas:
Identificar el rol del empleado.
Buscar en la tabla de factores el valor vigente para la fecha del registro.
Horas_Ponderadas = Horas_Reloj * Factor_Vigente.
Persistir ambos valores (horas_reales, horas_ponderadas) para auditoría.
