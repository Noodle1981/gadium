# Gadium - Sistema de Gestión Empresarial Industrial

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-4E56A6?style=flat&logo=livewire)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3-38B2AC?style=flat&logo=tailwind-css)](https://tailwindcss.com)
[![SQLite](https://img.shields.io/badge/SQLite-Dev-003B57?style=flat&logo=sqlite)](https://www.sqlite.org)

## 📋 Descripción

Gadium es un sistema SaaS de gestión empresarial diseñado específicamente para transformar la operación de Gaudium, una empresa industrial que actualmente gestiona sus procesos mediante archivos Excel desconectados.

El sistema centraliza la gestión de:
- 📊 **Ventas y Facturación**
- 👥 **Capital Humano** (Horas ponderadas)
- 🏭 **Producción y Calidad**
- 📈 **KPIs e Inteligencia de Negocios**
- 🔐 **Control de Accesos** (RBAC Dinámico)

## 🎯 Objetivos del Proyecto

| Métrica | Actual | Objetivo |
|---------|--------|----------|
| Tiempo de reportes | 5 días | Tiempo real |
| Duplicidad de datos | Alta | 0% |
| Errores de carga | ~95% | ~5% |
| Tiempo de carga | Variable | < 5s (2000 filas) |

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 12 (PHP 8.2+)
- **Base de Datos**: 
  - SQLite (Desarrollo)
  - MySQL 8.0 (Producción)
- **Autenticación**: Laravel Fortify/Breeze
- **Permisos**: Spatie Laravel Permission

### Frontend
- **Framework**: Livewire 3 (TALL Stack)
- **UI**: Tailwind CSS
- **JavaScript**: Alpine.js

### Infraestructura
- **Desarrollo**: Local
- **Producción**: Hostinger VPS/Cloud Startup
- **Visualización**: Grafana (API REST JSON)

## 📁 Estructura del Proyecto

```
Gadium/
├── .agent/                    # Configuración del agente IA
│   ├── contex.md             # Contexto del proyecto
│   └── reglas_de_trabajo.md  # Reglas de desarrollo
├── Epica1/                   # ÉPICA 01: Gestión de Accesos
├── Epica2/                   # ÉPICA 02: Motor de Ingesta
├── Epica3/                   # ÉPICA 03: Producción y Calidad
├── Epica4/                   # ÉPICA 04: Capital Humano
├── Epica5/                   # ÉPICA 05: Inteligencia de Negocios
├── Epica6/                   # ÉPICA 06: Integración Grafana
├── arquitectura.md           # Documento de arquitectura
└── README.md                 # Este archivo
```

## 🚀 Instalación

### Prerrequisitos

- PHP 8.2 o superior
- Composer
- Node.js 18+ y NPM
- SQLite (para desarrollo)
- MySQL 8.0 (para producción)

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/Noodle1981/gadium.git
cd gadium
```

2. **Instalar dependencias de PHP**
```bash
composer install
```

3. **Instalar dependencias de Node**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configurar base de datos**

Para desarrollo (SQLite):
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

Para producción (MySQL):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gadium
DB_USERNAME=root
DB_PASSWORD=
```

6. **Crear base de datos SQLite**
```bash
touch database/database.sqlite
```

7. **Ejecutar migraciones**
```bash
php artisan migrate
```

8. **Ejecutar seeders**
```bash
php artisan db:seed
```

9. **Compilar assets**
```bash
npm run dev
```

10. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000`

## 👥 Roles del Sistema

| Rol | Descripción | Permisos |
|-----|-------------|----------|
| **Super Admin** | Acceso total al sistema | Gestión de roles, permisos y configuración |
| **Tenant Admin** | Administrador de empresa | Configuración de KPIs y usuarios |
| **Manager** | Gerente operativo | Carga de archivos y validación |
| **Viewer** | Visualizador | Solo lectura de dashboards |

## 📊 Épicas del Proyecto

### ✅ ÉPICA 00: Instalación y Configuración
- Instalación de Laravel 12
- Configuración de SQLite
- Instalación de Livewire 3 y Tailwind CSS
- Configuración de Spatie Permission

### 🔐 ÉPICA 01: Gestión de Accesos y Gobierno de Datos
- Sistema de autenticación seguro
- CRUD de usuarios con autogestión
- Gestor dinámico de roles y permisos

### 📥 ÉPICA 02: Motor de Ingesta y Normalización
- Importador de archivos CSV/Excel
- Validación de esquema y datos
- Normalización de clientes (Fuzzy Matching)
- Prevención de duplicados (Hash SHA-256)

### 🏭 ÉPICA 03: Producción y Calidad
- Registro de producción por proyecto
- Cálculo automático de tasas de error
- Sistema de alertas críticas (> 20% defectos)

### 👷 ÉPICA 04: Capital Humano
- Gestión de factores de ponderación
- Procesamiento automático de horas
- Cálculo de horas ponderadas

### 📈 ÉPICA 05: Inteligencia de Negocios
- Algoritmo de Pareto (80/20)
- Análisis de diversificación de ventas
- KPIs estratégicos

### 📊 ÉPICA 06: Integración con Grafana
- API REST para métricas
- Autenticación con tokens
- Tablas de resumen optimizadas

## 🔧 Características Técnicas Clave

### Prevención de Duplicados
- Hash SHA-256 de campos clave
- Verificación antes de inserción
- Reporte detallado de duplicados

### Normalización de Clientes
- Algoritmo Levenshtein (similitud > 85%)
- Resolución interactiva
- Sistema de aliases con aprendizaje

### Performance Optimizado
- Índices en columnas críticas
- Chunking de 1000 filas
- Jobs en colas para importaciones
- Tablas de resumen pre-calculadas

### Sistema de Alertas
- Cálculo automático de métricas
- Umbrales configurables
- Notificaciones en dashboard + email

## 🧪 Testing

### Ejecutar todos los tests
```bash
php artisan test
```

### Ejecutar tests específicos
```bash
php artisan test --filter=NombreDelTest
```

### Tests por épica
```bash
php artisan test tests/Feature/Epica1
```

## 📝 Workflow de Desarrollo

### Reglas de Trabajo

1. **Una sesión = Una épica**
2. **Feature branches**: `feature/epica-{nombre}`
3. **SQLite en desarrollo**, MySQL en producción
4. **Cronometrar épicas** (inicio/fin)
5. **Crear auditoría** antes de merge
6. **Testing obligatorio** (Unit + Feature)

### Proceso de Desarrollo

```bash
# 1. Crear rama de épica
git checkout -b feature/epica-nombre

# 2. Desarrollar funcionalidad
# ... código ...

# 3. Ejecutar tests
php artisan test

# 4. Crear auditoría
# Crear archivo: auditoria_nombre_epica.md

# 5. Commit y push
git add .
git commit -m "feat: descripción de la épica"
git push origin feature/epica-nombre

# 6. Esperar aprobación para merge
```

## 📚 Documentación

- **Arquitectura**: [`arquitectura.md`](./arquitectura.md)
- **Contexto**: [`.agent/contex.md`](./.agent/contex.md)
- **Reglas**: [`.agent/reglas_de_trabajo.md`](./.agent/reglas_de_trabajo.md)
- **Épicas**: Carpetas `Epica{1-6}/`

## 🔒 Seguridad

- Autenticación con Laravel Fortify/Breeze
- Contraseñas encriptadas (Bcrypt/Argon2)
- RBAC dinámico con Spatie Permission
- Sesiones con timeout de 1 día
- Validación estricta de inputs
- Protección CSRF
- Sanitización de datos

## 🚧 Roadmap

- [x] Configuración inicial del repositorio
- [x] Documentación de arquitectura
- [x] Definición de épicas
- [ ] **Sprint 0: Instalación** ⬅️ Siguiente
- [ ] Sprint 1: ÉPICA 01 - Autenticación
- [ ] Sprint 2: ÉPICA 02 - Importador
- [ ] Sprint 3: ÉPICA 03 - Producción
- [ ] Sprint 4: ÉPICA 04 - RRHH
- [ ] Sprint 5: ÉPICA 05 - BI
- [ ] Sprint 6: ÉPICA 06 - Grafana

## 🤝 Contribución

Este es un proyecto privado para Gaudium. El desarrollo sigue las reglas establecidas en `.agent/reglas_de_trabajo.md`.

## 📄 Licencia

Propietario - Gaudium © 2026

## 📞 Contacto

- **Repositorio**: https://github.com/Noodle1981/gadium.git
- **Documentación**: Ver carpeta `.agent/`

---

**Versión**: 1.0  
**Estado**: En Desarrollo  
**Última actualización**: 2026-01-08
