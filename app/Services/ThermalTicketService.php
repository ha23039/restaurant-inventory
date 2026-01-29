<?php

namespace App\Services;

use App\Models\PrinterSettings;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\TicketSettings;
use Carbon\Carbon;
use Exception;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ThermalTicketService
{
    private $printer;

    private $config;

    private $kitchenPrinterConfig;

    private $customerPrinterConfig;

    private $ticketSettings;

    public function __construct()
    {
        // Usar el archivo de configuración como fallback
        $this->config = config('thermal_printer');

        // Obtener configuración de impresoras desde BD (con fallback a .env)
        $this->kitchenPrinterConfig = PrinterSettings::getKitchenPrinter();
        $this->customerPrinterConfig = PrinterSettings::getCustomerPrinter();

        // Obtener configuración de tickets desde BD (con fallback a config)
        $this->ticketSettings = TicketSettings::get();
    }

    /**
     * 🍳 GENERAR COMANDA PARA COCINA
     */
    public function generateKitchenOrder(Sale $sale): bool
    {
        try {
            // Verificar si la impresora está habilitada (desde BD o .env)
            if (!$this->kitchenPrinterConfig['enabled']) {
                \Log::info('Impresora de cocina deshabilitada, saltando impresión');
                return true; // Silenciosamente salir si está deshabilitada
            }

            // 🚀 MODO DESARROLLO: Simular éxito y crear archivo
            if (app()->environment(['local', 'development'])) {
                return $this->simulateKitchenOrder($sale);
            }

            // Conectar a impresora de cocina (58mm)
            $this->connectToKitchenPrinter();

            // Header de la comanda
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("🍔 COCINA\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("================================\n");

            // Información de la orden
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("COMANDA #{$sale->sale_number}\n");
            $this->printer->text('Fecha: ' . Carbon::parse($sale->created_at)->format('d/m/Y H:i') . "\n");
            $this->printer->text("Cajero: {$sale->user->name}\n");
            $this->printer->text('Mesa: ' . ($sale->table_number ?? 'Para llevar') . "\n");
            $this->printer->text("--------------------------------\n");

            // Items de la orden - SOLO LO QUE SE COCINA
            foreach ($sale->saleItems as $item) {
                if ($this->requiresKitchen($item)) {
                    $this->printer->setTextSize(1, 2);
                    $this->printer->text("{$item->quantity}x {$item->menu_item_name}\n");
                    $this->printer->setTextSize(1, 1);

                    // Notas especiales del item
                    if ($item->notes) {
                        $this->printer->text("   NOTA: {$item->notes}\n");
                    }

                    // Modificadores/extras
                    if ($item->modifiers) {
                        foreach (json_decode($item->modifiers, true) ?? [] as $modifier) {
                            $this->printer->text("   - {$modifier}\n");
                        }
                    }

                    $this->printer->text("\n");
                }
            }

            $this->printer->text("--------------------------------\n");

            // Prioridad y tiempo
            $priority = $this->calculatePriority($sale);
            $this->printer->setTextSize(1, 2);
            $this->printer->text("⚠️  PRIORIDAD: {$priority}\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text('Hora de orden: ' . Carbon::now()->format('H:i') . "\n");

            $this->printer->text("================================\n");
            $this->printer->cut();
            $this->printer->close();

            return true;

        } catch (Exception $e) {
            \Log::error('Error generando comanda de cocina: ' . $e->getMessage());

            // 🚀 FALLBACK EN DESARROLLO: Intentar simular
            if (app()->environment(['local', 'development'])) {
                return $this->simulateKitchenOrder($sale);
            }

            return false;
        }
    }

    /**
     * 🧾 GENERAR TICKET PARA CLIENTE
     */
    public function generateCustomerReceipt(Sale $sale): bool
    {
        try {
            // Verificar si la impresora está habilitada (desde BD o .env)
            if (!$this->customerPrinterConfig['enabled']) {
                \Log::info('Impresora de cliente deshabilitada, saltando impresión');
                return true; // Silenciosamente salir si está deshabilitada
            }

            // 🚀 MODO DESARROLLO: Simular éxito y crear archivo
            if (app()->environment(['local', 'development'])) {
                return $this->simulateCustomerReceipt($sale);
            }

            // Conectar a impresora de cliente (80mm)
            $this->connectToCustomerPrinter();

            // Header del restaurante
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("{$this->config['restaurant']['name']}\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("{$this->config['restaurant']['address']}\n");
            $this->printer->text("Tel: {$this->config['restaurant']['phone']}\n");
            $this->printer->text("================================\n");

            // Información del ticket
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Ticket: #{$sale->sale_number}\n");
            $this->printer->text('Fecha: ' . Carbon::parse($sale->created_at)->format('d/m/Y H:i') . "\n");
            $this->printer->text("Cajero: {$sale->user->name}\n");
            if ($sale->customer_name) {
                $this->printer->text("Cliente: {$sale->customer_name}\n");
            }
            $this->printer->text("--------------------------------\n");

            // Items de la venta
            foreach ($sale->saleItems as $item) {
                // Línea del producto
                $line = sprintf(
                    '%dx %-20s $%s',
                    $item->quantity,
                    substr($item->menu_item_name, 0, 20),
                    number_format($item->subtotal, 2)
                );
                $this->printer->text($line . "\n");

                // Notas del item
                if ($item->notes) {
                    $this->printer->text("   Nota: {$item->notes}\n");
                }
            }

            $this->printer->text("--------------------------------\n");

            // Totales
            $this->printer->text(sprintf("%-24s $%s\n", 'Subtotal:', number_format($sale->subtotal, 2)));

            if ($sale->discount > 0) {
                $this->printer->text(sprintf("%-24s -$%s\n", 'Descuento:', number_format($sale->discount, 2)));
            }

            if ($sale->tax > 0) {
                $this->printer->text(sprintf("%-24s $%s\n", 'Impuestos:', number_format($sale->tax, 2)));
            }

            $this->printer->setTextSize(1, 2);
            $this->printer->text(sprintf("%-24s $%s\n", 'TOTAL:', number_format($sale->total, 2)));
            $this->printer->setTextSize(1, 1);

            $this->printer->text("--------------------------------\n");

            // Información de pago
            $this->printer->text(sprintf("%-24s %s\n", 'Método de pago:', ucfirst($sale->payment_method)));

            if ($sale->payment_method === 'efectivo' && $sale->cash_received) {
                $this->printer->text(sprintf("%-24s $%s\n", 'Recibido:', number_format($sale->cash_received, 2)));
                $change = $sale->cash_received - $sale->total;
                if ($change > 0) {
                    $this->printer->text(sprintf("%-24s $%s\n", 'Cambio:', number_format($change, 2)));
                }
            }

            $this->printer->text("--------------------------------\n");

            // QR Code para rastreo
            $this->generateQRCode($sale);

            // Footer
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("¡Gracias por su compra!\n");
            $this->printer->text("{$this->config['restaurant_website']}\n");
            $this->printer->text("================================\n");

            $this->printer->cut();
            $this->printer->close();

            return true;

        } catch (Exception $e) {
            \Log::error('Error generando ticket de cliente: ' . $e->getMessage());

            // 🚀 FALLBACK EN DESARROLLO: Intentar simular
            if (app()->environment(['local', 'development'])) {
                return $this->simulateCustomerReceipt($sale);
            }

            return false;
        }
    }

    /**
     * 🚀 SIMULACIÓN PARA DESARROLLO - COMANDA DE COCINA
     */
    private function simulateKitchenOrder(Sale $sale): bool
    {
        try {
            $ticketPath = storage_path('app/tickets/');

            // Crear directorio si no existe
            if (!is_dir($ticketPath)) {
                mkdir($ticketPath, 0755, true);
            }

            $fileName = 'kitchen_' . $sale->sale_number . '_' . time() . '.txt';
            $filePath = $ticketPath . $fileName;

            $content = "🍔 COMANDA DE COCINA (SIMULACIÓN)\n";
            $content .= "================================\n";
            $content .= "COMANDA #{$sale->sale_number}\n";
            $content .= 'Fecha: ' . Carbon::parse($sale->created_at)->format('d/m/Y H:i') . "\n";
            $content .= "Cajero: {$sale->user->name}\n";
            $content .= "Mesa: Para llevar\n";
            $content .= "--------------------------------\n";

            // Items de la orden
            foreach ($sale->saleItems as $item) {
                if ($this->requiresKitchen($item)) {
                    $productName = $this->getProductName($item);
                    $content .= "{$item->quantity}x {$productName}\n";

                    // Notas especiales del item
                    if (isset($item->notes) && $item->notes) {
                        $content .= "   NOTA: {$item->notes}\n";
                    }
                }
            }

            $content .= "--------------------------------\n";
            $content .= '⚠️  PRIORIDAD: ' . $this->calculatePriority($sale) . "\n";
            $content .= 'Hora de orden: ' . Carbon::now()->format('H:i') . "\n";
            $content .= "================================\n";
            $content .= "📝 ARCHIVO SIMULADO EN DESARROLLO\n";
            $content .= "📂 Guardado en: {$fileName}\n";

            file_put_contents($filePath, $content);

            \Log::info('🚀 Comanda simulada generada', [
                'sale_id' => $sale->id,
                'file' => $fileName,
                'path' => $filePath,
            ]);

            return true;

        } catch (Exception $e) {
            \Log::error('Error simulando comanda de cocina: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * 🚀 SIMULACIÓN PARA DESARROLLO - TICKET DE CLIENTE
     */
    private function simulateCustomerReceipt(Sale $sale): bool
    {
        try {
            $ticketPath = storage_path('app/tickets/');

            // Crear directorio si no existe
            if (!is_dir($ticketPath)) {
                mkdir($ticketPath, 0755, true);
            }

            $fileName = 'customer_' . $sale->sale_number . '_' . time() . '.txt';
            $filePath = $ticketPath . $fileName;

            $content = "🧾 TICKET DE CLIENTE (SIMULACIÓN)\n";
            $content .= "================================\n";
            $content .= "{$this->config['restaurant']['name']}\n";
            $content .= "{$this->config['restaurant']['address']}\n";
            $content .= "Tel: {$this->config['restaurant']['phone']}\n";
            $content .= "================================\n";
            $content .= "Ticket: #{$sale->sale_number}\n";
            $content .= 'Fecha: ' . Carbon::parse($sale->created_at)->format('d/m/Y H:i') . "\n";
            $content .= "Cajero: {$sale->user->name}\n";
            $content .= "--------------------------------\n";

            // Items de la venta
            foreach ($sale->saleItems as $item) {
                $productName = $this->getProductName($item);
                $line = sprintf(
                    '%dx %-20s $%s',
                    $item->quantity,
                    substr($productName, 0, 20),
                    number_format($item->unit_price * $item->quantity, 2)
                );
                $content .= $line . "\n";
            }

            $content .= "--------------------------------\n";
            $content .= sprintf("%-24s $%s\n", 'Subtotal:', number_format($sale->subtotal, 2));

            if ($sale->discount > 0) {
                $content .= sprintf("%-24s -$%s\n", 'Descuento:', number_format($sale->discount, 2));
            }

            if ($sale->tax > 0) {
                $content .= sprintf("%-24s $%s\n", 'Impuestos:', number_format($sale->tax, 2));
            }

            $content .= sprintf("%-24s $%s\n", 'TOTAL:', number_format($sale->total, 2));
            $content .= "--------------------------------\n";
            $content .= sprintf("%-24s %s\n", 'Método de pago:', ucfirst($sale->payment_method));
            $content .= "--------------------------------\n";
            $content .= "¡Gracias por su compra!\n";
            $content .= "{$this->config['restaurant_website']}\n";
            $content .= "================================\n";
            $content .= "📝 ARCHIVO SIMULADO EN DESARROLLO\n";
            $content .= "📂 Guardado en: {$fileName}\n";

            file_put_contents($filePath, $content);

            \Log::info('🚀 Ticket de cliente simulado generado', [
                'sale_id' => $sale->id,
                'file' => $fileName,
                'path' => $filePath,
            ]);

            return true;

        } catch (Exception $e) {
            \Log::error('Error simulando ticket de cliente: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * 🔄 GENERAR TICKET DE DEVOLUCIÓN
     */
    public function generateReturnReceipt(SaleReturn $return): bool
    {
        try {
            // Verificar si la impresora está habilitada
            if (!($this->config['customer_printer']['enabled'] ?? true)) {
                return true; // Silenciosamente salir si está deshabilitada
            }

            // 🚀 MODO DESARROLLO: Simular si no hay impresora
            if (app()->environment(['local', 'development'])) {
                return $this->simulateReturnReceipt($return);
            }

            $this->connectToCustomerPrinter();

            // Header
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->setTextSize(2, 2);
            $this->printer->text("🔄 DEVOLUCIÓN\n");
            $this->printer->setTextSize(1, 1);
            $this->printer->text("{$this->config['restaurant_name']}\n");
            $this->printer->text("================================\n");

            // Información de la devolución
            $this->printer->setJustification(Printer::JUSTIFY_LEFT);
            $this->printer->text("Devolución: #{$return->return_number}\n");
            $this->printer->text("Venta Original: #{$return->sale->sale_number}\n");
            $this->printer->text('Fecha: ' . Carbon::parse($return->return_date)->format('d/m/Y H:i') . "\n");
            $this->printer->text("Procesado por: {$return->processedByUser->name}\n");
            $this->printer->text("--------------------------------\n");

            // Items devueltos
            foreach ($return->returnItems as $item) {
                $line = sprintf(
                    '%dx %-20s -$%s',
                    $item->quantity,
                    substr($item->menu_item_name, 0, 20),
                    number_format($item->subtotal, 2)
                );
                $this->printer->text($line . "\n");
            }

            $this->printer->text("--------------------------------\n");

            // Total devuelto
            $this->printer->setTextSize(1, 2);
            $this->printer->text(sprintf("%-24s -$%s\n", 'TOTAL DEVUELTO:', number_format($return->total_returned, 2)));
            $this->printer->setTextSize(1, 1);

            // Método de reembolso
            $this->printer->text(sprintf("%-24s %s\n", 'Reembolso:', ucfirst($return->refund_method)));
            $this->printer->text(sprintf("%-24s %s\n", 'Razón:', $this->getReasonText($return->reason)));

            $this->printer->text("--------------------------------\n");
            $this->printer->setJustification(Printer::JUSTIFY_CENTER);
            $this->printer->text("Devolución procesada\n");
            $this->printer->text("================================\n");

            $this->printer->cut();
            $this->printer->close();

            return true;

        } catch (Exception $e) {
            \Log::error('Error generando ticket de devolución: ' . $e->getMessage());

            // 🚀 FALLBACK EN DESARROLLO
            if (app()->environment(['local', 'development'])) {
                return $this->simulateReturnReceipt($return);
            }

            return false;
        }
    }

    /**
     * 🚀 SIMULACIÓN PARA DESARROLLO - TICKET DE DEVOLUCIÓN
     */
    private function simulateReturnReceipt(SaleReturn $return): bool
    {
        try {
            $ticketPath = storage_path('app/tickets/');

            if (!is_dir($ticketPath)) {
                mkdir($ticketPath, 0755, true);
            }

            $fileName = 'return_' . $return->return_number . '_' . time() . '.txt';
            $filePath = $ticketPath . $fileName;

            $content = "🔄 TICKET DE DEVOLUCIÓN (SIMULACIÓN)\n";
            $content .= "================================\n";
            $content .= "Devolución: #{$return->return_number}\n";
            $content .= "Venta Original: #{$return->sale->sale_number}\n";
            $content .= 'Fecha: ' . Carbon::parse($return->return_date)->format('d/m/Y H:i') . "\n";
            $content .= "--------------------------------\n";
            $content .= sprintf("%-24s -$%s\n", 'TOTAL DEVUELTO:', number_format($return->total_returned, 2));
            $content .= sprintf("%-24s %s\n", 'Reembolso:', ucfirst($return->refund_method));
            $content .= "================================\n";
            $content .= "📝 ARCHIVO SIMULADO EN DESARROLLO\n";
            $content .= "📂 Guardado en: {$fileName}\n";

            file_put_contents($filePath, $content);

            \Log::info('🚀 Ticket de devolución simulado generado', [
                'return_id' => $return->id,
                'file' => $fileName,
            ]);

            return true;

        } catch (Exception $e) {
            \Log::error('Error simulando ticket de devolución: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * 🔌 CONECTAR A IMPRESORA DE COCINA
     */
    private function connectToKitchenPrinter(): void
    {
        if (env('APP_ENV') === 'production') {
            $config = $this->kitchenPrinterConfig;

            if ($config['connection_type'] === 'windows_share' && $config['printer_name']) {
                $connector = new WindowsPrintConnector($config['printer_name']);
            } else {
                // Conectar por red (default)
                $connector = new NetworkPrintConnector($config['ip'], $config['port']);
            }
        } else {
            // Archivo temporal en desarrollo
            $connector = new FilePrintConnector(storage_path('app/tickets/kitchen_' . time() . '.txt'));
        }

        $this->printer = new Printer($connector);
    }

    /**
     * 🔌 CONECTAR A IMPRESORA DE CLIENTE
     */
    private function connectToCustomerPrinter(): void
    {
        if (env('APP_ENV') === 'production') {
            $config = $this->customerPrinterConfig;

            if ($config['connection_type'] === 'windows_share' && $config['printer_name']) {
                $connector = new WindowsPrintConnector($config['printer_name']);
            } elseif (PHP_OS_FAMILY === 'Windows' && $config['printer_name']) {
                $connector = new WindowsPrintConnector($config['printer_name']);
            } else {
                // Conectar por red (default)
                $connector = new NetworkPrintConnector($config['ip'], $config['port']);
            }
        } else {
            // Archivo temporal en desarrollo
            $connector = new FilePrintConnector(storage_path('app/tickets/customer_' . time() . '.txt'));
        }

        $this->printer = new Printer($connector);
    }

    /**
     * 📱 GENERAR CÓDIGO QR
     */
    private function generateQRCode(Sale $sale): void
    {
        try {
            $qrData = route('sales.show', $sale->id);
            $qrPath = storage_path('app/temp/qr_' . $sale->id . '.png');

            // Crear directorio si no existe
            $tempDir = storage_path('app/temp/');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            QrCode::format('png')
                ->size(150)
                ->generate($qrData, $qrPath);

            $image = EscposImage::load($qrPath);
            $this->printer->bitImage($image);
            $this->printer->text("\nEscanear para detalles\n");

            // Limpiar archivo temporal
            unlink($qrPath);

        } catch (Exception $e) {
            \Log::warning('No se pudo generar QR: ' . $e->getMessage());
        }
    }

    /**
     * 🍳 VERIFICAR SI ITEM REQUIERE COCINA
     */
    private function requiresKitchen($item): bool
    {
        // Obtener categorías que NO van a cocina desde configuración
        $nonKitchenCategories = $this->ticketSettings['non_kitchen_categories'] ?? [];

        $categorySlug = strtolower($item->category_slug ?? '');

        return !in_array($categorySlug, array_map('strtolower', $nonKitchenCategories));
    }

    /**
     * ⚠️ CALCULAR PRIORIDAD DE ORDEN
     */
    private function calculatePriority(Sale $sale): string
    {
        $itemCount = $sale->saleItems->sum('quantity');

        $highThreshold = $this->ticketSettings['priority_high_threshold'] ?? 10;
        $mediumThreshold = $this->ticketSettings['priority_medium_threshold'] ?? 5;

        if ($itemCount >= $highThreshold) {
            return 'ALTA';
        }
        if ($itemCount >= $mediumThreshold) {
            return 'MEDIA';
        }

        return 'NORMAL';
    }

    /**
     * 📝 OBTENER TEXTO DE RAZÓN DE DEVOLUCIÓN
     */
    private function getReasonText($reason): string
    {
        $reasons = [
            'defective' => 'Producto defectuoso',
            'wrong_order' => 'Orden incorrecta',
            'customer_request' => 'Solicitud del cliente',
            'error' => 'Error del sistema',
            'other' => 'Otra razón',
        ];

        return $reasons[$reason] ?? $reason;
    }

    /**
     * 🔧 OBTENER NOMBRE DEL PRODUCTO
     */
    private function getProductName($item): string
    {
        if ($item->product_type === 'menu' && isset($item->menuItem)) {
            return $item->menuItem->name;
        } elseif ($item->product_type === 'variant' && isset($item->menuItemVariant)) {
            // Para variantes, mostrar nombre del platillo padre + nombre de variante
            $parentName = $item->menuItemVariant->menuItem->name ?? '';
            $variantName = $item->menuItemVariant->variant_name;
            return $parentName ? "{$parentName} - {$variantName}" : $variantName;
        } elseif ($item->product_type === 'simple' && isset($item->simpleProduct)) {
            return $item->simpleProduct->name;
        } elseif ($item->product_type === 'free' && $item->free_sale_name) {
            return $item->free_sale_name;
        }

        return "Producto #{$item->id}";
    }
}
