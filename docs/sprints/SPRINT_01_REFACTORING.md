# 🏗️ SPRINT 1: REFACTORING DE ARQUITECTURA BASE

**Duración:** 2 semanas (Semana 1-2)
**Rama:** `sprint-01/refactoring-architecture`
**Prioridad:** CRÍTICA ⚠️
**Story Points:** 21 puntos

---

## 🎯 OBJETIVOS DEL SPRINT

1. Implementar Repository Pattern para modelos principales
2. Crear Service Layer para lógica de negocio compleja
3. Implementar Form Requests para validación
4. Crear API Resources para respuestas consistentes
5. Agregar índices de base de datos críticos
6. Configurar herramientas de calidad de código

---

## 📋 TAREAS DETALLADAS

### **1. Setup de Herramientas de Desarrollo** (2 puntos)

#### Subtareas:
- [ ] Instalar y configurar PHPStan
  ```bash
  composer require --dev phpstan/phpstan
  composer require --dev larastan/larastan
  ```
- [ ] Crear archivo de configuración `phpstan.neon`
- [ ] Instalar Laravel Pint (ya instalado, configurar)
- [ ] Configurar pre-commit hooks con Husky
- [ ] Crear archivo `.editorconfig`

#### Archivos a crear:
```
phpstan.neon
.editorconfig
pint.json
```

#### Entregable:
- Comando `composer phpstan` funcionando
- Comando `composer pint` ejecutándose automáticamente

---

### **2. Implementar Repository Pattern** (5 puntos)

#### Subtareas:
- [ ] Crear estructura de carpetas:
  ```
  app/
  ├── Repositories/
  │   ├── Contracts/
  │   │   ├── ProductRepositoryInterface.php
  │   │   ├── SaleRepositoryInterface.php
  │   │   ├── CashFlowRepositoryInterface.php
  │   │   ├── MenuItemRepositoryInterface.php
  │   │   └── SimpleProductRepositoryInterface.php
  │   └── Eloquent/
  │       ├── ProductRepository.php
  │       ├── SaleRepository.php
  │       ├── CashFlowRepository.php
  │       ├── MenuItemRepository.php
  │       └── SimpleProductRepository.php
  ```

- [ ] Crear BaseRepository con métodos comunes
- [ ] Implementar ProductRepository
- [ ] Implementar SaleRepository
- [ ] Implementar CashFlowRepository
- [ ] Implementar MenuItemRepository
- [ ] Implementar SimpleProductRepository
- [ ] Registrar bindings en AppServiceProvider

#### Código ejemplo - ProductRepositoryInterface:
```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Product;
    public function create(array $data): Product;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function getLowStockProducts(): Collection;
    public function getExpiringSoonProducts(int $days = 7): Collection;
    public function getByCategory(int $categoryId): Collection;
    public function search(string $query): Collection;
}
```

#### Entregable:
- 5 repositorios completos y funcionales
- Tests unitarios para cada repositorio
- Documentación inline completa

---

### **3. Crear Service Layer** (5 puntos)

#### Subtareas:
- [ ] Crear estructura:
  ```
  app/
  ├── Services/
  │   ├── SaleService.php
  │   ├── InventoryService.php
  │   ├── CashFlowService.php
  │   ├── MenuItemService.php
  │   └── ThermalTicketService.php (ya existe, refactorizar)
  ```

- [ ] Crear SaleService (extraer lógica de POSController)
- [ ] Crear InventoryService (deducción automática)
- [ ] Crear CashFlowService (registros financieros)
- [ ] Refactorizar ThermalTicketService
- [ ] Crear MenuItemService (disponibilidad de stock)

#### Código ejemplo - SaleService:
```php
<?php

namespace App\Services;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\InventoryService;
use App\Services\CashFlowService;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private InventoryService $inventoryService,
        private CashFlowService $cashFlowService
    ) {}

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            // Crear venta
            $sale = $this->saleRepository->create($data);

            // Deducir inventario
            $this->inventoryService->deductFromSale($sale);

            // Registrar flujo de efectivo
            $this->cashFlowService->recordSale($sale);

            return $sale;
        });
    }

    public function generateSaleNumber(): string
    {
        $date = now()->format('Ymd');
        $lastSale = $this->saleRepository->getLastSaleOfDay($date);
        $sequence = $lastSale ? intval(substr($lastSale->sale_number, -4)) + 1 : 1;

        return $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
```

