<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialDashboardTest extends TestCase
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

    public function test_admin_can_view_financial_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Financial')
            ->has('filters')
            ->has('kpis')
            ->has('trends')
            ->has('categoryChart')
            ->has('paymentMethodChart')
            ->has('topExpenses')
            ->has('recentTransactions')
            ->has('summary')
        );
    }

    public function test_non_admin_cannot_access_financial_dashboard(): void
    {
        $response = $this->actingAs($this->cajero)
            ->get(route('financial.dashboard'));

        $response->assertForbidden();
    }

    public function test_dashboard_displays_correct_kpis(): void
    {
        // Create test data
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
            ->get(route('financial.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('kpis.balance')
            ->has('kpis.income')
            ->has('kpis.expenses')
            ->has('kpis.profit_margin')
            ->where('kpis.balance.current', 700.0)
            ->where('kpis.income.current', 1000.0)
            ->where('kpis.expenses.current', 300.0)
        );
    }

    public function test_dashboard_filters_by_date_range(): void
    {
        // Create data in different date ranges
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

        // Filter for November only
        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('kpis.income.current', 1000.0)
        );
    }

    public function test_dashboard_returns_trend_data_correctly(): void
    {
        // Create data for multiple days
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta día 1',
            'flow_date' => '2025-11-01',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto día 1',
            'flow_date' => '2025-11-01',
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1500,
            'description' => 'Venta día 2',
            'flow_date' => '2025-11-02',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-02',
                'period' => 'day',
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('trends')
            ->has('trends.labels')
            ->has('trends.datasets')
        );
    }

    public function test_dashboard_shows_top_expense_categories(): void
    {
        // Create expenses in different categories
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 500,
            'description' => 'Gasto operativo',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'marketing',
            'amount' => 800,
            'description' => 'Marketing',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_admin',
            'amount' => 300,
            'description' => 'Admin',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('topExpenses', 3)
            ->where('topExpenses.0.category', 'marketing')
            ->where('topExpenses.0.total', 800.0)
        );
    }

    public function test_dashboard_shows_recent_transactions(): void
    {
        // Create several transactions
        for ($i = 1; $i <= 15; $i++) {
            CashFlow::create([
                'user_id' => $this->admin->id,
                'type' => $i % 2 === 0 ? 'entrada' : 'salida',
                'category' => $i % 2 === 0 ? 'ventas' : 'gastos_operativos',
                'amount' => 100 * $i,
                'description' => "Transacción {$i}",
                'flow_date' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('recentTransactions', 10) // Should return only 10 most recent
            ->where('recentTransactions.0.description', 'Transacción 1')
        );
    }

    public function test_dashboard_calculates_profit_margin_correctly(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('kpis.profit_margin.current', 70.0) // (1000 - 300) / 1000 * 100 = 70%
        );
    }

    public function test_api_kpis_endpoint_returns_json(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('financial.api.kpis'));

        $response->assertOk();
        $response->assertJsonStructure([
            'balance' => ['current', 'change'],
            'income' => ['current', 'change'],
            'expenses' => ['current', 'change'],
            'profit_margin' => ['current', 'change'],
        ]);
    }

    public function test_api_trends_endpoint_returns_json(): void
    {
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('financial.api.trends'));

        $response->assertOk();
        $response->assertJsonStructure([
            'labels',
            'datasets',
        ]);
    }

    public function test_dashboard_handles_empty_data_gracefully(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('kpis.balance.current', 0.0)
            ->where('kpis.income.current', 0.0)
            ->where('kpis.expenses.current', 0.0)
            ->where('topExpenses', [])
        );
    }

    public function test_dashboard_compares_periods_correctly(): void
    {
        // Current period (November 15)
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1500,
            'description' => 'Venta actual',
            'flow_date' => '2025-11-15',
        ]);

        // Previous period (October 15)
        CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta anterior',
            'flow_date' => '2025-10-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('financial.dashboard', [
                'date_from' => '2025-11-15',
                'date_to' => '2025-11-15',
            ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('kpis.income.current', 1500.0)
            ->where('kpis.income.change', 50.0) // (1500 - 1000) / 1000 * 100 = 50%
        );
    }
}
