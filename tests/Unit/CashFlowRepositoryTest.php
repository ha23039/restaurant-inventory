<?php

namespace Tests\Unit;

use App\Models\CashFlow;
use App\Models\User;
use App\Repositories\CashFlowRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashFlowRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected CashFlowRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CashFlowRepository(new CashFlow());
    }

    public function test_calculates_balance_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta 1',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta 2',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto 1',
            'flow_date' => now(),
        ]);

        // Act
        $balance = $this->repository->getBalance();

        // Assert
        $this->assertEquals(1200, $balance);
    }

    public function test_gets_summary_by_date_range(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Dentro del rango
        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto',
            'flow_date' => '2025-11-16',
        ]);

        // Fuera del rango
        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 500,
            'description' => 'Venta antigua',
            'flow_date' => '2025-10-01',
        ]);

        // Act
        $summary = $this->repository->getSummaryByDateRange('2025-11-01', '2025-11-30');

        // Assert
        $this->assertEquals(1000, $summary['income']['total']);
        $this->assertEquals(1, $summary['income']['count']);
        $this->assertEquals(300, $summary['expenses']['total']);
        $this->assertEquals(1, $summary['expenses']['count']);
        $this->assertEquals(700, $summary['balance']);
        $this->assertEquals(70, $summary['profit_margin']); // (1000 - 300) / 1000 * 100
    }

    public function test_gets_trends_by_period(): void
    {
        // Arrange
        $user = User::factory()->create();

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta día 1',
            'flow_date' => '2025-11-01',
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto día 1',
            'flow_date' => '2025-11-01',
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1500,
            'description' => 'Venta día 2',
            'flow_date' => '2025-11-02',
        ]);

        // Act
        $trends = $this->repository->getTrendsByPeriod('2025-11-01', '2025-11-02', 'day');

        // Assert
        $this->assertIsArray($trends);
        $this->assertArrayHasKey('labels', $trends);
        $this->assertArrayHasKey('datasets', $trends);
        $this->assertCount(2, $trends['labels']);
        $this->assertEquals(2, count($trends['datasets']));
        $this->assertEquals('Ingresos', $trends['datasets'][0]['label']);
        $this->assertEquals('Gastos', $trends['datasets'][1]['label']);
    }

    public function test_groups_by_category(): void
    {
        // Arrange
        $user = User::factory()->create();

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 300,
            'description' => 'Gasto 1',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 200,
            'description' => 'Gasto 2',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => now(),
        ]);

        // Act
        $byCategory = $this->repository->getByCategory();

        // Assert
        $this->assertIsArray($byCategory);
        $this->assertCount(2, $byCategory);

        // Verificar gastos_operativos
        $gastosOperativos = collect($byCategory)->firstWhere('category', 'gastos_operativos');
        $this->assertEquals(2, $gastosOperativos['count']);
        $this->assertEquals(500, $gastosOperativos['total']);
    }

    public function test_compares_periods(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Período actual
        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1500,
            'description' => 'Venta actual',
            'flow_date' => '2025-11-15',
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 500,
            'description' => 'Gasto actual',
            'flow_date' => '2025-11-15',
        ]);

        // Período anterior
        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta anterior',
            'flow_date' => '2025-10-15',
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 400,
            'description' => 'Gasto anterior',
            'flow_date' => '2025-10-15',
        ]);

        // Act
        $comparison = $this->repository->comparePeriods(
            '2025-11-01',
            '2025-11-30',
            '2025-10-01',
            '2025-10-31'
        );

        // Assert
        $this->assertArrayHasKey('current', $comparison);
        $this->assertArrayHasKey('previous', $comparison);
        $this->assertArrayHasKey('changes', $comparison);

        // Verificar cambio en ingresos: (1500 - 1000) / 1000 * 100 = 50%
        $this->assertEquals(50, $comparison['changes']['income_change']);

        // Verificar cambio en gastos: (500 - 400) / 400 * 100 = 25%
        $this->assertEquals(25, $comparison['changes']['expenses_change']);
    }

    public function test_gets_paginated_with_filters(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Crear 25 transacciones
        for ($i = 1; $i <= 25; $i++) {
            CashFlow::create([
                'user_id' => $user->id,
                'type' => $i % 2 === 0 ? 'entrada' : 'salida',
                'category' => 'ventas',
                'amount' => 100 * $i,
                'description' => "Transacción {$i}",
                'flow_date' => now()->subDays($i),
            ]);
        }

        // Act - Sin filtros
        $paginated = $this->repository->getPaginated([], 20);

        // Assert
        $this->assertEquals(25, $paginated->total());
        $this->assertEquals(20, $paginated->perPage());
        $this->assertEquals(2, $paginated->lastPage());

        // Act - Con filtro de tipo
        $paginatedIncome = $this->repository->getPaginated(['type' => 'entrada'], 20);

        // Assert
        $this->assertEquals(12, $paginatedIncome->total()); // 25 / 2 (redondeado hacia abajo) = 12
    }

    public function test_gets_recent_transactions(): void
    {
        // Arrange
        $user = User::factory()->create();

        for ($i = 1; $i <= 15; $i++) {
            CashFlow::create([
                'user_id' => $user->id,
                'type' => 'entrada',
                'category' => 'ventas',
                'amount' => 100,
                'description' => "Transacción {$i}",
                'flow_date' => now()->subDays($i),
            ]);
        }

        // Act
        $recent = $this->repository->getRecent(10);

        // Assert
        $this->assertCount(10, $recent);
        $this->assertEquals('Transacción 1', $recent[0]['description']);
    }

    public function test_gets_top_expense_categories(): void
    {
        // Arrange
        $user = User::factory()->create();

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 500,
            'description' => 'Gasto op',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'gastos_admin',
            'amount' => 300,
            'description' => 'Gasto admin',
            'flow_date' => now(),
        ]);

        CashFlow::create([
            'user_id' => $user->id,
            'type' => 'salida',
            'category' => 'marketing',
            'amount' => 800,
            'description' => 'Marketing',
            'flow_date' => now(),
        ]);

        // Act
        $topCategories = $this->repository->getTopExpenseCategories(2);

        // Assert
        $this->assertCount(2, $topCategories);
        $this->assertEquals('marketing', $topCategories[0]['category']);
        $this->assertEquals(800, $topCategories[0]['total']);
        $this->assertEquals('gastos_operativos', $topCategories[1]['category']);
        $this->assertEquals(500, $topCategories[1]['total']);
    }
}
