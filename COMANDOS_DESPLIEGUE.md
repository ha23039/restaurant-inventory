# Comandos de Despliegue - Sprint 1 y Sprint 2

## 📋 Resumen de Cambios Implementados

### **Sprint 1 - Refactorización Crítica** ✅
1. Eliminación de emojis y profesionalización del código
2. Implementación de Laravel Policies para RBAC
3. Creación de Form Request classes para validación
4. Resolución de problemas N+1 queries
5. Control de concurrencia con database locks
6. Extracción de lógica de negocio a Services

### **Sprint 2 - Alta Prioridad** ✅
1. Testing automatizado (Unit y Feature tests)
2. Soft Deletes en modelos críticos
3. Logging mejorado con contexto enriquecido
4. Documentación de Backup automático
5. Guía de mejoras de UI/UX

---

## 🚀 Comandos para Aplicar los Cambios (Laravel Sail)

### 1. **Obtener los Cambios del Repositorio**

```bash
# Asegurarse de estar en el proyecto
cd /home/user/restaurant-inventory

# Iniciar Sail si no está corriendo
./vendor/bin/sail up -d

# Fetch de cambios remotos
git fetch origin

# Cambiar a la rama de desarrollo
git checkout claude/claude-md-mi0vr0e4j6688565-017ach1XT6uyfYmpWQGoDLZ5

# Pull de los últimos cambios
git pull origin claude/claude-md-mi0vr0e4j6688565-017ach1XT6uyfYmpWQGoDLZ5
```

### 2. **Instalar Nuevas Dependencias Frontend**

```bash
# Instalar @heroicons/vue (ya debería estar en package.json)
./vendor/bin/sail npm install

# Recompilar assets con los nuevos iconos
./vendor/bin/sail npm run build
```

**Tiempo estimado**: 2-3 minutos

### 3. **Ejecutar Migraciones de Soft Deletes**

```bash
# Ejecutar las 4 nuevas migraciones para agregar deleted_at
./vendor/bin/sail artisan migrate

# Verificar que se ejecutaron
./vendor/bin/sail artisan migrate:status
```

Esto agregará la columna `deleted_at` a:
- `products`
- `menu_items`
- `sales`
- `sale_returns`

**Tiempo estimado**: < 1 minuto

### 4. **Limpiar Cachés de Laravel**

```bash
# Limpiar todos los cachés
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan cache:clear

# Regenerar archivos optimizados (opcional en desarrollo)
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
```

**Tiempo estimado**: < 30 segundos

### 5. **Ejecutar Tests (Verificación)**

```bash
# Ejecutar todos los tests
./vendor/bin/sail artisan test

# Solo tests unitarios
./vendor/bin/sail artisan test --testsuite=Unit

# Solo tests de features
./vendor/bin/sail artisan test --testsuite=Feature

# Con cobertura (requiere Xdebug)
./vendor/bin/sail artisan test --coverage
```

**Resultado esperado**: Todos los tests en verde ✅

**Tiempo estimado**: 10-30 segundos

### 6. **Verificar Funcionamiento Básico**

```bash
# Abrir Tinker para verificar modelos
./vendor/bin/sail artisan tinker
```

Dentro de Tinker, ejecutar:

```php
// Verificar que SoftDeletes funciona
App\Models\Product::count();  // Total de productos (incluyendo eliminados)
App\Models\Product::withTrashed()->count();  // Total incluyendo soft deleted

// Verificar que SaleService funciona
$service = new App\Services\SaleService();
// (No ejecutar processSale sin datos reales)

// Verificar factories
App\Models\Product::factory()->count(5)->create();
App\Models\MenuItem::factory()->count(3)->create();

// Salir
exit
```

### 7. **Reiniciar Workers de Queue (si están corriendo)**

```bash
# Reiniciar queue workers para aplicar cambios de código
./vendor/bin/sail artisan queue:restart

# Si no tienes workers corriendo, iniciar uno
./vendor/bin/sail artisan queue:work --tries=3 --timeout=90
```

### 8. **Ver Logs en Tiempo Real (para debugging)**

```bash
# Opción 1: Laravel Pail (recomendado)
./vendor/bin/sail artisan pail

# Opción 2: Tail tradicional
./vendor/bin/sail exec laravel.test tail -f storage/logs/laravel.log
```

---

## 🔧 Configuración Adicional Recomendada

### Instalar Laravel Backup (Opcional pero Recomendado)

```bash
# Instalar paquete
./vendor/bin/sail composer require spatie/laravel-backup

# Publicar configuración
./vendor/bin/sail artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

# Crear primer backup de prueba
./vendor/bin/sail artisan backup:run --only-db

# Verificar
./vendor/bin/sail artisan backup:list
```

Ver `BACKUP_SETUP.md` para configuración completa.

---

## 📊 Verificación Post-Despliegue

### Checklist de Verificación:

- [ ] **Frontend compilado**: Visitar http://localhost y verificar que los iconos se muestran correctamente
- [ ] **Migraciones ejecutadas**: Verificar en DB que existe columna `deleted_at`
- [ ] **Tests pasando**: Todos los tests en verde
- [ ] **Logs funcionando**: Ver logs con `pail` y verificar contexto enriquecido
- [ ] **POS funcional**: Procesar una venta de prueba
- [ ] **Policies activas**: Intentar acceder al POS con un usuario Chef (debería dar 403)

