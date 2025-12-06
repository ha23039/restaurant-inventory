# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planificado
- Sprint 1: Refactoring de arquitectura base
- Sprint 2: Componentes Vue reutilizables
- Sprint 3: Dashboard de transacciones financieras
- Sprint 4: Módulo de gestión de gastos
- Sprint 5: Estadísticas y reportes avanzados
- Sprint 6: Módulo de clientes y cuentas por cobrar
- Sprint 7: Módulo de proveedores y cuentas por pagar
- Sprint 8-9: Sistema de gestión de mesas
- Sprint 10: Control de caja y empleados
- Sprint 11: Configuraciones y catálogo virtual
- Sprint 12: Optimización y documentación final

---

## [0.9.0] - 2025-11-18

### Added (Funcionalidades existentes)
- ✅ Sistema POS completo
- ✅ Gestión de inventario de productos
- ✅ Sistema de recetas (Bill of Materials)
- ✅ Productos simples y menú items
- ✅ Movimientos de inventario (Kardex)
- ✅ Sistema de devoluciones
- ✅ CashFlow básico (flujo de efectivo)
- ✅ Impresión térmica (tickets y comandas)
- ✅ Sistema de roles (admin, chef, almacenero, cajero)
- ✅ Autenticación con Laravel Breeze
- ✅ Soft deletes en modelos principales
- ✅ Dashboard básico

### Technical
- Laravel 12.0
- Vue 3.5
- Inertia.js 2.0
- Tailwind CSS 3.2
- Chart.js 4.4
- PHP 8.2+
- SQLite (dev) / MySQL (prod)

---

## Formato de Cambios

### Added (Agregado)
Para nuevas funcionalidades

### Changed (Cambiado)
Para cambios en funcionalidades existentes

### Deprecated (Obsoleto)
Para funcionalidades que pronto serán removidas

### Removed (Removido)
Para funcionalidades removidas

### Fixed (Corregido)
Para corrección de bugs

### Security (Seguridad)
Para mejoras de seguridad

---

## Ejemplo de Entrada (Para futuros sprints)

```markdown
## [1.0.0] - 2025-XX-XX

### Added
- Sistema de gestión de mesas con grid visual
- Control de apertura y cierre de caja
- Módulo de clientes con cuentas por cobrar
- Dashboard de transacciones financieras con gráficos
- Exportación de reportes a PDF y Excel

### Changed
- Refactorizado POSController usando SaleService
- Mejorada arquitectura con Repository Pattern
- Optimizadas queries de base de datos con índices

### Fixed
- Corregido cálculo de stock disponible en menu items
- Solucionado problema de deducción de inventario en devoluciones

### Security
- Agregada validación de autorización en todos los endpoints
- Implementada protección contra mass assignment
```

---

**Nota:** Este changelog será actualizado al finalizar cada sprint.
