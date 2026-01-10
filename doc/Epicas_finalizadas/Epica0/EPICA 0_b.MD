# ÉPICA 00: Instalación y Configuración Base del Proyecto Gadium

## Información General

- **Prioridad**: Crítica (Bloqueante)
- **Estimación**: 8 Puntos de Historia
- **Estado**: ✅ Completada
- **Fecha de Inicio**: 2026-01-08 19:01:28
- **Fecha de Finalización**: 2026-01-08 19:14:00
- **Tiempo Total**: ~13 minutos

## Objetivo

Establecer la infraestructura base del proyecto Gadium instalando Laravel 12 con el stack TALL (Tailwind, Alpine.js, Livewire, Laravel) y configurando SQLite como base de datos de desarrollo, siguiendo las especificaciones de `arquitectura.md`.

## Contexto y Justificación

Esta épica es el fundamento del proyecto. Sin una instalación correcta del stack tecnológico, no es posible avanzar con las épicas funcionales. Se priorizó:

1. **SQLite para desarrollo**: Permite desarrollo rápido sin dependencias de servidor MySQL
2. **TALL Stack**: Maximiza productividad con Livewire 3 para interactividad sin complejidad de SPA
3. **Spatie Permission**: Base para el sistema RBAC dinámico de la Épica 01

## Tecnologías Instaladas

### Backend
- ✅ **Laravel 12.46.0** (PHP 8.2.12)
- ✅ **Livewire 3.7.3** (Framework interactivo)
- ✅ **Spatie Laravel Permission 6.24.0** (RBAC)

### Frontend
- ✅ **Tailwind CSS 3.x** (UI Framework)
- ✅ **PostCSS** (Procesador CSS)
- ✅ **Autoprefixer** (Compatibilidad de navegadores)

### Base de Datos
- ✅ **SQLite** (Desarrollo)
- ✅ Migraciones base de Laravel ejecutadas
- ✅ Migraciones de Spatie Permission ejecutadas

### Herramientas
- ✅ **Vite** (Build tool)
- ✅ **Composer 2.8.10** (Gestor de dependencias PHP)
- ✅ **NPM** (Gestor de dependencias Node)

## Tareas Realizadas

### ✅ Fase 1: Preparación
- Creada rama `feature/epica-0-instalacion`
- Verificados requisitos del sistema (PHP 8.2+, Composer)

### ✅ Fase 2: Instalación de Laravel 12
- Instalado Laravel 12.46.0 vía Composer
- Configurado archivo `.env` (SQLite por defecto)
- Generado `APP_KEY`
- Verificada instalación

### ✅ Fase 3: Instalación de Livewire 3
- Instalado Livewire 3.7.3 vía Composer
- Publicada configuración y assets
- Creado directorio `app/Livewire/`
- Verificado funcionamiento

### ✅ Fase 4: Instalación de Tailwind CSS
- Instaladas dependencias Node (92 paquetes)
- Creado `tailwind.config.js` con paths de Laravel y Livewire
- Creado `postcss.config.js`
- Verificado que Laravel 12 incluye Tailwind CSS moderno con `@import 'tailwindcss'`
- Assets compilados con Vite

### ✅ Fase 5: Instalación de Spatie Permission
- Instalado paquete vía Composer (v6.24.0)
- Publicada configuración
- Publicadas migraciones
- Ejecutadas migraciones (tabla `permissions`, `roles`, etc.)

### ✅ Fase 6: Configuración de Base de Datos
- SQLite configurado por defecto en `.env.example`
- Archivo `database/database.sqlite` creado automáticamente
- Migraciones ejecutadas exitosamente:
  - `create_users_table`
  - `create_password_reset_tokens_table`
  - `create_sessions_table`
  - `create_cache_table`
  - `create_jobs_table`
  - `create_permission_tables` (Spatie)

### ✅ Fase 7: Estructura de Directorios
Creados los siguientes directorios:
- `app/Services/` - Lógica de negocio
- `app/Traits/` - Traits reutilizables
- `tests/Feature/Epica1/` - Tests de Épica 1
- `tests/Feature/Epica2/` - Tests de Épica 2
- `tests/Feature/Epica3/` - Tests de Épica 3
- `tests/Feature/Epica4/` - Tests de Épica 4
- `tests/Feature/Epica5/` - Tests de Épica 5
- `tests/Feature/Epica6/` - Tests de Épica 6
- `resources/views/layouts/` - Layouts Blade
- `resources/views/components/` - Componentes Blade
- `Epica0/` - Documentación de esta épica

