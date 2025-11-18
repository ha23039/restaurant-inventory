<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashFlowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin puede registrar flujo de efectivo manual
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:entrada,salida',
            'category' => 'required|in:ventas,compras,gastos_operativos,gastos_admin,devoluciones,otros',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'flow_date' => 'required|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de flujo es requerido',
            'type.in' => 'El tipo debe ser entrada o salida',
            'category.required' => 'La categoría es requerida',
            'category.in' => 'Categoría inválida',
            'amount.required' => 'El monto es requerido',
            'amount.min' => 'El monto debe ser mayor a 0',
            'description.required' => 'La descripción es requerida',
            'flow_date.required' => 'La fecha es requerida',
        ];
    }
}
