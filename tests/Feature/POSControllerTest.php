<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\SimpleProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Usuario con rol cajero puede acceder al POS
     */
    public function test_cajero_puede_acceder_al_pos(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);

        $response = $this->actingAs($cajero)->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Sales/POS'));
    }

    /**
     * Test: Usuario sin permisos no puede acceder al POS
     */
    public function test_chef_no_puede_acceder_al_pos(): void
    {
        $chef = User::factory()->create(['role' => 'chef']);

        $response = $this->actingAs($chef)->get(route('pos.index'));

        $response->assertStatus(403); // Forbidden
    }

    /**
     * Test: Procesar venta con stock suficiente
     */
    public function test_procesar_venta_con_stock_suficiente(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);
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
                    'quantity' => 5,
                    'unit_price' => 50,
                ],
            ],
            'payment_method' => 'efectivo',
            'discount' => 0,
            'tax' => 0,
        ];

        $response = $this->actingAs($cajero)->post(route('pos.store'), $saleData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Venta procesada exitosamente');

        // Verificar que se creó la venta
        $this->assertDatabaseHas('sales', [
            'user_id' => $cajero->id,
            'total' => 250, // 5 * 50
            'status' => 'completada',
        ]);

        // Verificar deducción de inventario
        $product->refresh();
        $this->assertEquals(95, $product->current_stock);
    }

    /**
     * Test: Rechazar venta sin stock suficiente
     */
    public function test_rechazar_venta_sin_stock_suficiente(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'current_stock' => 2, // Solo 2 unidades disponibles
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
                    'quantity' => 10, // Intentar vender 10 cuando solo hay 2
                    'unit_price' => 50,
                ],
            ],
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($cajero)->post(route('pos.store'), $saleData);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verificar que NO se creó la venta
        $this->assertDatabaseCount('sales', 0);

        // Verificar que el stock NO cambió
        $product->refresh();
        $this->assertEquals(2, $product->current_stock);
    }

    /**
     * Test: Validación de datos requeridos
     */
    public function test_validacion_datos_venta_requeridos(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);

        // Intento de venta sin items
        $response = $this->actingAs($cajero)->post(route('pos.store'), [
            'payment_method' => 'efectivo',
        ]);

        $response->assertSessionHasErrors(['items']);

        // Intento de venta sin método de pago
        $response = $this->actingAs($cajero)->post(route('pos.store'), [
            'items' => [
                ['id' => 1, 'quantity' => 1, 'unit_price' => 50],
            ],
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    }
}
