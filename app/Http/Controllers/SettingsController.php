<?php

namespace App\Http\Controllers;

use App\Models\BusinessSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/BusinessSettings', [
            'settings' => BusinessSettings::get(),
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
}
