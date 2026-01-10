# Epica 2: Motor de Ingesta y Normalización de Datos - Task Checklist

## Estado Actual
- **Rama**: feature/epica-2-motor-ingesta
- **Fecha de inicio**: (Por registrar en bitácora)
- **Estimación total**: 2-2.5 horas
- **Decisión**: Implementar las 3 User Stories completas

## HU-02: Gestor Dinámico de Roles y Permisos ✅
- [x] RoleController implementado
- [x] Vistas de roles creadas (index, create, edit, permissions)
- [x] Rutas configuradas en web.php
- [x] Protección de Super Admin implementada
- [x] Testing completado
  - [x] RoleManagementTest (5 tests, 10 assertions) ✅ PASSING
  - [x] AccessControlTest (5 tests, 20 assertions) ✅ PASSING

## HU-03: Asistente de Importación de Ventas (Estimado: 45-60 min)
- [ ] Crear migraciones
  - [ ] create_sales_table
  - [ ] create_clients_table
- [ ] Crear modelos
  - [ ] Sale.php con hash generation
  - [ ] Client.php con relaciones
- [ ] Instalar Laravel Excel
- [ ] Crear SalesImport class
  - [ ] Validación de cabeceras
  - [ ] Validación de tipos
  - [ ] Sistema de hash para duplicados
  - [ ] Procesamiento por chunks
- [ ] Crear SalesImportController
  - [ ] index() - Wizard
  - [ ] upload() - Validar CSV
  - [ ] preview() - Preview
  - [ ] process() - Procesar
- [ ] Crear vistas del wizard
  - [ ] index.blade.php
  - [ ] preview.blade.php
  - [ ] result.blade.php
- [ ] Agregar rutas
- [ ] Testing
  - [ ] SalesImportTest (4 tests mínimo)

## HU-04: Normalización Inteligente de Clientes (Estimado: 30-40 min)
- [ ] Crear migración client_aliases_table
- [ ] Crear modelo ClientAlias
- [ ] Crear ClientNormalizationService
  - [ ] Algoritmo Levenshtein
  - [ ] findSimilarClients()
  - [ ] createAlias()
  - [ ] resolveClientByAlias()
- [ ] Crear ClientResolutionController
- [ ] Crear vista de resolución
- [ ] Integrar con SalesImport
- [ ] Testing
  - [ ] ClientNormalizationTest (4 tests mínimo)

## Seeders y Datos de Prueba
- [ ] Crear Epica2Seeder
  - [ ] 10 clientes de ejemplo
  - [ ] 50 ventas de ejemplo
  - [ ] 5 aliases de ejemplo
- [ ] Actualizar DatabaseSeeder

## Documentación (Estimado: 20 min)
- [ ] Crear bitácora_epica_2.md
  - [ ] Registrar inicio (fecha/hora)
  - [ ] Documentar progreso
  - [ ] Documentar errores
  - [ ] Registrar fin (fecha/hora)
- [ ] Crear resultados_testing.md
  - [ ] Tests de HU-02 (10 tests)
  - [ ] Tests de HU-03 (4+ tests)
  - [ ] Tests de HU-04 (4+ tests)
- [ ] Crear auditoria_epica_2.md
  - [ ] Estado de implementación
  - [ ] Cumplimiento de criterios
  - [ ] Puntos a mejorar

## Finalización
- [ ] Ejecutar todos los tests
- [ ] Arreglar issues de auditoría
- [ ] Commit final
- [ ] Esperar instrucciones para merge

