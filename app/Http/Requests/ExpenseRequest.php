<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'category' => ['required', 'string', 'in:compras,gastos_operativos,gastos_admin,mantenimiento,marketing,otros'],
            'description' => ['required', 'string', 'min:3', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto debe ser mayor a 0',
            'amount.max' => 'El monto es demasiado grande',
            'category.required' => 'La categoría es requerida',
            'category.in' => 'La categoría seleccionada no es válida',
            'description.required' => 'La descripción es requerida',
            'description.min' => 'La descripción debe tener al menos 3 caracteres',
            'expense_date.required' => 'La fecha es requerida',
            'expense_date.date' => 'La fecha no es válida',
            'expense_date.before_or_equal' => 'La fecha no puede ser futura',
            'supplier_id.exists' => 'El proveedor seleccionado no existe',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'amount' => 'monto',
            'category' => 'categoría',
            'description' => 'descripción',
            'notes' => 'notas',
            'expense_date' => 'fecha del gasto',
            'supplier_id' => 'proveedor',
        ];
    }
}
