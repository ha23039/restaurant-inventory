<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin y chef pueden actualizar menu items
        return in_array($this->user()?->role, ['admin', 'chef']);
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
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image_path' => 'nullable|string|max:500',
            'is_available' => 'boolean',
            'recipes' => 'nullable|array',
            'recipes.*.product_id' => 'required|exists:products,id',
            'recipes.*.quantity_needed' => 'required|numeric|min:0.001',
            'recipes.*.unit' => 'required|string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del platillo es requerido',
            'price.required' => 'El precio es requerido',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'recipes.*.product_id.exists' => 'El ingrediente seleccionado no existe',
            'recipes.*.quantity_needed.min' => 'La cantidad debe ser mayor a 0',
        ];
    }
}
