<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SimpleProduct;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SaleService $saleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = new SaleService;
    }

    /**
     * Test: Procesar venta simple exitosamente
     */
    public function test_procesa_venta_simple_exitosamente(): void
    {
        // Arrange - Preparar datos
        $user = User::factory()->create(['role' => 'cajero']);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 100,
            'unit_cost' => 10,
        ]);

        $simpleProduct = SimpleProduct::factory()->create([
            'product_id' => $product->id,
            'sale_price' => 25,
            'cost_per_unit' => 1,
        ]);

        $saleData = [
            'items' => [
                [
                    'id' => $simpleProduct->id,
                    'product_type' => 'simple',
                    'quantity' => 5,
                    'unit_price' => 25,
                ],
            ],
            'payment_method' => 'efectivo',
            'discount' => 0,
            'tax' => 0,
        ];

        // Act - Ejecutar la acción
        $sale = $this->saleService->processSale($saleData, $user->id);

        // Assert - Verificar resultados
        $this->assertInstanceOf(Sale::class, $sale);
        $this->assertEquals(125, $sale->total); // 5 * 25
        $this->assertEquals('completada', $sale->status);
        $this->assertCount(1, $sale->saleItems);

        // Verificar deducción de inventario
        $product->refresh();
        $this->assertEquals(95, $product->current_stock); // 100 - (5 * 1)
    }

    /**
     * Test: Falla cuando no hay stock suficiente
     */
    public function test_falla_cuando_no_hay_stock_suficiente(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No hay suficiente stock');

        $user = User::factory()->create(['role' => 'cajero']);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 2, // Solo 2 unidades
            'unit_cost' => 10,
        ]);

        $simpleProduct = SimpleProduct::factory()->create([
            'product_id' => $product->id,
            'sale_price' => 25,
            'cost_per_unit' => 1,
        ]);

        $saleData = [
            'items' => [
                [
                    'id' => $simpleProduct->id,
                    'product_type' => 'simple',
                    'quantity' => 5, // Intentar vender 5 cuando solo hay 2
                    'unit_price' => 25,
                ],
            ],
            'payment_method' => 'efectivo',
            'discount' => 0,
            'tax' => 0,
        ];

        $this->saleService->processSale($saleData, $user->id);
    }

    /**
     * Test: Calcular totales correctamente con descuento e impuesto
     */
    public function test_calcula_totales_correctamente(): void
    {
        $user = User::factory()->create(['role' => 'cajero']);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 100,
        ]);

        $simpleProduct = SimpleProduct::factory()->create([
            'product_id' => $product->id,
            'sale_price' => 100,
            'cost_per_unit' => 1,
        ]);

        $saleData = [
            'items' => [
                [
                    'id' => $simpleProduct->id,
                    'product_type' => 'simple',
                    'quantity' => 2,
                    'unit_price' => 100,
                ],
            ],
            'payment_method' => 'tarjeta',
            'discount' => 20,  // $20 descuento
            'tax' => 16,       // $16 impuesto
        ];

        $sale = $this->saleService->processSale($saleData, $user->id);

        $this->assertEquals(200, $sale->subtotal);  // 2 * 100
        $this->assertEquals(20, $sale->discount);
        $this->assertEquals(16, $sale->tax);
        $this->assertEquals(196, $sale->total);     // 200 - 20 + 16
    }

    /**
     * Test: Generar número de venta único por día
     */
    public function test_genera_numero_venta_unico(): void
    {
        $user = User::factory()->create(['role' => 'cajero']);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 100,
        ]);

        $simpleProduct = SimpleProduct::factory()->create([
            'product_id' => $product->id,
            'sale_price' => 50,
            'cost_per_unit' => 1,
        ]);

        $saleData = [
            'items' => [
                [
                    'id' => $simpleProduct->id,
                    'product_type' => 'simple',
                    'quantity' => 1,
                    'unit_price' => 50,
                ],
            ],
            'payment_method' => 'efectivo',
        ];

        // Crear dos ventas
        $sale1 = $this->saleService->processSale($saleData, $user->id);
        $sale2 = $this->saleService->processSale($saleData, $user->id);

        // Verificar que los números son diferentes
        $this->assertNotEquals($sale1->sale_number, $sale2->sale_number);

        // Verificar formato: YYYYMMDD0001, YYYYMMDD0002
        $this->assertMatchesRegularExpression('/^\d{8}\d{4}$/', $sale1->sale_number);
        $this->assertMatchesRegularExpression('/^\d{8}\d{4}$/', $sale2->sale_number);
    }

    /**
     * Test: Procesar venta de menu item con receta
     */
    public function test_procesa_venta_menu_item_con_receta(): void
    {
        $user = User::factory()->create(['role' => 'cajero']);
        $category = Category::factory()->create();

        // Crear productos base
        $pollo = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Pollo',
            'current_stock' => 10, // 10 kg
            'unit_cost' => 50,
        ]);

        $tortillas = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tortillas',
            'current_stock' => 5, // 5 kg
            'unit_cost' => 20,
        ]);

        // Crear menu item
        $tacos = MenuItem::factory()->create([
            'name' => 'Tacos de Pollo',
            'price' => 80,
        ]);

        // Crear recetas
        Recipe::factory()->create([
            'menu_item_id' => $tacos->id,
            'product_id' => $pollo->id,
            'quantity_needed' => 0.2, // 200g de pollo por porción
        ]);

        Recipe::factory()->create([
            'menu_item_id' => $tacos->id,
            'product_id' => $tortillas->id,
            'quantity_needed' => 0.1, // 100g de tortillas por porción
        ]);

        $saleData = [
            'items' => [
                [
                    'id' => $tacos->id,
                    'product_type' => 'menu',
                    'quantity' => 3, // 3 órdenes
                    'unit_price' => 80,
                ],
            ],
            'payment_method' => 'efectivo',
        ];

        $sale = $this->saleService->processSale($saleData, $user->id);

        // Verificar venta
        $this->assertEquals(240, $sale->total); // 3 * 80

        // Verificar deducción de inventario
        $pollo->refresh();
        $tortillas->refresh();

        $this->assertEquals(9.4, $pollo->current_stock);      // 10 - (3 * 0.2)
        $this->assertEquals(4.7, $tortillas->current_stock);  // 5 - (3 * 0.1)
    }
}
