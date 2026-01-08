basado en esto 

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

