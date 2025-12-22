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
            ['phone' => $validated['phone']],
            [
                'country_code' => $validated['country_code'],
                'restaurant_id' => 1,
            ]
        );

        $code = $customer->generateVerificationCode();

        $settings = BusinessSettings::get();
        $whatsappNumber = $settings->whatsapp_number;
        $message = urlencode("Mi codigo de verificacion es: {$code}");
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$message}";

        return response()->json([
            'success' => true,
            'whatsapp_url' => $whatsappUrl,
            'customer_id' => $customer->id,
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
