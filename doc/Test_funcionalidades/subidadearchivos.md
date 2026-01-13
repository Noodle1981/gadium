# Validación de Esquema y Contenido (HU-03) [IMPLEMENTADO]

* El sistema valida la estructura del archivo antes de procesarlo para prevenir la corrupción de la base de datos:
* **Validación de Cabeceras**: Rechazo inmediato si faltan columnas obligatorias (Fecha, Cliente, Monto, Comprobante).
* **Validación de Tipos de Datos**: Identifica la fila exacta de error si una columna contiene texto no parseable (ej. Fecha).

# Prevención de Duplicados (Idempotencia) (HU-03) [IMPLEMENTADO]

* Para cada fila, se genera un hash único (SHA-256) basado en campos clave.
* Si el hash ya existe en la base de datos, el registro es ignorado (skipped) y contado como "Duplicado Omitido".
* El reporte final muestra un resumen con el conteo de "Nuevos" y "Duplicados Omitidos" (cumple el KPI de "0 duplicados tras re-subida").

#  Normalización Inteligente de Clientes (HU-04) [IMPLEMENTADO]

* Para asegurar la precisión del análisis de Pareto, el sistema detecta y unifica clientes con nombres similares:
* **Detección**: Uso de la distancia de Levenshtein para marcar como "Posible Duplicado" si la similitud es superior al 85%.
* **Resolución Interactiva**: El usuario cuenta con una pantalla de "Resolución de Entidades" para vincular el nuevo nombre (alias) con un cliente existente o crear uno nuevo.
* **Aprendizaje (Aliasing)**: Los alias confirmados se almacenan para que futuras importaciones se resuelvan automáticamente sin preguntar al usuario.

# Performance (HU-03) [IMPLEMENTADO]

* El procesamiento se realiza utilizando lectura por lotes (Chunks de 1000 filas) y Jobs en colas (Queues) para evitar problemas de memoria y timeouts en archivos grandes.

###  Estado Actual
El asistente de importación actúa exitosamente como un "filtro sanitario", curando activamente los datos importados y garantizando su integridad para los indicadores de negocio. Validado por tests automatizados (`CsvImportTest`).