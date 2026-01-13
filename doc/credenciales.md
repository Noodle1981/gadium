# Credenciales de Acceso para Pruebas (Entorno de Desarrollo)

A continuación se detallan las credenciales para los usuarios predeterminados generados por los seeders.

## Usuarios por Rol

| Rol | Nombre | Email | Contraseña | Acceso Principal |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Super Administrador | `admin@gaudium.com` | `password` | `/admin/dashboard` (Gestión Global) |
| **Admin** | Administrador | `administrador@gaudium.com` | `password` | `/admin/dashboard` (Gestión Técnica y Global) |
| **Manager** | Gerente | `gerente@gaudium.com` | `password` | `/manager/dashboard` (Gestión Global y KPIs) |


> **Nota**: La contraseña por defecto para todos los usuarios en desarrollo es `password`.

## Accesos API (Grafana / BI)



### Endpoints Disponibles
- **Pareto**: `GET /api/v1/metrics/sales-concentration`
- **Eficiencia**: `GET /api/v1/metrics/production-efficiency`
