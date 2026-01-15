# Guía de QA y Testing para Revisión de Épicas

Esta guía te ayudará a estructurar tu proceso de revisión ("Testing") para las épicas que has completado, utilizando Trello como tablero de control.

## 1. Tipos de Testing Requeridos

Para una revisión de producto final, nos enfocaremos principalmente en dos tipos:

### A. Testing Funcional (Manual / Caja Negra)
Es lo que harás "como usuario". No miras el código, miras la aplicación.
*   **Happy Path (Camino Feliz)**: ¿Funciona todo cuando el usuario hace lo correcto? (Ej: Llenar un formulario bien y guardar).
*   **Edge Cases (Casos Borde)**: ¿Qué pasa si intento romperlo? (Ej: Subir un archivo de 0MB, dejar campos vacíos requeridos, poner texto en campos numéricos).
*   **Role Testing**: ¿Veo solo lo que mi rol debe ver? (Vital para tu sistema Multitenant/Roles).

### B. Testing de Regresión (Smoke Test)
Verificar que lo nuevo no rompió lo viejo.
*   Ej: Al implementar *Grafana*, ¿siguen funcionando los *Checklists de Operarios*?

---

## 2. Flujo de Trabajo con Trello

Mueve tus tarjetas de **"Done"** (o "En Desarrollo") a una columna llamada **"QA / Testing"**.

**Pasos por Tarjeta (Épica/Historia):**

1.  **Leer Criterios de Aceptación**: Antes de probar, ten claro qué *debe* hacer la feature.
2.  **Ejecutar Prueba Manual**:
    *   Usa el rol correcto (Gerente, Operario, etc.).
    *   Realiza la acción completa.
3.  **Veredicto**:
    *   ✅ **Aprobado**: Si cumple todo, mueve la tarjeta a **"Done / Finalizado"**.
    *   ❌ **Bug Encontrado**:
        *   No muevas la tarjeta.
        *   Crea una etiqueta roja "Bug" en Trello.
        *   Agrega un comentario en la tarjeta:
            *   **Pasos**: Qué hiciste.
            *   **Resultado Esperado**: Qué debió pasar.
            *   **Resultado Real**: El error que viste.
        *   (Opcional) Crea una sub-tarea o nueva tarjeta "Bugfix: [Nombre]" y muévela a "To Do".

---

## 3. Checklist de QA General (Tu "Hoja de Ruta")

Usa esta lista para cada Épica que revises:

### 🔐 Seguridad & Roles (Prioridad Alta)
- [ ] ¿Puedo entrar a rutas de Admin siendo Operario? (Prueba de URL directa).
- [ ] ¿El sidebar muestra enlaces rotos o prohibidos?
- [ ] ¿Al cerrar sesión y dar "Atrás" en el navegador, me pide login?

### 💾 Datos & Formularios
- [ ] **Crear**: ¿Se guarda en la base de datos?
- [ ] **Leer**: ¿Se muestra en la tabla/lista correctamente?
- [ ] **Editar**: ¿Si cambio un dato, se actualiza o crea uno nuevo duplicado?
- [ ] **Borrar**: ¿El borrado es lógico (soft delete) o físico? ¿Rompe algo si borro un padre (ej: Cliente) con hijos (ej: Ventas)?

### 🎨 UI/UX (Experiencia de Usuario)
- [ ] ¿Los mensajes de error son claros? (No "Error 500", sino "Campo obligatorio").
- [ ] ¿Los mensajes de éxito aparecen tras guardar?
- [ ] ¿Funciona en tamaño móvil (si es requisito)?

---

## 4. Herramientas Recomendadas

*   **Navegador en Incógnito**: Para probar roles "Operario" sin desloguear tu "Super Admin".
*   **Herramientas de Desarrollador (F12)**: Mira la consola. Si ves letras rojas al hacer clic, es un bug técnico.
*   **Imágenes/Grabación**: Si encuentras un bug visual, pega un screenshot en Trello.