#### Entregable:
- 5 servicios completos y testeados
- Lógica de negocio extraída de controladores
- Documentación de métodos públicos

---

### **4. Implementar Form Requests** (3 puntos)

#### Subtareas:
- [ ] Crear estructura:
  ```
  app/
  ├── Http/
  │   ├── Requests/
  │   │   ├── Product/
  │   │   │   ├── StoreProductRequest.php
  │   │   │   └── UpdateProductRequest.php
  │   │   ├── Sale/
  │   │   │   └── StoreSaleRequest.php
  │   │   ├── MenuItem/
  │   │   │   ├── StoreMenuItemRequest.php
  │   │   │   └── UpdateMenuItemRequest.php
  │   │   └── CashFlow/
  │   │       └── StoreCashFlowRequest.php
  ```

- [ ] Crear Form Requests para Product
- [ ] Crear Form Requests para Sale
- [ ] Crear Form Requests para MenuItem
- [ ] Crear Form Requests para CashFlow
- [ ] Actualizar controladores para usar Form Requests

#### Código ejemplo - StoreProductRequest:
```php
<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit_type' => ['required', 'in:kg,lt,pcs,g,ml'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'max_stock' => ['required', 'numeric', 'min:0', 'gte:min_stock'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'La categoría es obligatoria',
            'name.required' => 'El nombre del producto es obligatorio',
            'name.unique' => 'Ya existe un producto con este nombre',
            'max_stock.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo',
        ];
    }
}
```

#### Entregable:
- Form Requests para todas las operaciones críticas
- Validación centralizada
- Mensajes de error en español

---

### **5. Crear API Resources** (2 puntos)

#### Subtareas:
- [ ] Crear estructura:
  ```
  app/
  ├── Http/
  │   ├── Resources/
  │   │   ├── ProductResource.php
  │   │   ├── ProductCollection.php
  │   │   ├── SaleResource.php
  │   │   ├── SaleCollection.php
  │   │   ├── MenuItemResource.php
  │   │   └── CashFlowResource.php
  ```

- [ ] Crear ProductResource
- [ ] Crear SaleResource (con relaciones)
- [ ] Crear MenuItemResource
- [ ] Crear CashFlowResource
- [ ] Actualizar controladores para usar Resources

#### Código ejemplo - SaleResource:
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sale_number' => $this->sale_number,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'items' => SaleItemResource::collection($this->whenLoaded('saleItems')),
            'cash_flow' => new CashFlowResource($this->whenLoaded('cashFlow')),
        ];
    }
}
```

#### Entregable:
- API Resources para modelos principales
- Respuestas JSON consistentes
- Eager loading optimizado

---

### **6. Optimización de Base de Datos** (2 puntos)

#### Subtareas:
- [ ] Crear migración para índices:
  ```bash
  php artisan make:migration add_indexes_to_critical_tables
  ```

- [ ] Agregar índices en tabla `sales`
- [ ] Agregar índices en tabla `cash_flow`
- [ ] Agregar índices en tabla `products`
- [ ] Agregar índices en tabla `inventory_movements`
- [ ] Agregar índices compuestos donde sea necesario

#### Código - Migración de índices:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->index('sale_number');
            $table->index('created_at');
            $table->index('user_id');
            $table->index('status');
            $table->index(['status', 'created_at']);
        });

        Schema::table('cash_flow', function (Blueprint $table) {
            $table->index('flow_date');
            $table->index('category');
            $table->index('type');
            $table->index(['category', 'flow_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('is_active');
            $table->index('expiry_date');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('movement_type');
            $table->index('movement_date');
            $table->index(['product_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['sale_number']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('cash_flow', function (Blueprint $table) {
            $table->dropIndex(['flow_date']);
            $table->dropIndex(['category']);
            $table->dropIndex(['type']);
            $table->dropIndex(['category', 'flow_date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['expiry_date']);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['movement_type']);
            $table->dropIndex(['movement_date']);
            $table->dropIndex(['product_id', 'movement_date']);
        });
    }
};
```

