<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\ThermalTicketService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    private $ticketService;

    public function __construct(ThermalTicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * 🍳 IMPRIMIR COMANDA DE COCINA - VERSIÓN INERTIA
     */
    public function printKitchenOrder(Sale $sale)
    {
        try {
            $sale->load(['saleItems.menuItem', 'saleItems.simpleProduct', 'user']);
            // Verificar que la venta existe y está completada
            if ($sale->status !== 'completada') {
                return back()->withErrors([
                    'message' => 'Solo se pueden imprimir comandas de ventas completadas'
                ]);
            }

            // Verificar que hay items que requieren cocina
            $kitchenItems = $sale->saleItems->filter(function ($item) {
                return $this->requiresKitchen($item);
            });

            if ($kitchenItems->isEmpty()) {
                return back()->withErrors([
                    'message' => 'Esta venta no tiene items que requieran cocina'
                ]);
            }

            // Generar e imprimir comanda
            $success = $this->ticketService->generateKitchenOrder($sale);

            if ($success) {
                // Registrar en log para auditoría
                Log::info("Comanda de cocina impresa", [
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'user_id' => auth()->id(),
                    'kitchen_items_count' => $kitchenItems->count()
                ]);

                return back()->with('success', 'Comanda enviada a cocina exitosamente');
            } else {
                return back()->withErrors([
                    'message' => 'Error al enviar comanda a cocina. Verificar impresora.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error imprimiendo comanda de cocina', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors([
                'message' => 'Error interno al procesar la comanda'
            ]);
        }
    }

    /**
     * 🧾 IMPRIMIR TICKET DE CLIENTE - VERSIÓN INERTIA
     */
    public function printCustomerReceipt(Sale $sale)
    {
        try {
             $sale->load(['saleItems.menuItem', 'saleItems.simpleProduct', 'user']);
            // Verificar que la venta existe
            if ($sale->status === 'cancelada') {
                return back()->withErrors([
                    'message' => 'No se puede imprimir ticket de venta cancelada'
                ]);
            }

            // Generar e imprimir ticket
            $success = $this->ticketService->generateCustomerReceipt($sale);

            if ($success) {
                // Actualizar timestamp de última impresión
                $sale->update(['last_printed_at' => now()]);

                Log::info("Ticket de cliente impreso", [
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'user_id' => auth()->id(),
                    'total' => $sale->total
                ]);

                return back()->with('success', 'Ticket impreso exitosamente');
            } else {
                return back()->withErrors([
                    'message' => 'Error al imprimir ticket. Verificar impresora.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error imprimiendo ticket de cliente', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors([
                'message' => 'Error interno al procesar el ticket'
            ]);
        }
    }

    /**
     * 🔄 IMPRIMIR TICKET DE DEVOLUCIÓN - VERSIÓN INERTIA
     */
    public function printReturnReceipt(SaleReturn $return)
    {
        try {
            // Verificar que la devolución está completada
            if ($return->status !== 'completed') {
                return back()->withErrors([
                    'message' => 'Solo se pueden imprimir tickets de devoluciones completadas'
                ]);
            }

            // Generar e imprimir ticket de devolución
            $success = $this->ticketService->generateReturnReceipt($return);

            if ($success) {
                Log::info("Ticket de devolución impreso", [
                    'return_id' => $return->id,
                    'return_number' => $return->return_number,
                    'sale_id' => $return->sale_id,
                    'user_id' => auth()->id(),
                    'total_returned' => $return->total_returned
                ]);

                return back()->with('success', 'Ticket de devolución impreso exitosamente');
            } else {
                return back()->withErrors([
                    'message' => 'Error al imprimir ticket de devolución. Verificar impresora.'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error imprimiendo ticket de devolución', [
                'return_id' => $return->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors([
                'message' => 'Error interno al procesar el ticket de devolución'
            ]);
        }
    }

    /**
     * 🎯 IMPRESIÓN AUTOMÁTICA AL COMPLETAR VENTA - VERSIÓN INERTIA
     */
    public function autoprint(Sale $sale, Request $request)
    {
        $request->validate([
            'print_kitchen' => 'boolean',
            'print_customer' => 'boolean',
        ]);

        $results = [];
        $messages = [];

        try {
            // Imprimir comanda de cocina si se solicita
            if ($request->get('print_kitchen', config('thermal_printer.tickets.auto_print_kitchen'))) {
                $kitchenResult = $this->ticketService->generateKitchenOrder($sale);
                $results['kitchen'] = $kitchenResult;
                
                if ($kitchenResult) {
                    $messages[] = 'Comanda enviada a cocina';
                } else {
                    $messages[] = 'Error en comanda de cocina';
                }
            }

            // Imprimir ticket de cliente si se solicita
            if ($request->get('print_customer', config('thermal_printer.tickets.auto_print_customer'))) {
                $customerResult = $this->ticketService->generateCustomerReceipt($sale);
                $results['customer'] = $customerResult;
                
                if ($customerResult) {
                    $messages[] = 'Ticket de cliente impreso';
                } else {
                    $messages[] = 'Error en ticket de cliente';
                }
            }

            // Determinar resultado general
            $allSuccess = collect($results)->every(function ($result) {
                return $result;
            });

            $message = implode('. ', $messages);

            Log::info("Impresión automática procesada", [
                'sale_id' => $sale->id,
                'results' => $results,
                'user_id' => auth()->id()
            ]);

            if ($allSuccess) {
                return back()->with('success', $message ?: 'Impresión automática completada');
            } else {
                return back()->withErrors(['message' => $message ?: 'Error en impresión automática']);
            }

        } catch (\Exception $e) {
            Log::error('Error en impresión automática', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors(['message' => 'Error en impresión automática']);
        }
    }

    /**
     * 🔄 REIMPRIMIR TICKET - VERSIÓN INERTIA
     */
    public function reprint(Sale $sale, Request $request)
    {
        $request->validate([
            'type' => 'required|in:kitchen,customer,both'
        ]);

        try {
            $type = $request->get('type');
            $results = [];
            $messages = [];

            if ($type === 'kitchen' || $type === 'both') {
                $kitchenResult = $this->ticketService->generateKitchenOrder($sale);
                $results['kitchen'] = $kitchenResult;
                
                if ($kitchenResult) {
                    $messages[] = 'Comanda reimpresa';
                } else {
                    $messages[] = 'Error reimprimiendo comanda';
                }
            }

            if ($type === 'customer' || $type === 'both') {
                $customerResult = $this->ticketService->generateCustomerReceipt($sale);
                $results['customer'] = $customerResult;
                
                if ($customerResult) {
                    $messages[] = 'Ticket reimpreso';
                } else {
                    $messages[] = 'Error reimprimiendo ticket';
                }
            }

            Log::info("Reimpresión solicitada", [
                'sale_id' => $sale->id,
                'type' => $type,
                'results' => $results,
                'user_id' => auth()->id()
            ]);

            $allSuccess = collect($results)->every(function ($result) {
                return $result;
            });

            $message = implode('. ', $messages);

            if ($allSuccess) {
                return back()->with('success', 'Reimpresión exitosa: ' . $message);
            } else {
                return back()->withErrors(['message' => 'Reimpresión con errores: ' . $message]);
            }

        } catch (\Exception $e) {
            Log::error('Error en reimpresión', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->withErrors(['message' => 'Error en reimpresión: ' . $e->getMessage()]);
        }
    }

    // 🧪 MÉTODOS DE TESTING Y ESTADÍSTICAS (mantienen JSON porque no son llamados desde Inertia)
    
    /**
     * 🧪 ENDPOINT DE TESTING (Solo en desarrollo)
     */
    public function testPrinters()
    {
        if (app()->environment('production')) {
            abort(404);
        }

        // Este endpoint SÍ puede devolver JSON porque se llama directamente o vía fetch
        try {
            $results = [];
            // ... resto del código de testing igual ...
            return response()->json([
                'success' => true,
                'message' => 'Test de impresoras completado',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en test general: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 OBTENER ESTADÍSTICAS DE IMPRESIÓN
     */
    public function getPrintingStats()
    {
        // Este endpoint SÍ puede devolver JSON porque es una API
        try {
            $today = now()->startOfDay();
            
            $stats = [
                'today' => [
                    'sales_printed' => Sale::where('last_printed_at', '>=', $today)->count(),
                    'returns_printed' => SaleReturn::whereDate('created_at', $today)->count(),
                    'total_tickets' => Sale::where('last_printed_at', '>=', $today)->count() * 2,
                ],
                // ... resto igual ...
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🍳 VERIFICAR SI ITEM REQUIERE COCINA
     */
    private function requiresKitchen($item): bool
    {
        $nonKitchenCategories = config('thermal_printer.non_kitchen_categories', []);
        
        if (isset($item->category_slug)) {
            return !in_array($item->category_slug, $nonKitchenCategories);
        }
        
        return true;
    }

    // 👁️ AGREGAR ESTOS MÉTODOS A TU TicketController.php EXISTENTE

/**
 * 👁️ VISTA PREVIA DE COMANDA DE COCINA (NUEVO)
 */
public function previewKitchenOrder(Sale $sale)
{
    try {
        // 🔧 CARGAR RELACIONES NECESARIAS
        $sale->load(['saleItems.menuItem', 'saleItems.simpleProduct', 'user']);

        // Generar contenido de la comanda
        $content = $this->generateKitchenPreview($sale);

        return response()->json([
            'success' => true,
            'content' => $content,
            'sale_number' => $sale->sale_number
        ]);

    } catch (\Exception $e) {
        \Log::error('Error generando vista previa de cocina', [
            'sale_id' => $sale->id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error generando vista previa: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * 👁️ VISTA PREVIA DE TICKET DE CLIENTE (NUEVO)
 */
public function previewCustomerReceipt(Sale $sale)
{
    try {
        // 🔧 CARGAR RELACIONES NECESARIAS
        $sale->load(['saleItems.menuItem', 'saleItems.simpleProduct', 'user']);

        // Generar contenido del ticket
        $content = $this->generateCustomerPreview($sale);

        return response()->json([
            'success' => true,
            'content' => $content,
            'sale_number' => $sale->sale_number,
            'total' => $sale->total
        ]);

    } catch (\Exception $e) {
        \Log::error('Error generando vista previa de cliente', [
            'sale_id' => $sale->id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error generando vista previa: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * 🔧 GENERAR VISTA PREVIA DE COMANDA DE COCINA (MÉTODO PRIVADO)
 */
private function generateKitchenPreview(Sale $sale): string
{
    $content = "🍔 COMANDA DE COCINA\n";
    $content .= "================================\n";
    $content .= "COMANDA #{$sale->sale_number}\n";
    $content .= "Fecha: " . $sale->created_at->format('d/m/Y H:i') . "\n";
    $content .= "Cajero: {$sale->user->name}\n";
    $content .= "Mesa: Para llevar\n";
    $content .= "--------------------------------\n";
    
    // Items de la orden - SOLO LO QUE SE COCINA
    foreach ($sale->saleItems as $item) {
        if ($this->requiresKitchen($item)) {
            $productName = $this->getProductName($item);
            $content .= "{$item->quantity}x {$productName}\n";
            
            // Notas especiales del item
            if (isset($item->notes) && $item->notes) {
                $content .= "   NOTA: {$item->notes}\n";
            }
            
            $content .= "\n";
        }
    }
    
    $content .= "--------------------------------\n";
    $content .= "⚠️  PRIORIDAD: " . $this->calculatePriority($sale) . "\n";
    $content .= "Hora de orden: " . now()->format('H:i') . "\n";
    $content .= "================================\n";
    
    return $content;
}

/**
 * 🔧 GENERAR VISTA PREVIA DE TICKET DE CLIENTE (MÉTODO PRIVADO)
 */
private function generateCustomerPreview(Sale $sale): string
{
    $restaurantName = env('RESTAURANT_NAME', 'Restaurante Demo');
    $restaurantAddress = env('RESTAURANT_ADDRESS', 'Calle Principal #123');
    $restaurantPhone = env('RESTAURANT_PHONE', '(555) 123-4567');
    $restaurantWebsite = env('RESTAURANT_WEBSITE', 'www.restaurante.com');

    $content = "🧾 TICKET DE CLIENTE\n";
    $content .= "================================\n";
    $content .= "{$restaurantName}\n";
    $content .= "{$restaurantAddress}\n";
    $content .= "Tel: {$restaurantPhone}\n";
    $content .= "================================\n";
    $content .= "Ticket: #{$sale->sale_number}\n";
    $content .= "Fecha: " . $sale->created_at->format('d/m/Y H:i') . "\n";
    $content .= "Cajero: {$sale->user->name}\n";
    $content .= "--------------------------------\n";
    
    // Items de la venta
    foreach ($sale->saleItems as $item) {
        $productName = $this->getProductName($item);
        $line = sprintf("%dx %-20s $%s", 
            $item->quantity, 
            substr($productName, 0, 20),
            number_format($item->unit_price * $item->quantity, 2)
        );
        $content .= $line . "\n";
    }
    
    $content .= "--------------------------------\n";
    $content .= sprintf("%-24s $%s\n", "Subtotal:", number_format($sale->subtotal, 2));
    
    if ($sale->discount > 0) {
        $content .= sprintf("%-24s -$%s\n", "Descuento:", number_format($sale->discount, 2));
    }
    
    if ($sale->tax > 0) {
        $content .= sprintf("%-24s $%s\n", "Impuestos:", number_format($sale->tax, 2));
    }
    
    $content .= sprintf("%-24s $%s\n", "TOTAL:", number_format($sale->total, 2));
    $content .= "--------------------------------\n";
    $content .= sprintf("%-24s %s\n", "Método de pago:", ucfirst($sale->payment_method));
    $content .= "--------------------------------\n";
    $content .= "¡Gracias por su compra!\n";
    $content .= "{$restaurantWebsite}\n";
    $content .= "================================\n";
    
    return $content;
}

/**
 * 🔧 OBTENER NOMBRE DEL PRODUCTO (MÉTODO PRIVADO)
 */
private function getProductName($item): string
{
    if ($item->product_type === 'menu' && $item->menuItem) {
        return $item->menuItem->name;
    } elseif ($item->product_type === 'simple' && $item->simpleProduct) {
        return $item->simpleProduct->name;
    }
    
    return "Producto #{$item->id}";
}

/**
 * ⚠️ CALCULAR PRIORIDAD DE ORDEN (MÉTODO PRIVADO)
 */
private function calculatePriority(Sale $sale): string
{
    $itemCount = $sale->saleItems->sum('quantity');
    
    if ($itemCount >= 10) return 'ALTA';
    if ($itemCount >= 5) return 'MEDIA';
    return 'NORMAL';
}
}