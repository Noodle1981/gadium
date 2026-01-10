# Credenciales de Acceso para Pruebas (Entorno de Desarrollo)

A continuación se detallan las credenciales para los usuarios predeterminados generados por los seeders.

## Usuarios por Rol

| Rol | Nombre | Email | Contraseña | Acceso Principal |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Super Administrador | `admin@gaudium.com` | `password` | `/admin/dashboard` (Gestión Global) |
| **Admin** | Administrador | `administrador@gaudium.com` | `password` | `/admin/dashboard` (Operativa Diaria) |
| **Manager** | Gerente | `gerente@gaudium.com` | `password` | `/manager/dashboard` (KPIs y Análisis) |
| **Viewer** | Visualizador | `viewer@gaudium.com` | `password` | `/viewer/dashboard` (Solo Lectura / Grafana API) |

> **Nota**: La contraseña por defecto para todos los usuarios en desarrollo es `password`.

## Accesos API (Grafana / BI)

Los endpoints de inteligencia de negocios requieren un token de acceso (`Bearer Token`).

1. Loguearse como `viewer@gaudium.com`.
2. Habilitar la generación de tokens (Funcionalidad futura o vía `tinker`: `$user->createToken('grafana')->plainTextToken`).
3. Usar el token en el Header: `Authorization: Bearer <TOKEN>`.
   - **Token Permanente (Viewer)**: `1|IWUuRUTrTR6j0b4sKTK5YR1TXMHZu2q78Dvjb9b1121ffd87`

### Endpoints Disponibles
- **Pareto**: `GET /api/v1/metrics/sales-concentration`
- **Eficiencia**: `GET /api/v1/metrics/production-efficiency`
