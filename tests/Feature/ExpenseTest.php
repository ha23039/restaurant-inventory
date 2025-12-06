<?php

namespace Tests\Feature;

use App\Models\CashFlow;
use App\Models\User;
use App\Services\ExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ExpenseService $expenseService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->expenseService = new ExpenseService();
    }

    public function test_admin_can_view_expenses_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('expenses.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Expenses/Index')
            ->has('expenses')
            ->has('categories')
            ->has('statistics')
        );
    }

    public function test_non_admin_cannot_access_expenses(): void
    {
        $cajero = User::factory()->create(['role' => 'cajero']);

        $response = $this->actingAs($cajero)
            ->get(route('expenses.index'));

        $response->assertForbidden();
    }

    public function test_can_create_expense(): void
    {
        $expenseData = [
            'amount' => 500.00,
            'category' => 'gastos_operativos',
            'description' => 'Pago de luz',
            'notes' => 'Recibo de noviembre',
            'expense_date' => '2025-11-15',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('expenses.store'), $expenseData);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        // Verify expense was created in cash_flow
        $this->assertDatabaseHas('cash_flow', [
            'type' => 'salida',
            'category' => 'gastos_operativos',
            'amount' => 500.00,
            'description' => 'Pago de luz',
        ]);
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('expenses.store'), []);

        $response->assertSessionHasErrors(['amount', 'category', 'description', 'expense_date']);
    }

    public function test_validates_amount_is_positive(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'amount' => -100,
                'category' => 'gastos_operativos',
                'description' => 'Test',
                'expense_date' => '2025-11-15',
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_validates_category_is_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'amount' => 100,
                'category' => 'invalid_category',
                'description' => 'Test',
                'expense_date' => '2025-11-15',
            ]);

        $response->assertSessionHasErrors(['category']);
    }

    public function test_validates_date_is_not_future(): void
    {
        $futureDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->post(route('expenses.store'), [
                'amount' => 100,
                'category' => 'gastos_operativos',
                'description' => 'Test',
                'expense_date' => $futureDate,
            ]);

        $response->assertSessionHasErrors(['expense_date']);
    }

    public function test_can_update_expense(): void
    {
        $expense = $this->expenseService->create([
            'amount' => 500.00,
            'category' => 'gastos_operativos',
            'description' => 'Pago de luz',
            'expense_date' => '2025-11-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('expenses.update', $expense->id), [
                'amount' => 600.00,
                'category' => 'gastos_admin',
                'description' => 'Pago de luz actualizado',
                'expense_date' => '2025-11-16',
            ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_flow', [
            'id' => $expense->id,
            'amount' => 600.00,
            'category' => 'gastos_admin',
            'description' => 'Pago de luz actualizado',
        ]);
    }

    public function test_cannot_update_non_expense_cash_flow(): void
    {
        // Create an income entry (not an expense)
        $income = CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('expenses.update', $income->id), [
                'amount' => 600.00,
                'category' => 'gastos_admin',
                'description' => 'Test',
                'expense_date' => '2025-11-16',
            ]);

        $response->assertNotFound();
    }

    public function test_can_delete_expense(): void
    {
        $expense = $this->expenseService->create([
            'amount' => 500.00,
            'category' => 'gastos_operativos',
            'description' => 'Pago de luz',
            'expense_date' => '2025-11-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('expenses.destroy', $expense->id));

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cash_flow', [
            'id' => $expense->id,
        ]);
    }

    public function test_cannot_delete_non_expense_cash_flow(): void
    {
        $income = CashFlow::create([
            'user_id' => $this->admin->id,
            'type' => 'entrada',
            'category' => 'ventas',
            'amount' => 1000,
            'description' => 'Venta',
            'flow_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('expenses.destroy', $income->id));

        $response->assertNotFound();
    }

    public function test_expense_service_creates_expense_correctly(): void
    {
        $expense = $this->expenseService->create([
            'amount' => 500.00,
            'category' => 'gastos_operativos',
            'description' => 'Pago de luz',
            'notes' => 'Recibo de noviembre',
            'expense_date' => '2025-11-15',
        ]);

        $this->assertInstanceOf(CashFlow::class, $expense);
        $this->assertEquals('salida', $expense->type);
        $this->assertEquals(500.00, $expense->amount);
        $this->assertEquals('gastos_operativos', $expense->category);
        $this->assertEquals('Pago de luz', $expense->description);
    }

    public function test_expense_service_gets_statistics(): void
    {
        // Create some test expenses
        $this->expenseService->create([
            'amount' => 500.00,
            'category' => 'gastos_operativos',
            'description' => 'Gasto 1',
            'expense_date' => '2025-11-15',
        ]);

        $this->expenseService->create([
            'amount' => 300.00,
            'category' => 'gastos_admin',
            'description' => 'Gasto 2',
            'expense_date' => '2025-11-16',
        ]);

        $this->expenseService->create([
            'amount' => 200.00,
            'category' => 'gastos_operativos',
            'description' => 'Gasto 3',
            'expense_date' => '2025-11-17',
        ]);

        $stats = $this->expenseService->getStatistics();

        $this->assertEquals(1000.00, $stats['total']);
        $this->assertEquals(3, $stats['count']);
        $this->assertEquals(333.33, round($stats['average'], 2));
        $this->assertCount(2, $stats['by_category']);
    }

    public function test_expense_filters_work_correctly(): void
    {
        // Create expenses in different dates
        $this->expenseService->create([
            'amount' => 500.00,
            'category' => 'gastos_operativos',
            'description' => 'Gasto nov',
            'expense_date' => '2025-11-15',
        ]);

        $this->expenseService->create([
            'amount' => 300.00,
            'category' => 'gastos_admin',
            'description' => 'Gasto oct',
            'expense_date' => '2025-10-15',
        ]);

        // Test date filter
        $response = $this->actingAs($this->admin)
            ->get(route('expenses.index', [
                'date_from' => '2025-11-01',
                'date_to' => '2025-11-30',
            ]));

        $response->assertOk();

        // Test category filter
        $response = $this->actingAs($this->admin)
            ->get(route('expenses.index', [
                'category' => 'gastos_operativos',
            ]));

        $response->assertOk();

        // Test search filter
        $response = $this->actingAs($this->admin)
            ->get(route('expenses.index', [
                'search' => 'nov',
            ]));

        $response->assertOk();
    }
}
