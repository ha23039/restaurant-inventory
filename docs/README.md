# 📚 DOCUMENTACIÓN DEL PROYECTO

Bienvenido a la documentación completa del Sistema de Inventario y POS para Restaurantes.

---

## 🗂️ ÍNDICE DE DOCUMENTACIÓN

### 📖 Documentación Principal

| Documento | Descripción | Para quién |
|-----------|-------------|------------|
| [CLAUDE.md](../CLAUDE.md) | Guía completa del proyecto existente | Desarrolladores nuevos |
| [WORKFLOW.md](../WORKFLOW.md) | Funcionalidades a implementar | Product Owner, Desarrolladores |
| [SPRINT_PLANNING.md](../SPRINT_PLANNING.md) | Plan maestro de todos los sprints | Todos |
| [CHANGELOG.md](../CHANGELOG.md) | Registro de cambios del proyecto | Todos |

### ⚡ Guías Rápidas

| Documento | Descripción | Tiempo de lectura |
|-----------|-------------|-------------------|
| [QUICK_START_SPRINTS.md](./QUICK_START_SPRINTS.md) | Referencia rápida de todos los sprints | 10 min |
| [GIT_WORKFLOW.md](./GIT_WORKFLOW.md) | Comandos Git y flujo de trabajo | 15 min |

### 🏃 Documentación de Sprints

| Sprint | Documento | Estado | Duración |
|--------|-----------|--------|----------|
| Sprint 1 | [Refactoring de Arquitectura](./sprints/SPRINT_01_REFACTORING.md) | 📝 Planificado | 2 semanas |
| Sprint 2 | [Componentes Vue + Testing](./sprints/SPRINT_02_VUE_COMPONENTS.md) | 📝 Planificado | 2 semanas |
| Sprint 3 | Dashboard Financiero | 📝 Planificado | 2 semanas |
| Sprint 4 | Gestión de Gastos | 📝 Planificado | 2 semanas |
| Sprint 5 | Estadísticas y Reportes | 📝 Planificado | 2 semanas |
| Sprint 6 | Clientes y Cuentas por Cobrar | 📝 Planificado | 2 semanas |
| Sprint 7 | Proveedores y Cuentas por Pagar | 📝 Planificado | 2 semanas |
| Sprint 8 | Sistema de Mesas (Parte 1) | 📝 Planificado | 2 semanas |
| Sprint 9 | Sistema de Mesas (Parte 2) | 📝 Planificado | 2 semanas |
| Sprint 10 | Control de Caja + Empleados | 📝 Planificado | 2 semanas |
| Sprint 11 | Configuraciones + Catálogo | 📝 Planificado | 2 semanas |
| Sprint 12 | Optimización Final | 📝 Planificado | 2 semanas |

### 🔧 Templates

| Template | Descripción | Uso |
|----------|-------------|-----|
| [Pull Request Template](../.github/PULL_REQUEST_TEMPLATE.md) | Template para PRs | Automático en GitHub |

---

## 🚀 CÓMO EMPEZAR

### Para Desarrolladores Nuevos

1. **Leer primero:**
   - [CLAUDE.md](../CLAUDE.md) - Entender el proyecto existente (30 min)
   - [WORKFLOW.md](../WORKFLOW.md) - Ver qué se va a construir (20 min)

2. **Configurar entorno:**
   ```bash
   # Ver CLAUDE.md sección "Configuración de Desarrollo"
   composer install
   npm install
   php artisan migrate:fresh --seed
   ```

3. **Entender el plan:**
   - [SPRINT_PLANNING.md](../SPRINT_PLANNING.md) - Plan completo (15 min)
   - [QUICK_START_SPRINTS.md](./QUICK_START_SPRINTS.md) - Referencia rápida (10 min)

4. **Aprender Git workflow:**
   - [GIT_WORKFLOW.md](./GIT_WORKFLOW.md) - Comandos y flujo (15 min)

