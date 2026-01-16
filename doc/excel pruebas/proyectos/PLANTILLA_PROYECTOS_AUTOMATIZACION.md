# Plantilla Excel - Proyectos de Automatización

## Estructura del Archivo

El archivo Excel debe tener exactamente **5 columnas** con los siguientes headers en la primera fila:

| Columna | Nombre del Header | Tipo de Dato | Obligatorio | Valores Permitidos |
|---------|------------------|--------------|-------------|-------------------|
| A | Proyecto ID | Texto | ✅ Sí | Cualquier texto o número (ej: "3503", "PRY-001") |
| B | Cliente | Texto | ✅ Sí | Nombre del cliente (ej: "EL TAMBILLO SRL") |
| C | Proyecto Descripción | Texto | ❌ No | Descripción del proyecto |
| D | FAT | Texto | ❌ No | "SI" o "NO" (default: "NO") |
| E | PEM | Texto | ❌ No | "SI" o "NO" (default: "NO") |

## Ejemplo de Datos

```
Proyecto ID | Cliente          | Proyecto Descripción                           | FAT | PEM
3503        | EL TAMBILLO SRL  | Control Remoto para cisterna El Tambillo      | SI  | NO
3530        | SAINT GOBAIN     | Automatismo de secadero                       | NO  | NO
3545        | ARCOR            | Tablero de control principal                  | SI  | SI
3560        | COCA COLA        | Sistema de monitoreo                          | NO  | SI
3575        | PEPSI            | Automatización de línea de producción         | SI  | NO
```

## Reglas de Validación

1. **Proyecto ID** y **Cliente** son obligatorios
2. Si falta **FAT** o **PEM**, se asume "NO"
3. Los valores de FAT/PEM se convierten automáticamente a mayúsculas
4. Las filas vacías se ignoran automáticamente
5. Los duplicados (mismo Proyecto ID + Cliente + Descripción) se omiten

## Formato de Archivo

- **Extensión:** `.xlsx` o `.xls`
- **Tamaño máximo:** 10 MB
- **Formato de celdas:** General (para todas las columnas)
- **Primera fila:** Debe contener los headers exactos

## Notas Importantes

⚠️ **Los nombres de los headers deben ser exactos:**
- "Proyecto ID" (con espacio)
- "Proyecto Descripción" (con espacio y tilde en "ó")
- "FAT" y "PEM" en mayúsculas

✅ **Cambios vs versión anterior:**
- Ya NO se usan dos columnas "Proyecto"
- Ahora son headers únicos y claros
- Código mucho más simple y robusto
