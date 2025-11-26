<?php

namespace App\Http\Controllers;

use App\Repositories\Interfaces\CashRegisterRepositoryInterface;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;

class CashRegisterController extends Controller
{
    public function __construct(
        private CashRegisterRepositoryInterface $repository,
        private CashRegisterService $service
    ) {
    }

    /**
     * Vista principal de gestión de caja
     */
    public function index(): Response
    {
        $currentSession = $this->service->getCurrentSession();

        $sales = null;
        $transactions = null;

        if ($currentSession) {
            // Obtener ventas de la sesión actual
            $sales = $currentSession->sales()
                ->with(['saleItems.menuItem', 'saleItems.simpleProduct', 'user'])
                ->latest()
                ->get();

            // Obtener transacciones (cash flow) relacionadas
            $transactions = \App\Models\CashFlow::where('type', 'entrada')
                ->where('category', 'ventas')
                ->whereHas('sale', function ($query) use ($currentSession) {
                    $query->where('cash_register_session_id', $currentSession->id);
                })
                ->with(['sale', 'user'])
                ->latest()
                ->get();
        }

        return Inertia::render('CashRegister/Index', [
            'currentSession' => $currentSession ? [
                'id' => $currentSession->id,
                'opening_amount' => $currentSession->opening_amount,
                'opened_at' => $currentSession->opened_at,
                'opening_notes' => $currentSession->opening_notes,
                'total_cash_sales' => $currentSession->total_cash_sales,
                'total_card_sales' => $currentSession->total_card_sales,
                'total_transfer_sales' => $currentSession->total_transfer_sales,
                'total_all_sales' => $currentSession->total_all_sales,
                'transaction_count' => $currentSession->transaction_count,
                'duration_hours' => $currentSession->current_duration_in_hours,
                'user' => $currentSession->user,
            ] : null,
            'sales' => $sales,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Mostrar formulario para abrir caja
     */
    public function create(): Response
    {
        // Verificar si ya tiene una caja abierta
        if ($this->service->hasOpenSession()) {
            return Inertia::render('CashRegister/Index', [
                'error' => 'Ya tienes una sesión de caja abierta',
            ]);
        }

        return Inertia::render('CashRegister/Open');
    }

    /**
     * Abrir nueva sesión de caja
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'opening_amount' => 'required|numeric|min:0',
                'opening_notes' => 'nullable|string|max:1000',
            ]);

            $session = $this->service->openSession($validated);

            return redirect()
                ->route('cashregister.index')
                ->with('success', 'Caja abierta exitosamente. ¡Puedes comenzar a procesar ventas!');
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Ver detalles de una sesión específica
     */
    public function show(int $id): Response
    {
        try {
            $details = $this->service->getSessionDetails($id);

            return Inertia::render('CashRegister/Show', [
                'session' => $details['session'],
                'total_cash_sales' => $details['total_cash_sales'],
                'total_card_sales' => $details['total_card_sales'],
                'total_transfer_sales' => $details['total_transfer_sales'],
                'total_all_sales' => $details['total_all_sales'],
                'transaction_count' => $details['transaction_count'],
                'duration_hours' => $details['duration_hours'],
                'sales' => $details['sales'],
            ]);
        } catch (Exception $e) {
            return Inertia::render('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Mostrar formulario para cerrar caja
     */
    public function closeForm(): Response
    {
        $currentSession = $this->service->getCurrentSession();

        if (!$currentSession) {
            return Inertia::render('CashRegister/Index', [
                'error' => 'No tienes una sesión de caja abierta',
            ]);
        }

        return Inertia::render('CashRegister/Close', [
            'session' => [
                'id' => $currentSession->id,
                'opening_amount' => $currentSession->opening_amount,
                'opened_at' => $currentSession->opened_at,
                'opening_notes' => $currentSession->opening_notes,
                'total_cash_sales' => $currentSession->total_cash_sales,
                'total_card_sales' => $currentSession->total_card_sales,
                'total_transfer_sales' => $currentSession->total_transfer_sales,
                'total_all_sales' => $currentSession->total_all_sales,
                'transaction_count' => $currentSession->transaction_count,
                'duration_hours' => $currentSession->current_duration_in_hours,
                'expected_closing' => $currentSession->opening_amount + $currentSession->total_cash_sales,
            ],
        ]);
    }

    /**
     * Cerrar sesión de caja
     */
    public function close(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'session_id' => 'required|exists:cash_register_sessions,id',
                'closing_amount' => 'required|numeric|min:0',
                'closing_notes' => 'nullable|string|max:1000',
            ]);

            $session = $this->service->closeSession(
                $validated['session_id'],
                $validated
            );

            $message = 'Caja cerrada exitosamente.';

            if ($session->hasDifference()) {
                if ($session->hasShortage()) {
                    $message .= ' ATENCIÓN: Se detectó un faltante de $' . number_format(abs($session->difference), 2);
                } else {
                    $message .= ' Se detectó un sobrante de $' . number_format($session->difference, 2);
                }
            } else {
                $message .= ' ¡Todo cuadró perfectamente!';
            }

            return redirect()
                ->route('cashregister.show', $session->id)
                ->with('success', $message);
        } catch (Exception $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Historial de sesiones de caja
     */
    public function history(Request $request): Response
    {
        $filters = $request->only(['status', 'user_id', 'date_from', 'date_to', 'has_difference']);

        $sessions = $this->repository->getPaginated($filters, 20);

        return Inertia::render('CashRegister/History', [
            'sessions' => $sessions,
            'filters' => $filters,
        ]);
    }

    /**
     * Estadísticas de cajero
     */
    public function stats(Request $request): Response
    {
        $userId = $request->input('user_id', Auth::id());
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $stats = $this->service->getCashierStats($userId, $dateFrom, $dateTo);

        return Inertia::render('CashRegister/Stats', [
            'stats' => $stats,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    /**
     * API: Obtener sesión actual
     */
    public function current()
    {
        $session = $this->service->getCurrentSession();

        if (!$session) {
            return response()->json([
                'session' => null,
                'has_open_session' => false,
            ]);
        }

        return response()->json([
            'session' => [
                'id' => $session->id,
                'opening_amount' => $session->opening_amount,
                'opened_at' => $session->opened_at,
                'total_cash_sales' => $session->total_cash_sales,
                'total_all_sales' => $session->total_all_sales,
                'transaction_count' => $session->transaction_count,
            ],
            'has_open_session' => true,
        ]);
    }
}