**Tiempo total:** ~2 horas de lectura antes de empezar a programar

---

### Para Continuar el Desarrollo

1. **Verificar sprint actual:**
   - Ver [SPRINT_PLANNING.md](../SPRINT_PLANNING.md)

2. **Leer documentación del sprint:**
   - Ver [sprints/SPRINT_XX_NOMBRE.md](./sprints/)

3. **Crear rama y empezar:**
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b sprint-XX/nombre-descriptivo
   ```

4. **Seguir Definition of Done:**
   - Cada sprint tiene su propio DoD

---

## 📊 ESTADO DEL PROYECTO

### Funcionalidades Implementadas ✅

- Sistema POS completo
- Gestión de inventario
- Sistema de recetas
- Devoluciones
- CashFlow básico
- Impresión térmica
- Sistema de roles

### En Desarrollo 🚧

- Refactoring de arquitectura (Sprint 1)
- Componentes Vue reutilizables (Sprint 2)

### Planificado 📝

- Dashboard financiero
- Gestión de gastos
- Sistema de mesas
- Control de caja
- Módulo de clientes
- Módulo de proveedores
- Estadísticas avanzadas
- Configuraciones

---

## 🎯 CRONOGRAMA RESUMIDO

| Fase | Sprints | Duración | Objetivo |
|------|---------|----------|----------|
| **Fase 1: Base** | 1-2 | 4 semanas | Refactoring + Componentes |
| **Fase 2: Finanzas** | 3-5 | 6 semanas | Dashboard + Gastos + Stats |
| **Fase 3: CRM** | 6-7 | 4 semanas | Clientes + Proveedores |
| **Fase 4: Operaciones** | 8-10 | 6 semanas | Mesas + Caja |
| **Fase 5: Pulido** | 11-12 | 4 semanas | Config + Optimización |

**Total:** 24 semanas (6 meses)

---

## 🔑 CONCEPTOS CLAVE

### Arquitectura del Proyecto

- **Backend:** Laravel 12 (Repository Pattern + Services)
- **Frontend:** Vue 3 + Inertia.js (Componentes reutilizables)
- **Base de Datos:** MySQL/SQLite (con índices optimizados)
- **Testing:** PHPUnit + Vitest (>70% coverage)

### Flujo de Trabajo

1. **Sprint Planning** → Leer documentación del sprint
2. **Development** → Crear rama, desarrollar, committear
3. **Testing** → Tests + Pint + PHPStan
4. **Pull Request** → Usar template, code review
5. **Merge** → A develop después de aprobación
6. **Sprint Review** → Demo y retrospectiva

### Módulos Principales

1. **Transacciones Financieras** (CashFlow)
2. **Gestión de Mesas** (Order/Sale)
3. **Inventario** (Products + Movements)
4. **Menú** (MenuItems + Recipes)
5. **Clientes** (CRM + Cuentas por cobrar)
6. **Proveedores** (Gestión + Cuentas por pagar)
7. **Empleados** (Roles + Permisos)
8. **Control de Caja** (Apertura/Cierre)
9. **Gastos** (Registro + Categorización)
10. **Estadísticas** (Analytics + Reportes)
11. **Configuraciones** (Settings + Catálogo)

---

## 🧪 TESTING

### Estrategia de Testing

```
testing/
├── Backend (PHPUnit)
│   ├── Unit Tests (Repositories, Services)
│   ├── Feature Tests (Endpoints, Flujos)
│   └── Integration Tests
│
└── Frontend (Vitest)
    ├── Component Tests
    ├── Store Tests (Pinia)
    └── Composables Tests
```

### Ejecutar Tests

```bash
# Backend
php artisan test
php artisan test --coverage --min=70

# Frontend
npm run test
npm run test:coverage

