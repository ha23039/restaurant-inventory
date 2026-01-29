<?php

namespace App\Http\Controllers;

use App\Models\BusinessSettings;
use App\Models\OrderSettings;
use App\Models\PaymentMethod;
use App\Models\PrinterSettings;
use App\Models\TicketSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/BusinessSettings', [
            'settings' => BusinessSettings::get(),
            'paymentMethods' => PaymentMethod::ordered()->get(),
            'printerSettings' => [
                'kitchen' => PrinterSettings::getKitchenPrinter(),
                'customer' => PrinterSettings::getCustomerPrinter(),
            ],
            'ticketSettings' => TicketSettings::get(),
            'orderSettings' => OrderSettings::get(),
        ]);
    }

    public function update(Request $request)
    {
        // Decode digital_menu_schedule if it's a JSON string
        if ($request->has('digital_menu_schedule') && is_string($request->digital_menu_schedule)) {
            $decoded = json_decode($request->digital_menu_schedule, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['digital_menu_schedule' => $decoded]);
            }
        }

        $validated = $request->validate([
            'restaurant_name' => 'required|string|max:255',
            'restaurant_address' => 'nullable|string|max:500',
            'restaurant_phone' => 'nullable|string|max:20',
            'restaurant_email' => 'nullable|email',
            'restaurant_tax_id' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:png,jpg,svg,webp|max:2048',
            'primary_color' => 'required|regex:/^#([A-Fa-f0-9]{6})$/',
            'secondary_color' => 'required|regex:/^#([A-Fa-f0-9]{6})$/',
            'accent_color' => 'required|regex:/^#([A-Fa-f0-9]{6})$/',
            'welcome_message' => 'nullable|string|max:1000',
            'footer_message' => 'nullable|string|max:1000',
            'currency' => 'required|string|max:10',
            'country_code' => 'nullable|string|max:10',
            'timezone' => 'required|string|max:50',
            'social_media' => 'nullable|array',

            // Digital Menu Fields
            'digital_menu_enabled' => 'boolean',
            'whatsapp_number' => 'nullable|string|max:20',
            'digital_menu_welcome_message' => 'nullable|string|max:1000',
            'digital_menu_closed_message' => 'nullable|string|max:500',
            'min_order_amount' => 'nullable|numeric|min:0',
            'estimated_prep_time' => 'nullable|integer|min:1|max:300',
            'require_phone_verification' => 'boolean',
            'allow_pickup' => 'boolean',
            'allow_delivery' => 'boolean',
            'allow_dine_in' => 'boolean',
            'delivery_fee' => 'nullable|numeric|min:0',
            'delivery_min_amount' => 'nullable|numeric|min:0',
            'digital_menu_schedule' => 'nullable|array',
            'digital_menu_custom_css' => 'nullable|string|max:5000',
        ]);

        $settings = BusinessSettings::get();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($settings->logo_path && Storage::disk('public')->exists(str_replace('/storage/', '', $settings->logo_path))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $settings->logo_path));
            }

            $path = $request->file('logo')->store('settings', 'public');
            $validated['logo_path'] = '/storage/' . $path;
        }

        $settings->update($validated);

        return back()->with('success', 'Configuración actualizada exitosamente');
    }

    /**
     * Update payment methods
     */
    public function updatePaymentMethods(Request $request)
    {
        $validated = $request->validate([
            'methods' => 'required|array',
            'methods.*.id' => 'nullable|exists:payment_methods,id',
            'methods.*.name' => 'required|string|max:50',
            'methods.*.label' => 'required|string|max:100',
            'methods.*.icon' => 'nullable|string|max:50',
            'methods.*.is_active' => 'boolean',
            'methods.*.requires_reference' => 'boolean',
            'methods.*.requires_amount_input' => 'boolean',
            'methods.*.commission_percent' => 'nullable|numeric|min:0|max:100',
            'methods.*.sort_order' => 'integer|min:0',
        ]);

        foreach ($validated['methods'] as $methodData) {
            if (isset($methodData['id'])) {
                PaymentMethod::find($methodData['id'])->update($methodData);
            } else {
                PaymentMethod::create($methodData);
            }
        }

        return back()->with('success', 'Métodos de pago actualizados');
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();
        return back()->with('success', 'Método de pago eliminado');
    }

    /**
     * Update printer settings
     */
    public function updatePrinterSettings(Request $request)
    {
        $validated = $request->validate([
            'kitchen' => 'required|array',
            'kitchen.ip_address' => 'nullable|ip',
            'kitchen.port' => 'nullable|integer|min:1|max:65535',
            'kitchen.width_mm' => 'required|in:58,80',
            'kitchen.is_enabled' => 'boolean',
            'kitchen.auto_print' => 'boolean',
            'customer' => 'required|array',
            'customer.ip_address' => 'nullable|ip',
            'customer.port' => 'nullable|integer|min:1|max:65535',
            'customer.width_mm' => 'required|in:58,80',
            'customer.is_enabled' => 'boolean',
            'customer.auto_print' => 'boolean',
        ]);

        // Update kitchen printer
        PrinterSettings::updateOrCreate(
            ['type' => 'kitchen'],
            array_merge($validated['kitchen'], ['type' => 'kitchen', 'name' => 'Cocina'])
        );

        // Update customer printer
        PrinterSettings::updateOrCreate(
            ['type' => 'customer'],
            array_merge($validated['customer'], ['type' => 'customer', 'name' => 'Cliente'])
        );

        return back()->with('success', 'Configuración de impresoras actualizada');
    }

    /**
     * Update ticket settings
     */
    public function updateTicketSettings(Request $request)
    {
        $validated = $request->validate([
            // Ticket de cliente
            'show_logo' => 'boolean',
            'show_address' => 'boolean',
            'show_phone' => 'boolean',
            'show_qr_code' => 'boolean',
            'qr_content' => 'nullable|string|in:sale_number,sale_url,menu_url,custom',
            'header_message' => 'nullable|string|max:500',
            'footer_message' => 'nullable|string|max:500',
            // Ticket de cocina
            'kitchen_show_customer_name' => 'boolean',
            'kitchen_show_table_number' => 'boolean',
            'kitchen_show_notes' => 'boolean',
            'kitchen_show_order_number' => 'boolean',
            'kitchen_font_size' => 'nullable|string|in:small,normal,large',
            // Categorías y prioridades
            'non_kitchen_categories' => 'nullable|array',
            'priority_high_threshold' => 'nullable|integer|min:1',
            'priority_medium_threshold' => 'nullable|integer|min:1',
        ]);

        TicketSettings::updateOrCreate(['id' => 1], $validated);

        return back()->with('success', 'Configuración de tickets actualizada');
    }

    /**
     * Update order settings
     */
    public function updateOrderSettings(Request $request)
    {
        $validated = $request->validate([
            'order_number_prefix' => 'nullable|string|max:10',
            'order_number_padding' => 'required|integer|min:1|max:10',
            'order_number_reset' => 'required|in:never,daily,monthly,yearly',
            'allow_dine_in' => 'boolean',
            'allow_takeout' => 'boolean',
            'allow_delivery' => 'boolean',
            'allow_scheduled' => 'boolean',
            'tax_included' => 'boolean',
            'tax_percentage' => 'required|numeric|min:0|max:100',
            'show_tax_breakdown' => 'boolean',
            'tip_enabled' => 'boolean',
            'tip_suggestions' => 'nullable|array',
            'tip_mandatory_above' => 'nullable|integer|min:0',
            'require_customer_name' => 'boolean',
            'require_customer_phone' => 'boolean',
            'allow_notes_per_item' => 'boolean',
            'default_prep_time_minutes' => 'required|integer|min:1|max:300',
            'min_order_amount' => 'nullable|numeric|min:0',
            'delivery_min_amount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
        ]);

        OrderSettings::updateOrCreate(['id' => 1], $validated);

        return back()->with('success', 'Configuración de pedidos actualizada');
    }
}