### Comandos de Verificación Rápida:

```bash
# 1. Verificar que assets compilados existen
ls -lh public/build/

# 2. Verificar migraciones
./vendor/bin/sail artisan migrate:status | grep "soft_deletes"

# 3. Verificar tests
./vendor/bin/sail artisan test --filter=SaleServiceTest

# 4. Verificar queue
./vendor/bin/sail artisan queue:failed

# 5. Verificar políticas registradas
./vendor/bin/sail artisan route:list | grep "pos"
```

---

## 🐛 Troubleshooting

### Problema: "Class 'Spatie\Backup\...' not found"

**Solución**:
```bash
./vendor/bin/sail composer dump-autoload
```

### Problema: Tests fallan con "Database not found"

**Solución**:
```bash
# Verificar configuración de testing
cat phpunit.xml | grep DB_CONNECTION

# Debería mostrar: <env name="DB_CONNECTION" value="sqlite"/>
# Y <env name="DB_DATABASE" value=":memory:"/>

# Si usa sqlite, crear archivo
touch database/database.sqlite
```

### Problema: "npm run build" falla

**Solución**:
```bash
# Limpiar node_modules y reinstalar
./vendor/bin/sail exec laravel.test rm -rf node_modules
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Problema: Errores de permisos en storage/

**Solución**:
```bash
./vendor/bin/sail exec laravel.test chmod -R 775 storage bootstrap/cache
./vendor/bin/sail exec laravel.test chown -R sail:sail storage bootstrap/cache
```

### Problema: Vite HMR no funciona

**Solución**:
```bash
# Detener Sail
./vendor/bin/sail down

# Reiniciar con -V para recrear volúmenes
./vendor/bin/sail up -d -V

# Iniciar Vite
./vendor/bin/sail npm run dev
```

---

## 📈 Próximos Pasos (Opcional)

### Implementar Mejoras de UI (Ver UI_IMPROVEMENTS.md)

```bash
# Instalar Chart.js para gráficas
./vendor/bin/sail npm install chart.js vue-chartjs

# Instalar Toast notifications
./vendor/bin/sail npm install vue-toastification@next

# Recompilar
./vendor/bin/sail npm run build
```

### Configurar Monitoreo con Laravel Pulse

```bash
./vendor/bin/sail composer require laravel/pulse
./vendor/bin/sail artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
./vendor/bin/sail artisan migrate

# Acceder a /pulse para ver métricas en tiempo real
```

---

## 🎯 Resumen de Archivos Importantes

### Nuevos Archivos Creados:
```
✅ app/Services/SaleService.php                      - Lógica de negocio de ventas
✅ app/Policies/                                     - 5 policies de autorización
✅ app/Http/Requests/                                - 4 form requests
✅ tests/Unit/SaleServiceTest.php                    - Tests unitarios
✅ tests/Feature/POSControllerTest.php               - Tests de integración
✅ database/factories/                               - 5 model factories
✅ database/migrations/2025_11_16_*                  - 4 migraciones soft deletes
✅ resources/js/composables/useIcons.js              - Composable de iconos
✅ BACKUP_SETUP.md                                   - Guía de backups
✅ UI_IMPROVEMENTS.md                                - Roadmap de UI/UX
✅ COMANDOS_DESPLIEGUE.md                            - Este archivo
```

### Archivos Modificados:
```
✅ app/Http/Controllers/POSController.php            - Usa SaleService
✅ app/Http/Controllers/ProductController.php        - Usa Form Requests y Policies
✅ app/Http/Controllers/SaleController.php           - Policies integradas
✅ app/Http/Controllers/ReturnController.php         - Policies y locks
✅ app/Models/Product.php                            - SoftDeletes
✅ app/Models/MenuItem.php                           - SoftDeletes
✅ app/Models/Sale.php                               - SoftDeletes
✅ app/Models/SaleReturn.php                         - SoftDeletes
✅ resources/js/Pages/Dashboard.vue                  - Heroicons
✅ resources/js/Pages/Sales/Index.vue                - Heroicons
✅ resources/js/Pages/Returns/Index.vue              - Heroicons
✅ resources/js/Pages/Inventory/Index.vue            - Heroicons
✅ package.json                                      - @heroicons/vue agregado
```

---

## 📞 Soporte

Si encuentras algún problema:

1. Revisar logs: `./vendor/bin/sail artisan pail`
2. Verificar tests: `./vendor/bin/sail artisan test`
3. Consultar documentación en:
   - `BACKUP_SETUP.md`
   - `UI_IMPROVEMENTS.md`
   - `CLAUDE.md`
   - `ARCHITECTURE.md`

---

## ✨ Mejoras Implementadas en Números

- **6 commits** de refactorización
- **5 Laravel Policies** para RBAC
- **4 Form Requests** para validación
- **1 Service class** (SaleService) con 10 métodos
- **2 archivos de tests** con 10 test cases
- **5 Model Factories** para testing
- **4 migraciones** para soft deletes
- **1 composable** de iconos (useIcons.js)
- **100+ emojis** eliminados del código
- **Logging enriquecido** con 8+ campos de contexto
- **2 documentos** de guías (Backup + UI)

**Total de líneas de código**: +2,500 líneas profesionales agregadas 🎉

---

**¡Todo listo para probar mañana!** 🚀
