# ⚡ GUÍA RÁPIDA DE SPRINTS

Esta es una referencia rápida de todos los sprints planificados para el desarrollo del sistema.

---

## 📅 CRONOGRAMA GENERAL

| Sprint | Fechas | Rama | Objetivo Principal | Prioridad |
|--------|--------|------|-------------------|-----------|
| 1 | Sem 1-2 | `sprint-01/refactoring-architecture` | Refactoring Base | ⚠️ CRÍTICA |
| 2 | Sem 3-4 | `sprint-02/vue-components-testing` | Componentes Vue | ⚠️ CRÍTICA |
| 3 | Sem 5-6 | `sprint-03/financial-transactions-dashboard` | Dashboard Financiero | 🔥 ALTA |
| 4 | Sem 7-8 | `sprint-04/expense-management` | Gestión de Gastos | 🔥 ALTA |
| 5 | Sem 9-10 | `sprint-05/statistics-reports` | Estadísticas | 📊 MEDIA |
| 6 | Sem 11-12 | `sprint-06/customers-receivables` | Clientes | 🔥 ALTA |
| 7 | Sem 13-14 | `sprint-07/suppliers-payables` | Proveedores | 📊 MEDIA |
| 8 | Sem 15-16 | `sprint-08/table-management` | Mesas (Parte 1) | 🔥 ALTA |
| 9 | Sem 17-18 | `sprint-09/table-management-integration` | Mesas (Parte 2) | 🔥 ALTA |
| 10 | Sem 19-20 | `sprint-10/cash-register-employees` | Caja + Empleados | ⚠️ CRÍTICA |
| 11 | Sem 21-22 | `sprint-11/settings-catalog` | Configuraciones | 📊 MEDIA |
| 12 | Sem 23-24 | `sprint-12/optimization-documentation` | Optimización | 🔥 ALTA |

**DURACIÓN TOTAL:** 24 semanas (6 meses)

---

## 🎯 FASE 1: REFACTORING Y BASE (Sprints 1-2)

### Sprint 1: Refactoring de Arquitectura
**🎯 Objetivo:** Implementar Repository Pattern, Services, Form Requests

**Entregables clave:**
- ✅ 5 Repositorios (Product, Sale, CashFlow, MenuItem, SimpleProduct)
- ✅ 5 Servicios (Sale, Inventory, CashFlow, MenuItem, ThermalTicket)
- ✅ 8+ Form Requests
- ✅ 4+ API Resources
- ✅ Índices de BD optimizados

**Testing:** 70%+ coverage

**Documentación:** → [SPRINT_01_REFACTORING.md](./sprints/SPRINT_01_REFACTORING.md)

---

### Sprint 2: Componentes Vue + Testing
**🎯 Objetivo:** Crear librería de componentes reutilizables

**Entregables clave:**
- ✅ 15+ componentes Vue reutilizables
- ✅ Pinia configurado (4 stores)
- ✅ 7 composables
- ✅ Sistema de notificaciones mejorado
- ✅ Vitest configurado

**Testing:** 80%+ coverage en componentes

**Documentación:** → [SPRINT_02_VUE_COMPONENTS.md](./sprints/SPRINT_02_VUE_COMPONENTS.md)

---

## 💰 FASE 2: MÓDULOS FINANCIEROS (Sprints 3-5)

### Sprint 3: Dashboard de Transacciones Financieras
**🎯 Objetivo:** Módulo completo de movimientos financieros

**Entregables clave:**
- ✅ Vista de dashboard con filtros
- ✅ Gráficos de tendencias (Chart.js)
- ✅ Exportación PDF/Excel
- ✅ API de estadísticas financieras

**Nuevos modelos:** Ninguno (usa CashFlow existente)

---

### Sprint 4: Gestión de Gastos
**🎯 Objetivo:** Sistema completo de registro y control de gastos

**Entregables clave:**
- ✅ CRUD de gastos
- ✅ Tabla ExpenseCategory
- ✅ Estados: pagado/en deuda
- ✅ Asociación con proveedores

**Nuevos modelos:** `Expense`, `ExpenseCategory`

---

### Sprint 5: Estadísticas y Reportes
**🎯 Objetivo:** Analytics avanzados y reportes exportables

**Entregables clave:**
- ✅ Gráficos de ventas por período
- ✅ Top productos vendidos
- ✅ Comparativas de períodos
- ✅ Rendimiento por empleado
- ✅ Reportes personalizados

**Nuevos modelos:** Ninguno (queries avanzadas)

---

## 👥 FASE 3: CRM Y RELACIONES (Sprints 6-7)

