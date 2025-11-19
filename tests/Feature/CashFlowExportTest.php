<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_export_to_excel(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta 1',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-excel'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('flujo_efectivo_', $response->headers->get('Content-Disposition'));
    }

    public function test_can_export_to_pdf(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta PDF',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_excel_export_respects_filters(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta incluida',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta excluida',
            'flow_date' => '2025-10-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-excel', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();
    }

    public function test_pdf_export_respects_filters(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto incluido',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta excluida',
            'flow_date' => '2025-11-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-pdf', [
                'type' => 'salida',
                'category' => 'gastos_operativos',
            ]));

        $response->assertOk();
    }

    public function test_pdf_includes_summary_when_date_range_provided(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto',
            'flow_date' => '2025-11-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-pdf', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_non_admin_cannot_export_to_excel(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);

        $response = $this->actingAs($cajero)
            ->get(route('cashflow.export-excel'));

        $response->assertForbidden();
    }

    public function test_non_admin_cannot_export_to_pdf(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);

        $response = $this->actingAs($cajero)
            ->get(route('cashflow.export-pdf'));

        $response->assertForbidden();
    }

    public function test_excel_export_handles_empty_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-excel'));

        $response->assertOk();
    }

    public function test_pdf_export_handles_empty_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-pdf'));

        $response->assertOk();
    }

    public function test_exports_include_user_information(): void
    {
        $user2 = User::factory()->create(['role' => 'cajero', 'name' => 'Test Cashier']);

        CashFlow::create([
            'user_id' => $user2->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta cajero',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-excel'));

        $response->assertOk();
    }

    public function test_exports_include_sale_information(): void
    {
        $sale = \App\Models\Sale::factory()->create([
            'user_id' => $this->admin->id,
            'sale_number' => '202511150001',
            'total_amount' => 1000,
            'payment_method' => 'efectivo',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'sale_id' => $sale->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta con referencia',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-pdf'));

        $response->assertOk();
    }

    public function test_excel_filename_includes_timestamp(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-excel'));

        $disposition = $response->headers->get('Content-Disposition');

        $this->assertStringContainsString('flujo_efectivo_', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);
    }

    public function test_pdf_filename_includes_timestamp(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.export-pdf'));

        $disposition = $response->headers->get('Content-Disposition');

        $this->assertStringContainsString('flujo_efectivo_', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
    }
}
