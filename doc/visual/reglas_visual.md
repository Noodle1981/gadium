# Visual Rules & Standards

## History & List Views (Vistas de Historial)

All History views (e.g., Sales History, Budget History) must follow this standard structure:

### 1. Gradient Banner Header (`x-slot:header`)
Instead of a simple white background, use a gradient header that matches the module's identity.

*   **Wrapper**: `bg-gradient-to-r from-orange-600 to-orange-800 rounded-xl shadow-2xl overflow-hidden -mx-6 sm:-mx-8` (Adjust colors per module if needed).
*   **Content**:
    *   **Left**: Title (`h1 text-2xl font-bold text-white`) + Subtitle (`p text-orange-100 text-sm`).
    *   **Right**: Primary Actions (e.g., "Importar", "Crear Nuevo").
*   **Actions**: Buttons should be placed *inside* the banner on the right side, using `bg-white text-orange-700` styling for high contrast.

### 2. Livewire/Volt Component Architecture
Convert static views to **Livewire/Volt** components to enable SPA-like interactivity (Search, Pagination) without full page reloads.

### 3. Controls Bar
Immediately above the data table (`p-6 text-gray-900`), include:
*   **Search**: A real-time search input (`wire:model.live.debounce.300ms="search"`).
*   **Count**: "Mostrando X registros".
*   **View Options**: Toggle Expand/Collapse columns if the table is wide.

### 4. Data Table
*   **Style**: `min-w-full divide-y divide-gray-200 text-xs`.
*   **Responsiveness**: Use `overflow-x-auto`.
*   **Pagination**: Use standard Laravel pagination links at the bottom (`{{ $items->links() }}`).

## Feedback & Modals

For critical actions (e.g., creating or updating records), distinct feedback is required.

### 1. Success Modal (Modal de Éxito)
Instead of subtle flash messages, use a blocking modal for successful operations that require redirection.
*   **Trigger**: Use `Livewire.dispatch('event-name')` from PHP.
*   **UI**: fixed inset-0 overlay, centered white card.
*   **Content**: Success Icon (Green check), Title "¡Cambio Realizado!", Message "El [entidad] ha sido...".
*   **Action**: Single button "Aceptar" that redirects to the index/history view.

### 2. Error Modal (Modal de Error)
For validation errors or blockers, show a modal to alert the user.
*   **Trigger**: Use `Livewire.dispatch('show-error-modal')` when catching `ValidationException` or adding manual errors.
*   **UI**: fixed inset-0 overlay, centered white card.
*   **Content**: Error Icon (Red X), Title "¡Atención!", Message "Revise los campos para más información" or specific error.
*   **Action**: Single button "Entendido" or "Cerrar" that closes the modal.

## Navigation & Sidebar (Navegación Lateral)

Modules should be grouped logically in the sidebar using `x-nav-link` components.

### 1. Structure
*   **Role-Based Visibility**: Use `@role('RoleName')` to conditionally show/hide links.
*   **Grouping**: Group related links (e.g., "Operaciones", "Gestión").
*   **Active State**: Ensure `request()->routeIs('pattern.*')` is used for `active` prop to highlight the current section.

### 2. Icons
*   Use Heroicons (Outline version) consistently.
*   Size: `w-5 h-5` or `w-6 h-6`.

---

## Example Implementation (Sales History)

```blade
<x-slot name="header">
    <div class="bg-gradient-to-r from-orange-600 to-orange-800 ...">
        <div class="flex justify-between ...">
            <h1>Historial</h1>
            <a href="..." class="bg-white text-orange-700 ...">Importar</a>
        </div>
    </div>
</x-slot>

<div class="p-6 ...">
    <input wire:model.live="search" ... />
    <table>...</table>
    {{ $items->links() }}
</div>
```