### Sprint 6: Módulo de Clientes
**🎯 Objetivo:** CRM de clientes con cuentas por cobrar

**Entregables clave:**
- ✅ CRUD de clientes
- ✅ Sistema de cuentas por cobrar
- ✅ Historial de compras
- ✅ Registro de pagos parciales

**Nuevos modelos:** `Customer`, `CustomerPayment`

**Modificaciones:** Agregar `customer_id` a tabla `sales`

---

### Sprint 7: Módulo de Proveedores
**🎯 Objetivo:** Gestión completa de proveedores

**Entregables clave:**
- ✅ CRUD de proveedores (UI completa)
- ✅ Sistema de cuentas por pagar
- ✅ Asociar gastos a proveedores
- ✅ Historial de compras

**Nuevos modelos:** `SupplierPayment`, `Purchase`

**Nota:** Modelo `Supplier` ya existe

---

## 🍽️ FASE 4: OPERACIONES RESTAURANTE (Sprints 8-10)

### Sprint 8: Sistema de Mesas (Parte 1)
**🎯 Objetivo:** Estructura base del sistema de mesas

**Entregables clave:**
- ✅ Modelos: Table, Order, OrderItem
- ✅ Vista de grid de mesas
- ✅ Estados: disponible/ocupada/procesando
- ✅ Abrir/cerrar mesa

**Nuevos modelos:** `Table`, `Order`, `OrderItem`, `TableOrder`

**CRÍTICO:** Diseño de arquitectura Order vs Sale

---

### Sprint 9: Mesas (Parte 2) - Integración
**🎯 Objetivo:** Integración completa con POS

**Entregables clave:**
- ✅ Agregar productos a mesa
- ✅ Dividir cuenta
- ✅ Transferir items entre mesas
- ✅ Cerrar cuenta y generar venta
- ✅ Integración con inventario

**Modificaciones:** Refactorizar POS para soportar ambos flujos

---

### Sprint 10: Control de Caja + Empleados
**🎯 Objetivo:** Apertura/cierre de caja y gestión de personal

**Entregables clave:**
- ✅ Sistema de apertura/cierre de caja
- ✅ Arqueo de efectivo
- ✅ Asignación de cajeros
- ✅ Gestión de empleados extendida
- ✅ Reportes de turno

**Nuevos modelos:** `CashRegister`, `CashRegisterSession`

**Modificaciones:** Extender modelo `User` o crear `Employee`

---

## ⚙️ FASE 5: MEJORAS Y PULIDO (Sprints 11-12)

### Sprint 11: Configuraciones + Catálogo
**🎯 Objetivo:** Módulo de configuraciones y menú público

**Entregables clave:**
- ✅ CRUD de configuraciones del negocio
- ✅ Upload de logo
- ✅ Configuración de impuestos y propinas
- ✅ Catálogo virtual público
- ✅ QR code para menú

**Nuevos modelos:** `BusinessSetting`

---

### Sprint 12: Optimización Final
**🎯 Objetivo:** Optimización, testing y documentación completa

**Entregables clave:**
- ✅ Optimización de performance
- ✅ Testing completo (>80% coverage)
- ✅ Documentación de API (Swagger)
- ✅ Manual de usuario
- ✅ Guía de deployment

**Actividades:**
- Refactoring final
- Corrección de bugs
- Mejoras de UX
- Preparación para producción

---

## 🚀 CÓMO EMPEZAR

### 1. Preparación Inicial
```bash
# Crear rama develop
git checkout -b develop

# Instalar herramientas
composer require --dev phpstan/phpstan larastan/larastan
npm install -D vitest @vue/test-utils jsdom

# Backup de BD
php artisan db:seed
```

### 2. Iniciar Sprint 1
```bash
# Crear rama del sprint
git checkout -b sprint-01/refactoring-architecture

# Leer documentación
cat docs/sprints/SPRINT_01_REFACTORING.md

# Empezar a trabajar!
```

### 3. Workflow Diario
```bash
# Commits frecuentes
git add .
git commit -m "feat: Implementar ProductRepository"

# Push a rama del sprint
git push origin sprint-01/refactoring-architecture

# Ejecutar tests
php artisan test
npm run test
```

### 4. Finalizar Sprint
```bash
# Verificar DoD
php artisan pint
php artisan test --coverage

# Crear Pull Request usando el template
# Esperar code review
# Merge a develop
```

---

## 📚 RECURSOS IMPORTANTES

### Documentación del Proyecto
- 📖 [CLAUDE.md](../CLAUDE.md) - Guía completa del proyecto
- 📋 [WORKFLOW.md](../WORKFLOW.md) - Funcionalidades a implementar
- 🚀 [SPRINT_PLANNING.md](../SPRINT_PLANNING.md) - Plan maestro de sprints