# Todos
composer test && npm run test
```

---

## 📈 MÉTRICAS Y CALIDAD

### Objetivos de Calidad

| Métrica | Objetivo | Actual |
|---------|----------|--------|
| Coverage Backend | >80% | ~5% |
| Coverage Frontend | >80% | 0% |
| PHPStan Level | 5 | - |
| Performance (API) | <200ms | Variable |
| Bug Density | <5/sprint | - |

### Herramientas de Calidad

- **Laravel Pint:** Estilo de código PSR-12
- **PHPStan:** Análisis estático PHP
- **Vitest:** Testing frontend
- **PHPUnit:** Testing backend

---

## 🚨 CONVENCIONES

### Nomenclatura

```php
// PHP
Models: PascalCase, singular (Product, MenuItem)
Controllers: PascalCase + Controller (ProductController)
Services: PascalCase + Service (SaleService)
Repositories: PascalCase + Repository (ProductRepository)

// JavaScript/Vue
Components: PascalCase (BaseButton.vue)
Composables: camelCase + use prefix (useForm.js)
Stores: camelCase (useCartStore)

// Base de Datos
Tables: snake_case, plural (menu_items, cash_flow)
Columns: snake_case (sale_number, created_at)
```

### Commits

```bash
Format: <type>: <description>

Types:
- feat: Nueva funcionalidad
- fix: Corrección de bug
- docs: Documentación
- test: Tests
- refactor: Refactorización
- chore: Mantenimiento
```

---

## 🤝 CONTRIBUIR

### Proceso de Contribución

1. Crear rama desde `develop`
2. Desarrollar siguiendo DoD del sprint
3. Escribir tests (>70% coverage)
4. Ejecutar Pint + PHPStan
5. Crear Pull Request usando template
6. Esperar code review
7. Hacer merge después de aprobación

### Code Review Checklist

- [ ] Código sigue convenciones
- [ ] Tests passing
- [ ] Coverage adecuado
- [ ] Documentación actualizada
- [ ] Sin warnings de Pint/PHPStan
- [ ] Performance aceptable

---

## 📞 SOPORTE Y CONTACTO

### Recursos

- **Documentación Laravel:** https://laravel.com/docs
- **Documentación Vue:** https://vuejs.org
- **Documentación Inertia:** https://inertiajs.com

### Issues y Bugs

- Crear issue en GitHub con template correspondiente
- Incluir steps to reproduce
- Agregar screenshots si aplica

---

## 📝 GLOSARIO

| Término | Definición |
|---------|-----------|
| **POS** | Point of Sale - Sistema de punto de venta |
| **Kardex** | Registro de movimientos de inventario |
| **Comanda** | Orden de cocina impresa |
| **Menu Item** | Platillo preparado con receta |
| **Simple Product** | Producto vendible individual |
| **BOM** | Bill of Materials - Lista de materiales (Receta) |
| **DoD** | Definition of Done - Criterios de completitud |
| **PR** | Pull Request - Solicitud de merge |
| **CI/CD** | Continuous Integration/Deployment |

---

## ✅ CHECKLIST INICIAL

Antes de empezar a desarrollar:

- [ ] Leer CLAUDE.md completo
- [ ] Leer WORKFLOW.md
- [ ] Leer SPRINT_PLANNING.md
- [ ] Configurar entorno local
- [ ] Ejecutar migraciones con seed
- [ ] Familiarizarse con código existente
- [ ] Entender Git workflow
- [ ] Configurar herramientas (Pint, PHPStan)

---

## 🎉 ¡ÉXITO!

Estás listo para empezar a desarrollar. Recuerda:

- 📖 **Leer la documentación** antes de empezar
- 🧪 **Escribir tests** para tu código
- 💬 **Comunicar** cuando tengas dudas
- 🎯 **Seguir el DoD** de cada sprint
- 🚀 **Disfrutar** el proceso de desarrollo

---

**Última actualización:** 2025-11-18
**Versión de documentación:** 1.0
