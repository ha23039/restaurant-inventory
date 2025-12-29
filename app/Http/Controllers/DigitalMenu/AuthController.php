<?php

namespace App\Http\Controllers\DigitalMenu;

use App\Http\Controllers\Controller;
use App\Models\BusinessSettings;
use App\Models\DigitalCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Request verification code
     */
    public function requestCode(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|min:8|max:15',
            'country_code' => 'required|string|max:5',
        ]);

        $customer = DigitalCustomer::firstOrCreate(
            ['phone' => $validated['phone'], 'country_code' => $validated['country_code']],
            [
                'country_code' => $validated['country_code'],
                'restaurant_id' => 1,
            ]
        );

        $isNewCustomer = !$customer->name;
        $code = $customer->generateVerificationCode();

        $settings = BusinessSettings::get();
        $whatsappNumber = $settings->whatsapp_number;
        $customerFullPhone = $validated['country_code'] . $validated['phone'];

        // URL para enviar el código al cliente por WhatsApp
        $message = urlencode("🔐 Tu código de verificación es: *{$code}*\n\nVálido por 10 minutos.\n\n- {$settings->restaurant_name}");
        $whatsappUrlToCustomer = "https://wa.me/{$customerFullPhone}?text={$message}";

        return response()->json([
            'success' => true,
            'customer_id' => $customer->id,
            'is_new_customer' => $isNewCustomer,
            'verification_code' => $code, // Para mostrar en pantalla
            'whatsapp_url' => $whatsappUrlToCustomer, // Para enviar opcional
        ]);
    }

    /**
     * Verify code and authenticate customer
     */
    public function verifyCode(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:digital_customers,id',
            'code' => 'required|string|size:6',
            'name' => 'nullable|string|max:255',
        ]);

        $customer = DigitalCustomer::findOrFail($validated['customer_id']);

        if (!$customer->verifyCode($validated['code'])) {
            return response()->json([
                'success' => false,
                'message' => 'Codigo invalido o expirado',
            ], 422);
        }

        if (!$customer->name && isset($validated['name'])) {
            $customer->update(['name' => $validated['name']]);
        }

        Session::put('digital_customer_id', $customer->id);

        return response()->json([
            'success' => true,
            'customer' => $customer->only(['id', 'name', 'phone', 'country_code']),
        ]);
    }

    /**
     * Logout customer
     */
    public function logout()
    {
        Session::forget('digital_customer_id');

        return response()->json(['success' => true]);
    }

    /**
     * Get current authenticated customer
     */
    public function me()
    {
        $customerId = Session::get('digital_customer_id');

        if (!$customerId) {
            return response()->json([
                'authenticated' => false,
            ]);
        }

        $customer = DigitalCustomer::find($customerId);

        if (!$customer) {
            Session::forget('digital_customer_id');
            return response()->json([
                'authenticated' => false,
            ]);
        }

        return response()->json([
            'authenticated' => true,
            'customer' => $customer->only(['id', 'name', 'phone', 'country_code']),
        ]);
    }
}