#### Entregable:
- Migración de índices ejecutada
- Performance mejorado en queries críticas
- Documentación de índices creados

---

### **7. Refactorizar Controladores** (2 puntos)

#### Subtareas:
- [ ] Refactorizar ProductController (usar Repository + Service)
- [ ] Refactorizar POSController (usar SaleService)
- [ ] Refactorizar InventoryMovementController
- [ ] Actualizar todos los controladores para usar Form Requests
- [ ] Actualizar respuestas para usar API Resources

#### Antes vs Después:

**ANTES (POSController):**
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    DB::beginTransaction();

    $sale = Sale::create([...]);

    foreach ($items as $item) {
        // Lógica compleja aquí
    }

    // Deducir inventario
    // Crear cash flow
    // Imprimir ticket

    DB::commit();

    return redirect()->back();
}
```

**DESPUÉS:**
```php
public function store(StoreSaleRequest $request, SaleService $saleService)
{
    $sale = $saleService->createSale($request->validated());

    return redirect()
        ->route('sales.show', $sale)
        ->with('success', 'Venta registrada exitosamente');
}
```

#### Entregable:
- Controladores slim y limpios
- Lógica de negocio en servicios
- Código más testeable

---

## 🧪 TESTING REQUERIDO

### **Unit Tests**
- [ ] ProductRepository tests
- [ ] SaleRepository tests
- [ ] CashFlowRepository tests
- [ ] SaleService tests
- [ ] InventoryService tests

### **Feature Tests**
- [ ] Product CRUD tests
- [ ] Sale creation with inventory deduction
- [ ] Cash flow automatic creation

### **Comando:**
```bash
php artisan test --coverage --min=70
```

---

## 📊 MÉTRICAS DE ÉXITO

- [ ] PHPStan nivel 5 sin errores
- [ ] Laravel Pint sin warnings
- [ ] Coverage de código > 70%
- [ ] Queries críticas optimizadas (< 100ms)
- [ ] Código refactorizado sin bugs

---

## 🎯 DEFINITION OF DONE

- [ ] Todos los repositorios implementados y funcionando
- [ ] Todos los servicios creados y testeados
- [ ] Form Requests en todos los controladores críticos
- [ ] API Resources implementados
- [ ] Índices de BD creados y migración ejecutada
- [ ] Controladores refactorizados
- [ ] Tests passing al 100%
- [ ] Coverage > 70%
- [ ] Documentación actualizada
- [ ] Pull Request creado y revisado
- [ ] Merge a develop completado

---

## 📝 NOTAS TÉCNICAS

### **Dependency Injection**
Usar constructor injection en todos los servicios:
```php
public function __construct(
    private ProductRepositoryInterface $productRepository,
    private InventoryService $inventoryService
) {}
```

### **Transactions**
Todas las operaciones críticas deben estar en transacciones:
```php
DB::transaction(function () {
    // Operaciones
});
```

### **Eager Loading**
Siempre cargar relaciones necesarias:
```php
$sales = $this->saleRepository->all()->load(['user', 'saleItems.menuItem']);
```

---

## 🚀 ENTREGABLES FINALES

1. **Código:**
   - 5 Repositorios completos
   - 5 Servicios funcionales
   - 8+ Form Requests
   - 4+ API Resources
   - Controladores refactorizados

2. **Base de Datos:**
   - Migración de índices
   - Performance mejorado

3. **Testing:**
   - 20+ tests nuevos
   - Coverage > 70%

4. **Documentación:**
   - Código documentado
   - README actualizado
   - CHANGELOG.md actualizado

---

## 🔗 REFERENCIAS

- [Laravel Repository Pattern](https://github.com/andersao/l5-repository)
- [Service Layer in Laravel](https://medium.com/@remi_collin/keeping-your-laravel-applications-dry-with-single-action-classes-6a950ec54d1d)
- [Form Requests](https://laravel.com/docs/12.x/validation#form-request-validation)
- [API Resources](https://laravel.com/docs/12.x/eloquent-resources)

---

**Siguiente Sprint:** [Sprint 2 - Componentes Vue Reutilizables](./SPRINT_02_VUE_COMPONENTS.md)
