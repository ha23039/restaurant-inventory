<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin y cajero pueden procesar devoluciones
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
            'sale_id' => 'required|exists:sales,id',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'restore_inventory' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sale_id.required' => 'Debe seleccionar una venta',
            'sale_id.exists' => 'La venta seleccionada no existe',
            'reason.required' => 'Debe especificar el motivo de la devolución',
            'items.required' => 'Debe seleccionar al menos un item para devolver',
            'items.*.sale_item_id.exists' => 'El item seleccionado no existe',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1',
        ];
    }
}
