<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSimpleProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin y almacenero pueden crear productos simples
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'cost_per_unit' => 'required|numeric|min:0.001',
            'sale_price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'product_id.required' => 'Debe seleccionar un producto base',
            'product_id.exists' => 'El producto base seleccionado no existe',
            'cost_per_unit.required' => 'El costo por unidad es requerido',
            'cost_per_unit.min' => 'El costo debe ser mayor a 0',
            'sale_price.required' => 'El precio de venta es requerido',
            'category_id.required' => 'Debe seleccionar una categoría',
        ];
    }
}
