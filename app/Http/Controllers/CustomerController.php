<?php

namespace App\Http\Controllers;

use App\Models\DigitalCustomer;
use App\Services\CustomerService;
use App\Http\Resources\DigitalCustomerResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of customers
     */
    public function index(Request $request): Response
    {
        $customers = $this->customerService->getPaginated(
            $request->only(['is_verified', 'is_active', 'search']),
            $request->get('per_page', 15)
        );

        return Inertia::render('Customers/Index', [
            'customers' => DigitalCustomerResource::collection($customers),
            'filters' => $request->only(['is_verified', 'is_active', 'search']),
            'statistics' => $this->customerService->getStatistics(),
        ]);
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|max:5',
            'email' => 'nullable|email|max:255',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->customerService->create($validated);

            return redirect()
                ->route('customers.index')
                ->with('success', 'Cliente creado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al crear el cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified customer
     */
    public function show(DigitalCustomer $customer): Response
    {
        $customer->load(['sales' => function ($query) {
            $query->latest()->take(10);
        }]);

        return Inertia::render('Customers/Show', [
            'customer' => new DigitalCustomerResource($customer),
            'recent_orders' => $customer->sales,
        ]);
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, DigitalCustomer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'country_code' => 'required|string|max:5',
            'email' => 'nullable|email|max:255',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->customerService->update($customer, $validated);

            return redirect()
                ->route('customers.index')
                ->with('success', 'Cliente actualizado exitosamente');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Error al actualizar el cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(DigitalCustomer $customer): RedirectResponse
    {
        // Prevent deleting customers with orders (check real relationship)
        $ordersCount = $customer->sales()->count();

        if ($ordersCount > 0) {
            return back()->withErrors([
                'error' => "No se puede eliminar un cliente con {$ordersCount} orden(es) registrada(s). Los datos históricos deben conservarse."
            ]);
        }

        try {
            $customerName = $customer->name ?: 'Sin nombre';
            $this->customerService->delete($customer);

            return redirect()
                ->route('customers.index')
                ->with('success', "Cliente '{$customerName}' eliminado exitosamente");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus(DigitalCustomer $customer): RedirectResponse
    {
        try {
            $updatedCustomer = $this->customerService->toggleStatus($customer);

            $message = $updatedCustomer->is_active
                ? 'Cliente activado exitosamente'
                : 'Cliente desactivado exitosamente';

            return redirect()
                ->route('customers.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cambiar el estado: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle customer verified status
     */
    public function toggleVerified(DigitalCustomer $customer): RedirectResponse
    {
        try {
            $updatedCustomer = $this->customerService->toggleVerified($customer);

            $message = $updatedCustomer->is_verified
                ? 'Cliente verificado exitosamente'
                : 'Verificación de cliente removida';

            return redirect()
                ->route('customers.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cambiar el estado de verificación: ' . $e->getMessage()]);
        }
    }

    /**
     * Clean up incomplete customers (no name and no orders)
     */
    public function cleanupIncomplete(): RedirectResponse
    {
        try {
            $count = $this->customerService->deleteIncompleteCustomers();

            if ($count > 0) {
                return redirect()
                    ->route('customers.index')
                    ->with('success', "Se eliminaron {$count} cliente(s) incompleto(s) sin órdenes");
            }

            return redirect()
                ->route('customers.index')
                ->with('info', 'No se encontraron clientes incompletos para eliminar');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al limpiar clientes incompletos: ' . $e->getMessage()]);
        }
    }
}
