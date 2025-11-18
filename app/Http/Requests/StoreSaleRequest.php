<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin y cajero pueden procesar ventas
        return in_array($this->user()?->role, ['admin', 'cajero']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.product_type' => 'required|in:menu,simple',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:efectivo,tarjeta,transferencia',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Debe agregar al menos un producto a la venta',
            'items.*.id.required' => 'ID de producto requerido',
            'items.*.product_type.in' => 'Tipo de producto inválido',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1',
            'payment_method.required' => 'Debe seleccionar un método de pago',
            'payment_method.in' => 'Método de pago inválido',
        ];
    }
}
