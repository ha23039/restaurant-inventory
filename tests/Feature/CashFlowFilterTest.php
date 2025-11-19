<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowFilterTest extends TestCase
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

    public function test_admin_can_view_cash_flow_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('CashFlow/Index')
            ->has('transactions')
            ->has('filters')
            ->has('categories')
            ->has('users')
        );
    }

    public function test_non_admin_cannot_access_cash_flow(): void
    {
        $response = $this->actingAs($this->cajero)
            ->get(route('cashflow.index'));

        $response->assertForbidden();
    }

    public function test_can_filter_by_type(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta 1',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto 1',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', ['type' => 'entrada']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.type', 'entrada')
        );
    }

    public function test_can_filter_by_category(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto operativo',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'marketing',
            'amount' => 500,
            'description' => 'Marketing',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', ['category' => 'marketing']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.category', 'marketing')
        );
    }

    public function test_can_filter_by_date_range(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta noviembre',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta octubre',
            'flow_date' => '2025-10-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.description', 'Venta noviembre')
        );
    }

    public function test_can_search_by_description(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta de producto especial',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta normal',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', ['search' => 'especial']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.description', 'Venta de producto especial')
        );
    }

    public function test_can_filter_by_user(): void
    {
        $user2 = User::factory()->create(['role' => 'cajero']);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta admin',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user2->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta cajero',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', ['user_id' => $user2->id]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.description', 'Venta cajero')
        );
    }

    public function test_can_filter_by_amount_range(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta pequeña',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1500,
            'description' => 'Venta grande',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 2500,
            'description' => 'Venta muy grande',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', [
                'amount_min' => 1000,
                'amount_max' => 2000,
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 1)
            ->where('transactions.data.0.description', 'Venta grande')
        );
    }

    public function test_can_combine_multiple_filters(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 500,
            'description' => 'Gasto de luz',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto de agua',
            'flow_date' => '2025-11-16',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'marketing',
            'amount' => 1000,
            'description' => 'Campaña publicitaria',
            'flow_date' => '2025-11-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index', [
                'type' => 'salida',
                'category' => 'gastos_operativos',
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
                'amount_max' => 600,
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 2) // Should match both gastos_operativos under 600
        );
    }

    public function test_shows_summary_when_date_range_provided(): void
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
            ->get(route('cashflow.index', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('summary')
            ->where('summary.income.total', 1000.0)
            ->where('summary.expenses.total', 300.0)
            ->where('summary.balance', 700.0)
        );
    }

    public function test_can_export_to_csv(): void
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
            ->get(route('cashflow.export-csv'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Venta 1', $response->getContent());
    }

    public function test_export_csv_respects_filters(): void
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
            ->get(route('cashflow.export-csv', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('Venta incluida', $content);
        $this->assertStringNotContainsString('Venta excluida', $content);
    }

    public function test_transactions_are_paginated(): void
    {
        // Create 25 transactions
        for ($i = 1; $i <= 25; $i++) {
            CashFlow::create([
                'user_id' => $this->admin->id,
                'type' => 'entrada',
                'category' => 'ventas',
                'amount' => 100 * $i,
                'description' => "Venta {$i}",
                'flow_date' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('cashflow.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('transactions.data', 20) // Default per page
            ->has('transactions.links')
            ->where('transactions.total', 25)
        );
    }
}