### Documentación por Sprint
- 🏗️ [Sprint 1 - Refactoring](./sprints/SPRINT_01_REFACTORING.md)
- 🎨 [Sprint 2 - Vue Components](./sprints/SPRINT_02_VUE_COMPONENTS.md)
- 💰 Sprint 3 - Financial Dashboard (pendiente)
- 💵 Sprint 4 - Expense Management (pendiente)
- 📊 Sprint 5 - Statistics (pendiente)
- 👥 Sprint 6 - Customers (pendiente)
- 🏭 Sprint 7 - Suppliers (pendiente)
- 🍽️ Sprint 8 - Tables Part 1 (pendiente)
- 🍽️ Sprint 9 - Tables Part 2 (pendiente)
- 💵 Sprint 10 - Cash Register (pendiente)
- ⚙️ Sprint 11 - Settings (pendiente)
- 🚀 Sprint 12 - Optimization (pendiente)

### Templates
- 📝 [Pull Request Template](../.github/PULL_REQUEST_TEMPLATE.md)

---

## ✅ CHECKLIST GENERAL

### Antes de Empezar
- [ ] Leer CLAUDE.md completo
- [ ] Leer WORKFLOW.md
- [ ] Leer SPRINT_PLANNING.md
- [ ] Configurar entorno de desarrollo
- [ ] Crear rama develop
- [ ] Backup de base de datos

### Durante Cada Sprint
- [ ] Leer documentación del sprint
- [ ] Crear rama del sprint
- [ ] Completar todas las tareas
- [ ] Escribir tests (70%+ coverage)
- [ ] Ejecutar Laravel Pint
- [ ] Ejecutar PHPStan
- [ ] Actualizar CHANGELOG.md
- [ ] Crear Pull Request
- [ ] Code review
- [ ] Merge a develop

### Después de Cada Sprint
- [ ] Sprint Review
- [ ] Sprint Retrospective
- [ ] Actualizar backlog
- [ ] Planear siguiente sprint

---

## 🎯 MÉTRICAS DE ÉXITO

### Por Sprint
- ✅ Velocity: Story points completados
- ✅ Bug count: < 5 bugs por sprint
- ✅ Code coverage: > 70%
- ✅ Performance: < 200ms en endpoints críticos

### Global
- ✅ Funcionalidades: 11/11 módulos completados
- ✅ Coverage total: > 80%
- ✅ Deuda técnica: Bajo control
- ✅ Tiempo: 24 semanas

---

## 🚨 PUNTOS CRÍTICOS

### ⚠️ Sprint 1 (Refactoring)
**Importancia:** Este sprint es LA BASE de todo. No apresurarse.
**Riesgo:** Si se hace mal, afectará todos los demás sprints.
**Tip:** Tomar el tiempo necesario para hacerlo bien.

### ⚠️ Sprint 8-9 (Mesas)
**Importancia:** Cambia arquitectura fundamental del POS.
**Riesgo:** Conflicto entre venta directa y sistema de mesas.
**Tip:** Diseñar bien la separación Order vs Sale.

### ⚠️ Sprint 10 (Caja)
**Importancia:** Crítico para integridad financiera.
**Riesgo:** Errores pueden causar pérdida de dinero.
**Tip:** Testing exhaustivo. Validación estricta.

---

## 💪 MOTIVACIÓN

> "El código bien escrito es su mejor documentación" - Steve McConnell

Estás construyendo algo increíble. Cada sprint te acerca más a tener un sistema completo, profesional y robusto.

**Recuerda:**
- 🎯 Focus en calidad, no en velocidad
- 🧪 Tests son tus amigos
- 📝 Documenta mientras programas
- 🤝 Pide ayuda cuando la necesites
- 🎉 Celebra cada sprint completado

---

## 🔗 LINKS ÚTILES

- [Laravel Docs](https://laravel.com/docs)
- [Vue 3 Docs](https://vuejs.org)
- [Inertia.js Docs](https://inertiajs.com)
- [Tailwind CSS Docs](https://tailwindcss.com)
- [Pinia Docs](https://pinia.vuejs.org)
- [Vitest Docs](https://vitest.dev)

---

## 📞 SOPORTE

¿Tienes dudas? ¿Necesitas ayuda?

1. Revisa la documentación del sprint
2. Consulta CLAUDE.md
3. Revisa ejemplos de código
4. Pregunta en el equipo

---

**¡Éxito en tu desarrollo! 🚀**

**Última actualización:** 2025-11-18