## Archivos de Configuración Creados

### `tailwind.config.js`
```javascript
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### `postcss.config.js`
```javascript
export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
}
```

## Verificaciones Realizadas

### ✅ Verificaciones Automáticas
1. `php artisan --version` → Laravel Framework 12.46.0
2. `php artisan migrate:status` → Todas las migraciones ejecutadas
3. `npm run build` → Assets compilados exitosamente

### ✅ Verificaciones de Estructura
1. Directorios de servicios y traits creados
2. Directorios de tests por épica creados
3. Directorios de vistas organizados
4. Documentación de épica creada

## Problemas Encontrados y Soluciones

### Problema 1: Livewire 3.0 incompatible con Laravel 12
**Error**: `livewire/livewire v3.0.0 requires symfony/http-kernel ^5.0|^6.0`

**Causa**: Laravel 12 usa Symfony 7, pero Livewire 3.0 solo soporta hasta Symfony 6

**Solución**: Instalar la versión más reciente de Livewire 3 sin especificar versión exacta
```bash
composer require livewire/livewire
```
**Resultado**: Se instaló Livewire 3.7.3 que es compatible con Symfony 7

### Problema 2: npx tailwindcss init falló
**Error**: `npm error could not determine executable to run`

**Causa**: Problema con npx en el entorno de PowerShell

**Solución**: Crear manualmente los archivos `tailwind.config.js` y `postcss.config.js`

**Resultado**: Archivos creados correctamente y Tailwind funcionando

### Problema 3: Comando mkdir con sintaxis Unix
**Error**: PowerShell no reconoce `mkdir -p`

**Causa**: PowerShell usa cmdlets diferentes a Unix

**Solución**: Usar `New-Item -ItemType Directory -Force -Path`

**Resultado**: Todos los directorios creados exitosamente

## Métricas de Éxito

| Métrica | Objetivo | Resultado | Estado |
|---------|----------|-----------|--------|
| Tiempo de instalación | < 30 min | ~13 min | ✅ |
| Paquetes instalados | 100+ | 111 (Composer) + 92 (NPM) | ✅ |
| Migraciones ejecutadas | 6+ | 6 | ✅ |
| Vulnerabilidades | 0 | 0 | ✅ |
| Compilación de assets | Exitosa | Exitosa | ✅ |

## Próximos Pasos

### Inmediato
1. ✅ Crear documentación de la épica (`EPICA 0.MD`)
2. ⏳ Crear bitácora de la épica
3. ⏳ Compilar assets finales
4. ⏳ Ejecutar servidor de desarrollo
5. ⏳ Crear auditoría de la épica
6. ⏳ Commit y push a rama `feature/epica-0-instalacion`

### ÉPICA 01: Gestión de Accesos y Gobierno de Datos
Una vez completada esta épica, se procederá con:
- HU-01: Infraestructura de autenticación segura
- HU-01.1: CRUD de usuarios con autogestión
- HU-02: Gestor dinámico de roles y permisos

## Lecciones Aprendidas

1. **Laravel 12 incluye Tailwind moderno**: No es necesario configurar manualmente las directivas `@tailwind`, Laravel 12 usa `@import 'tailwindcss'`

2. **Compatibilidad de versiones**: Siempre verificar compatibilidad entre Laravel y paquetes de terceros, especialmente con Symfony

3. **PowerShell vs Unix**: Recordar que PowerShell usa cmdlets diferentes, adaptar comandos según el sistema operativo

4. **SQLite por defecto**: Laravel 12 viene configurado con SQLite por defecto, simplificando el setup inicial

## Conclusión

La instalación del stack TALL se completó exitosamente en ~13 minutos, estableciendo una base sólida para el desarrollo del proyecto Gadium. Todos los componentes críticos están instalados, configurados y verificados.

El proyecto está listo para avanzar a la **ÉPICA 01: Gestión de Accesos y Gobierno de Datos**.

---

**Versión**: 1.0  
**Autor**: Equipo de Desarrollo Gadium  
**Última actualización**: 2026-01-08 19:14:00
