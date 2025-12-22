<?php

namespace App\Http\Controllers\DigitalMenu;

use App\Http\Controllers\Controller;
use App\Models\BusinessSettings;
use App\Models\DigitalCustomer;
use App\Models\MenuItem;
use App\Models\MenuItemVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SimpleProduct;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    /**
     * Create a new order from digital menu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:menu,simple,variant',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_method' => 'required|in:pickup,delivery,dine_in',
            'customer_address' => 'required_if:delivery_method,delivery|nullable|string|max:500',
            'customer_notes' => 'nullable|string|max:500',
            'table_id' => 'required_if:delivery_method,dine_in|nullable|integer|exists:tables,id',
        ]);

        $customerId = Session::get('digital_customer_id');

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesion expirada, por favor inicia sesion nuevamente',
            ], 401);
        }

        $customer = DigitalCustomer::findOrFail($customerId);
        $settings = BusinessSettings::get();

        if (!$settings->isDigitalMenuOpen()) {
            return response()->json([
                'success' => false,
                'message' => $settings->digital_menu_closed_message,
            ], 422);
        }

        DB::beginTransaction();

        try {
            $subtotal = 0;
            $itemsData = [];

            // Process each item and calculate total
            foreach ($validated['items'] as $item) {
                $product = $this->resolveProduct($item['type'], $item['id']);

                if (!$product) {
                    throw new \Exception("Producto no encontrado");
                }

                // Check stock availability
                if ($product->available_quantity < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: " . $product->name);
                }

                $unitPrice = $this->getProductPrice($product, $item['type']);
                $totalPrice = $unitPrice * $item['quantity'];
                $subtotal += $totalPrice;

                $itemsData[] = [
                    'type' => $item['type'],
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            // Calculate delivery fee
            $deliveryFee = $validated['delivery_method'] === 'delivery' ? $settings->delivery_fee : 0;
            $total = $subtotal + $deliveryFee;

            // Check minimum order amount
            if ($total < $settings->min_order_amount) {
                throw new \Exception("El pedido minimo es de \${$settings->min_order_amount}");
            }

            // Create sale
            $sale = Sale::create([
                'restaurant_id' => 1,
                'sale_number' => $this->generateSaleNumber(),
                'source' => 'digital_menu',
                'status' => 'pendiente',
                'digital_customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->full_phone,
                'delivery_method' => $validated['delivery_method'],
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax' => 0,
                'total' => $total,
                'payment_method' => 'efectivo',
                'user_id' => null,
                'table_id' => $validated['table_id'] ?? null,
                'estimated_ready_at' => now()->addMinutes($settings->estimated_prep_time),
            ]);

            // Create sale items
            foreach ($itemsData as $itemData) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_type' => $itemData['type'],
                    'menu_item_id' => $itemData['type'] === 'menu' ? $itemData['product']->id : null,
                    'menu_item_variant_id' => $itemData['type'] === 'variant' ? $itemData['product']->id : null,
                    'simple_product_id' => $itemData['type'] === 'simple' ? $itemData['product']->id : null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['total_price'],
                ]);
            }

            // Update customer stats
            $customer->incrementOrderStats($total);

            // If dine-in, occupy table
            if ($validated['table_id']) {
                $table = Table::find($validated['table_id']);
                if ($table) {
                    $table->update([
                        'status' => 'ocupada',
                        'current_sale_id' => $sale->id,
                        'last_occupied_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Build WhatsApp confirmation message
            $whatsappMessage = $this->buildWhatsAppMessage($sale, $itemsData);
            $whatsappUrl = "https://wa.me/{$settings->whatsapp_number}?text=" . urlencode($whatsappMessage);

            return response()->json([
                'success' => true,
                'sale' => [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'total' => $sale->total,
                    'estimated_ready_at' => $sale->estimated_ready_at,
                ],
                'whatsapp_url' => $whatsappUrl,
                'tracking_url' => route('digital-menu.order.show', $sale->sale_number),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Resolve product by type and ID
     */
    private function resolveProduct(string $type, int $id)
    {
        return match ($type) {
            'menu' => MenuItem::find($id),
            'variant' => MenuItemVariant::find($id),
            'simple' => SimpleProduct::find($id),
            default => null,
        };
    }

    /**
     * Get product price by type
     */
    private function getProductPrice($product, string $type): float
    {
        return match ($type) {
            'menu' => (float) $product->price,
            'variant' => (float) $product->price,
            'simple' => (float) $product->sale_price,
            default => 0,
        };
    }

    /**
     * Generate unique sale number for digital menu
     */
    private function generateSaleNumber(): string
    {
        $prefix = 'DM';
        $date = now()->format('Ymd');
        $lastSale = Sale::where('sale_number', 'like', "{$prefix}{$date}%")
            ->orderBy('sale_number', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->sale_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}{$date}{$newNumber}";
    }

    /**
     * Build WhatsApp confirmation message
     */
    private function buildWhatsAppMessage(Sale $sale, array $items): string
    {
        $trackingUrl = route('digital-menu.order.show', $sale->sale_number);

        $lines = [
            "Nuevo Pedido #{$sale->sale_number}",
            "",
            "Cliente: {$sale->customer_name}",
            "Telefono: {$sale->customer_phone}",
            "Entrega: " . $this->getDeliveryMethodLabel($sale->delivery_method),
        ];

        if ($sale->customer_address) {
            $lines[] = "Direccion: {$sale->customer_address}";
        }

        if ($sale->table_id) {
            $table = Table::find($sale->table_id);
            if ($table) {
                $lines[] = "Mesa: {$table->table_number}";
            }
        }

        $lines[] = "";
        $lines[] = "Productos:";

        foreach ($items as $item) {
            $name = $item['type'] === 'variant'
                ? $item['product']->menuItem->name . ' - ' . $item['product']->variant_name
                : $item['product']->name;
            $lines[] = "- {$item['quantity']}x {$name} (\${$item['total_price']})";
        }

        $lines[] = "";
        $lines[] = "Total: \${$sale->total}";

        if ($sale->customer_notes) {
            $lines[] = "";
            $lines[] = "Notas: {$sale->customer_notes}";
        }

        $lines[] = "";
        $lines[] = "Rastrea tu orden aqui:";
        $lines[] = $trackingUrl;

        return implode("\n", $lines);
    }

    /**
     * Get delivery method label in Spanish
     */
    private function getDeliveryMethodLabel(string $method): string
    {
        return match ($method) {
            'pickup' => 'Para llevar',
            'delivery' => 'Delivery',
            'dine_in' => 'Comer aqui',
            default => $method,
        };
    }
}
