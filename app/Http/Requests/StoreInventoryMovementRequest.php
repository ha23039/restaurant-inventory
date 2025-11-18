<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryMovementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin y almacenero pueden registrar movimientos de inventario
        return in_array($this->user()?->role, ['admin', 'almacenero']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:entrada,salida,ajuste',
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'required|numeric|min:0',
            'reason' => 'required|in:compra,venta_automatica,ajuste_inventario,merma,devolucion,otro',
            'notes' => 'nullable|string',
            'movement_date' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Debe seleccionar un producto',
            'product_id.exists' => 'El producto seleccionado no existe',
            'movement_type.required' => 'El tipo de movimiento es requerido',
            'movement_type.in' => 'Tipo de movimiento inválido',
            'quantity.required' => 'La cantidad es requerida',
            'quantity.min' => 'La cantidad debe ser mayor a 0',
            'unit_cost.required' => 'El costo unitario es requerido',
            'reason.required' => 'El motivo es requerido',
            'reason.in' => 'Motivo inválido',
            'movement_date.required' => 'La fecha es requerida',
        ];
    }
}
