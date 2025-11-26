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
        $isFreeSale = $this->boolean('is_free_sale');

        return [
            'is_free_sale' => 'nullable|boolean',
            'free_sale_description' => $isFreeSale ? 'required|string|min:3|max:500' : 'nullable|string|max:500',
            'free_sale_total' => $isFreeSale ? 'required|numeric|min:0.01' : 'nullable|numeric',
            'items' => $isFreeSale ? 'nullable|array' : 'required|array|min:1',
            'items.*.id' => 'required_unless:items.*.product_type,free|integer',
            'items.*.product_type' => 'required_with:items.*|in:menu,simple,free',
            'items.*.quantity' => 'required_with:items.*|integer|min:1',
            'items.*.unit_price' => 'required_with:items.*|numeric|min:0',
            'items.*.name' => 'required_if:items.*.product_type,free|string|max:100',
            'items.*.price' => 'required_if:items.*.product_type,free|numeric|min:0.01',
            'items.*.category' => 'nullable|string|in:servicio,evento,propina,ajuste,otro',
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
            'free_sale_description.required' => 'Debe proporcionar una descripción para la venta libre',
            'free_sale_description.min' => 'La descripción debe tener al menos 3 caracteres',
            'free_sale_total.required' => 'Debe especificar el monto de la venta libre',
            'free_sale_total.min' => 'El monto debe ser mayor a 0',
        ];
    }
}
