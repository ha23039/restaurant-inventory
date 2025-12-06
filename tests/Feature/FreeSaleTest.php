<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeSaleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $cajero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->cajero = User::factory()->create(['role' => 'cajero']);
    }

    public function test_can_create_free_sale_without_products(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Servicio de catering para evento corporativo',
            'free_sale_total' => 5000.00,
            'payment_method' => 'transferencia',
            'discount' => 0,
            'tax' => 0,
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sales', [
            'is_free_sale' => true,
            'free_sale_description' => 'Servicio de catering para evento corporativo',
            'total' => 5000.00,
            'payment_method' => 'transferencia',
        ]);
    }

    public function test_free_sale_creates_cash_flow_entry(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Venta de servicio especial',
            'free_sale_total' => 3000.00,
            'payment_method' => 'efectivo',
        ];

        $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $sale = Sale::where('is_free_sale', true)->first();

        $this->assertDatabaseHas('cash_flow', [
            'sale_id' => $sale->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 3000.00,
        ]);
    }

    public function test_free_sale_does_not_create_sale_items(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Consultoría especial',
            'free_sale_total' => 2000.00,
            'payment_method' => 'tarjeta',
        ];

        $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $sale = Sale::where('is_free_sale', true)->first();

        $this->assertEquals(0, $sale->saleItems()->count());
    }

    public function test_free_sale_requires_description(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => '', // Vacío
            'free_sale_total' => 1000.00,
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertSessionHasErrors(['free_sale_description']);
    }

    public function test_free_sale_description_must_be_at_least_3_characters(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'AB', // Solo 2 caracteres
            'free_sale_total' => 1000.00,
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertSessionHasErrors(['free_sale_description']);
    }

    public function test_free_sale_description_max_length_is_500_characters(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => str_repeat('A', 501), // 501 caracteres
            'free_sale_total' => 1000.00,
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertSessionHasErrors(['free_sale_description']);
    }

    public function test_free_sale_requires_total_amount(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Servicio especial',
            // free_sale_total omitido
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertSessionHasErrors(['free_sale_total']);
    }

    public function test_free_sale_amount_must_be_greater_than_zero(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Servicio especial',
            'free_sale_total' => 0, // Cero no es válido
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertSessionHasErrors(['free_sale_total']);
    }

    public function test_free_sale_does_not_require_items_array(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Servicio sin productos',
            'free_sale_total' => 1500.00,
            'payment_method' => 'transferencia',
            // No se incluye 'items'
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_normal_sale_still_requires_items(): void
    {
        $normalSaleData = [
            'is_free_sale' => false,
            'payment_method' => 'efectivo',
            // No se incluye 'items'
        ];

        $response = $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $normalSaleData);

        $response->assertSessionHasErrors(['items']);
    }

    public function test_free_sale_generates_unique_sale_number(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Primera venta libre',
            'free_sale_total' => 1000.00,
            'payment_method' => 'efectivo',
        ];

        $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $sale = Sale::where('is_free_sale', true)->first();

        $this->assertNotNull($sale->sale_number);
        $this->assertMatchesRegularExpression('/^\d{12}$/', $sale->sale_number); // YYYYMMDD0001 format
    }

    public function test_can_create_multiple_free_sales(): void
    {
        $freeSaleData1 = [
            'is_free_sale' => true,
            'free_sale_description' => 'Primera venta libre',
            'free_sale_total' => 1000.00,
            'payment_method' => 'efectivo',
        ];

        $freeSaleData2 = [
            'is_free_sale' => true,
            'free_sale_description' => 'Segunda venta libre',
            'free_sale_total' => 2000.00,
            'payment_method' => 'tarjeta',
        ];

        $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData1);

        $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData2);

        $this->assertEquals(2, Sale::where('is_free_sale', true)->count());
    }

    public function test_free_sale_respects_payment_method(): void
    {
        $paymentMethods = ['efectivo', 'tarjeta', 'transferencia'];

        foreach ($paymentMethods as $method) {
            $freeSaleData = [
                'is_free_sale' => true,
                'free_sale_description' => "Venta con {$method}",
                'free_sale_total' => 500.00,
                'payment_method' => $method,
            ];

            $this->actingAs($this->cajero)
                ->post(route('sales.pos.store'), $freeSaleData);

            $this->assertDatabaseHas('sales', [
                'is_free_sale' => true,
                'payment_method' => $method,
            ]);
        }
    }

    public function test_free_sale_total_matches_sale_total(): void
    {
        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Servicio de consultoría',
            'free_sale_total' => 4500.50,
            'payment_method' => 'transferencia',
        ];

        $this->actingAs($this->cajero)
            ->post(route('sales.pos.store'), $freeSaleData);

        $sale = Sale::where('is_free_sale', true)->first();

        $this->assertEquals(4500.50, $sale->total);
        $this->assertEquals(4500.50, $sale->subtotal);
    }

    public function test_non_admin_and_non_cajero_cannot_create_free_sale(): void
    {
        $chef = User::factory()->create(['role' => 'chef']);

        $freeSaleData = [
            'is_free_sale' => true,
            'free_sale_description' => 'Intento no autorizado',
            'free_sale_total' => 1000.00,
            'payment_method' => 'efectivo',
        ];

        $response = $this->actingAs($chef)
            ->post(route('sales.pos.store'), $freeSaleData);

        $response->assertForbidden();
    }
}
